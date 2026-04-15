<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Fix #3: Whitelist of allowed OAuth providers.
     */
    private const ALLOWED_PROVIDERS = ['google', 'twitter-oauth-2', 'microsoft'];

    public function redirectToProvider(string $provider)
    {
        // Fix #3: Validate provider against whitelist before passing to Socialite
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        // Fix #3: Validate provider against whitelist
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Erro ao autenticar com ' . $provider . '. Tente novamente.']);
        }

        // Prevent OAuth users without email from registering
        if (!$socialUser->getEmail()) {
            return redirect()->route('login')->withErrors(['email' => 'Este provedor não forneceu um e-mail. Use outro método de login.']);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name'        => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário',
                'email'       => $socialUser->getEmail(),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'pontos'      => 0,
            ]);

            // Fix #4: Fire Registered event so MustVerifyEmail sends verification,
            // but also pre-verify social logins since the email was validated by the provider.
            $user->markEmailAsVerified();
            event(new Registered($user));
        }

        Auth::login($user, true);

        // If missing geolocation/phone, send to setup page
        if (!$user->latitude || !$user->telefone) {
            return redirect()->route('register')->with('info', 'Complete seus dados para ativar o Radar 1 KM.');
        }

        return redirect()->route('pets.index');
    }
}
