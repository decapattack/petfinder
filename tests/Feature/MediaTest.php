<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\PetMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->pet = Pet::create([
            'user_id' => $this->user->id,
            'nome' => 'Bob',
            'especie' => 'Cachorro',
            'raca' => 'Vira-lata',
            'cor' => 'Caramelo',
        ]);
    }

    public function test_media_returns_temporary_signed_url_for_images(): void
    {
        $media = $this->pet->media()->create([
            'path' => 'pets/test_image.jpg',
            'type' => 'image',
        ]);

        $this->assertNotEmpty($media->url);
        $this->assertStringContainsString('/media/' . $media->id, $media->url);
        $this->assertStringContainsString('signature=', $media->url);
        $this->assertStringContainsString('expires=', $media->url);

        // Thumbnail URL should also use signed url
        $this->assertSame($media->url, $media->thumbnail_url);
    }

    public function test_valid_signed_url_serves_private_image(): void
    {
        Storage::fake('local');
        $filePath = 'pets/sample_pet.jpg';
        Storage::disk('local')->put($filePath, 'fake-image-bytes');

        $media = $this->pet->media()->create([
            'path' => $filePath,
            'type' => 'image',
        ]);

        $signedUrl = $media->url;

        $response = $this->get($signedUrl);
        $response->assertOk();
    }

    public function test_unsigned_request_to_media_returns_forbidden(): void
    {
        Storage::fake('local');
        $filePath = 'pets/sample_pet.jpg';
        Storage::disk('local')->put($filePath, 'fake-image-bytes');

        $media = $this->pet->media()->create([
            'path' => $filePath,
            'type' => 'image',
        ]);

        // Direct request without signature
        $response = $this->get("/media/{$media->id}");
        $response->assertStatus(403);
    }

    public function test_tampered_signature_returns_forbidden(): void
    {
        Storage::fake('local');
        $filePath = 'pets/sample_pet.jpg';
        Storage::disk('local')->put($filePath, 'fake-image-bytes');

        $media = $this->pet->media()->create([
            'path' => $filePath,
            'type' => 'image',
        ]);

        $validSignedUrl = $media->url;
        // Tamper with signature parameter
        $tamperedUrl = preg_replace('/signature=[a-f0-9]+/', 'signature=invalidhash123', $validSignedUrl);

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    public function test_expired_signed_url_returns_forbidden(): void
    {
        Storage::fake('local');
        $filePath = 'pets/sample_pet.jpg';
        Storage::disk('local')->put($filePath, 'fake-image-bytes');

        $media = $this->pet->media()->create([
            'path' => $filePath,
            'type' => 'image',
        ]);

        // Generate expired signed URL (expired 10 minutes ago)
        $expiredUrl = URL::temporarySignedRoute(
            'media.serve',
            now()->subMinutes(10),
            ['media' => $media->id]
        );

        $response = $this->get($expiredUrl);
        $response->assertStatus(403);
    }

    public function test_nonexistent_file_returns_404_with_valid_signature(): void
    {
        Storage::fake('local');

        $media = $this->pet->media()->create([
            'path' => 'pets/non_existent.jpg',
            'type' => 'image',
        ]);

        $response = $this->get($media->url);
        $response->assertStatus(404);
    }

    public function test_video_media_redirects_to_external_url(): void
    {
        $videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        $media = $this->pet->media()->create([
            'path' => $videoUrl,
            'type' => 'video',
        ]);

        $this->assertSame($videoUrl, $media->url);

        $signedUrl = URL::temporarySignedRoute(
            'media.serve',
            now()->addMinutes(30),
            ['media' => $media->id]
        );

        $response = $this->get($signedUrl);
        $response->assertRedirect($videoUrl);
    }

    public function test_direct_public_storage_path_returns_404(): void
    {
        // Direct attempt to access public path /storage/pets/something.jpg should 404
        $response = $this->get('/storage/pets/19deFTm6xauQesB3M5M4dONKNvD5mT63z5TwwryH.png');
        $response->assertNotFound();
    }
}
