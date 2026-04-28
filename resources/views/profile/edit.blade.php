<x-app-layout>
    <x-slot name="title">Meu Perfil - PetFinder</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Informações do Perfil --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4">Informações do Perfil</h5>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Alterar Senha --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4">Alterar Senha</h5>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Excluir Conta --}}
            <div class="card shadow-sm border-danger mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold text-danger mb-4">Excluir Conta</h5>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

