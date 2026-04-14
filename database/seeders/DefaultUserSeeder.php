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
            ['email' => 'teste@teste.com'],
            [
                'name' => 'Usuário Padrão',
                'password' => Hash::make('teste'),
                'telefone' => '11999999999',
                'latitude' => -23.5505,
                'longitude' => -46.6333,
                'pontos' => 100,
                'email_verified_at' => now(),
            ]
        );
    }
}
