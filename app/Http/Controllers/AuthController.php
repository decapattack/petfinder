<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::check() && Auth::user()->latitude && Auth::user()->telefone) {
            return view('auth.already-logged-in');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'telefone' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];

        if (!Auth::check()) {
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'name' => $request->name,
                'telefone' => $request->telefone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefone' => $request->telefone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'pontos' => 0,
            ]);
            Auth::login($user);
            event(new Registered($user));
        }

        return redirect('/dashboard')->with('success', 'Cadastro finalizado com sucesso!');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return view('auth.already-logged-in');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/')->with('success', 'Olá novamente!');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Erro ao autenticar com ' . $provider]);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            // New User via Social
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'pontos' => 0,
                // latitude, longitude and telefone will be null and should be asked later
            ]);
        }

        Auth::login($user);

        // If missing critical data, send to registration/profile
        if (!$user->latitude || !$user->telefone) {
            return redirect('/register')->with('success', 'Quase lá! Só precisamos de mais alguns dados.');
        }

        return redirect('/dashboard');
    }
}
