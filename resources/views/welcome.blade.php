@extends('layouts.app')

@section('content')
<div class="p-5 mb-4 bg-white premium-card rounded-3">
    <div class="container-fluid py-5 text-center">
        <h1 class="display-5 fw-bold">🐾 Bem-vindo ao PetFinder</h1>
        <p class="col-md-12 fs-4">Ajudando você a proteger e encontrar seus amigos de quatro patas usando o poder da comunidade e da geolocalização.</p>
        <hr class="my-4">
        @auth
            <div class="alert alert-success">
                Olá, <strong>{{ Auth::user()->name }}</strong>! Você tem <strong>{{ Auth::user()->pontos }}</strong> pontos de herói.
            </div>
            <a class="btn btn-premium btn-lg" href="{{ route('pets.index') }}">Gerenciar Meus Pets</a>
        @else
            <a class="btn btn-premium btn-lg" href="/register">Começar Agora</a>
        @endauth
    </div>
</div>

<div class="row align-items-md-stretch">
    <div class="col-md-6">
        <div class="h-100 p-5 bg-white premium-card rounded-3">
            <h2>Radar 1 KM</h2>
            <p>Seja notificado instantaneamente se um pet desaparecer perto de você. Sua ajuda pode salvar vidas.</p>
            <button class="btn btn-outline-primary" type="button">Saiba mais</button>
        </div>
    </div>
    <div class="col-md-6">
        <div class="h-100 p-5 bg-white premium-card rounded-3">
            <h2>QR Code Inteligente</h2>
            <p>Gere identificações únicas para seus pets. Quem encontrar pode entrar em contato com você em um clique.</p>
            <button class="btn btn-outline-primary" type="button">Ver exemplo</button>
        </div>
    </div>
</div>
@endsection
