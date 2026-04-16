<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller: HealthRecordController
 * 
 * Gerencia fichas clínicas do pet: upload, privacidade, visualização segura.
 * Segurança: Arquivos armazenados em storage/app/private/health_records
 */
class HealthRecordController extends Controller
{
    use AuthorizesRequests;
    /**
     * Categorias válidas para validação
     */
    private array $validCategories = ['exame', 'vacina', 'consulta', 'receita', 'outro'];

    /**
     * STORE: Cria nova ficha clínica com upload de arquivo
     * POST /pets/{pet}/records
     */
    public function store(Request $request, Pet $pet)
    {
        // Policy: apenas dono do pet pode adicionar
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|in:' . implode(',', $this->validCategories),
            'record_date' => 'required|date',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'is_public' => 'sometimes|boolean',
        ], [
            'file.mimes' => 'Apenas arquivos JPG, PNG ou PDF são permitidos.',
            'file.max' => 'O arquivo não pode ter mais que 5MB.',
            'title.required' => 'Informe um título para a ficha.',
        ]);

        // Processar upload - salvar em storage privado
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        
        // Diretório privado: health_records/{pet_uuid}/
        $directory = 'private/health_records/' . $pet->uuid;
        $filePath = $file->storeAs($directory, $fileName, 'local');

        // Criar registro no banco
        $record = HealthRecord::create([
            'pet_id' => $pet->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'file_path' => $filePath,
            'file_extension' => $extension,
            'is_public' => $validated['is_public'] ?? false,
            'record_date' => $validated['record_date'],
        ]);

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', 'Ficha clínica adicionada com sucesso!');
    }

    /**
     * UPDATE PRIVACY: Alterna status is_public (via AJAX ou form POST)
     * POST /pets/{pet}/records/{record}/privacy
     */
    public function updatePrivacy(Request $request, Pet $pet, HealthRecord $record)
    {
        // Verificar se o record pertence ao pet
        if ($record->pet_id !== $pet->id) {
            abort(404);
        }

        // Policy: apenas dono pode alterar privacidade
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'is_public' => 'required|boolean',
        ]);

        $record->update([
            'is_public' => $validated['is_public'],
        ]);

        // Resposta JSON para AJAX, ou redirect para form normal
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_public' => $record->is_public,
                'message' => $record->is_public ? 'Ficha tornada pública' : 'Ficha privada',
            ]);
        }

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', $record->is_public ? 'Ficha agora é pública (visível em SOS)' : 'Ficha agora é privada');
    }

    /**
     * SHOW FILE: Retorna o arquivo para visualização/download
     * GET /pets/{pet}/records/{record}/view
     * 
     * Security: Rota protegida - permite apenas se:
     *   1. Usuário é o dono do pet, OU
     *   2. Usuário está autenticado E is_public = true
     */
    public function showFile(Pet $pet, HealthRecord $record): BinaryFileResponse
    {
        // Verificar se o record pertence ao pet
        if ($record->pet_id !== $pet->id) {
            abort(404);
        }

        // Verificar permissão de acesso
        $isOwner = Auth::check() && Auth::id() === $pet->user_id;
        $isPublic = $record->is_public;
        $isAuthenticated = Auth::check();

        // Permitir se: (dono) OU (autenticado E público)
        // Nota: Comunidade pode ver públicos, mas precisa estar logada (evita scraping)
        if (!$isOwner && !($isAuthenticated && $isPublic)) {
            abort(403, 'Acesso negado. Esta ficha é privada.');
        }

        // Verificar se arquivo existe
        if (!Storage::disk('local')->exists($record->file_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        // Retornar arquivo com headers apropriados
        $mimeType = match($record->file_extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        $fullPath = Storage::disk('local')->path($record->file_path);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $record->title . '.' . $record->file_extension . '"',
        ]);
    }

    /**
     * DESTROY: Remove ficha e arquivo
     * DELETE /pets/{pet}/records/{record}
     */
    public function destroy(Pet $pet, HealthRecord $record)
    {
        if ($record->pet_id !== $pet->id) {
            abort(404);
        }

        $this->authorize('update', $pet);

        // Deletar arquivo físico
        if (Storage::disk('local')->exists($record->file_path)) {
            Storage::disk('local')->delete($record->file_path);
        }

        $record->delete();

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', 'Ficha removida com sucesso.');
    }
}
