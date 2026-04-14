@extends('layouts.app')

@section('title', 'Verificar E-mail - PetFinder')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card premium-card">
            <div class="card-body p-5 text-center">
                <h2 class="mb-4">Verifique seu E-mail</h2>
                <p class="text-muted">
                    Obrigado por se cadastrar! Antes de começar, por favor verifique seu endereço de e-mail clicando no link que acabamos de enviar para você.
                </p>
                <p class="text-muted">
                    Se você não recebeu o e-mail, teremos prazer em lhe enviar outro.
                </p>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-4 d-flex justify-content-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-premium">
                            Reenviar E-mail de Verificação
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-decoration-none text-muted">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
