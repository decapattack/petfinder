<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: HealthRecord (Ficha Clínica)
 * 
 * Representa registros médicos do pet: exames, vacinas, consultas, receitas.
 * Suporta upload de arquivos (PDF/Imagem) com controle de privacidade is_public.
 * Quando is_public=true, a comunidade pode ver em caso de pet perdido (SOS).
 */
class HealthRecord extends Model
{
    use HasFactory;

    protected $table = 'health_records';

    /**
     * Mass assignable attributes
     * Segurança: user_id NÃO está aqui, é setado automaticamente no controller
     */
    protected $fillable = [
        'pet_id',
        'title',
        'category',
        'file_path',
        'file_extension',
        'is_public',
        'record_date',
    ];

    /**
     * Auto-cast para tipos nativos do PHP
     */
    protected $casts = [
        'is_public' => 'boolean',
        'record_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Categorias disponíveis para uso em selects/forms
     */
    public static array $categories = [
        'exame' => 'Exame/Laboratorial',
        'vacina' => 'Vacina',
        'consulta' => 'Consulta Veterinária',
        'receita' => 'Receita Medicamentosa',
        'outro' => 'Outro Documento',
    ];

    /**
     * Relacionamento: Ficha pertence a um Pet
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    /**
     * Acessor: URL completa do arquivo (via rota protegida)
     * Não expõe o path real do storage
     */
    public function getViewUrlAttribute(): string
    {
        return route('pets.records.view', [
            'pet' => $this->pet_id,
            'record' => $this->id,
        ]);
    }

    /**
     * Acessor: Ícone baseado na extensão do arquivo
     * Usado nos cards da UI (PDF = vermelho, Imagem = azul)
     */
    public function getFileIconAttribute(): string
    {
        return match(strtolower($this->file_extension)) {
            'pdf' => 'bi-file-earmark-pdf',
            'jpg', 'jpeg' => 'bi-file-earmark-image',
            'png' => 'bi-file-earmark-image',
            default => 'bi-file-earmark-medical',
        };
    }

    /**
     * Acessor: Cor do ícone para UI
     */
    public function getIconColorAttribute(): string
    {
        return match(strtolower($this->file_extension)) {
            'pdf' => '#D9534F', // Vermelho
            'jpg', 'jpeg', 'png' => '#1565C0', // Azul
            default => '#6c757d', // Cinza
        };
    }

    /**
     * Scope: Apenas registros públicos (visíveis para comunidade em SOS)
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Filtrar por categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
