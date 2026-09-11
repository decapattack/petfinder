<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PetFinder') }}</title>

    <!-- Favicon (2 patinhas) -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .auth-card { border: none; border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        .brand-gradient { color: #0d6efd; font-weight: 800; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="text-center mb-4">
                    <a href="/" class="text-decoration-none">
                        <svg width="80" height="60" viewBox="0 0 190 140" fill="#0d6efd" xmlns="http://www.w3.org/2000/svg"><g transform="translate(5,0) rotate(-12,50,75)"><ellipse cx="50" cy="80" rx="20" ry="22"/><ellipse cx="21" cy="52" rx="9" ry="13" transform="rotate(-20 21 52)"/><ellipse cx="37" cy="41" rx="8" ry="12" transform="rotate(-5 37 41)"/><ellipse cx="63" cy="41" rx="8" ry="12" transform="rotate(5 63 41)"/><ellipse cx="79" cy="52" rx="9" ry="13" transform="rotate(20 79 52)"/></g><g transform="translate(95,18) rotate(12,50,75)"><ellipse cx="50" cy="80" rx="22" ry="24"/><ellipse cx="20" cy="50" rx="10" ry="14" transform="rotate(-20 20 50)"/><ellipse cx="37" cy="39" rx="9" ry="13" transform="rotate(-5 37 39)"/><ellipse cx="63" cy="39" rx="9" ry="13" transform="rotate(5 63 39)"/><ellipse cx="80" cy="50" rx="10" ry="14" transform="rotate(20 80 50)"/></g></svg>
                        <h1 class="fs-3 brand-gradient mb-0">PetFinder</h1>
                    </a>
                </div>
                <div class="card auth-card p-4 p-md-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
