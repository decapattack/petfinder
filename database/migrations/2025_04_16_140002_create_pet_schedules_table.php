<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabela de Agendamentos (Schedules)
 * Lembretes de vacinas, remédios e consultas futuras
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_schedules', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com pet
            $table->foreignId('pet_id')
                  ->constrained('pets')
                  ->onDelete('cascade');
            
            // Dados do lembrete
            $table->string('title', 150); // Ex: "Antirrábica", "Nexgard", "Consulta de Rotina"
            $table->enum('type', ['medication', 'vaccine'])
                  ->default('medication');
            
            // Data e hora
            $table->date('due_date');
            $table->time('time')->nullable(); // Opcional para remédios com horário específico
            
            // Status
            $table->boolean('is_completed')->default(false);
            
            $table->timestamps();
            
            // Índices
            $table->index(['pet_id', 'due_date']);
            $table->index(['pet_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_schedules');
    }
};
