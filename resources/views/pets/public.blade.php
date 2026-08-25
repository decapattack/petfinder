<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm overflow-hidden">
                <!-- Media Carousel -->
                <div id="petMediaCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner">
                        @forelse($pet->media as $index => $item)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                @if($item->type === 'video')
                                    <video src="{{ asset('storage/' . $item->path) }}" class="d-block w-100" style="height: 400px; object-fit: cover;" controls autoplay muted playsinline></video>
                                @else
                                    <img src="{{ asset('storage/' . $item->path) }}" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="{{ $pet->nome }}">
                                @endif
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
                                    <i class="bi bi-image" style="font-size: 4rem; color: rgba(0,0,0,.15);"></i>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if($pet->media->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#petMediaCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#petMediaCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Próximo</span>
                        </button>

                        <div class="carousel-indicators position-absolute" style="bottom: 10px;">
                            @foreach($pet->media as $index => $item)
                                <button type="button" data-bs-target="#petMediaCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

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

                    @if($pet->status == 'desaparecido' && $pet->user->telefone && $pet->user->show_phone_on_public_page)
                        <p class="text-muted">
                            Por favor, entre em contato com
                            <strong>{{ explode(' ', $pet->user->name)[0] }}</strong> (Responsável)
                        </p>

                        @php
                            $phoneDigits = preg_replace('/\D/', '', $pet->user->telefone);
                            $maskedPhone = strlen($phoneDigits) > 4
                                ? substr($phoneDigits, 0, -4) . '****'
                                : $phoneDigits;
                        @endphp

                        <div class="d-grid gap-3">
                            <a href="https://wa.me/55{{ $phoneDigits }}"
                               class="btn btn-success btn-lg"
                               target="_blank" rel="noopener noreferrer">
                                💬 Falar via WhatsApp
                            </a>
                            <a href="tel:+55{{ $phoneDigits }}"
                               class="btn btn-outline-primary btn-lg">
                                📞 Ligar para Responsável
                            </a>
                        </div>
                    @elseif($pet->status == 'desaparecido')
                        <div class="alert alert-warning py-3">
                            Este pet está perdido. O responsável optou por não exibir o telefone público nesta página.
                        </div>
                    @else
                        <div class="alert alert-info py-3">
                            Este pet não está com alerta ativo no momento.
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/" class="text-decoration-none text-muted small">🐾 PetFinder — Conectando pets e heróis</a>
            </div>
        </div>
    </div>
</x-app-layout>

