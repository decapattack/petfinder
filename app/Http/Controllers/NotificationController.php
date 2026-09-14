<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Marca uma notificação específica como lida e redireciona para a página do pet.
     */
    public function readAndRedirect(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $petUuid = $notification->data['pet_uuid'] ?? null;
        if ($petUuid) {
            return redirect()->route('pets.public', $petUuid);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Marca uma notificação individual como lida (POST / AJAX).
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notificação marcada como lida.');
    }

    /**
     * Marca todas as notificações não lidas do usuário como lidas.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
