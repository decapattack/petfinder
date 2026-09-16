<x-app-layout>
    <x-slot name="title">Editar Pet: {{ $pet->nome }} - PetFinder</x-slot>

    @push('styles')
    <style>
        .border-dashed:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd !important;
        }
        .media-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 160px;
            background-color: #f0f0f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .media-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-container .delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 10;
            background-color: #dc3545;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, transform 0.1s;
        }
        .media-container .delete-btn:hover {
            background-color: rgb(220, 53, 69);
            transform: scale(1.1);
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
    @endpush

    <div class="row justify-content-center">
        <div class="col-md-11">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary me-3 px-3">
                    Voltar
                </a>
                <div>
                    <h2 class="mb-0 fw-bold">Editar Pet: {{ $pet->nome }}</h2>
                    <p class="text-muted mb-0">Atualize os dados cadastrais, fotos e vídeos incorporados de identificação.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                <!-- Coluna da Esquerda: Formulário de Dados -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">Dados Cadastrais</h4>

                            <form action="{{ route('pets.update', $pet) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Nome do Pet</label>
                                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                           value="{{ old('nome', $pet->nome) }}" required>
                                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Espécie</label>
                                    <select name="especie" class="form-select @error('especie') is-invalid @enderror" required>
                                        <option value="Cachorro" {{ old('especie', $pet->especie) == 'Cachorro' ? 'selected' : '' }}>Cachorro</option>
                                        <option value="Gato"     {{ old('especie', $pet->especie) == 'Gato'     ? 'selected' : '' }}>Gato</option>
                                        <option value="Outro"    {{ old('especie', $pet->especie) == 'Outro'    ? 'selected' : '' }}>Outro</option>
                                    </select>
                                    @error('especie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Raça</label>
                                    <input type="text" name="raca" class="form-control @error('raca') is-invalid @enderror"
                                           value="{{ old('raca', $pet->raca) }}" required>
                                    @error('raca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Cor Predominante</label>
                                    <input type="text" name="cor" class="form-control @error('cor') is-invalid @enderror"
                                           value="{{ old('cor', $pet->cor) }}" required>
                                    @error('cor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small">Condições Especiais (Opcional)</label>
                                    <textarea name="condicoes_especiais" class="form-control @error('condicoes_especiais') is-invalid @enderror"
                                              rows="3" placeholder="Precisa de cuidados específicos?">{{ old('condicoes_especiais', $pet->condicoes_especiais) }}</textarea>
                                    @error('condicoes_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Visibilidade Pública -->
                                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_public_edit" name="is_public" value="1"
                                               {{ old('is_public', $pet->is_public) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="is_public_edit">
                                            Página Pública Ativada (QR Code e link público)
                                        </label>
                                        <div class="form-text mt-1" style="font-size: 0.75rem;">
                                            Se desmarcado, visitantes externos receberão erro 404 a menos que haja um alerta de desaparecimento ativo.
                                        </div>
                                    </div>
                                </div>

                                <!-- Localização Residencial do Pet -->
                                @php
                                    $currentLat = old('latitude', $pet->latitude ?? auth()->user()->latitude);
                                    $currentLng = old('longitude', $pet->longitude ?? auth()->user()->longitude);
                                @endphp
                                <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="fw-bold d-flex align-items-center">
                                            Localização Residencial
                                        </div>
                                        <span class="badge {{ $currentLat && $currentLng ? 'bg-success' : 'bg-secondary' }}" id="petGpsBadge">
                                            {{ $currentLat && $currentLng ? 'Definida' : 'Não definida' }}
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Ponto de busca padrão em alertas residenciais. Pode ser atualizado a qualquer momento.
                                    </p>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center" onclick="capturePetGps()" id="btnCaptureGps">
                                            <span id="btnCaptureGpsText">Atualizar via GPS</span>
                                        </button>
                                        <small class="text-muted" id="petGpsCoords">
                                            @if($currentLat && $currentLng)
                                                (Coordenadas salvas no cadastro)
                                            @endif
                                        </small>
                                    </div>
                                    <div id="petGpsFeedback" class="alert alert-warning py-1 px-2 small mt-2 mb-0" style="display: none;"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label for="pet_latitude" class="form-label small">Latitude</label>
                                        <input type="number" step="any" name="latitude" id="pet_latitude" class="form-control form-control-sm" value="{{ $currentLat }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="pet_longitude" class="form-label small">Longitude</label>
                                        <input type="number" step="any" name="longitude" id="pet_longitude" class="form-control form-control-sm" value="{{ $currentLng }}">
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Coluna da Direita: Gerenciador de Mídias -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h4 class="fw-bold mb-1">Mídias do Pet</h4>
                                    <p class="text-muted small mb-0">Fotos enviadas e vídeos externos incorporados.</p>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                                    + Link de Vídeo
                                </button>
                            </div>

                            <div class="row g-3">
                                <!-- Listagem das Mídias Atuais -->
                                @forelse($pet->media as $mediaItem)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="media-container position-relative">
                                            <!-- Formulário para exclusão individual -->
                                            <form action="{{ route('pets.media.destroy', [$pet, $mediaItem]) }}" method="POST" onsubmit="return confirm('Deseja realmente remover esta mídia?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="delete-btn" title="Remover mídia">
                                                    &times;
                                                </button>
                                            </form>

                                            @if($mediaItem->type === 'video')
                                                @php $embedData = $mediaItem->embed_data; @endphp
                                                @if(!empty($embedData['thumbnail_url']))
                                                    <img src="{{ $embedData['thumbnail_url'] }}" alt="Vídeo {{ $pet->nome }}">
                                                @else
                                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-dark text-white p-2 text-center">
                                                        
                                                        <span class="small text-truncate w-100">{{ ucfirst($embedData['platform'] ?? 'Vídeo') }}</span>
                                                    </div>
                                                @endif
                                                <div class="position-absolute bottom-0 start-0 m-2 bg-dark bg-opacity-75 text-white rounded px-2 py-1 small">
                                                     {{ ucfirst($embedData['platform'] ?? 'Vídeo') }}
                                                </div>
                                            @else
                                                <img src="{{ $mediaItem->url }}" alt="Foto do pet">
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-3 text-center text-muted">
                                        Nenhuma mídia cadastrada ainda.
                                    </div>
                                @endforelse

                                <!-- Card para Envio Rápido de Fotos -->
                                <div class="col-md-4 col-sm-6">
                                    <form action="{{ route('pets.media.store', $pet) }}" method="POST" enctype="multipart/form-data" id="addMediaForm">
                                        @csrf
                                        <label class="card justify-content-center align-items-center border-dashed cursor-pointer w-100" 
                                               style="border: 2px dashed #0d6efd; border-radius: 12px; height: 160px; transition: all 0.2s;">
                                            <input type="file" name="media[]" class="d-none" multiple accept="image/jpeg,image/png,image/webp,image/bmp,image/gif" 
                                                   onchange="document.getElementById('addMediaForm').submit()">
                                            <div class="text-center text-primary">
                                                
                                                <div class="small fw-bold">+ Adicionar Fotos</div>
                                            </div>
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Link de Vídeo -->
    <div class="modal fade" id="addVideoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Adicionar Link de Vídeo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pets.media.store', $pet) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">
                            Você pode incorporar vídeos de plataformas públicas como <strong>YouTube</strong> (vídeos e Shorts), <strong>Instagram</strong> (Reels) ou <strong>TikTok</strong>.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link do Vídeo</label>
                            <input type="url" name="video_url" class="form-control" required placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Vídeo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function capturePetGps() {
            const btn = document.getElementById('btnCaptureGps');
            const btnText = document.getElementById('btnCaptureGpsText');
            const badge = document.getElementById('petGpsBadge');
            const coordsText = document.getElementById('petGpsCoords');
            const feedback = document.getElementById('petGpsFeedback');

            feedback.style.display = 'none';

            if (!navigator.geolocation) {
                feedback.textContent = 'Seu navegador não suporta geolocalização.';
                feedback.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btnText.textContent = 'Obtendo GPS...';

            const geoOptions = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    document.getElementById('pet_latitude').value = pos.coords.latitude;
                    document.getElementById('pet_longitude').value = pos.coords.longitude;
                    btn.disabled = false;
                    btnText.textContent = 'Atualizar via GPS';
                    badge.className = 'badge bg-success';
                    badge.textContent = 'Definida via GPS';
                    coordsText.textContent = '(Localização atual capturada com alta precisão!)';
                },
                function(err) {
                    btn.disabled = false;
                    btnText.textContent = 'Atualizar via GPS';
                    let errorMsg = 'Não foi possível obter a localização exata.';
                    if (err.code === 1) {
                        errorMsg = 'Permissão negada. Permita o acesso à localização no navegador.';
                    } else if (err.code === 2) {
                        errorMsg = 'Sinal de GPS indisponível no momento.';
                    } else if (err.code === 3) {
                        errorMsg = 'Tempo limite excedido ao buscar GPS de alta precisão.';
                    }
                    feedback.textContent = errorMsg;
                    feedback.style.display = 'block';
                },
                geoOptions
            );
        }
    </script>
    @endpush
</x-app-layout>
