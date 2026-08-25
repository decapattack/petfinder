<?php

namespace App\Http\Controllers;

use App\Models\Pet;
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
        return view('pets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'               => 'required|string|max:255',
            'especie'            => 'required|string|max:100',
            'raca'               => 'required|string|max:100',
            'cor'                => 'required|string|max:100',
            'condicoes_especiais'=> 'nullable|string|max:500',
            'media'              => 'required|array|min:1',
            'media.*'            => 'file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,webm|max:20480',
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
                $path = $file->store('pets', 'public');
                $mime = $file->getClientMimeType();
                $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($path, PATHINFO_EXTENSION));
                $isVideo = str_contains($mime, 'video') || in_array($extension, ['mp4', 'mov', 'avi', 'webm', 'ogg', 'quicktime']);
                $type = $isVideo ? 'video' : 'image';

                $pet->media()->create([
                    'path' => $path,
                    'type' => $type,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Pet cadastrado com sucesso!');
    }

    public function showPublic($uuid)
    {
        $pet = Pet::where('uuid', $uuid)->with('user')->firstOrFail();
        return view('pets.public', compact('pet'));
    }

    public function destroy(Pet $pet)
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($pet->media as $mediaItem) {
            Storage::disk('public')->delete($mediaItem->path);
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

        // Eager load das relações
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
        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        $pet->load('media');

        return view('pets.edit', compact('pet'));
    }

    /**
     * UPDATE: Atualiza os dados cadastrais do pet
     */
    public function update(Request $request, Pet $pet)
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

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
    public function storeMedia(Request $request, Pet $pet)
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'media'   => 'required|array|min:1',
            'media.*' => 'file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,webm|max:20480',
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('pets', 'public');
                $mime = $file->getClientMimeType();
                $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($path, PATHINFO_EXTENSION));
                $isVideo = str_contains($mime, 'video') || in_array($extension, ['mp4', 'mov', 'avi', 'webm', 'ogg', 'quicktime']);
                $type = $isVideo ? 'video' : 'image';

                $pet->media()->create([
                    'path' => $path,
                    'type' => $type,
                ]);
            }
        }

        return back()->with('success', 'Mídias adicionadas com sucesso!');
    }

    /**
     * DESTROY MEDIA: Remove uma mídia específica do pet
     */
    public function destroyMedia(Pet $pet, \App\Models\PetMedia $media)
    {
        if ($pet->user_id !== Auth::id() || $media->pet_id !== $pet->id) {
            abort(403);
        }

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('success', 'Mídia removida com sucesso!');
    }
}
