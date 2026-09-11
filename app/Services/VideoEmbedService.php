<?php

namespace App\Services;

class VideoEmbedService
{
    /**
     * Identifica a plataforma e retorna os dados de incorporação da URL informada.
     *
     * @param string $url
     * @return array|null [platform, embed_url, thumbnail_url, original_url] ou null se inválido
     */
    public function parse(string $url): ?array
    {
        $url = trim($url);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // 1. YouTube (normal, youtu.be, shorts, embed)
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/i', $url, $matches)) {
            $videoId = $matches[1];
            return [
                'platform' => 'youtube',
                'id' => $videoId,
                'original_url' => $url,
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$videoId}",
                'thumbnail_url' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
            ];
        }

        // 2. Instagram (Reels ou Posts)
        if (preg_match('/instagram\.com\/(?:p|reel|reels)\/([a-zA-Z0-9_-]+)/i', $url, $matches)) {
            $postCode = $matches[1];
            return [
                'platform' => 'instagram',
                'id' => $postCode,
                'original_url' => $url,
                'embed_url' => "https://www.instagram.com/p/{$postCode}/embed",
                'thumbnail_url' => null,
            ];
        }

        // 3. TikTok (vídeos padrão ou compartilhamento curto)
        if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/i', $url, $matches)) {
            $videoId = $matches[1];
            return [
                'platform' => 'tiktok',
                'id' => $videoId,
                'original_url' => $url,
                'embed_url' => "https://www.tiktok.com/embed/v2/{$videoId}",
                'thumbnail_url' => null,
            ];
        }

        return null;
    }

    /**
     * Verifica se uma URL é suportada para incorporação.
     */
    public function isSupported(string $url): bool
    {
        return $this->parse($url) !== null;
    }
}
