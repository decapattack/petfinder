<x-app-layout>
    <x-slot name="title">Meus Pets - PetFinder</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🐾 Meus Pets</h2>
        <div class="d-flex gap-2">
            <form action="{{ route('alerts.test') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Testar Notificação</button>
            </form>
            <a href="{{ route('pets.create') }}" class="btn btn-primary">+ Cadastrar Pet</a>
        </div>
    </div>

    <div class="row">
        @forelse($pets as $pet)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 {{ $pet->status == 'desaparecido' ? 'border-danger border-3 shadow' : '' }}">
                    @if($pet->cover_photo)
                        @if($pet->cover_photo->type === 'video')
                            @php $embedData = $pet->cover_photo->embed_data; @endphp
                            @if(!empty($embedData['thumbnail_url']))
                                <img src="{{ $embedData['thumbnail_url'] }}" class="card-img-top" alt="{{ $pet->nome }}" style="height: 200px; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            @else
                                <div class="card-img-top d-flex flex-column align-items-center justify-content-center bg-dark text-white" style="height: 200px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                    
                                    <span class="small">{{ ucfirst($embedData['platform'] ?? 'Vídeo') }}</span>
                                </div>
                            @endif
                        @else
                            <img src="{{ asset('storage/' . $pet->cover_photo->path) }}" class="card-img-top" alt="{{ $pet->nome }}" style="height: 200px; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        @endif
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            @auth
                                <a href="{{ route('pets.edit', $pet) }}" class="text-decoration-none text-dark link-primary">{{ $pet->nome }}</a>
                            @else
                                <a href="{{ url('/pet/' . $pet->uuid) }}" class="text-decoration-none text-dark link-primary">{{ $pet->nome }}</a>
                            @endauth
                        </h5>
                        <p class="card-text text-muted">{{ $pet->especie }} - {{ $pet->raca }}</p>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge {{ $pet->status == 'seguro' ? 'bg-success' : 'bg-danger animate-pulse' }}">
                                {{ $pet->status == 'seguro' ? 'SEGURO' : 'DESAPARECIDO' }}
                            </span>
                            @if(!$pet->is_public)
                                <span class="badge bg-secondary ms-2" title="Página pública desativada para visitantes">
                                    Privado
                                </span>
                            @endif
                        </div>

                        <div class="bg-light p-3 rounded text-center mb-3">
                            <div class="mb-2 small fw-bold">QR Code de Identificação</div>
                            {!! QrCode::size(120)->generate(url('/pet/' . $pet->uuid)) !!}
                        </div>

                        <div class="d-grid gap-2">
                            @if($pet->status == 'seguro')
                                <button type="button" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#emitAlertModal{{ $pet->id }}">
                                    Emitir Alerta (1 KM)
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $pet->id }}">
                                    Encontrei meu Pet!
                                </button>
                            @endif

                            <div class="d-flex gap-2">
                                <a href="{{ route('pets.health', $pet) }}" class="btn btn-success btn-sm flex-fill">
                                    Saúde
                                </a>
                                <a href="{{ url('/pet/' . $pet->uuid) }}" class="btn btn-primary btn-sm flex-fill" target="_blank">Pública</a>
                            </div>

                            <form action="{{ route('pets.destroy', $pet) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger btn-sm w-100 p-0 text-decoration-none small">Remover Pet</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Emitir Alerta --}}
                @if($pet->status == 'seguro')
                <div class="modal fade" id="emitAlertModal{{ $pet->id }}" tabindex="-1" aria-labelledby="emitAlertLabel{{ $pet->id }}" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="modal-title fw-bold text-dark" id="emitAlertLabel{{ $pet->id }}"> Fugiu de onde?</h4>
                                    <p class="text-muted small mb-0">Ponto de partida das buscas por <strong>{{ $pet->nome }}</strong>:</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body p-4">
                                <!-- Opção 1: Casa -->
                                <form action="{{ route('alerts.store') }}" method="POST" id="alertHomeForm{{ $pet->id }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                                    <input type="hidden" name="origem" value="casa">
                                    <button type="submit" class="btn btn-primary w-100 p-3 text-start shadow-sm border-0 d-flex align-items-center justify-content-between transition-hover">
                                        <div>
                                            <div class="fw-bold fs-5 text-white">Casa (Endereço Cadastrado)</div>
                                            <small class="text-white-50 d-block">Usa a localização da residência salva no cadastro.</small>
                                        </div>
                                        <span class="fs-5 text-white">&rarr;</span>
                                    </button>
                                </form>

                                <!-- Opção 2: Rua -->
                                <form action="{{ route('alerts.store') }}" method="POST" id="alertStreetForm{{ $pet->id }}">
                                    @csrf
                                    <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                                    <input type="hidden" name="origem" value="rua">
                                    <input type="hidden" name="latitude" id="streetLat{{ $pet->id }}">
                                    <input type="hidden" name="longitude" id="streetLng{{ $pet->id }}">
                                    <button type="button" class="btn btn-danger w-100 p-3 text-start shadow-sm border-0 d-flex align-items-center justify-content-between transition-hover" onclick="triggerStreetAlert({{ $pet->id }})" id="btnStreetAlert{{ $pet->id }}">
                                        <div>
                                            <div class="fw-bold fs-5 text-white" id="streetTitle{{ $pet->id }}">Rua (Localização Atual)</div>
                                            <small class="text-white-50 d-block" id="streetSubtitle{{ $pet->id }}">Captura onde você está agora pelo GPS.</small>
                                        </div>
                                        <span class="fs-5 text-white" id="streetArrow{{ $pet->id }}">&rarr;</span>
                                    </button>
                                    <div id="streetFeedback{{ $pet->id }}" class="alert alert-warning py-2 px-3 small mt-3 rounded-3" style="display: none;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                {{-- Modal Resolver Alerta --}}
                <div class="modal fade" id="resolveModal{{ $pet->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Encerrar Alerta: {{ $pet->nome }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('alerts.resolve', $pet->active_alert ?? 0) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p>Alguém na plataforma te ajudou a encontrar o {{ $pet->nome }}?</p>
                                    <div class="mb-3">
                                        <label class="form-label text-start d-block">E-mail do Herói (Opcional)</label>
                                        <input type="email" name="hero_email" class="form-control" placeholder="heroi@email.com">
                                        <small class="text-muted">Ele ganhará +50 pontos de herói!</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success w-100">Finalizar Alerta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">Nenhum pet cadastrado.</h4>
                <a href="{{ route('pets.create') }}" class="btn btn-primary mt-3">Cadastrar agora</a>
            </div>
        @endforelse
    </div>

    @push('styles')
    <style>
        /* Fundo escuro com leve desfoque para destacar o modal */
        .modal-backdrop.show {
            background-color: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        .transition-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .transition-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function triggerStreetAlert(petId) {
            const btn = document.getElementById('btnStreetAlert' + petId);
            const title = document.getElementById('streetTitle' + petId);
            const subtitle = document.getElementById('streetSubtitle' + petId);
            const iconContainer = document.getElementById('streetIconContainer' + petId);
            const feedback = document.getElementById('streetFeedback' + petId);
            const form = document.getElementById('alertStreetForm' + petId);

            feedback.style.display = 'none';

            if (!navigator.geolocation) {
                feedback.textContent = 'Seu navegador não suporta geolocalização. Por favor, utilize a opção "Casa".';
                feedback.style.display = 'block';
                return;
            }

            // Estado de carregamento
            btn.disabled = true;
            title.textContent = 'Obtendo coordenadas do GPS...';
            subtitle.textContent = 'Aguardando precisão do satélite/aparelho...';
            iconContainer.innerHTML = '<span class="spinner-border spinner-border-sm text-danger" role="status"></span>';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    document.getElementById('streetLat' + petId).value = pos.coords.latitude;
                    document.getElementById('streetLng' + petId).value = pos.coords.longitude;
                    title.textContent = 'Localização obtida!';
                    subtitle.textContent = 'Emitindo alerta na região...';
                    form.submit();
                },
                function(err) {
                    btn.disabled = false;
                    title.textContent = 'Rua (Localização Atual)';
                    subtitle.textContent = 'Captura onde você está agora pelo GPS do celular/computador.';
                    iconContainer.innerHTML = '';

                    let errorMsg = 'Não foi possível obter sua localização atual.';
                    if (err.code === 1) {
                        errorMsg = 'Permissão de localização negada pelo navegador. Ative a localização nas permissões do site ou use a opção "Casa".';
                    } else if (err.code === 2) {
                        errorMsg = 'Sinal de GPS indisponível no momento. Tente novamente ou use a opção "Casa".';
                    } else if (err.code === 3) {
                        errorMsg = 'Tempo limite excedido ao buscar GPS. Tente novamente.';
                    }
                    feedback.textContent = errorMsg;
                    feedback.style.display = 'block';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    </script>
    @endpush
</x-app-layout>

