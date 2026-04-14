@extends('layouts.app')

@section('title', 'Cadastrar Pet - PetFinder')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card premium-card">
            <div class="card-body p-5">
                <h2 class="mb-4">Cadastrar Novo Pet</h2>

                <form action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Pet</label>
                            <input type="text" name="nome" class="form-control" required placeholder="Ex: Rex">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Espécie</label>
                            <select name="especie" class="form-select" required>
                                <option value="Cachorro">Cachorro</option>
                                <option value="Gato">Gato</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Raça</label>
                            <input type="text" name="raca" class="form-control" required placeholder="Ex: Labrador">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cor Predominante</label>
                            <input type="text" name="cor" class="form-control" required placeholder="Ex: Caramelo">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto do Pet</label>
                        <input type="file" name="foto" class="form-control" required accept="image/*">
                        <small class="text-muted">Dica: Uma foto clara ajuda na identificação rápida.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Condições Especiais (Opcional)</label>
                        <textarea name="condicoes_especiais" class="form-control" rows="3" placeholder="Ex: Precisa de medicação controlada, é surdo, amigável com estranhos..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-premium btn-lg">
                            Salvar e Gerar Identificação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
