@extends('layouts.base')

@section('title', 'PetFinder - Encontre seu Pet')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .hover-translate-y {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-translate-y:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        }
    </style>
@endpush

@section('content')
<!-- ============ NAVBAR ============ -->
<nav class="navbar navbar-expand-lg navbar-petfinder sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}" style="font-family:'Inter',sans-serif;font-weight:800;font-size:1.75rem;color:#0d6efd;text-decoration:none;display:flex;align-items:center;gap:.4rem;"><svg width="38" height="28" viewBox="0 0 190 140" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;"><g transform="translate(5,0) rotate(-12,50,75)"><ellipse cx="50" cy="80" rx="20" ry="22"/><ellipse cx="21" cy="52" rx="9" ry="13" transform="rotate(-20 21 52)"/><ellipse cx="37" cy="41" rx="8" ry="12" transform="rotate(-5 37 41)"/><ellipse cx="63" cy="41" rx="8" ry="12" transform="rotate(5 63 41)"/><ellipse cx="79" cy="52" rx="9" ry="13" transform="rotate(20 79 52)"/></g><g transform="translate(95,18) rotate(12,50,75)"><ellipse cx="50" cy="80" rx="22" ry="24"/><ellipse cx="20" cy="50" rx="10" ry="14" transform="rotate(-20 20 50)"/><ellipse cx="37" cy="39" rx="9" ry="13" transform="rotate(-5 37 39)"/><ellipse cx="63" cy="39" rx="9" ry="13" transform="rotate(5 63 39)"/><ellipse cx="80" cy="50" rx="10" ry="14" transform="rotate(20 80 50)"/></g></svg>PetFinder</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
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
                @auth
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-fill me-1"></i>Meus Pets ({{ Auth::user()->name }})
                        </a>
                    </li>
                @else
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                        </a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link text-danger fw-bold" href="{{ route('register') }}">
                            <i class="bi bi-megaphone-fill me-1"></i>Reportar Pet
                        </a>
                    </li>
                @endauth
            </ul>
            @auth
                <div class="d-none d-lg-flex align-items-center gap-3">
                    <span class="text-muted">Olá, {{ Auth::user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-reportar">
                        <i class="bi bi-grid-fill me-1"></i> Meus Pets
                    </a>
                </div>
            @else
                <div class="d-none d-lg-flex align-items-center gap-2">
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
            <div class="col-lg-5 d-none d-lg-flex justify-content-center align-items-center mt-4 mt-lg-0">
                <svg width="320" height="280" viewBox="0 0 320 280" fill="none" style="opacity:.25" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <g id="hero-paw" fill="white">
                            <!-- Almofada principal -->
                            <ellipse cx="50" cy="70" rx="28" ry="32" />
                            <!-- 4 dedos -->
                            <ellipse cx="20" cy="36" rx="12" ry="16" transform="rotate(-20 20 36)" />
                            <ellipse cx="38" cy="16" rx="10" ry="15" transform="rotate(-6 38 16)" />
                            <ellipse cx="62" cy="16" rx="10" ry="15" transform="rotate(6 62 16)" />
                            <ellipse cx="80" cy="36" rx="12" ry="16" transform="rotate(20 80 36)" />
                        </g>
                    </defs>
                    <!-- Patinha 1 (superior esquerda) -->
                    <use href="#hero-paw" transform="translate(25, 20) rotate(-14 50 50) scale(1.05)" />
                    <!-- Patinha 2 (inferior direita) -->
                    <use href="#hero-paw" transform="translate(155, 90) rotate(14 50 50) scale(1.3)" />
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
                <div class="stat-num">{{ $stats['found'] }}</div>
                <div class="stat-label">Pets Reencontrados</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ $stats['active_alerts'] }}</div>
                <div class="stat-label">Alertas Ativos</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ $stats['volunteers'] }}</div>
                <div class="stat-label">Voluntários</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-num">{{ $stats['cities'] }}</div>
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
            @forelse($lostPets as $pet)
                <div class="col-sm-6 col-lg-3">
                    <div class="pet-card">
                        @if($pet->cover_photo)
                            @if($pet->cover_photo->type === 'video')
                                <div class="pet-card-img" style="background-color: #f0f0f0; overflow: hidden;">
                                    <video src="{{ asset('storage/' . $pet->cover_photo->path) }}" style="width: 100%; height: 100%; object-fit: cover;" autoplay muted loop playsinline></video>
                            @else
                                <div class="pet-card-img" style="background-image: url('{{ asset('storage/' . $pet->cover_photo->path) }}'); background-color: #f0f0f0;">
                            @endif
                        @else
                            <div class="pet-card-img" style="background-color: #f0f0f0;">
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

