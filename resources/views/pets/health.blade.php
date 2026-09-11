<x-app-layout>
    <x-slot name="title">Saúde de {{ $pet->nome }} - PetFinder</x-slot>

    @push('styles')
    <style>
        .health-record-card { transition: transform .15s, box-shadow .15s; border-radius: 12px; }
        .health-record-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
        .nav-pills .nav-link { border-radius: 50px; padding: 10px 20px; font-weight: 600; color: #6c757d; }
        .nav-pills .nav-link.active { background: var(--primary-gradient); color: white; }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="d-flex align-items-center">
                    @if($pet->cover_photo && $pet->cover_photo->type === 'video')
                        <video src="{{ asset('storage/' . $pet->cover_photo->path) }}" 
                               class="rounded-circle me-3" 
                               style="width: 60px; height: 60px; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" 
                               autoplay muted loop playsinline></video>
                    @elseif($pet->cover_photo)
                        <img src="{{ asset('storage/' . $pet->cover_photo->path) }}" 
                             alt="{{ $pet->nome }}" 
                             class="rounded-circle me-3" 
                             style="width: 60px; height: 60px; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    @else
                        <div class="rounded-circle me-3 bg-light d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <i class="bi bi-image" style="font-size: 1.5rem; color: rgba(0,0,0,.15);"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="h4 mb-0 fw-bold">{{ $pet->nome }}</h2>
                        <span class="badge bg-light text-muted border">Saúde & Bem-estar</span>
                    </div>
                </div>
            </div>

            <!-- Card: Veterinário -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0 d-flex align-items-center">
                            <i class="bi bi-heart-pulse me-2 text-danger"></i>
                            Veterinário de Confiança
                        </h5>
                        @if($pet->veterinarian)
                            <form action="{{ route('pets.vet.update', $pet) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja desvincular este veterinário do pet?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="remove_vet" value="1">
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Desvincular Vet
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(isset($veterinarians) && $veterinarians->count() > 0)
                        <form action="{{ route('pets.vet.update', $pet) }}" method="POST" class="row g-3 mb-4 pb-3 border-bottom">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Selecionar Veterinário Cadastrado</label>
                                <select name="veterinarian_id" class="form-select">
                                    <option value="">-- Escolha um veterinário --</option>
                                    @foreach($veterinarians as $v)
                                        <option value="{{ $v->id }}" {{ old('veterinarian_id', $pet->veterinarian_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->nome }} ({{ $v->telefone }}) {{ $v->crv ? '- CRV: '.$v->crv : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-secondary w-100 rounded-pill">Selecionar Existente</button>
                            </div>
                        </form>
                    @endif

                    <form action="{{ route('pets.vet.update', $pet) }}" method="POST" class="row g-3">
                        @csrf
                        @method('PATCH')
                        <div class="col-12">
                            <h6 class="fw-bold text-muted mb-2">
                                {{ $pet->veterinarian ? 'Editar Dados do Veterinário' : 'Cadastrar Novo Veterinário' }}
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nome do Veterinário / Clínica <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control" value="{{ old('nome', $pet->veterinarian->nome ?? '') }}" placeholder="Ex: Dr. Carlos Silva" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Telefone <span class="text-danger">*</span></label>
                            <input type="tel" name="telefone" class="form-control" value="{{ old('telefone', $pet->veterinarian->telefone ?? '') }}" placeholder="(00) 00000-0000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">CRV (Nº de Registro)</label>
                            <input type="text" name="crv" class="form-control" value="{{ old('crv', $pet->veterinarian->crv ?? '') }}" placeholder="Ex: CRV-SP 12345">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $pet->veterinarian->email ?? '') }}" placeholder="vet@clinica.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $pet->veterinarian->cidade ?? '') }}" placeholder="São Paulo">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Estado (UF)</label>
                            <input type="text" name="estado" class="form-control" value="{{ old('estado', $pet->veterinarian->estado ?? '') }}" placeholder="SP" maxlength="2">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                {{ $pet->veterinarian ? 'Salvar' : 'Cadastrar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-pills mb-4 gap-2" id="healthTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button">
                        <i class="bi bi-folder2-open me-2"></i>Fichas Clínicas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" data-bs-target="#schedules" type="button">
                        <i class="bi bi-calendar-check me-2"></i>Lembretes
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="healthTabContent">
                <!-- ABA 1: FICHAS CLÍNICAS -->
                <div class="tab-pane fade show active" id="records" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Documentos e Exames</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                            + Nova Ficha
                        </button>
                    </div>

                    @if($pet->healthRecords->count() > 0)
                        <div class="row g-3">
                            @foreach($pet->healthRecords as $record)
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm health-record-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi {{ $record->file_icon }} display-4" style="color: {{ $record->icon_color }};"></i>
                                            <h6 class="fw-bold mt-3 mb-1 text-truncate">{{ $record->title }}</h6>
                                            <span class="badge bg-light text-dark border mb-3">
                                                {{ \App\Models\HealthRecord::$categories[$record->category] ?? $record->category }}
                                            </span>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ $record->view_url }}" target="_blank" class="btn btn-sm btn-primary rounded-pill">Ver</a>
                                                <form action="{{ route('pets.records.destroy', ['pet' => $pet, 'record' => $record]) }}" method="POST" onsubmit="return confirm('Excluir permanentemente?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill">Excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                            <i class="bi bi-folder2-open display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">Nenhuma ficha cadastrada para este pet.</p>
                        </div>
                    @endif
                </div>

                <!-- ABA 2: LEMBRETES -->
                <div class="tab-pane fade" id="schedules" role="tabpanel">
                    <div class="card shadow-sm mb-4 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Adicionar Novo Lembrete (Vacina/Remédio)</h6>
                            <form action="{{ route('pets.schedules.store', $pet) }}" method="POST" class="row g-2">
                                @csrf
                                <div class="col-md-4">
                                    <input type="text" name="title" class="form-control" placeholder="Título do lembrete" required>
                                </div>
                                <div class="col-md-3">
                                    <select name="type" class="form-select" required>
                                        <option value="vaccine">Vacina</option>
                                        <option value="medication">Remédio</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="due_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Adicionar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($pet->schedules->count() > 0)
                        <div class="list-group shadow-sm rounded-4 overflow-hidden border-0">
                            @foreach($pet->schedules as $schedule)
                                <div class="list-group-item d-flex align-items-center justify-content-between p-3 {{ $schedule->is_overdue ? 'bg-danger-subtle' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <form action="{{ route('pets.schedules.toggle', ['pet' => $pet, 'schedule' => $schedule]) }}" method="POST" class="me-3">
                                            @csrf
                                            <input class="form-check-input" type="checkbox" {{ $schedule->is_completed ? 'checked' : '' }} onchange="this.form.submit()" style="width: 1.5rem; height: 1.5rem;">
                                        </form>
                                        <div class="{{ $schedule->is_completed ? 'text-decoration-line-through opacity-50' : '' }}">
                                            <h6 class="mb-0 fw-bold">{{ $schedule->title }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i> {{ $schedule->due_date->format('d/m/Y') }} 
                                                <span class="badge bg-light text-dark border ms-2">
                                                    {{ \App\Models\PetSchedule::$types[$schedule->type] ?? $schedule->type }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                    <form action="{{ route('pets.schedules.destroy', ['pet' => $pet, 'schedule' => $schedule]) }}" method="POST" onsubmit="return confirm('Remover?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                            <i class="bi bi-calendar-check display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">Tudo em dia! Nenhum lembrete pendente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Nova Ficha -->
    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Nova Ficha Clínica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pets.records.store', $pet) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Título</label>
                            <input type="text" name="title" class="form-control" placeholder="Ex: Vacina V8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Categoria</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\HealthRecord::$categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Data do Registro</label>
                            <input type="date" name="record_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Arquivo (PDF ou Imagem)</label>
                            <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-check form-switch mb-0 bg-light p-3 rounded-3">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_public" value="1" id="isPublicCheck">
                            <label class="form-check-label fw-bold" for="isPublicCheck">Público (SOS)</label>
                            <div class="form-text">Visível para a comunidade se o pet se perder.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Ficha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

