<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetMedia;
use App\Models\Veterinarian;
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
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'is_public'           => 'nullable|boolean',
            'media'               => 'required_without:video_url|array|min:1',
            'media.*'             => 'file|image|mimes:jpeg,png,jpg,webp,bmp,gif|max:20480',
            'video_url'           => 'nullable|url|max:500',
        ], [
            'media.required_without' => 'Por favor, envie ao menos uma foto ou informe um link de vídeo.',
            'media.*.mimes'          => 'Os arquivos de imagem devem ser nos formatos: JPG, PNG, WEBP, BMP ou GIF.',
            'media.*.image'          => 'Apenas imagens são permitidas para upload direto.',
            'video_url.url'          => 'Informe uma URL válida para o vídeo (YouTube, TikTok ou Instagram).',
        ]);

        $user = Auth::user();
        $lat = $request->filled('latitude') ? $request->latitude : $user->latitude;
        $lng = $request->filled('longitude') ? $request->longitude : $user->longitude;

        $pet = Pet::create([
            'user_id'             => $user->id,
            'nome'                => $request->nome,
            'especie'             => $request->especie,
            'raca'                => $request->raca,
            'cor'                 => $request->cor,
            'condicoes_especiais' => $request->condicoes_especiais,
            'latitude'            => $lat,
            'longitude'           => $lng,
            'is_public'           => $request->has('is_public') ? $request->boolean('is_public') : true,
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
        $pet = Pet::where('uuid', $uuid)->with('user', 'media', 'alerts')->firstOrFail();

        $isOwner = Auth::check() && Auth::id() === $pet->user_id;
        $isMissing = $pet->status === 'desaparecido';

        if (!$pet->is_public && !$isOwner && !$isMissing) {
            abort(404, 'A página pública deste pet está desativada pelo tutor.');
        }

        $alert = $pet->active_alert ?: $pet->alerts()->latest()->first();

        return view('pets.public', compact('pet', 'alert'));
    }

    public function destroy(Pet $pet)
    {
        $this->authorize('delete', $pet);

        foreach ($pet->media as $mediaItem) {
            if ($mediaItem->type === 'image' && !filter_var($mediaItem->path, FILTER_VALIDATE_URL)) {
                Storage::disk('local')->delete($mediaItem->path);
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
            'veterinarian',
            'healthRecords' => fn($q) => $q->latest('record_date'),
            'schedules' => fn($q) => $q->upcoming(),
        ]);

        $veterinarians = Veterinarian::orderBy('nome')->get();

        return view('pets.health', compact('pet', 'veterinarians'));
    }

    /**
     * UPDATE VET: Atualiza dados do veterinário de confiança
     * PATCH /pets/{pet}/vet
     */
    public function updateVet(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        if ($request->has('remove_vet') && $request->boolean('remove_vet')) {
            $pet->update(['veterinarian_id' => null]);
        } elseif ($request->filled('veterinarian_id')) {
            $validated = $request->validate([
                'veterinarian_id' => 'required|exists:veterinarians,id',
            ]);
            $pet->update(['veterinarian_id' => $validated['veterinarian_id']]);
        } else {
            $validated = $request->validate([
                'nome' => 'required|string|max:150',
                'crv' => 'nullable|string|max:50',
                'telefone' => 'required|string|max:30',
                'email' => 'nullable|email|max:150',
                'cidade' => 'nullable|string|max:100',
                'estado' => 'nullable|string|max:2',
            ]);

            if ($pet->veterinarian_id && !$request->boolean('create_new')) {
                $pet->veterinarian->update($validated);
            } else {
                $vet = Veterinarian::create($validated);
                $pet->update(['veterinarian_id' => $vet->id]);
            }
        }

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
            'raca'                => 'required|string|max:100',
            'cor'                 => 'required|string|max:100',
            'condicoes_especiais' => 'nullable|string|max:500',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'is_public'           => 'nullable|boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

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
            Storage::disk('local')->delete($media->path);
        }
        $media->delete();

        return back()->with('success', 'Mídia removida com sucesso!');
    }

    /**
     * SHOW PUBLIC MAP: Exibe a página do mapa para dispositivos móveis ou visualização separada
     */
    public function showPublicMap($uuid)
    {
        $pet = Pet::where('uuid', $uuid)->with('user', 'alerts')->firstOrFail();

        $isOwner = Auth::check() && Auth::id() === $pet->user_id;
        $isMissing = $pet->status === 'desaparecido';

        if (!$pet->is_public && !$isOwner && !$isMissing) {
            abort(404, 'A página pública deste pet está desativada.');
        }

        $alert = $pet->active_alert ?: $pet->alerts()->latest()->first();

        return view('pets.map', compact('pet', 'alert'));
    }
}
