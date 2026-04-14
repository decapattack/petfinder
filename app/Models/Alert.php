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
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
