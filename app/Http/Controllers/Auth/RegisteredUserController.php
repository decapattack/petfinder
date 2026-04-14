<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        if (Auth::check()) {
            return view('auth.already-logged-in');
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefone'  => ['required', 'string', 'max:20'],
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'telefone'  => $request->telefone,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'pontos'    => 0,
            'password'  => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('pets.index', absolute: false));
    }
}
