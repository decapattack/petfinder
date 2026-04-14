<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold">Criar Conta PetFinder</h4>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" :value="__('Nome Completo')" />
            <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="telefone" :value="__('Telefone (WhatsApp)')" />
            <x-text-input id="telefone" name="telefone" type="tel" :value="old('telefone')" required placeholder="(00) 00000-0000" />
            <x-input-error :messages="$errors->get('telefone')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" name="password" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- Hidden geolocation fields preenchidos via JS -->
        <input type="hidden" name="latitude" id="lat">
        <input type="hidden" name="longitude" id="lng">

        <div id="geo-alert" class="alert alert-info py-2 small d-none">
            📍 <strong>Radar Pet:</strong> Precisamos da sua localização para alertas num raio de 1 KM.
            <button type="button" class="btn btn-sm btn-outline-info ms-2" onclick="getLocation()">Ativar GPS</button>
        </div>
        <div id="geo-success" class="alert alert-success py-2 small d-none">
            ✅ Localização capturada! Você já pode finalizar o cadastro.
        </div>

        <div class="d-grid gap-2 mt-3">
            <x-primary-button id="btn-submit" class="btn-lg w-100" disabled>{{ __('Finalizar Cadastro') }}</x-primary-button>
        </div>

        <div class="d-flex align-items-center my-4">
            <hr class="flex-grow-1"><span class="px-3 text-muted small">ou cadastre com</span><hr class="flex-grow-1">
        </div>

        <div class="d-flex justify-content-center gap-2 mb-4">
            <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-dark rounded-circle p-2" title="Google">
                <img src="https://img.icons8.com/color/24/google-logo.png" alt="Google">
            </a>
            <a href="{{ route('auth.social.redirect', 'twitter-oauth-2') }}" class="btn btn-outline-dark rounded-circle p-2" title="X">
                <img src="https://img.icons8.com/ios-filled/24/twitterx--v2.png" alt="X">
            </a>
            <a href="{{ route('auth.social.redirect', 'microsoft') }}" class="btn btn-outline-dark rounded-circle p-2" title="Microsoft">
                <img src="https://img.icons8.com/color/24/microsoft.png" alt="Microsoft">
            </a>
        </div>
    </form>

    <p class="text-center small">Já tem conta? <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Login</a></p>
</x-guest-layout>

@push('scripts')
<script>
    const geoAlert = document.getElementById('geo-alert');
    const geoSuccess = document.getElementById('geo-success');
    const submitBtn = document.getElementById('btn-submit');

    window.onload = function() { geoAlert.classList.remove('d-none'); };

    function getLocation() {
        if (!navigator.geolocation) { alert('Geolocalização não suportada.'); return; }
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('lat').value = pos.coords.latitude;
                document.getElementById('lng').value = pos.coords.longitude;
                geoAlert.classList.add('d-none');
                geoSuccess.classList.remove('d-none');
                submitBtn.disabled = false;
            },
            () => alert('Não foi possível obter a localização.')
        );
    }
</script>
@endpush
