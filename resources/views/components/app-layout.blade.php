<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'PetFinder') }}</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <style>
        :root { --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-brand { font-weight: 800; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .premium-card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); transition: transform 0.25s ease; }
        .premium-card:hover { transform: translateY(-4px); }
        .btn-premium { background: var(--primary-gradient); border: none; color: white; border-radius: 50px; font-weight: 600; padding: 10px 28px; }
        .btn-premium:hover { opacity: 0.88; color: white; }
        .main-content { flex-grow: 1; padding: 2rem 0; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3" href="/">🐾 PetFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-premium btn-sm" href="{{ route('register') }}">Cadastrar</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('pets.index') }}">Meus Pets</a></li>

                        {{-- Notification Bell --}}
                        <li class="nav-item dropdown px-1">
                            <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                                🔔
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
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

                        <li class="nav-item ms-lg-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Sair</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
