<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'especie',
        'raca',
        'cor',
        'condicoes_especiais',
        'vet_name',
        'vet_phone',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pet) {
            $pet->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function getActiveAlertAttribute()
    {
        return $this->alerts()->where('status', 'ativo')->first();
    }

    /**
     * Relacionamento: Fichas clínicas do pet
     */
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class)->orderBy('record_date', 'desc');
    }

    /**
     * Relacionamento: Lembretes de vacinas/remédios
     */
    public function schedules()
    {
        return $this->hasMany(PetSchedule::class)->orderBy('due_date', 'asc');
    }

    /**
     * Relacionamento: Mídias do pet (fotos e vídeos)
     */
    public function media()
    {
        return $this->hasMany(PetMedia::class);
    }

    /**
     * Atributo: Foto de capa (primeira foto disponível, ou qualquer mídia se não houver foto)
     */
    public function getCoverPhotoAttribute()
    {
        $firstImage = $this->media()->where('type', 'image')->first();
        return $firstImage ?: $this->media()->first();
    }
}
