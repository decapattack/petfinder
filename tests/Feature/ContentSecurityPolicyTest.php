<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;
    public function test_response_contains_content_security_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_csp_header_restricts_frame_src_to_trusted_domains(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'Content-Security-Policy header is missing.');
        $this->assertStringContainsString('frame-src', $csp);
        $this->assertStringContainsString("'self'", $csp);
        $this->assertStringContainsString('https://www.youtube.com', $csp);
        $this->assertStringContainsString('https://www.youtube-nocookie.com', $csp);
        $this->assertStringContainsString('https://www.instagram.com', $csp);
        $this->assertStringContainsString('https://www.tiktok.com', $csp);
    }

    public function test_csp_header_does_not_allow_untrusted_frame_origins(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('http://malicious-site.com', $csp);
        $this->assertStringNotContainsString('https://evil.com', $csp);
    }
}
