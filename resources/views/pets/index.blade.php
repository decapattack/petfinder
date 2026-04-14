<x-app-layout>
    <x-slot name="title">Meus Pets - PetFinder</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🐾 Meus Pets</h2>
        <a href="{{ route('pets.create') }}" class="btn btn-premium">+ Cadastrar Pet</a>
    </div>

    <div class="row">
        @forelse($pets as $pet)
            <div class="col-md-4 mb-4">
                <div class="card premium-card h-100 {{ $pet->status == 'desaparecido' ? 'border-danger border-3 shadow' : '' }}">
                    <img src="{{ asset('storage/' . $pet->foto) }}" class="card-img-top" alt="{{ $pet->nome }}" style="height: 200px; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $pet->nome }}</h5>
                        <p class="card-text text-muted">{{ $pet->especie }} - {{ $pet->raca }}</p>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge {{ $pet->status == 'seguro' ? 'bg-success' : 'bg-danger animate-pulse' }}">
                                {{ $pet->status == 'seguro' ? 'SEGURO' : '🚨 DESAPARECIDO' }}
                            </span>
                        </div>

                        <div class="bg-light p-3 rounded text-center mb-3">
                            <div class="mb-2 small fw-bold">QR Code de Identificação</div>
                            {!! QrCode::size(120)->generate(url('/pet/' . $pet->uuid)) !!}
                        </div>

                        <div class="d-grid gap-2">
                            @if($pet->status == 'seguro')
                                <form action="{{ route('alerts.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm w-100 mb-2">🚨 Emitir Alerta (1 KM)</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-success btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $pet->id }}">
                                    ✅ Encontrei meu Pet!
                                </button>

                                <!-- Modal Resolvido -->
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
                                                    <p>Ficamos muito felizes! Alguém na plataforma te ajudou a encontrar o {{ $pet->nome }}?</p>
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
                            
                            <a href="{{ url('/pet/' . $pet->uuid) }}" class="btn btn-outline-primary btn-sm" target="_blank">Ver Página Pública</a>
                            
                            <form action="{{ route('pets.destroy', $pet) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger btn-sm w-100 mt-2 p-0 text-decoration-none small">Remover Pet</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">Nenhum pet cadastrado.</h4>
                <a href="{{ route('pets.create') }}" class="btn btn-premium mt-3">Cadastrar agora</a>
            </div>
        @endforelse
    </div>
</x-app-layout>
