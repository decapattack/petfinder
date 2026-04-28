<section>
    <p class="text-muted small mb-4">
        Atualize as informações do seu perfil e endereço de e-mail.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning py-2 small mt-2">
                    {{ __('Seu endereço de e-mail não está verificado.') }}
                    <button form="send-verification" type="submit" class="btn btn-sm btn-outline-warning ms-2">
                        {{ __('Reenviar verificação') }}
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success py-2 small mt-2">
                        {{ __('Um novo link de verificação foi enviado para seu e-mail.') }}
                    </div>
                @endif
            @endif
        </div>

        <div class="mb-3">
            <x-input-label for="telefone" :value="__('Telefone (WhatsApp)')" />
            <x-text-input id="telefone" name="telefone" type="tel" :value="old('telefone', $user->telefone)" placeholder="(00) 00000-0000" />
            <x-input-error :messages="$errors->get('telefone')" />
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <x-input-label for="latitude" :value="__('Latitude')" />
                <x-text-input id="latitude" name="latitude" type="text" :value="old('latitude', $user->latitude)" readonly />
            </div>
            <div class="col-6">
                <x-input-label for="longitude" :value="__('Longitude')" />
                <x-text-input id="longitude" name="longitude" type="text" :value="old('longitude', $user->longitude)" readonly />
            </div>
            <div class="col-12 mt-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="captureLocation()">
                    📍 Atualizar Localização (Radar 1 KM)
                </button>
                <div id="geo-status" class="form-text"></div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">✔ Salvo com sucesso!</span>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
function captureLocation() {
    const status = document.getElementById('geo-status');
    if (!navigator.geolocation) { status.textContent = 'Geolocalização não suportada.'; return; }
    status.textContent = 'Obtendo localização...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('latitude').value = pos.coords.latitude;
            document.getElementById('longitude').value = pos.coords.longitude;
            status.innerHTML = '<span class="text-success">✔ Localização atualizada!</span>';
        },
        () => { status.innerHTML = '<span class="text-danger">✗ Não foi possível obter a localização.</span>'; }
    );
}
</script>
@endpush
