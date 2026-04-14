<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'aleste2@ymail.com'],
            [
                'name' => 'Usuário Padrão',
                'password' => Hash::make('Gun!star2'),
                'telefone' => '11999999999',
                'latitude' => -23.5505,
                'longitude' => -46.6333,
                'pontos' => 100,
                'email_verified_at' => now(),
            ]
        );
    }
}
