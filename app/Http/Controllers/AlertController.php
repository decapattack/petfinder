<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetLostNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class AlertController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $request->validate([
            'pet_id'    => 'required|exists:pets,id',
            'origem'    => 'nullable|in:casa,rua',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $pet = Pet::findOrFail($request->pet_id);

        // Policy: apenas o dono pode criar alerta para o pet
        $this->authorize('create', [Alert::class, $pet]);

        // Prevent duplicate active alerts for the same pet
        if ($pet->status === 'desaparecido') {
            return back()->with('error', 'Este pet já possui um alerta ativo.');
        }

        $user = Auth::user();
        $origem = $request->input('origem', 'casa');

        if ($origem === 'rua' && $request->filled('latitude') && $request->filled('longitude')) {
            $fugaLat = (float) $request->latitude;
            $fugaLng = (float) $request->longitude;
        } else {
            // Origem Casa: busca do pet, com fallback no tutor
            $fugaLat = $pet->latitude ?? $user->latitude;
            $fugaLng = $pet->longitude ?? $user->longitude;

            // Se o pet ainda não tinha coordenadas cadastradas, salva as do tutor nele
            if (is_null($pet->latitude) && !is_null($user->latitude)) {
                $pet->update([
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ]);
            }
        }

        if (is_null($fugaLat) || is_null($fugaLng)) {
            return redirect()->route('profile.edit')
                ->with('error', '⚠️ Você precisa cadastrar sua localização antes de emitir um alerta para que o Radar 1 KM funcione.');
        }

        $alert = Alert::create([
            'pet_id'         => $pet->id,
            'latitude_fuga'  => $fugaLat,
            'longitude_fuga' => $fugaLng,
            'status'         => 'ativo',
        ]);

        $pet->update(['status' => 'desaparecido']);

        $neighbors = $this->getNeighborsWithinRadius($fugaLat, $fugaLng, 1);
        $neighborsToNotify = $neighbors->where('id', '!=', $user->id);
        Notification::send($neighborsToNotify, new PetLostNotification($pet));

        return back()->with('success', 'Alerta emitido! Os heróis vizinhos foram notificados no raio da fuga.');
    }

    public function resolve(Request $request, Alert $alert)
    {
        // Policy: apenas o dono do pet pode encerrar o alerta
        $this->authorize('resolve', $alert);

        $pet = $alert->pet;

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

            if ($hero && $hero->id !== Auth::id() && !$alert->hero_id) {
                $hero->increment('pontos', 50);
                $alert->update([
                    'hero_id' => $hero->id,
                    'hero_awarded_at' => now(),
                ]);
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
