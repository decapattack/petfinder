<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetLostNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notification_routes(): void
    {
        $response = $this->get('/notifications/some-id/read');
        $response->assertRedirect('/login');

        $response = $this->post('/notifications/some-id/read');
        $response->assertRedirect('/login');

        $response = $this->post('/notifications/read-all');
        $response->assertRedirect('/login');
    }

    public function test_user_can_read_and_redirect_to_pet_page(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $neighbor = User::factory()->create(['email_verified_at' => now()]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'status' => 'desaparecido',
            'is_public' => true,
        ]);

        $neighbor->notify(new PetLostNotification($pet));

        $this->assertEquals(1, $neighbor->unreadNotifications()->count());

        $notification = $neighbor->unreadNotifications()->first();

        $response = $this->actingAs($neighbor)->get(route('notifications.read', $notification->id));

        $response->assertRedirect(route('pets.public', $pet->uuid));

        $this->assertEquals(0, $neighbor->fresh()->unreadNotifications()->count());
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $pet1 = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pet 1',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Preto',
            'is_public' => true,
        ]);

        $pet2 = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pet 2',
            'especie' => 'Gato',
            'raca' => 'Siamês',
            'cor' => 'Branco',
            'is_public' => true,
        ]);

        $user->notify(new PetLostNotification($pet1));
        $user->notify(new PetLostNotification($pet2));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->post(route('notifications.readAll'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Todas as notificações foram marcadas como lidas.');

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_individual_notification_as_read_via_post(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Thor',
            'especie' => 'Cachorro',
            'raca' => 'Pastor',
            'cor' => 'Capa Preta',
            'is_public' => true,
        ]);

        $user->notify(new PetLostNotification($pet));

        $notification = $user->unreadNotifications()->first();

        $response = $this->actingAs($user)->postJson(route('notifications.markAsRead', $notification->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $userA = User::factory()->create(['email_verified_at' => now()]);
        $userB = User::factory()->create(['email_verified_at' => now()]);

        $pet = Pet::create([
            'user_id' => $userA->id,
            'nome' => 'Bob',
            'especie' => 'Cachorro',
            'raca' => 'Beagle',
            'cor' => 'Tricolor',
            'is_public' => true,
        ]);

        $userA->notify(new PetLostNotification($pet));
        $notification = $userA->unreadNotifications()->first();

        // User B tries to mark User A's notification
        $response = $this->actingAs($userB)->get(route('notifications.read', $notification->id));
        $response->assertStatus(404);

        $responsePost = $this->actingAs($userB)->post(route('notifications.markAsRead', $notification->id));
        $responsePost->assertStatus(404);

        // Notification of userA is still unread
        $this->assertEquals(1, $userA->fresh()->unreadNotifications()->count());
    }
}
