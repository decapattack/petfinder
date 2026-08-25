<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'latitude_fuga',
        'longitude_fuga',
        'status',
        'hero_id',
        'hero_awarded_at',
    ];

    protected $casts = [
        'hero_awarded_at' => 'datetime',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function hero()
    {
        return $this->belongsTo(User::class, 'hero_id');
    }
}
