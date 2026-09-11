<x-app-layout>
    <x-slot name="title">Cadastrar Pet - PetFinder</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="mb-4">Cadastrar Novo Pet</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome do Pet</label>
                                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                       value="{{ old('nome') }}" required placeholder="Ex: Rex">
                                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Espécie</label>
                                <select name="especie" class="form-select @error('especie') is-invalid @enderror" required>
                                    <option value="Cachorro" {{ old('especie') == 'Cachorro' ? 'selected' : '' }}>Cachorro</option>
                                    <option value="Gato"     {{ old('especie') == 'Gato'     ? 'selected' : '' }}>Gato</option>
                                    <option value="Outro"    {{ old('especie') == 'Outro'    ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('especie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Raça</label>
                                <input type="text" name="raca" class="form-control @error('raca') is-invalid @enderror"
                                       value="{{ old('raca') }}" required placeholder="Ex: Labrador">
                                @error('raca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cor Predominante</label>
                                <input type="text" name="cor" class="form-control @error('cor') is-invalid @enderror"
                                       value="{{ old('cor') }}" required placeholder="Ex: Caramelo">
                                @error('cor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Upload de Imagens -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fotos do Pet (Imagens)</label>
                            <input type="file" name="media[]" class="form-control @error('media') is-invalid @enderror @error('media.*') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp,image/bmp,image/gif" multiple>
                            <div class="form-text">
                                Formatos aceitos: JPG, PNG, WEBP, BMP ou GIF. As fotos são automaticamente otimizadas e redimensionadas proporcionalmente (máx 1920x1080) em alta qualidade.
                            </div>
                            @error('media')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('media.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <!-- Link de Vídeo Externo (Embed) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vídeo do Pet (Opcional - YouTube, TikTok ou Instagram)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-play-circle-fill text-danger"></i></span>
                                <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror"
                                       value="{{ old('video_url') }}" placeholder="Ex: https://www.youtube.com/watch?v=... ou https://www.instagram.com/reel/...">
                            </div>
                            <div class="form-text">
                                Cole o link de um vídeo do YouTube, TikTok ou Reels do Instagram para exibir na página do pet.
                            </div>
                            @error('video_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Condições Especiais (Opcional)</label>
                            <textarea name="condicoes_especiais" class="form-control @error('condicoes_especiais') is-invalid @enderror"
                                      rows="3" placeholder="Ex: Precisa de medicação controlada, é surdo, amigável com estranhos...">{{ old('condicoes_especiais') }}</textarea>
                            @error('condicoes_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Visibilidade Pública -->
                        <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_public" name="is_public" value="1"
                                       {{ old('is_public', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_public">
                                    Página Pública Ativada (QR Code e link público)
                                </label>
                                <div class="form-text mt-1">
                                    Se desmarcado, visitantes externos receberão página não encontrada (404) a menos que um alerta de desaparecimento esteja ativo.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Salvar e Gerar Identificação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
