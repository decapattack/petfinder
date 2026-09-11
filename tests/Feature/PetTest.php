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

    public function test_create_pet_screen_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/pets/create');

        $response->assertStatus(200);
        $response->assertSee('Cadastrar Novo Pet');
    }

    public function test_user_can_create_pet_with_multiple_media_and_video_url(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $imageFile = UploadedFile::fake()->image('dog.png', 800, 600);
        $videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        $response = $this
            ->actingAs($user)
            ->post('/pets', [
                'nome' => 'Bidu',
                'especie' => 'Cachorro',
                'raca' => 'Poodle',
                'cor' => 'Branco',
                'media' => [
                    $imageFile,
                ],
                'video_url' => $videoUrl,
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
        
        $imageMedia = $pet->media->where('type', 'image')->first();
        $this->assertNotNull($imageMedia);
        $this->assertStringEndsWith('.jpg', $imageMedia->path);
        Storage::disk('public')->assertExists($imageMedia->path);

        $videoMedia = $pet->media->where('type', 'video')->first();
        $this->assertNotNull($videoMedia);
        $this->assertSame($videoUrl, $videoMedia->path);
        $this->assertTrue($videoMedia->is_embed);
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $videoMedia->embed_url);

        // Test cover photo attribute
        $this->assertSame($imageMedia->path, $pet->cover_photo->path);
    }

    public function test_user_cannot_upload_raw_video_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $videoFile = UploadedFile::fake()->create('dog_video.mp4', 1024, 'video/mp4');

        $response = $this
            ->actingAs($user)
            ->post('/pets', [
                'nome' => 'Bidu',
                'especie' => 'Cachorro',
                'raca' => 'Poodle',
                'cor' => 'Branco',
                'media' => [
                    $videoFile,
                ]
            ]);

        $response->assertSessionHasErrors(['media.0']);
        $this->assertNull(Pet::first());
    }

    public function test_image_is_resized_proportionally_to_maximum_dimensions_and_stored_as_jpg(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Criar imagem grande de 3000 x 2000 (aspect ratio 1.5)
        $largeImage = UploadedFile::fake()->image('large.png', 3000, 2000);

        $response = $this
            ->actingAs($user)
            ->post('/pets', [
                'nome' => 'Max',
                'especie' => 'Cachorro',
                'raca' => 'Pastor Alemão',
                'cor' => 'Capa Preta',
                'media' => [$largeImage],
            ]);

        $response->assertSessionHasNoErrors();

        $pet = Pet::first();
        $media = $pet->media->first();

        // O arquivo deve ter sido salvo como JPG
        $this->assertStringEndsWith('.jpg', $media->path);
        Storage::disk('public')->assertExists($media->path);

        // Obter dimensões do arquivo processado
        $storedContent = Storage::disk('public')->get($media->path);
        $imageResource = imagecreatefromstring($storedContent);
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        imagedestroy($imageResource);

        // Não pode ultrapassar 1920 de largura nem 1080 de altura
        $this->assertLessThanOrEqual(1920, $width);
        $this->assertLessThanOrEqual(1080, $height);

        // No aspect ratio 3000x2000, o limite restritivo é a altura (1080):
        // 2000 * (1080/2000) = 1080
        // 3000 * (1080/2000) = 1620
        $this->assertSame(1620, $width);
        $this->assertSame(1080, $height);
    }

    public function test_small_image_is_not_enlarged_but_converted_to_jpg(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $smallImage = UploadedFile::fake()->image('small.png', 500, 400);

        $this->actingAs($user)->post('/pets', [
            'nome' => 'Nina',
            'especie' => 'Gato',
            'raca' => 'Siamês',
            'cor' => 'Branco',
            'media' => [$smallImage],
        ]);

        $pet = Pet::first();
        $media = $pet->media->first();

        $storedContent = Storage::disk('public')->get($media->path);
        $imageResource = imagecreatefromstring($storedContent);
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        imagedestroy($imageResource);

        // Mantém as dimensões originais sem ampliação
        $this->assertSame(500, $width);
        $this->assertSame(400, $height);
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
        $videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        Storage::disk('public')->put($imagePath, 'dummy content');

        $pet->media()->create(['path' => $imagePath, 'type' => 'image']);
        $pet->media()->create(['path' => $videoUrl, 'type' => 'video']);

        Storage::disk('public')->assertExists($imagePath);

        $response = $this
            ->actingAs($user)
            ->delete("/pets/{$pet->id}");

        $response->assertRedirect();
        
        // Assert pet and its media database records are deleted
        $this->assertNull(Pet::find($pet->id));
        $this->assertCount(0, \DB::table('pet_media')->where('pet_id', $pet->id)->get());

        // Assert image file is physically deleted from storage
        Storage::disk('public')->assertMissing($imagePath);
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
        $this->assertStringEndsWith('.jpg', $pet->media->first()->path);
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
