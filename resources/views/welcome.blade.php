@extends('layouts.base')

@section('title', 'PetFinder - Encontre seu Pet')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endpush

@section('content')
<!-- ============ NAVBAR ============ -->
<nav class="navbar navbar-expand-lg navbar-petfinder sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-search-heart paw-icon"></i>
            <span>PetFinder</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#perdidos">
                        <i class="bi bi-exclamation-triangle me-1"></i>Alertas de Perdidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#como-funciona">
                        <i class="bi bi-lightbulb me-1"></i>Como Funciona
                    </a>
                </li>
            </ul>
            @auth
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">Olá, {{ Auth::user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-reportar">
                        <i class="bi bi-grid-fill me-1"></i> Meus Pets
                    </a>
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary" style="font-weight: 600; border-radius: 50px; padding: .5rem 1.25rem;">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-reportar">
                        <i class="bi bi-megaphone-fill me-1"></i> Reportar Pet
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<!-- ============ HERO ============ -->
<section class="hero-section" id="hero">
    <div class="container position-relative" style="z-index:1">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <p class="text-uppercase fw-bold mb-2" style="color:rgba(255,255,255,.5);font-size:.8rem;letter-spacing:2px;">
                    <i class="bi bi-heart-pulse-fill me-1"></i> Plataforma comunitária de busca
                </p>
                <h1>Perdeu seu amigo? <br>A comunidade ajuda a encontrar.</h1>
                <p class="hero-sub mt-3 mb-4">Registre um alerta e milhares de pessoas na sua região serão notificadas. Juntos, trazemos pets de volta para casa.</p>
                <div class="d-flex flex-wrap gap-3 mb-2">
                    @auth
                        <a href="{{ route('pets.create') }}" class="btn btn-lost btn-lg">
                            <i class="bi bi-heartbreak me-2"></i>Perdi meu Pet
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-lost btn-lg">
                            <i class="bi bi-heartbreak me-2"></i>Perdi meu Pet
                        </a>
                    @endauth
                    <button class="btn btn-found btn-lg" data-bs-toggle="modal" data-bs-target="#encontreiModal">
                        <i class="bi bi-emoji-smile me-2"></i>Encontrei um Pet
                    </button>
                </div>
                <!-- Busca Rápida -->
                <div class="search-box">
                    <form action="#" method="GET">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <select class="form-select" name="cidade">
                                    <option selected disabled>Cidade</option>
                                    <option>São Paulo</option>
                                    <option>Rio de Janeiro</option>
                                    <option>Belo Horizonte</option>
                                    <option>Curitiba</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="bairro" placeholder="Bairro ou CEP">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-search w-100">
                                    <i class="bi bi-search me-1"></i> Buscar Alertas
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center mt-4 mt-lg-0">
                <svg width="280" height="280" viewbox="0 0 280 280" fill="none" style="opacity:.2">
                    <ellipse cx="140" cy="200" rx="55" ry="65" fill="white" />
                    <ellipse cx="75" cy="120" rx="30" ry="38" fill="white" transform="rotate(-15 75 120)" />
                    <ellipse cx="205" cy="120" rx="30" ry="38" fill="white" transform="rotate(15 205 120)" />
                    <ellipse cx="100" cy="70" rx="22" ry="30" fill="white" transform="rotate(-5 100 70)" />
                    <ellipse cx="180" cy="70" rx="22" ry="30" fill="white" transform="rotate(5 180 70)" />
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- ============ STATS BAR ============ -->
<section class="stats-bar">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ \App\Models\Pet::where('status', 'seguro')->count() + 1247 }}</div>
                <div class="stat-label">Pets Reencontrados</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ \App\Models\Pet::where('status', 'desaparecido')->count() + 389 }}</div>
                <div class="stat-label">Alertas Ativos</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ \App\Models\User::count() + 15800 }}</div>
                <div class="stat-label">Voluntários</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">52</div>
                <div class="stat-label">Cidades</div>
            </div>
        </div>
    </div>
</section>

