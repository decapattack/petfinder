@extends('layouts.base')

@section('title', config('app.name', 'Laravel'))

@section('content')
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center pt-4 bg-light">
        <div class="mb-4">
            <a href="/" class="text-decoration-none">
                <x-application-logo class="w-20 h-20" />
            </a>
        </div>

        <div class="w-100" style="max-width: 400px;">
            <div class="card shadow">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
@endsection
