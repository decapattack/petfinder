<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'PetFinder' }}</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); }
        body { background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-brand { font-weight: 700; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .premium-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s ease; }
        .btn-premium { background: var(--primary-gradient); border: none; color: white; border-radius: 50px; font-weight: 600; padding: 10px 25px; }
        .btn-premium:hover { opacity: 0.9; color: white; }
        .main-content { flex-grow: 1; padding: 2rem 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3" href="/">🐾 PetFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#authNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="authNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="btn btn-premium btn-sm ms-lg-3" href="{{ route('register') }}">Cadastrar</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('pets.index') }}">Meus Pets</a></li>
                        
                        <!-- Notificações -->
                        <li class="nav-item dropdown px-2">
                            <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                                🔔
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 300px;">
                                <li class="dropdown-header">Notificações de Pets</li>
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <li><a class="dropdown-item py-3 border-bottom small" href="{{ route('pets.public', $notification->data['pet_uuid']) }}">
                                        🚨 ALERTA PRÓXIMO: {{ $notification->data['mensagem'] }}
                                    </a></li>
                                @empty
                                    <li class="dropdown-item text-center text-muted py-3">Nenhum alerta</li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item ms-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link text-danger">Sair</button>
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
            {{ $slot }}
        </div>
    </main>

    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center text-muted">
            <small>&copy; {{ date('Y') }} PetFinder. Todos os direitos reservados.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
