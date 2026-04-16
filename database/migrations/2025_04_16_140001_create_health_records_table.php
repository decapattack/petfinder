<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabela de Fichas Clínicas (Health Records)
 * Armazena exames, vacinas, consultas e receitas com controle de privacidade
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com pet (cascata: se pet for deletado, deleta registros)
            $table->foreignId('pet_id')
                  ->constrained('pets')
                  ->onDelete('cascade');
            
            // Dados da ficha
            $table->string('title', 150); // Ex: "Raio-X de Tórax", "Vacina Antirrábica"
            $table->enum('category', ['exame', 'vacina', 'consulta', 'receita', 'outro'])
                  ->default('outro');
            
            // Arquivo (storage privado)
            $table->string('file_path'); // Caminho no storage: private/health_records/
            $table->string('file_extension', 10); // pdf, jpg, png
            
            // Controle de privacidade (crucial para SOS - pet perdido)
            $table->boolean('is_public')->default(false);
            
            // Data do registro médico (quando ocorreu o evento)
            $table->date('record_date');
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['pet_id', 'category']);
            $table->index(['pet_id', 'is_public']); // Importante para buscas SOS
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
