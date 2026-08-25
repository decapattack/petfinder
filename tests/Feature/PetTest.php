<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pet_with_multiple_media(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $imageFile = UploadedFile::fake()->image('dog.jpg');
        $videoFile = UploadedFile::fake()->create('dog_video.mp4', 1024, 'video/mp4');

        $response = $this
            ->actingAs($user)
            ->post('/pets', [
                'nome' => 'Bidu',
                'especie' => 'Cachorro',
                'raca' => 'Poodle',
                'cor' => 'Branco',
                'media' => [
                    $imageFile,
                    $videoFile,
                ]
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/dashboard');

        // Check if pet was created
        $pet = Pet::first();
        $this->assertNotNull($pet);
        $this->assertSame('Bidu', $pet->nome);
        $this->assertSame($user->id, $pet->user_id);

        // Check if pet media items were saved
        $this->assertCount(2, $pet->media);
        
        $firstMedia = $pet->media->first();
        $this->assertSame('image', $firstMedia->type);
        Storage::disk('public')->assertExists($firstMedia->path);

        $secondMedia = $pet->media->last();
        $this->assertSame('video', $secondMedia->type);
        Storage::disk('public')->assertExists($secondMedia->path);

        // Test cover photo attribute
        $this->assertSame($firstMedia->path, $pet->cover_photo->path);
    }

    public function test_user_can_delete_pet_and_its_files_are_removed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $imagePath = 'pets/test_image.jpg';
        $videoPath = 'pets/test_video.mp4';

        Storage::disk('public')->put($imagePath, 'dummy content');
        Storage::disk('public')->put($videoPath, 'dummy content');

        $pet->media()->create(['path' => $imagePath, 'type' => 'image']);
        $pet->media()->create(['path' => $videoPath, 'type' => 'video']);

        // Assert files exist initially
        Storage::disk('public')->assertExists($imagePath);
        Storage::disk('public')->assertExists($videoPath);

        $response = $this
            ->actingAs($user)
            ->delete("/pets/{$pet->id}");

        $response->assertRedirect();
        
        // Assert pet and its media database records are deleted
        $this->assertNull(Pet::find($pet->id));
        $this->assertCount(0, \DB::table('pet_media')->where('pet_id', $pet->id)->get());

        // Assert files are physically deleted from storage
        Storage::disk('public')->assertMissing($imagePath);
        Storage::disk('public')->assertMissing($videoPath);
    }

    public function test_user_can_access_edit_page_of_their_pet(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $response = $this->actingAs($user)->get("/pets/{$pet->id}/edit");
        $response->assertOk();
        $response->assertViewIs('pets.edit');
    }

    public function test_user_cannot_access_edit_page_of_other_user_pet(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $otherUser->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $response = $this->actingAs($user)->get("/pets/{$pet->id}/edit");
        $response->assertStatus(403);
    }

    public function test_user_can_update_their_pet_details(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $response = $this->actingAs($user)->put("/pets/{$pet->id}", [
            'nome' => 'Bidu Editado',
            'especie' => 'Gato',
            'raca' => 'Persa',
            'cor' => 'Preto',
            'condicoes_especiais' => 'Alergia a ração comum',
        ]);

        $response->assertRedirect("/pets/{$pet->id}/edit");
        $response->assertSessionHasNoErrors();

        $pet->refresh();
        $this->assertSame('Bidu Editado', $pet->nome);
        $this->assertSame('Gato', $pet->especie);
        $this->assertSame('Alergia a ração comum', $pet->condicoes_especiais);
    }

    public function test_user_can_add_media_to_existing_pet(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $newImage = UploadedFile::fake()->image('new_dog.png');

        $response = $this->actingAs($user)->post("/pets/{$pet->id}/media", [
            'media' => [$newImage]
        ]);

        $response->assertRedirect();
        $pet->refresh();

        $this->assertCount(1, $pet->media);
        Storage::disk('public')->assertExists($pet->media->first()->path);
    }

    public function test_user_can_delete_specific_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'nome' => 'Bidu',
            'especie' => 'Cachorro',
            'raca' => 'Poodle',
            'cor' => 'Branco',
        ]);

        $imagePath = 'pets/test_image.jpg';
        Storage::disk('public')->put($imagePath, 'dummy content');
        $media = $pet->media()->create(['path' => $imagePath, 'type' => 'image']);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($user)->delete("/pets/{$pet->id}/media/{$media->id}");
        $response->assertRedirect();

        $this->assertCount(0, $pet->media()->get());
        Storage::disk('public')->assertMissing($imagePath);
    }
}
