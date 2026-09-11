<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityMediumPriorityTest extends TestCase
{
    use RefreshDatabase;

    // ─── Item 5: Alert Ownership & Policy ─────────────────────────────────────

    public function test_user_cannot_create_alert_for_pet_they_do_not_own(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'latitude' => -23.55, 'longitude' => -46.63]);
        $attacker = User::factory()->create(['email_verified_at' => now(), 'latitude' => -23.55, 'longitude' => -46.63]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
        ]);

        $response = $this->actingAs($attacker)->post('/alerts', [
            'pet_id' => $pet->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('alerts', ['pet_id' => $pet->id]);
    }

    public function test_user_cannot_resolve_alert_belonging_to_another_user(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'latitude' => -23.55, 'longitude' => -46.63]);
        $attacker = User::factory()->create(['email_verified_at' => now(), 'latitude' => -23.55, 'longitude' => -46.63]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'status' => 'desaparecido',
        ]);

        $alert = Alert::create([
            'pet_id' => $pet->id,
            'latitude_fuga' => $owner->latitude,
            'longitude_fuga' => $owner->longitude,
            'status' => 'ativo',
        ]);

        $response = $this->actingAs($attacker)->post("/alerts/{$alert->id}/resolve");

        $response->assertStatus(403);
        $this->assertSame('ativo', $alert->fresh()->status);
    }

    public function test_owner_can_resolve_their_own_alert(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now(), 'latitude' => -23.55, 'longitude' => -46.63]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'status' => 'desaparecido',
        ]);

        $alert = Alert::create([
            'pet_id' => $pet->id,
            'latitude_fuga' => $owner->latitude,
            'longitude_fuga' => $owner->longitude,
            'status' => 'ativo',
        ]);

        $response = $this->actingAs($owner)->post("/alerts/{$alert->id}/resolve");

        $response->assertRedirect(route('dashboard'));
        $this->assertSame('resolvido', $alert->fresh()->status);
        $this->assertSame('seguro', $pet->fresh()->status);
    }

    // ─── Item 6: HealthRecord Policy & Cross-Pet Ownership ─────────────────────

    public function test_user_cannot_view_private_health_record_of_another_user(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $fakePath = 'private/health_records/' . $pet->uuid . '/exam.pdf';
        Storage::disk('local')->put($fakePath, '%PDF-fake-content');

        $record = HealthRecord::create([
            'pet_id' => $pet->id,
            'title' => 'Exame de Sangue',
            'category' => 'exame',
            'file_path' => $fakePath,
            'file_extension' => 'pdf',
            'is_public' => false,
            'record_date' => '2026-09-01',
        ]);

        // Outro usuário tenta acessar arquivo privado
        $response = $this->actingAs($otherUser)->get("/pets/{$pet->id}/records/{$record->id}/view");

        $response->assertStatus(403);
    }

    public function test_user_can_view_public_health_record(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $pet = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $fakePath = 'private/health_records/' . $pet->uuid . '/vaccine.pdf';
        Storage::disk('local')->put($fakePath, '%PDF-fake-content');

        $record = HealthRecord::create([
            'pet_id' => $pet->id,
            'title' => 'Carteira de Vacinação',
            'category' => 'vacina',
            'file_path' => $fakePath,
            'file_extension' => 'pdf',
            'is_public' => true,
            'record_date' => '2026-09-01',
        ]);

        $response = $this->actingAs($otherUser)->get("/pets/{$pet->id}/records/{$record->id}/view");

        $response->assertStatus(200);
    }

    public function test_cross_pet_tampering_is_rejected(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['email_verified_at' => now()]);

        $pet1 = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Pet 1',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Preto',
        ]);

        $pet2 = Pet::create([
            'user_id' => $owner->id,
            'nome' => 'Pet 2',
            'especie' => 'Gato',
            'raca' => 'SRD',
            'cor' => 'Branco',
        ]);

        $fakePath = 'private/health_records/' . $pet1->uuid . '/exam.pdf';
        Storage::disk('local')->put($fakePath, '%PDF-fake-content');

        // Registro pertence ao Pet 1
        $record = HealthRecord::create([
            'pet_id' => $pet1->id,
            'title' => 'Exame Pet 1',
            'category' => 'exame',
            'file_path' => $fakePath,
            'file_extension' => 'pdf',
            'is_public' => true,
            'record_date' => '2026-09-01',
        ]);

        // Tentativa de acessar o registro passando Pet 2 na URL
        $response = $this->actingAs($owner)->get("/pets/{$pet2->id}/records/{$record->id}/view");

        $response->assertStatus(403);
    }

    // ─── Item 4: OAuth Security ──────────────────────────────────────────────

    public function test_oauth_rejects_unwhitelisted_provider(): void
    {
        $response = $this->get('/auth/facebook/redirect');
        $response->assertStatus(404);

        $responseCallback = $this->get('/auth/facebook/callback');
        $responseCallback->assertStatus(404);
    }

    public function test_oauth_callback_handles_user_cancellation(): void
    {
        $response = $this->get('/auth/google/callback?error=access_denied&error_description=User+denied+access');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }
}
