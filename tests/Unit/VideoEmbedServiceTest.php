<?php

namespace Tests\Unit;

use App\Services\VideoEmbedService;
use Tests\TestCase;

class VideoEmbedServiceTest extends TestCase
{
    private VideoEmbedService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VideoEmbedService();
    }

    public function test_parses_standard_youtube_url(): void
    {
        $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $result = $this->service->parse($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['platform']);
        $this->assertSame('dQw4w9WgXcQ', $result['id']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $result['embed_url']);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $result['thumbnail_url']);
    }

    public function test_parses_short_youtube_url(): void
    {
        $url = 'https://youtu.be/dQw4w9WgXcQ';
        $result = $this->service->parse($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['platform']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $result['embed_url']);
    }

    public function test_parses_youtube_shorts_url(): void
    {
        $url = 'https://www.youtube.com/shorts/dQw4w9WgXcQ';
        $result = $this->service->parse($url);

        $this->assertNotNull($result);
        $this->assertSame('youtube', $result['platform']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $result['embed_url']);
    }

    public function test_parses_instagram_post_or_reel(): void
    {
        $url = 'https://www.instagram.com/reel/C3xABCD123_/';
        $result = $this->service->parse($url);

        $this->assertNotNull($result);
        $this->assertSame('instagram', $result['platform']);
        $this->assertSame('https://www.instagram.com/p/C3xABCD123_/embed', $result['embed_url']);
    }

    public function test_parses_tiktok_video_url(): void
    {
        $url = 'https://www.tiktok.com/@user/video/7123456789012345678';
        $result = $this->service->parse($url);

        $this->assertNotNull($result);
        $this->assertSame('tiktok', $result['platform']);
        $this->assertSame('https://www.tiktok.com/embed/v2/7123456789012345678', $result['embed_url']);
    }

    public function test_returns_null_for_invalid_url(): void
    {
        $this->assertNull($this->service->parse('not-a-url'));
        $this->assertNull($this->service->parse('https://example.com/unsupported'));
    }
}
