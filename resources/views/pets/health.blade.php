@extends('layouts.base')

@section('title', "Saúde de {$pet->nome} - PetFinder")

@push('styles')
<style>
/* Mobile-first adjustments */
.health-record-card {
    transition: transform .15s, box-shadow .15s;
}
.health-record-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}

/* Min-width for text truncation */
.min-width-0 {
    min-width: 0;
}

/* Form switch custom */
.form-switch .form-check-input {
    cursor: pointer;
}

/* Tab pills active state */
.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
}

/* Mobile sticky button improvements */
@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
    
    .list-group-item {
        padding: .75rem;
    }
    
    /* Larger touch targets */
    .btn-sm {
        padding: .5rem .75rem;
        font-size: .875rem;
    }
}
</style>
@endpush

@section('content')
<!-- Navigation -->
@include('layouts.navigation')

<div class="container py-3 py-md-4">
    <!-- Header Mobile-Friendly -->
    <div class="d-flex align-items-center mb-3 mb-md-4">
        <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none p-0 me-2">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>
        <div class="d-flex align-items-center flex-grow-1">
            <img src="{{ $pet->foto ? asset('storage/' . $pet->foto) : asset('images/pet-placeholder.png') }}" 
                 alt="{{ $pet->nome }}" 
                 class="rounded-circle me-2" 
                 style="width: 48px; height: 48px; object-fit: cover;">
            <div>
                <h1 class="h5 mb-0 fw-bold">{{ $pet->nome }}</h1>
                <small class="text-muted">Saúde & Veterinário</small>
            </div>
        </div>
    </div>

    <!-- Card: Veterinário de Confiança -->
    <div class="card border-0 shadow-sm mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-heart-pulse me-2 text-danger"></i>
                Veterinário de Confiança
            </h2>
            <form action="{{ route('pets.vet.update', $pet) }}" method="POST" class="row g-2 g-md-3">
                @csrf
                @method('PATCH')
                <div class="col-12 col-md-6">
                    <label class="form-label small text-muted mb-1">Nome do Vet / Clínica</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-person-badge text-muted"></i>
                        </span>
                        <input type="text" 
                               name="vet_name" 
                               class="form-control border-start-0" 
                               value="{{ old('vet_name', $pet->vet_name) }}"
                               placeholder="Ex: Dr. Carlos, PetCare Clinic">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small text-muted mb-1">Telefone</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-telephone text-muted"></i>
                        </span>
                        <input type="tel" 
                               name="vet_phone" 
                               class="form-control border-start-0" 
                               value="{{ old('vet_phone', $pet->vet_phone) }}"
                               placeholder="(11) 99999-9999">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm w-100 w-md-auto">
                        <i class="bi bi-check-lg me-1"></i>
                        Salvar Contato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Abas: Fichas vs Lembretes -->
    <ul class="nav nav-pills nav-fill mb-3 mb-md-4" id="healthTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button">
                <i class="bi bi-folder2-open me-1"></i>
                <span class="d-none d-sm-inline">Fichas Clínicas</span>
                <span class="d-sm-none">Fichas</span>
                @if($pet->healthRecords->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $pet->healthRecords->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="schedules-tab" data-bs-toggle="tab" data-bs-target="#schedules" type="button">
                <i class="bi bi-calendar-check me-1"></i>
                <span class="d-none d-sm-inline">Lembretes</span>
                <span class="d-sm-none">Lembretes</span>
                @php
                    $pendingCount = $pet->schedules->where('is_completed', false)->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content" id="healthTabContent">
        <!-- ABA 1: FICHAS CLÍNICAS -->
        <div class="tab-pane fade show active" id="records" role="tabpanel">
            <!-- Botão Add (Mobile Sticky) -->
            <div class="d-grid mb-3">
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                    <i class="bi bi-plus-lg me-2"></i>
                    Adicionar Nova Ficha
                </button>
            </div>

            <!-- Grid de Cards -->
            @if($pet->healthRecords->count() > 0)
                <div class="row g-3">
                    @foreach($pet->healthRecords as $record)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm health-record-card">
                                <!-- Card Body -->
                                <div class="card-body p-3 text-center">
                                    <!-- Ícone Grande (Vermelho=PDF, Azul=Imagem) -->
                                    <a href="{{ $record->view_url }}" target="_blank" class="text-decoration-none">
                                        <i class="bi {{ $record->file_icon }} display-4" 
                                           style="color: {{ $record->icon_color }};"></i>
                                    </a>
                                    
                                    <!-- Título -->
                                    <h5 class="card-title h6 mt-2 mb-1 fw-bold text-truncate" title="{{ $record->title }}">
                                        {{ $record->title }}
                                    </h5>
                                    
                                    <!-- Categoria Badge -->
                                    <span class="badge bg-light text-dark mb-2">
                                        {{ \App\Models\HealthRecord::$categories[$record->category] ?? $record->category }}
                                    </span>
                                    
                                    <!-- Data -->
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $record->record_date->format('d/m/Y') }}
                                    </p>
                                </div>
                                
                                <!-- Card Footer: Privacidade + Ações -->
                                <div class="card-footer bg-light border-0 p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Toggle Privacidade -->
                                        <form action="{{ route('pets.records.privacy', ['pet' => $pet, 'record' => $record]) }}" 
                                              method="POST" 
                                              class="m-0"
                                              id="privacy-form-{{ $record->id }}">
                                            @csrf
                                            <div class="form-check form-switch m-0">
                                                <input type="hidden" name="is_public" value="0">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="is_public" 
                                                       value="1"
                                                       {{ $record->is_public ? 'checked' : '' }}
                                                       onchange="this.form.submit()"
                                                       id="privacy-{{ $record->id }}">
                                                <label class="form-check-label small text-muted" for="privacy-{{ $record->id }}">
                                                    {{ $record->is_public ? 'Público' : 'Privado' }}
                                                </label>
                                            </div>
                                        </form>
                                        
                                        <!-- Ações -->
                                        <div class="d-flex gap-1">
                                            <a href="{{ $record->view_url }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('pets.records.destroy', ['pet' => $pet, 'record' => $record]) }}" 
                                                  method="POST" 
                                                  class="m-0"
                                                  onsubmit="return confirm('Excluir esta ficha permanentemente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="bi bi-folder2-open display-1 text-muted opacity-25"></i>
                    <p class="text-muted mt-3">Nenhuma ficha clínica cadastrada.</p>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                        <i class="bi bi-plus-lg me-1"></i>
                        Adicionar primeira ficha
                    </button>
                </div>
            @endif
        </div>

        <!-- ABA 2: LEMBRETES -->
        <div class="tab-pane fade" id="schedules" role="tabpanel">
            <!-- Form Rápido: Adicionar Lembrete -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <h3 class="h6 fw-bold mb-3">Novo Lembrete</h3>
                    <form action="{{ route('pets.schedules.store', $pet) }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" 
                                   name="title" 
                                   class="form-control" 
                                   placeholder="Ex: Vacina Antirrábica, Nexgard..."
                                   required>
                        </div>
                        <div class="col-6 col-md-3">
                            <select name="type" class="form-select" required>
                                <option value="vaccine">Vacina</option>
                                <option value="medication">Remédio</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="date" 
                                   name="due_date" 
                                   class="form-control" 
                                   min="{{ date('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="time" 
                                   name="time" 
                                   class="form-control"
                                   placeholder="Horário">
                        </div>
                        <div class="col-6 col-md-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-lg me-1"></i>
                                Adicionar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Lembretes -->
            @if($pet->schedules->count() > 0)
                <div class="list-group shadow-sm">
                    @foreach($pet->schedules as $schedule)
                        @php
                            $isOverdue = $schedule->is_overdue;
                            $status = $schedule->status_badge;
                        @endphp
                        <div class="list-group-item p-3 {{ $isOverdue ? 'border-start border-4 border-danger' : '' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-grow-1 min-width-0">
                                    <!-- Checkbox Toggle -->
                                    <form action="{{ route('pets.schedules.toggle', ['pet' => $pet, 'schedule' => $schedule]) }}" 
                                          method="POST" 
                                          class="me-3">
                                        @csrf
                                        <div class="form-check m-0">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   {{ $schedule->is_completed ? 'checked' : '' }}
                                                   onchange="this.form.submit()"
                                                   style="width: 1.5em; height: 1.5em; cursor: pointer;">
                                        </div>
                                    </form>
                                    
                                    <!-- Info -->
                                    <div class="min-width-0 {{ $schedule->is_completed ? 'text-decoration-line-through opacity-50' : '' }}">
                                        <h6 class="mb-0 fw-bold text-truncate">{{ $schedule->title }}</h6>
                                        <small class="text-muted d-flex align-items-center flex-wrap gap-1">
                                            <span class="badge bg-light text-dark">
                                                {{ \App\Models\PetSchedule::$types[$schedule->type] ?? $schedule->type }}
                                            </span>
                                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $schedule->due_date->format('d/m/Y') }}
                                                @if($schedule->time)
                                                    às {{ substr($schedule->time, 0, 5) }}
                                                @endif
                                            </span>
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Badge + Delete -->
                                <div class="d-flex align-items-center gap-2 ms-2">
                                    <span class="badge bg-{{ $status['class'] }} {{ in_array($status['class'], ['warning', 'light']) ? 'text-dark' : '' }} d-none d-sm-inline">
                                        {{ $status['text'] }}
                                    </span>
                                    <form action="{{ route('pets.schedules.destroy', ['pet' => $pet, 'schedule' => $schedule]) }}" 
                                          method="POST" 
                                          class="m-0"
                                          onsubmit="return confirm('Remover este lembrete?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" title="Remover">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Mobile: Badge em linha separada -->
                            <div class="d-sm-none mt-2">
                                <span class="badge bg-{{ $status['class'] }} {{ in_array($status['class'], ['warning', 'light']) ? 'text-dark' : '' }}">
                                    {{ $status['text'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="bi bi-calendar-check display-1 text-muted opacity-25"></i>
                    <p class="text-muted mt-3">Nenhum lembrete agendado.</p>
                    <p class="small text-muted">Adicione vacinas ou remédios acima.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: Upload de Ficha Clínica -->
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>
                    Nova Ficha Clínica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pets.records.store', $pet) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Título -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="title" 
                               class="form-control" 
                               placeholder="Ex: Raio-X de Tórax, Vacina V8..."
                               required>
                    </div>
                    
                    <!-- Categoria -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach(\App\Models\HealthRecord::$categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Data do Registro -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data do Exame/Vacina <span class="text-danger">*</span></label>
                        <input type="date" 
                               name="record_date" 
                               class="form-control"
                               max="{{ date('Y-m-d') }}"
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>
                    
                    <!-- Arquivo -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Arquivo (PDF ou Imagem) <span class="text-danger">*</span></label>
                        <input type="file" 
                               name="file" 
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf"
                               required>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Formatos: JPG, PNG ou PDF. Máx: 5MB.
                        </div>
                    </div>
                    
                    <!-- Privacidade -->
                    <div class="alert alert-warning py-2 mb-0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicCheck">
                            <label class="form-check-label" for="isPublicCheck">
                                <i class="bi bi-globe me-1"></i>
                                <strong>Tornar Público (SOS)</strong>
                            </label>
                            <div class="form-text mt-1 mb-0">
                                Se o pet se perder, a comunidade poderá ver esta ficha para ajudar no resgate.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Salvar Ficha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
