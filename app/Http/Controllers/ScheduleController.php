<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetSchedule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller: ScheduleController
 * 
 * Gerencia lembretes de vacinas e remédios do pet.
 * CRUD simples com toggle de conclusão.
 */
class ScheduleController extends Controller
{
    use AuthorizesRequests;
    private array $validTypes = ['medication', 'vaccine'];

    /**
     * STORE: Cria novo agendamento/lembrete
     * POST /pets/{pet}/schedules
     */
    public function store(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:' . implode(',', $this->validTypes),
            'due_date' => 'required|date|after_or_equal:today',
            'time' => 'nullable|date_format:H:i',
        ], [
            'title.required' => 'Informe o nome do medicamento ou vacina.',
            'due_date.after_or_equal' => 'A data deve ser hoje ou futura.',
            'time.date_format' => 'Horário inválido (use formato 24h: HH:MM).',
        ]);

        PetSchedule::create([
            'pet_id' => $pet->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'due_date' => $validated['due_date'],
            'time' => $validated['time'] ?? null,
            'is_completed' => false,
        ]);

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', 'Lembrete adicionado!');
    }

    /**
     * TOGGLE: Alterna status is_completed (concluído/pendente)
     * POST /pets/{pet}/schedules/{schedule}/toggle
     */
    public function toggle(Pet $pet, PetSchedule $schedule)
    {
        if ($schedule->pet_id !== $pet->id) {
            abort(404);
        }

        $this->authorize('update', $pet);

        $schedule->update([
            'is_completed' => !$schedule->is_completed,
        ]);

        $message = $schedule->is_completed 
            ? 'Lembrete marcado como concluído!' 
            : 'Lembrete reaberto.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $schedule->is_completed,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', $message);
    }

    /**
     * DESTROY: Remove lembrete
     * DELETE /pets/{pet}/schedules/{schedule}
     */
    public function destroy(Pet $pet, PetSchedule $schedule)
    {
        if ($schedule->pet_id !== $pet->id) {
            abort(404);
        }

        $this->authorize('update', $pet);

        $schedule->delete();

        return redirect()
            ->route('pets.health', $pet)
            ->with('success', 'Lembrete removido.');
    }
}
