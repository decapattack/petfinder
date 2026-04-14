<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PetFinder') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .auth-card { border: none; border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .brand-gradient { background: linear-gradient(135deg, #0d6efd, #6610f2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <a href="/" class="text-decoration-none">
                        <span class="fs-1">🐾</span>
                        <h1 class="fs-3 brand-gradient mb-0">PetFinder</h1>
                    </a>
                </div>
                <div class="card auth-card p-4 p-md-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
