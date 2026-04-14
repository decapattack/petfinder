@extends('layouts.app')

@section('title', 'Acesso Negado - PetFinder')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card premium-card text-center">
            <div class="card-body p-5">
                <div class="mb-4">
                    <span class="fs-1">👋</span>
                </div>
                <h3>Olá, {{ Auth::user()->name }}!</h3>
                <p class="text-muted fs-5 mb-4">Você já está logado na plataforma.</p>
                <div class="d-grid gap-2">
                    <a href="/" class="btn btn-premium btn-lg">Voltar para Home</a>
                    <a href="{{ route('pets.index') }}" class="btn btn-outline-primary">Ir para Meus Pets</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
