<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Pet;
use App\Models\User;
use App\Notifications\PetLostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

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

        $user = Auth::user();

        // 1. Create Alert
        $alert = Alert::create([
            'pet_id' => $pet->id,
            'latitude_fuga' => $user->latitude,
            'longitude_fuga' => $user->longitude,
            'status' => 'ativo',
        ]);

        // 2. Update Pet Status
        $pet->update(['status' => 'desaparecido']);

        // 3. Find Neighbors within 1 KM
        $neighbors = $this->getNeighborsWithinRadius($user->latitude, $user->longitude, 1);

        // 4. Notify Neighbors (except the owner)
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

        // 1. Update statuses
        $alert->update(['status' => 'resolvido']);
        $pet->update(['status' => 'seguro']);

        // 2. Gamification (Add points to hero if provided)
        $heroMessage = "";
        if ($request->hero_email) {
            $hero = User::where('email', $request->hero_email)->first();
            if ($hero) {
                $hero->increment('pontos', 50);
                $heroMessage = " O herói recebeu +50 pontos!";
            }
        }

        return redirect()->route('pets.index')->with('success', 'Ficamos felizes que seu pet foi encontrado!' . $heroMessage);
    }

    private function getNeighborsWithinRadius($lat, $lng, $radiusKm)
    {
        // Bounding box approximation for 1km (roughly 0.009 deg)
        $delta = $radiusKm / 111;
        
        $potentialNeighbors = User::whereBetween('latitude', [$lat - $delta, $lat + $delta])
            ->whereBetween('longitude', [$lng - $delta, $lng + $delta])
            ->get();

        // Refine with Haversine in PHP
        return $potentialNeighbors->filter(function ($neighbor) use ($lat, $lng, $radiusKm) {
            $distance = $this->haversineDistance($lat, $lng, $neighbor->latitude, $neighbor->longitude);
            return $distance <= $radiusKm;
        });
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
