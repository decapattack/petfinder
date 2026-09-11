<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'PetFinder') }}</title>

    <!-- Favicon (2 patinhas) -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap 5 CDN (latest) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-brand { font-weight: 800; color: #0d6efd; }
        .main-content { flex-grow: 1; padding: 2rem 0; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3" href="/" style="display:flex;align-items:center;gap:.4rem;"><svg width="38" height="28" viewBox="0 0 190 140" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;"><g transform="translate(5,0) rotate(-12,50,75)"><ellipse cx="50" cy="80" rx="20" ry="22"/><ellipse cx="21" cy="52" rx="9" ry="13" transform="rotate(-20 21 52)"/><ellipse cx="37" cy="41" rx="8" ry="12" transform="rotate(-5 37 41)"/><ellipse cx="63" cy="41" rx="8" ry="12" transform="rotate(5 63 41)"/><ellipse cx="79" cy="52" rx="9" ry="13" transform="rotate(20 79 52)"/></g><g transform="translate(95,18) rotate(12,50,75)"><ellipse cx="50" cy="80" rx="22" ry="24"/><ellipse cx="20" cy="50" rx="10" ry="14" transform="rotate(-20 20 50)"/><ellipse cx="37" cy="39" rx="9" ry="13" transform="rotate(-5 37 39)"/><ellipse cx="63" cy="39" rx="9" ry="13" transform="rotate(5 63 39)"/><ellipse cx="80" cy="50" rx="10" ry="14" transform="rotate(20 80 50)"/></g></svg>PetFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Entrar</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm rounded-pill px-3 mt-2 mt-lg-0" href="{{ route('register') }}">Cadastrar</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Meus Pets</a></li>

                        {{-- Notification Bell --}}
                        <li class="nav-item dropdown px-1">
                            <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                                🔔 Notificações
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="badge rounded-pill bg-danger" style="font-size:.6rem;">
                                        {{ Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="min-width:300px;">
                                <li class="dropdown-header border-bottom py-2">Notificações</li>
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <li>
                                        <a class="dropdown-item py-3 border-bottom small" href="{{ route('pets.public', $notification->data['pet_uuid']) }}">
                                            <strong class="d-block text-danger">🚨 Alerta Próximo</strong>
                                            {{ $notification->data['mensagem'] }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="dropdown-item text-center text-muted py-3 small">Nenhum alerta recente</li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">Sair</button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            {{ $slot }}
        </div>
    </main>

    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center text-muted">
            <small>&copy; {{ date('Y') }} PetFinder &mdash; Conectando pets e heróis.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(r => console.log('SW registrado', r))
                    .catch(e => console.log('SW erro', e));
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

