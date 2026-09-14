<?php

namespace App\Http\Controllers;

use App\Models\PetMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve a imagem de mídia protegida por rota assinada.
     *
     * @param Request $request
     * @param PetMedia $media
     * @return StreamedResponse|RedirectResponse
     */
    public function serve(Request $request, PetMedia $media)
    {
        // Se for um link de vídeo externo, redireciona para a URL original
        if ($media->type === 'video' || (bool) filter_var($media->path, FILTER_VALIDATE_URL)) {
            return redirect()->away($media->path);
        }

        // Verifica existência do arquivo no disco privado local
        if (!Storage::disk('local')->exists($media->path)) {
            abort(404, 'Imagem não encontrada.');
        }

        // Retorna o arquivo com os headers de imagem adequados
        return Storage::disk('local')->response($media->path);
    }
}
