<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card premium-card overflow-hidden">
                <img src="{{ asset('storage/' . $pet->foto) }}"
                     class="img-fluid"
                     alt="{{ $pet->nome }}"
                     style="width: 100%; height: 400px; object-fit: cover;">

                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <span class="badge {{ $pet->status == 'seguro' ? 'bg-success' : 'bg-danger' }} fs-6 p-2">
                            {{ $pet->status == 'seguro' ? 'ESTOU SEGURO' : 'ESTOU PERDIDO! 🚨' }}
                        </span>
                    </div>

                    <h1 class="display-6 fw-bold mb-0">{{ $pet->nome }}</h1>
                    <p class="text-muted fs-5">{{ $pet->especie }} | {{ $pet->raca }} | {{ $pet->cor }}</p>

                    @if($pet->condicoes_especiais)
                        <div class="alert alert-warning py-2 text-start">
                            <strong>⚠️ Condições Especiais:</strong><br>
                            {{ $pet->condicoes_especiais }}
                        </div>
                    @endif

                    <hr class="my-4">

                    <h5 class="mb-3">Encontrou este pet?</h5>
                    <p class="text-muted">
                        Por favor, entre em contato com
                        <strong>{{ explode(' ', $pet->user->name)[0] }}</strong> (Responsável)
                    </p>

                    <div class="d-grid gap-3">
                        <a href="https://wa.me/55{{ preg_replace('/\D/', '', $pet->user->telefone) }}"
                           class="btn btn-success btn-lg"
                           target="_blank" rel="noopener noreferrer">
                            💬 Falar via WhatsApp
                        </a>
                        <a href="tel:+55{{ preg_replace('/\D/', '', $pet->user->telefone) }}"
                           class="btn btn-outline-primary btn-lg">
                            📞 Ligar para Responsável
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/" class="text-decoration-none text-muted small">🐾 PetFinder — Conectando pets e heróis</a>
            </div>
        </div>
    </div>
</x-app-layout>
