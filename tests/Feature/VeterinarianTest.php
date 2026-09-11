<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeterinarianTest extends TestCase
{
    use RefreshDatabase;

    private function createPet(User $user, array $overrides = []): Pet
    {
        return Pet::create(array_merge([
            'user_id' => $user->id,
            'nome' => 'Rex',
            'especie' => 'Cachorro',
            'raca' => 'Vira-lata',
            'cor' => 'Caramelo',
            'status' => 'seguro',
        ], $overrides));
    }

    public function test_user_can_create_and_link_veterinarian_to_pet(): void
    {
        $user = User::factory()->create();
        $pet = $this->createPet($user);

        $response = $this->actingAs($user)->patch(route('pets.vet.update', $pet), [
            'nome' => 'Dr. Ana Paula',
            'crv' => 'CRV-SP 98765',
            'telefone' => '(11) 98888-7777',
            'email' => 'ana@clinica.com',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
        ]);

        $response->assertRedirect(route('pets.health', $pet));

        $this->assertDatabaseHas('veterinarians', [
            'nome' => 'Dr. Ana Paula',
            'crv' => 'CRV-SP 98765',
            'telefone' => '(11) 98888-7777',
            'email' => 'ana@clinica.com',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
        ]);

        $vet = Veterinarian::where('nome', 'Dr. Ana Paula')->first();
        $this->assertEquals($vet->id, $pet->fresh()->veterinarian_id);
    }

    public function test_nome_and_telefone_are_required_for_new_veterinarian(): void
    {
        $user = User::factory()->create();
        $pet = $this->createPet($user);

        $response = $this->actingAs($user)->patch(route('pets.vet.update', $pet), [
            'email' => 'teste@clinica.com',
        ]);

        $response->assertSessionHasErrors(['nome', 'telefone']);
    }

    public function test_user_can_link_existing_veterinarian(): void
    {
        $user = User::factory()->create();
        $pet = $this->createPet($user);
        $vet = Veterinarian::create([
            'nome' => 'Dr. Roberto',
            'telefone' => '(11) 91111-2222',
        ]);

        $response = $this->actingAs($user)->patch(route('pets.vet.update', $pet), [
            'veterinarian_id' => $vet->id,
        ]);

        $response->assertRedirect(route('pets.health', $pet));
        $this->assertEquals($vet->id, $pet->fresh()->veterinarian_id);
    }

    public function test_user_can_unlink_veterinarian_from_pet(): void
    {
        $user = User::factory()->create();
        $vet = Veterinarian::create([
            'nome' => 'Dr. Roberto',
            'telefone' => '(11) 91111-2222',
        ]);
        $pet = $this->createPet($user, ['veterinarian_id' => $vet->id]);

        $response = $this->actingAs($user)->patch(route('pets.vet.update', $pet), [
            'remove_vet' => '1',
        ]);

        $response->assertRedirect(route('pets.health', $pet));
        $this->assertNull($pet->fresh()->veterinarian_id);
    }
}
