@extends('layouts.app')

@section('title', 'Login - PetFinder')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card premium-card">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Entrar</h2>

                <form action="/login" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-premium">
                            Acessar Plataforma
                        </button>
                    </div>
                </form>

                <p class="text-center mt-3">
                    Ainda não tem conta? <a href="/register" class="text-decoration-none">Cadastre-se</a>
                </p>

                <div class="divider d-flex align-items-center my-4">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted">ou entre com</span>
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
                    <a href="{{ route('auth.social.redirect', 'apple') }}" class="btn btn-outline-dark btn-sm rounded-circle p-2" title="Apple">
                        <img src="https://img.icons8.com/ios-filled/24/mac-os.png" alt="Apple">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
