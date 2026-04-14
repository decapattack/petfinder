<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'nome',
        'especie',
        'raca',
        'cor',
        'condicoes_especiais',
        'foto',
        'status',
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
}
