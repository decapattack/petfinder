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

                        <div class="mb-3">
                            <label class="form-label">Foto do Pet</label>
                            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror"
                                   required accept="image/*">
                            <div class="form-text">Dica: Uma foto clara ajuda na identificação rápida.</div>
                            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Condições Especiais (Opcional)</label>
                            <textarea name="condicoes_especiais" class="form-control @error('condicoes_especiais') is-invalid @enderror"
                                      rows="3" placeholder="Ex: Precisa de medicação controlada, é surdo, amigável com estranhos...">{{ old('condicoes_especiais') }}</textarea>
                            @error('condicoes_especiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

