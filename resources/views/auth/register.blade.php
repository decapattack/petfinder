@extends('layouts.app')

@section('title', 'Finalizar Cadastro - PetFinder')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card premium-card">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">
                    {{ Auth::check() ? 'Completar Perfil' : 'Criar Conta' }}
                </h2>
                
                <div id="geo-alert" class="alert alert-info d-none">
                    <strong>📍 Precisamos da sua localização:</strong> 
                    Para o radar de pets perdidos funcionar, precisamos saber onde você e seus pets estão.
                    <button class="btn btn-sm btn-outline-info ms-2" onclick="getLocation()">Ativar GPS</button>
                </div>

                <form action="/register" method="POST" id="register-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="name" class="form-control" required value="{{ Auth::user()->name ?? '' }}">
                    </div>

                    @if(!Auth::check())
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Telefone (WhatsApp)</label>
                        <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" required value="{{ Auth::user()->telefone ?? '' }}">
                    </div>

                    @if(!Auth::check())
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Senha</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    @endif

                    <!-- Hidden Geolocation Fields -->
                    <input type="hidden" name="latitude" id="lat" value="{{ Auth::user()->latitude ?? '' }}">
                    <input type="hidden" name="longitude" id="lng" value="{{ Auth::user()->longitude ?? '' }}">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-premium" id="btn-submit" {{ (Auth::check() && Auth::user()->latitude) ? '' : 'disabled' }}>
                            {{ Auth::check() ? 'Salvar Alterações' : 'Finalizar Cadastro' }}
                        </button>
                    </div>
                </form>

                @if(!Auth::check())
                    <p class="text-center mt-3">
                        Já tem conta? <a href="/login" class="text-decoration-none">Faça login</a>
                    </p>

                    <div class="divider d-flex align-items-center my-4">
                        <hr class="flex-grow-1">
                        <span class="px-3 text-muted">ou</span>
                        <hr class="flex-grow-1">
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                         <a href="{{ route('auth.social.redirect', 'google') }}" class="btn btn-outline-dark btn-sm rounded-circle p-2" title="Google">
                            <img src="https://img.icons8.com/color/24/google-logo.png" alt="Google">
                        </a>
                        <a href="{{ route('auth.social.redirect', 'twitter-oauth-2') }}" class="btn btn-outline-dark btn-sm rounded-circle p-2" title="X (Twitter)">
                            <img src="https://img.icons8.com/ios-filled/24/twitterx--v2.png" alt="X">
                        </a>
                        <a href="{{ route('auth.social.redirect', 'microsoft') }}" class="btn btn-outline-dark btn-sm rounded-circle p-2" title="Microsoft">
                            <img src="https://img.icons8.com/color/24/microsoft.png" alt="Microsoft">
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const geoAlert = document.getElementById('geo-alert');
    const submitBtn = document.getElementById('btn-submit');
    const latField = document.getElementById('lat');
    const lngField = document.getElementById('lng');

    window.onload = function() {
        if (!latField.value) {
            geoAlert.classList.remove('d-none');
        }
    };

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        } else {
            alert("Geolocalização não é suportada pelo seu navegador.");
        }
    }

    function showPosition(position) {
        latField.value = position.coords.latitude;
        lngField.value = position.coords.longitude;
        
        geoAlert.classList.replace('alert-info', 'alert-success');
        geoAlert.innerHTML = '<strong>✅ Localização capturada!</strong> Você já pode prosseguir.';
        submitBtn.disabled = false;
    }

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                alert("Usuário negou a solicitação de Geolocalização.");
                break;
            case error.POSITION_UNAVAILABLE:
                alert("As informações de localização estão indisponíveis.");
                break;
            case error.TIMEOUT:
                alert("A requisição para obter a localização expirou.");
                break;
            case error.UNKNOWN_ERROR:
                alert("Ocorreu um erro desconhecido.");
                break;
        }
    }
</script>
@endpush
@endsection
