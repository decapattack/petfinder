<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetLostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class AlertController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pet_id' => 'required|exists:pets,id',
        ]);

        $pet = Pet::findOrFail($request->pet_id);

        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        // Prevent duplicate active alerts for the same pet
        if ($pet->status === 'desaparecido') {
            return back()->with('error', 'Este pet já possui um alerta ativo.');
        }

        $user = Auth::user();

        $alert = Alert::create([
            'pet_id'         => $pet->id,
            'latitude_fuga'  => $user->latitude,
            'longitude_fuga' => $user->longitude,
            'status'         => 'ativo',
        ]);

        $pet->update(['status' => 'desaparecido']);

        $neighbors = $this->getNeighborsWithinRadius($user->latitude, $user->longitude, 1);
        $neighborsToNotify = $neighbors->where('id', '!=', $user->id);
        Notification::send($neighborsToNotify, new PetLostNotification($pet));

        return back()->with('success', 'Alerta emitido! Os heróis vizinhos foram notificados.');
    }

    public function resolve(Request $request, Alert $alert)
    {
        $pet = $alert->pet;

        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        // Fix #8: Prevent resolving an already-resolved alert
        if ($alert->status === 'resolvido') {
            return redirect()->route('dashboard')->with('error', 'Este alerta já foi encerrado.');
        }

        $alert->update(['status' => 'resolvido']);
        $pet->update(['status' => 'seguro']);

        // Fix #7: Gamification — always return the same message to prevent email enumeration.
        // Points are added silently without confirming if the hero email exists.
        $heroMessage = '';
        if ($request->filled('hero_email')) {
            $hero = User::where('email', $request->hero_email)->first();
            if ($hero && $hero->id !== Auth::id()) {
                $hero->increment('pontos', 50);
            }
            // Always show the same message regardless of whether hero was found
            $heroMessage = ' Se o e-mail informado pertencer a um usuário PetFinder, ele receberá os pontos!';
        }

        return redirect()->route('dashboard')->with('success', 'Ficamos felizes que seu pet voltou para casa!' . $heroMessage);
    }

    public function testNotification()
    {
        $user = Auth::user();

        // Fix #5: Only use the authenticated user's own pets, not Pet::first() globally
        $pet = $user->pets()->first();

        if (!$pet) {
            return back()->with('error', 'Cadastre pelo menos um pet para testar a notificação.');
        }

        $user->notify(new PetLostNotification($pet));

        return back()->with('success', '🔔 Notificação de teste enviada! Confira o sino na barra superior.');
    }

    private function getNeighborsWithinRadius($lat, $lng, $radiusKm)
    {
        $delta = $radiusKm / 111;

        $potentialNeighbors = User::whereBetween('latitude', [$lat - $delta, $lat + $delta])
            ->whereBetween('longitude', [$lng - $delta, $lng + $delta])
            ->get();

        return $potentialNeighbors->filter(function ($neighbor) use ($lat, $lng, $radiusKm) {
            return $this->haversineDistance($lat, $lng, $neighbor->latitude, $neighbor->longitude) <= $radiusKm;
        });
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