<!-- ============ GESTÃO DE SAÚDE E RECURSOS ============ -->
<section class="py-5 bg-white border-top border-bottom" id="recursos-saude">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2">Tudo em um só lugar</span>
            <h2 class="section-title">Muito mais que buscas: Gestão de Saúde Completa</h2>
            <p class="text-muted mt-2">A praticidade de gerenciar a segurança e a saúde do seu pet no mesmo aplicativo.</p>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex bg-light p-4 rounded-4 shadow-sm border-start border-primary border-4 hover-translate-y">
                            <div class="me-4 text-primary fs-1">
                                <i class="fa-solid fa-syringe"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Controle de Vacinas & Lembretes</h5>
                                <p class="text-muted mb-0">Agende vacinas e remédios de forma simples e intuitiva. Tenha lembretes ativos para nunca perder o prazo da dose do seu melhor amigo.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex bg-light p-4 rounded-4 shadow-sm border-start border-success border-4 hover-translate-y">
                            <div class="me-4 text-success fs-1">
                                <i class="fa-solid fa-notes-medical"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Prontuário Clínico & Exames</h5>
                                <p class="text-muted mb-0">Centralize receitas, laudos e históricos médicos do pet. Suba PDFs ou imagens de exames e acesse tudo na nuvem com um clique, de onde estiver.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex bg-light p-4 rounded-4 shadow-sm border-start border-info border-4 hover-translate-y">
                            <div class="me-4 text-info fs-1">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-2">Identificação Inteligente Integrada</h5>
                                <p class="text-muted mb-0">Gere um QR Code exclusivo para a coleira do pet. Se ele se perder, qualquer um que escanear poderá ver os dados de contato e ficha de saúde pública.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center position-relative">
                <div class="bg-primary bg-opacity-10 rounded-circle position-absolute start-50 top-50 translate-middle" style="width: 400px; height: 400px; z-index: 0;"></div>
                <div class="card border-0 shadow-lg mx-auto rounded-4 overflow-hidden position-relative" style="max-width: 380px; z-index: 1;">
                    <div class="bg-primary text-white p-4 text-start" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                        <h4 class="fw-bold mb-0">🐾 Painel do Pet</h4>
                        <small class="text-white-50">Histórico unificado de saúde e alertas</small>
                    </div>
                    <div class="p-4 text-start bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-success">Seguro</span>
                            <span class="text-muted small">QR Code Ativo</span>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <strong class="d-block text-dark small mb-1">PRÓXIMO LEMBRETE:</strong>
                            <div class="d-flex align-items-center text-danger small">
                                <i class="fa-solid fa-clock me-2"></i>
                                <span>Vacina Antirrábica - 10/10/2026</span>
                            </div>
                        </div>
                        <div>
                            <strong class="d-block text-dark small mb-2">FICHAS CLÍNICAS RECENTES:</strong>
                            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-2 text-dark small">
                                <span><i class="fa-solid fa-file-pdf me-2 text-danger"></i>Hemograma.pdf</span>
                                <span class="badge bg-secondary">Exame</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded text-dark small">
                                <span><i class="fa-solid fa-file-image me-2 text-primary"></i>Vacina_V10.jpg</span>
                                <span class="badge bg-primary">Vacina</span>
                            </div>
                        </div>
                    </div>
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
                <h6 class="d-flex align-items-center gap-2"><svg width="28" height="20" viewBox="0 0 190 140" fill="#0d6efd" xmlns="http://www.w3.org/2000/svg"><g transform="translate(5,0) rotate(-12,50,75)"><ellipse cx="50" cy="80" rx="20" ry="22"/><ellipse cx="21" cy="52" rx="9" ry="13" transform="rotate(-20 21 52)"/><ellipse cx="37" cy="41" rx="8" ry="12" transform="rotate(-5 37 41)"/><ellipse cx="63" cy="41" rx="8" ry="12" transform="rotate(5 63 41)"/><ellipse cx="79" cy="52" rx="9" ry="13" transform="rotate(20 79 52)"/></g><g transform="translate(95,18) rotate(12,50,75)"><ellipse cx="50" cy="80" rx="22" ry="24"/><ellipse cx="20" cy="50" rx="10" ry="14" transform="rotate(-20 20 50)"/><ellipse cx="37" cy="39" rx="9" ry="13" transform="rotate(-5 37 39)"/><ellipse cx="63" cy="39" rx="9" ry="13" transform="rotate(5 63 39)"/><ellipse cx="80" cy="50" rx="10" ry="14" transform="rotate(20 80 50)"/></g></svg>PetFinder</h6>
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
