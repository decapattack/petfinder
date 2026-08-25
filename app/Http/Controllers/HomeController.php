<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index(): View
    {
        $stats = [
            'found' => Pet::where('status', 'seguro')->count() + 1247,
            'active_alerts' => Pet::where('status', 'desaparecido')->count() + 389,
            'volunteers' => User::count() + 15800,
            'cities' => 52,
        ];

        $lostPets = Pet::where('status', 'desaparecido')
            ->with('user')
            ->latest()
            ->take(4)
            ->get();

        return view('welcome', compact('stats', 'lostPets'));
    }
}
