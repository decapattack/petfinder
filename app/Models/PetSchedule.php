<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Model: PetSchedule (Agendamento/Lembrete)
 * 
 * Lembretes de vacinas futuras ou medicamentos diários/semanais.
 * Controle de status is_completed para marcar como feito.
 */
class PetSchedule extends Model
{
    use HasFactory;

    protected $table = 'pet_schedules';

    protected $fillable = [
        'pet_id',
        'title',
        'type',
        'due_date',
        'time',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'date',
        'time' => 'datetime:H:i', // Cast como time apenas
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Labels para o type (usado em selects)
     */
    public static array $types = [
        'vaccine' => 'Vacina',
        'medication' => 'Remédio/Medicação',
    ];

    /**
     * Relacionamento: Lembrete pertence a um Pet
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    /**
     * Acessor: Verifica se está atrasado (due_date no passado e não concluído)
     * Usado na UI para destacar itens urgentes com borda vermelha
     */
    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_completed 
            && $this->due_date->lt(Carbon::today());
    }

    /**
     * Acessor: Dias restantes ou dias de atraso
     */
    public function getDaysRemainingAttribute(): int
    {
        return Carbon::today()->diffInDays($this->due_date, false); // negativo = atrasado
    }

    /**
     * Acessor: Badge de status para UI (mobile)
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_completed) {
            return ['text' => 'Concluído', 'class' => 'success'];
        }
        
        if ($this->is_overdue) {
            $days = abs($this->days_remaining);
            return ['text' => "Atrasado {$days}d", 'class' => 'danger'];
        }
        
        if ($this->days_remaining === 0) {
            return ['text' => 'Hoje', 'class' => 'warning'];
        }
        
        return ['text' => "Em {$this->days_remaining}d", 'class' => 'info'];
    }

    /**
     * Scope: Pendente (não concluído)
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope: Atrasados
     */
    public function scopeOverdue($query)
    {
        return $query->pending()
                     ->where('due_date', '<', Carbon::today());
    }

    /**
     * Scope: Próximos (ordenados por data)
     */
    public function scopeUpcoming($query)
    {
        return $query->orderBy('due_date', 'asc')
                     ->orderBy('time', 'asc');
    }
}
