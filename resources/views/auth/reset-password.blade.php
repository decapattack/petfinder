<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold">Redefinir Senha</h4>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $request->email)" required />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Nova Senha')" />
            <x-text-input id="password" name="password" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirmar Nova Senha')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" required />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-grid">
            <x-primary-button>{{ __('Redefinir Senha') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
