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
            height: 150px;
            background-color: #f0f0f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .media-container img, .media-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-container .delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 10;
            background-color: rgba(220, 53, 69, 0.9);
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
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="mb-0 fw-bold">Editar Pet: {{ $pet->nome }}</h2>
                    <p class="text-muted mb-0">Atualize os dados e gerencie as fotos/vídeos de identificação.</p>
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

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                                        Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Coluna da Direita: Gerenciador de Mídias -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-3">Fotos e Vídeos</h4>
                            <p class="text-muted small mb-4">Gerencie as imagens do pet. Você pode subir múltiplos arquivos de imagem ou vídeo de até 20MB cada.</p>

                            <div class="row g-3">
                                <!-- Listagem das Mídias Atuais -->
                                @foreach($pet->media as $mediaItem)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="media-container">
                                            <!-- Formulário para exclusão individual -->
                                            <form action="{{ route('pets.media.destroy', [$pet, $mediaItem]) }}" method="POST" onsubmit="return confirm('Deseja realmente remover esta mídia?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="delete-btn" title="Remover mídia">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>

                                            @if($mediaItem->type === 'video')
                                                <video src="{{ asset('storage/' . $mediaItem->path) }}" muted preload="metadata" playsinline></video>
                                                <!-- Overlay indicativo de vídeo -->
                                                <div class="position-absolute bottom-0 start-0 m-2 bg-dark bg-opacity-75 text-white rounded px-2 py-1 small">
                                                    <i class="fa-solid fa-video me-1"></i> Vídeo
                                                </div>
                                            @else
                                                <img src="{{ asset('storage/' . $mediaItem->path) }}" alt="Mídia do pet">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Card de Adição de Mídias -->
                                <div class="col-md-4 col-sm-6">
                                    <form action="{{ route('pets.media.store', $pet) }}" method="POST" enctype="multipart/form-data" id="addMediaForm">
                                        @csrf
                                        <label class="card justify-content-center align-items-center border-dashed cursor-pointer" 
                                               style="border: 2px dashed #0d6efd; border-radius: 12px; height: 150px; transition: all 0.2s;">
                                            <input type="file" name="media[]" class="d-none" multiple accept="image/*,video/*" 
                                                   onchange="document.getElementById('addMediaForm').submit()">
                                            <div class="text-center text-primary">
                                                <i class="fa-solid fa-plus fs-1 mb-2"></i>
                                                <div class="small fw-bold">Adicionar Mídias</div>
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
</x-app-layout>
