<x-app-layout>
    <x-slot name="title">Mapa de Localização - {{ $pet->nome }}</x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            #map-full {
                width: 100%;
                aspect-ratio: 1 / 1;
                border-radius: 16px;
            }
        </style>
    @endpush

    <div class="container py-3">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('pets.public', $pet->uuid) }}" class="btn btn-secondary me-3 px-3">
                Voltar
            </a>
            <div>
                <h4 class="fw-bold mb-0">Mapa de Busca: {{ $pet->nome }}</h4>
                <p class="text-muted small mb-0">Área de aproximação (~100m de margem de privacidade)</p>
            </div>
        </div>

        @php
            $rawLat = $alert ? $alert->latitude_fuga : ($pet->latitude ?? $pet->user->latitude ?? -23.5505);
            $rawLng = $alert ? $alert->longitude_fuga : ($pet->longitude ?? $pet->user->longitude ?? -46.6333);
        @endphp

        <div class="card shadow-sm border-0 overflow-hidden mb-4">
            <div class="card-body p-0">
                <div id="map-full"></div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('pets.public', $pet->uuid) }}" class="btn btn-primary btn-lg px-4">
                Voltar para Página do Pet
            </a>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rawLat = {{ $rawLat }};
                const rawLng = {{ $rawLng }};

                // Arredondamento para 3 casas decimais (grid de ~100m) para privacidade
                const lat = Number(parseFloat(rawLat).toFixed(3));
                const lng = Number(parseFloat(rawLng).toFixed(3));

                // Zoom reduzido para 14 para visualizar melhor o raio maior
                const map = L.map('map-full').setView([lat, lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                L.circle([lat, lng], {
                    color: '#d9534f',
                    fillColor: '#f0ad4e',
                    fillOpacity: 0.35,
                    radius: 1000 // 1km de raio
                }).addTo(map).bindPopup("<b>Região de Busca</b><br>Última localização aproximada.").openPopup();
            });
        </script>
    @endpush
</x-app-layout>
