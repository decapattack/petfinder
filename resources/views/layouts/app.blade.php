@extends('layouts.base')

@section('title', config('app.name', 'Laravel'))

@section('content')
    @include('layouts.navigation')

    <div class="container py-4">
        <!-- Page Heading -->
        @isset($header)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-4">
                        {{ $header }}
                    </div>
                </div>
            </div>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
@endsection
