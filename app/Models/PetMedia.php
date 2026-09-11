<?php

namespace App\Models;

use App\Services\VideoEmbedService;
use Illuminate\Database\Eloquent\Model;

class PetMedia extends Model
{
    protected $table = 'pet_media';

    protected $fillable = [
        'pet_id',
        'path',
        'type', // 'image' ou 'video'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Verifica se a mídia é um vídeo embutido/link externo.
     */
    public function getIsEmbedAttribute(): bool
    {
        return $this->type === 'video' || (bool) filter_var($this->path, FILTER_VALIDATE_URL);
    }

    /**
     * Retorna os dados analisados do embed (YouTube, Instagram, TikTok).
     */
    public function getEmbedDataAttribute(): ?array
    {
        if (!$this->is_embed) {
            return null;
        }

        return app(VideoEmbedService::class)->parse($this->path);
    }

    /**
     * Retorna a URL pronta para uso em <iframe>.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $data = $this->embed_data;
        return $data['embed_url'] ?? ($this->is_embed ? $this->path : null);
    }

    /**
     * Retorna a URL da miniatura/imagem representativa.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->type === 'image') {
            return asset('storage/' . $this->path);
        }

        $data = $this->embed_data;
        if (!empty($data['thumbnail_url'])) {
            return $data['thumbnail_url'];
        }

        // Fallback genérico para vídeos sem thumbnail pública direta
        return asset('images/video-placeholder.png');
    }
}
