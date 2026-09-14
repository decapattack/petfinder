<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetLostNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlertWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pet_with_custom_coordinates(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.55052000,
            'longitude' => -46.63330800,
        ]);

        $response = $this->actingAs($user)->post('/pets', [
            'nome' => 'Thor',
            'especie' => 'Cachorro',
            'raca' => 'Golden Retriever',
            'cor' => 'Dourado',
            'latitude' => -23.56781234,
            'longitude' => -46.65438765,
            'media' => [\Illuminate\Http\UploadedFile::fake()->image('thor.jpg')],
        ]);

        $response->assertRedirect('/dashboard');

        $pet = Pet::where('nome', 'Thor')->first();
        $this->assertNotNull($pet);
        $this->assertEquals(-23.56781234, (float) $pet->latitude);
        $this->assertEquals(-46.65438765, (float) $pet->longitude);
    }

    public function test_user_creates_pet_inheriting_user_coordinates_when_not_provided(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.55052000,
            'longitude' => -46.63330800,
        ]);

        $response = $this->actingAs($user)->post('/pets', [
            'nome' => 'Luna',
            'especie' => 'Gato',
            'raca' => 'Persa',
            'cor' => 'Branco',
            'media' => [\Illuminate\Http\UploadedFile::fake()->image('luna.jpg')],
        ]);

        $response->assertRedirect('/dashboard');

        $pet = Pet::where('nome', 'Luna')->first();
        $this->assertNotNull($pet);
        $this->assertEquals(-23.55052000, (float) $pet->latitude);
        $this->assertEquals(-46.63330800, (float) $pet->longitude);
    }

    public function test_user_can_update_pet_coordinates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Max',
            'especie' => 'Cachorro',
            'raca' => 'Labrador',
            'cor' => 'Preto',
            'latitude' => -23.55000000,
            'longitude' => -46.63000000,
        ]);

        $response = $this->actingAs($user)->put("/pets/{$pet->id}", [
            'nome' => 'Max',
            'especie' => 'Cachorro',
            'raca' => 'Labrador',
            'cor' => 'Preto',
            'latitude' => -23.58912345,
            'longitude' => -46.68123456,
        ]);

        $response->assertRedirect("/pets/{$pet->id}/edit");
        $pet->refresh();
        $this->assertEquals(-23.58912345, (float) $pet->latitude);
        $this->assertEquals(-46.68123456, (float) $pet->longitude);
    }

    public function test_emit_alert_origin_casa_uses_pet_coordinates_with_full_precision(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.50000000,
            'longitude' => -46.60000000,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'Pastor Alemão',
            'cor' => 'Capa Preta',
            'latitude' => -23.56789123,
            'longitude' => -46.65432198,
            'status' => 'seguro',
        ]);

        $response = $this->actingAs($user)->post('/alerts', [
            'pet_id' => $pet->id,
            'origem' => 'casa',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $pet->refresh();
        $this->assertSame('desaparecido', $pet->status);

        $alert = Alert::where('pet_id', $pet->id)->latest()->first();
        $this->assertNotNull($alert);
        $this->assertSame('ativo', $alert->status);
        $this->assertEquals(-23.56789123, (float) $alert->latitude_fuga);
        $this->assertEquals(-46.65432198, (float) $alert->longitude_fuga);
    }

    public function test_emit_alert_origin_rua_uses_real_time_gps_coordinates(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.50000000,
            'longitude' => -46.60000000,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bob',
            'especie' => 'Cachorro',
            'raca' => 'Beagle',
            'cor' => 'Tricolor',
            'latitude' => -23.51000000,
            'longitude' => -46.61000000,
            'status' => 'seguro',
        ]);

        $gpsLat = -23.59123456;
        $gpsLng = -46.69123456;

        $response = $this->actingAs($user)->post('/alerts', [
            'pet_id' => $pet->id,
            'origem' => 'rua',
            'latitude' => $gpsLat,
            'longitude' => $gpsLng,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $pet->refresh();
        $this->assertSame('desaparecido', $pet->status);

        $alert = Alert::where('pet_id', $pet->id)->latest()->first();
        $this->assertNotNull($alert);
        $this->assertEquals($gpsLat, (float) $alert->latitude_fuga);
        $this->assertEquals($gpsLng, (float) $alert->longitude_fuga);
    }

    public function test_cannot_emit_duplicate_alert_for_pet_already_desaparecido(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Mel',
            'especie' => 'Gato',
            'raca' => 'SRD',
            'cor' => 'Amarela',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
            'status' => 'desaparecido',
        ]);

        $response = $this->actingAs($user)->post('/alerts', [
            'pet_id' => $pet->id,
            'origem' => 'casa',
        ]);

        $response->assertSessionHas('error', 'Este pet já possui um alerta ativo.');
    }

    public function test_public_page_and_mobile_map_render_successfully(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.55052123,
            'longitude' => -46.63330876,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pipoca',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
            'latitude' => -23.55052123,
            'longitude' => -46.63330876,
            'is_public' => true,
            'status' => 'desaparecido',
        ]);

        Alert::create([
            'pet_id' => $pet->id,
            'latitude_fuga' => -23.55052123,
            'longitude_fuga' => -46.63330876,
            'status' => 'ativo',
        ]);

        // Página Pública (desktop & mobile links)
        $publicResponse = $this->get("/pet/{$pet->uuid}");
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Pipoca');
        $publicResponse->assertSee('Exibir no Mapa');
        $publicResponse->assertSee('map-desktop');

        // Página Mobile do Mapa
        $mapResponse = $this->get("/pet/{$pet->uuid}/map");
        $mapResponse->assertStatus(200);
        $mapResponse->assertSee('Mapa de Busca: Pipoca');
        $mapResponse->assertSee('map-full');
    }
}
