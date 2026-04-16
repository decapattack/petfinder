<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Adiciona colunas do veterinário à tabela pets
 * Permite cadastrar contato do vet de confiança no perfil do pet
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->string('vet_name', 100)->nullable()->after('foto');
            $table->string('vet_phone', 20)->nullable()->after('vet_name');
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn(['vet_name', 'vet_phone']);
        });
    }
};
