<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetMedia extends Model
{
    protected $table = 'pet_media';

    protected $fillable = [
        'pet_id',
        'path',
        'type',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
