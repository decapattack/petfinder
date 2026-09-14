<x-app-layout>
    <x-slot name="title">{{ $pet->nome }} - PetFinder</x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            #map-desktop {
                width: 100%;
                aspect-ratio: 1 / 1;
                border-radius: 12px;
            }
        </style>
    @endpush

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm overflow-hidden">
                <!-- Media Carousel -->
                <div id="petMediaCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner">
                        @forelse($pet->media as $index => $item)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                @if($item->type === 'video')
                                    @php $embedUrl = $item->embed_url; @endphp
                                    @if($embedUrl)
                                        <div class="d-flex align-items-center justify-content-center bg-black" style="height: 400px;">
                                            <iframe src="{{ $embedUrl }}" 
                                                    class="w-100 h-100 border-0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen>
                                            </iframe>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-4" style="height: 400px;">
                                            
                                            <a href="{{ $item->path }}" target="_blank" class="btn btn-light btn-sm">
                                                 Abrir Vídeo
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <img src="{{ asset('storage/' . $item->path) }}" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="{{ $pet->nome }}">
                                @endif
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
                                    
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
                    @if(!$pet->is_public && $pet->status !== 'desaparecido')
                        <div class="alert alert-secondary py-2 px-3 mb-3 text-start small d-flex align-items-center rounded-3">
                            <div>
                                <strong>Modo de Pré-visualização do Tutor:</strong> Esta página está <u>desativada para o público</u>. Visitantes não autorizados receberão erro 404.
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <span class="badge {{ $pet->status == 'seguro' ? 'bg-success' : 'bg-danger' }} fs-6 p-2">
                            {{ $pet->status == 'seguro' ? 'ESTOU SEGURO' : 'ESTOU PERDIDO!' }}
                        </span>
                    </div>

                    <h1 class="display-6 fw-bold mb-0">{{ $pet->nome }}</h1>
                    <p class="text-muted fs-5">{{ $pet->especie }} | {{ $pet->raca }} | {{ $pet->cor }}</p>

                    @if($pet->condicoes_especiais)
                        <div class="alert alert-warning py-2 text-start">
                            <strong>Condições Especiais:</strong><br>
                            {{ $pet->condicoes_especiais }}
                        </div>
                    @endif

                    @php
                        $rawLat = isset($alert) && $alert ? $alert->latitude_fuga : ($pet->latitude ?? $pet->user->latitude ?? null);
                        $rawLng = isset($alert) && $alert ? $alert->longitude_fuga : ($pet->longitude ?? $pet->user->longitude ?? null);
                    @endphp

                    @if($rawLat && $rawLng)
                        <!-- Exibição do Mapa: PC na mesma página | Mobile com botão para nova página -->
                        <div class="my-3">
                            <!-- Mapa para PC (Desktop: d-none d-md-block) -->
                            <div class="d-none d-md-block border rounded-3 p-3 bg-light text-start">
                                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center">
                                    Região do Desaparecimento
                                </h6>
                                <div id="map-desktop"></div>
                                <small class="text-muted d-block mt-1">Margem de segurança ~100m para proteção de privacidade.</small>
                            </div>

                            <!-- Botão para Mobile (d-md-none) -->
                            <div class="d-md-none">
                                <a href="{{ route('pets.public.map', $pet->uuid) }}" class="btn btn-primary btn-lg w-100 shadow-sm py-3 fw-bold">
                                    Exibir no Mapa
                                </a>
                            </div>
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
                                Falar via WhatsApp
                            </a>
                            <a href="tel:+55{{ $phoneDigits }}"
                               class="btn btn-primary btn-lg">
                                Ligar para Responsável
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

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const desktopMapEl = document.getElementById('map-desktop');
                const rawLat = {{ $rawLat ?? 'null' }};
                const rawLng = {{ $rawLng ?? 'null' }};

                if (desktopMapEl && rawLat !== null && rawLng !== null) {
                    // Arredondamento para 3 casas decimais (grid de ~100m) para privacidade
                    const lat = Number(parseFloat(rawLat).toFixed(3));
                    const lng = Number(parseFloat(rawLng).toFixed(3));

                    const map = L.map('map-desktop').setView([lat, lng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    L.circle([lat, lng], {
                        color: '#d9534f',
                        fillColor: '#f0ad4e',
                        fillOpacity: 0.35,
                        radius: 1000
                    }).addTo(map).bindPopup("<b>Região do Desaparecimento</b>");
                }
            });
        </script>
    @endpush
</x-app-layout>