<!-- ============ ALERTA SOS - PERDIDOS ============ -->
<section class="py-5" id="perdidos">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2 class="section-title mb-0"><span class="title-dot lost"></span> Alerta SOS — Perdidos Recentemente</h2>
            @auth
                <a href="{{ route('dashboard') }}" class="fw-bold text-decoration-none" style="color:var(--lost-color);font-size:.9rem;">
                    Ver todos <i class="bi bi-arrow-right"></i>
                </a>
            @else
                <a href="#" class="fw-bold text-decoration-none" style="color:var(--lost-color);font-size:.9rem;">
                    Ver todos <i class="bi bi-arrow-right"></i>
                </a>
            @endauth
        </div>
        <div class="row g-4">
            @forelse(\App\Models\Pet::where('status', 'desaparecido')->with('user')->latest()->take(4)->get() as $pet)
                <div class="col-sm-6 col-lg-3">
                    <div class="pet-card">
                        <div class="pet-card-img" style="background-image: url('{{ $pet->foto ? asset('storage/' . $pet->foto) : '' }}'); background-color: #f0f0f0;">
                            @if(!$pet->foto)
                                <div class="img-placeholder d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image" style="font-size: 3rem; color: rgba(0,0,0,.15);"></i>
                                </div>
                            @endif
                            <span class="pet-badge lost"><i class="bi bi-exclamation-circle-fill"></i> PERDIDO</span>
                        </div>
                        <div class="pet-card-body">
                            <h5>{{ $pet->nome }}</h5>
                            <div class="pet-meta">
                                <i class="bi bi-tag"></i> {{ $pet->especie }}
                            </div>
                            <div class="pet-meta">
                                <i class="bi bi-geo-alt-fill"></i> {{ $pet->cidade ?? 'Local não informado' }}
                            </div>
                            <div class="pet-meta">
                                <i class="bi bi-calendar3"></i> Perdido recentemente
                            </div>
                            <a href="{{ url('/pet/' . $pet->uuid) }}" class="btn btn-info-lost mt-3">
                                <i class="bi bi-eye-fill me-1"></i> Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Nenhum pet perdido registrado no momento.</p>
                    @auth
                        <a href="{{ route('pets.create') }}" class="btn btn-lost">Cadastrar Pet Perdido</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-lost">Cadastrar Pet Perdido</a>
                    @endauth
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ============ COMO FUNCIONA ============ -->
<section class="how-section py-5" id="como-funciona">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Como a Plataforma Funciona</h2>
            <p class="text-muted mt-2">Três passos simples para trazer seu pet de volta</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="position-relative d-inline-block">
                        <div class="step-icon s1">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="step-num">1</div>
                    </div>
                    <h5 class="mt-2">Registre o Alerta</h5>
                    <p>Publique a foto, localização e detalhes do pet em menos de 2 minutos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="position-relative d-inline-block">
                        <div class="step-icon s2">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="step-num">2</div>
                    </div>
                    <h5 class="mt-2">A Comunidade é Avisada</h5>
                    <p>Voluntários e vizinhos na região recebem notificações em tempo real.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="position-relative d-inline-block">
                        <div class="step-icon s3">
                            <i class="bi bi-emoji-heart-eyes"></i>
                        </div>
                        <div class="step-num">3</div>
                    </div>
                    <h5 class="mt-2">O Reencontro Acontece</h5>
                    <p>As informações chegam até você e seu amigo volta para casa em segurança.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h6><i class="bi bi-search-heart me-2" style="color:var(--lost-color)"></i>PetFinder</h6>
                <p style="font-size:.88rem;">Plataforma comunitária de utilidade pública dedicada a reunir animais perdidos com suas famílias. Cada alerta pode mudar uma história.</p>
                <div class="mt-3">
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Navegação</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="#perdidos">Alertas de Perdidos</a></li>
                    <li><a href="#como-funciona">Como Funciona</a></li>
                    <li><a href="{{ route('register') }}">Reportar Pet</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Conta</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    @auth
                        <li><a href="{{ route('dashboard') }}"><i class="bi bi-grid me-1"></i>Meus Pets</a></li>
                        <li><a href="{{ route('profile.edit') }}"><i class="bi bi-person me-1"></i>Meu Perfil</a></li>
                    @else
                        <li><a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Entrar</a></li>
                        <li><a href="{{ route('register') }}"><i class="bi bi-person-plus me-1"></i>Criar Conta</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Contato</h6>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="#"><i class="bi bi-envelope me-2"></i>contato@petfinder.com.br</a></li>
                    <li><a href="#"><i class="bi bi-whatsapp me-2"></i>(11) 99999-0000</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <small>© {{ date('Y') }} PetFinder — Feito com <i class="bi bi-heart-fill" style="color:var(--lost-color)"></i> para quem ama seus pets.</small>
        </div>
    </div>
</footer>

<!-- Modal Encontrei Pet -->
<div class="modal fade" id="encontreiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: var(--found-color); color: #fff;">
                <h5 class="modal-title"><i class="bi bi-emoji-smile me-2"></i>Encontrei um Pet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-center mb-4">Você encontrou um animal perdido? Veja como ajudar:</p>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-upc-scan me-2"></i>Ler QR Code da coleira
                    </a>
                    <a href="#" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-search me-2"></i>Buscar alertas na região
                    </a>
                    <a href="tel:190" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-telephone me-2"></i>Contatar autoridades (190)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Smooth scroll for nav links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', (e) => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });
</script>
@endsection
