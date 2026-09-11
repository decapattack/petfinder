<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlertOriginAndCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pet_can_be_created_with_full_coordinates(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.55052000,
            'longitude' => -46.63330800,
        ]);

        $imageFile = UploadedFile::fake()->image('pet.jpg', 600, 600);

        $response = $this->actingAs($user)->post('/pets', [
            'nome' => 'Thor',
            'especie' => 'Cachorro',
            'raca' => 'Labrador',
            'cor' => 'Dourado',
            'latitude' => -23.56123456,
            'longitude' => -46.65432109,
            'media' => [$imageFile],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/dashboard');

        $pet = Pet::where('nome', 'Thor')->first();
        $this->assertNotNull($pet);
        $this->assertEquals(-23.56123456, (float) $pet->latitude);
        $this->assertEquals(-46.65432109, (float) $pet->longitude);
    }

    public function test_alert_store_with_casa_origin_uses_pet_coordinates(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.50000000,
            'longitude' => -46.60000000,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pipoca',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'status' => 'seguro',
            'latitude' => -23.58765432,
            'longitude' => -46.68765432,
        ]);

        $response = $this->actingAs($user)->post('/alerts', [
            'pet_id' => $pet->id,
            'origem' => 'casa',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $alert = Alert::where('pet_id', $pet->id)->first();
        $this->assertNotNull($alert);
        $this->assertEquals(-23.58765432, (float) $alert->latitude_fuga);
        $this->assertEquals(-46.68765432, (float) $alert->longitude_fuga);
        $this->assertSame('desaparecido', $pet->fresh()->status);
    }

    public function test_alert_store_with_rua_origin_uses_browser_gps_coordinates(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'latitude' => -23.50000000,
            'longitude' => -46.60000000,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pipoca',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'status' => 'seguro',
            'latitude' => -23.58765432,
            'longitude' => -46.68765432,
        ]);

        $streetLat = -23.71234567;
        $streetLng = -46.77654321;

        $response = $this->actingAs($user)->post('/alerts', [
            'pet_id' => $pet->id,
            'origem' => 'rua',
            'latitude' => $streetLat,
            'longitude' => $streetLng,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $alert = Alert::where('pet_id', $pet->id)->first();
        $this->assertNotNull($alert);
        $this->assertEquals($streetLat, (float) $alert->latitude_fuga);
        $this->assertEquals($streetLng, (float) $alert->longitude_fuga);
    }

    public function test_public_pet_page_renders_map_and_frontend_rounding_code(): void
    {
        $user = User::factory()->create([
            'latitude' => -23.55050000,
            'longitude' => -46.63330000,
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Pipoca',
            'especie' => 'Cachorro',
            'raca' => 'SRD',
            'cor' => 'Caramelo',
            'is_public' => true,
            'status' => 'desaparecido',
            'latitude' => -23.55051234,
            'longitude' => -46.63335678,
        ]);

        Alert::create([
            'pet_id' => $pet->id,
            'latitude_fuga' => -23.55051234,
            'longitude_fuga' => -46.63335678,
            'status' => 'ativo',
        ]);

        $response = $this->get(route('pets.public', $pet->uuid));

        $response->assertStatus(200);
        $response->assertSee('Região do Desaparecimento');
        $response->assertSee('Exibir no Mapa');
        $response->assertSee('toFixed(3)');
    }
}
