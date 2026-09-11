<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetMedia;
use App\Services\ImageService;
use App\Services\VideoEmbedService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PetController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $pets = Auth::user()->pets;
        return view('pets.index', compact('pets'));
    }

    public function create()
    {
        $this->authorize('create', Pet::class);
        return view('pets.create');
    }

    public function store(Request $request, ImageService $imageService, VideoEmbedService $videoEmbedService)
    {
        $this->authorize('create', Pet::class);

        $request->validate([
            'nome'                => 'required|string|max:255',
            'especie'             => 'required|string|max:100',
            'raca'                => 'required|string|max:100',
            'cor'                 => 'required|string|max:100',
            'condicoes_especiais' => 'nullable|string|max:500',
            'media'               => 'required_without:video_url|array|min:1',
            'media.*'             => 'file|image|mimes:jpeg,png,jpg,webp,bmp,gif|max:20480',
            'video_url'           => 'nullable|url|max:500',
        ], [
            'media.required_without' => 'Por favor, envie ao menos uma foto ou informe um link de vídeo.',
            'media.*.mimes'          => 'Os arquivos de imagem devem ser nos formatos: JPG, PNG, WEBP, BMP ou GIF.',
            'media.*.image'          => 'Apenas imagens são permitidas para upload direto.',
            'video_url.url'          => 'Informe uma URL válida para o vídeo (YouTube, TikTok ou Instagram).',
        ]);

        $pet = Pet::create([
            'user_id' => Auth::id(),
            'nome' => $request->nome,
            'especie' => $request->especie,
            'raca' => $request->raca,
            'cor' => $request->cor,
            'condicoes_especiais' => $request->condicoes_especiais,
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $imageService->processAndStore($file, 'pets');
                $pet->media()->create([
                    'path' => $path,
                    'type' => 'image',
                ]);
            }
        }

        if ($request->filled('video_url')) {
            $parsed = $videoEmbedService->parse($request->video_url);
            $videoUrl = $parsed ? $parsed['original_url'] : $request->video_url;

            $pet->media()->create([
                'path' => $videoUrl,
                'type' => 'video',
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Pet cadastrado com sucesso!');
    }

    public function showPublic($uuid)
    {
        $pet = Pet::where('uuid', $uuid)->with('user', 'media')->firstOrFail();
        return view('pets.public', compact('pet'));
    }

    public function destroy(Pet $pet)
    {
        $this->authorize('delete', $pet);

        foreach ($pet->media as $mediaItem) {
            if ($mediaItem->type === 'image' && !filter_var($mediaItem->path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($mediaItem->path);
            }
        }

        $pet->delete();
        return back()->with('success', 'Pet removido com sucesso.');
    }

    /**
     * HEALTH: Tela principal da Aba de Saúde
     * Exibe formulário de veterinário e tabs de fichas/lembretes
     */
    public function health(Pet $pet)
    {
        $this->authorize('update', $pet);

        $pet->load([
            'healthRecords' => fn($q) => $q->latest('record_date'),
            'schedules' => fn($q) => $q->upcoming(),
        ]);

        return view('pets.health', compact('pet'));
    }

    /**
     * UPDATE VET: Atualiza dados do veterinário de confiança
     * PATCH /pets/{pet}/vet
     */
    public function updateVet(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'vet_name' => 'nullable|string|max:100',
            'vet_phone' => 'nullable|string|max:20',
        ]);

        $pet->update($validated);

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', 'Dados do veterinário atualizados!');
    }

    /**
     * EDIT: Tela de edição de pet e mídias
     */
    public function edit(Pet $pet)
    {
        $this->authorize('view', $pet);

        $pet->load('media');

        return view('pets.edit', compact('pet'));
    }

    /**
     * UPDATE: Atualiza os dados cadastrais do pet
     */
    public function update(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'especie'            => 'required|string|max:100',
            'raca'               => 'required|string|max:100',
            'cor'                => 'required|string|max:100',
            'condicoes_especiais'=> 'nullable|string|max:500',
        ]);

        $pet->update($validated);

        return redirect()
            ->route('pets.edit', $pet)
            ->with('success', 'Dados do pet atualizados com sucesso!');
    }

    /**
     * STORE MEDIA: Adiciona novas mídias ao pet pela tela de edição
     */
    public function storeMedia(Request $request, Pet $pet, ImageService $imageService, VideoEmbedService $videoEmbedService)
    {
        $this->authorize('manageMedia', $pet);

        $request->validate([
            'media'     => 'nullable|array',
            'media.*'   => 'file|image|mimes:jpeg,png,jpg,webp,bmp,gif|max:20480',
            'video_url' => 'nullable|url|max:500',
        ], [
            'media.*.mimes' => 'Os arquivos de imagem devem ser nos formatos: JPG, PNG, WEBP, BMP ou GIF.',
            'media.*.image' => 'Apenas imagens são permitidas para upload direto.',
            'video_url.url' => 'Informe uma URL válida para o vídeo (YouTube, TikTok ou Instagram).',
        ]);

        $addedCount = 0;

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $imageService->processAndStore($file, 'pets');
                $pet->media()->create([
                    'path' => $path,
                    'type' => 'image',
                ]);
                $addedCount++;
            }
        }

        if ($request->filled('video_url')) {
            $parsed = $videoEmbedService->parse($request->video_url);
            $videoUrl = $parsed ? $parsed['original_url'] : $request->video_url;

            $pet->media()->create([
                'path' => $videoUrl,
                'type' => 'video',
            ]);
            $addedCount++;
        }

        if ($addedCount === 0) {
            return back()->with('error', 'Selecione pelo menos uma imagem ou informe um link de vídeo.');
        }

        return back()->with('success', 'Mídias adicionadas com sucesso!');
    }

    /**
     * DESTROY MEDIA: Remove uma mídia específica do pet
     */
    public function destroyMedia(Pet $pet, PetMedia $media)
    {
        $this->authorize('destroyMedia', [$pet, $media]);

        if ($media->type === 'image' && !filter_var($media->path, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return back()->with('success', 'Mídia removida com sucesso!');
    }
}
