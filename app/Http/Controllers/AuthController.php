<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    /**
     * Whitelist de provedores OAuth permitidos.
     */
    private const ALLOWED_PROVIDERS = ['google', 'twitter-oauth-2', 'microsoft'];

    public function redirectToProvider(string $provider)
    {
        // Validar se o provedor está na whitelist
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, string $provider)
    {
        // Validar se o provedor está na whitelist
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        // Tratar cancelamento ou erro devolvido pelo provedor OAuth
        if ($request->has('error')) {
            $errorDesc = $request->get('error_description', 'Acesso não concedido pelo provedor.');
            return redirect()->route('login')->withErrors([
                'email' => 'Autenticação cancelada: ' . $errorDesc,
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            // CSRF state mismatch no fluxo OAuth
            return redirect()->route('login')->withErrors([
                'email' => 'A sessão de autenticação expirou ou o token de estado é inválido. Tente novamente.',
            ]);
        } catch (\Throwable $e) {
            Log::warning("Falha na autenticação OAuth com {$provider}: " . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Erro ao autenticar com ' . $provider . '. Tente novamente.',
            ]);
        }

        // Provedor precisa fornecer e-mail válido
        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Este provedor não forneceu um e-mail válido. Utilize outro método de login.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'        => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário',
                'email'       => $email,
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'pontos'      => 0,
            ]);

            $user->markEmailAsVerified();
            event(new Registered($user));
        } else {
            // Vincula o provedor caso o usuário tenha sido criado via formulário
            if (empty($user->provider_id)) {
                $user->update([
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }
        }

        Auth::login($user, true);

        // Se faltar geolocalização ou telefone, direciona para o perfil para completar
        if (!$user->latitude || !$user->telefone) {
            return redirect()->route('profile.edit')->with('info', 'Complete seus dados para ativar o Radar 1 KM.');
        }

        return redirect()->route('dashboard');
    }
}
