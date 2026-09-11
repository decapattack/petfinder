<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            if (Schema::hasColumn('pets', 'vet_name')) {
                $table->dropColumn(['vet_name', 'vet_phone']);
            }
            $table->foreignId('veterinarian_id')
                ->nullable()
                ->after('user_id')
                ->constrained('veterinarians')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropForeign(['veterinarian_id']);
            $table->dropColumn('veterinarian_id');
            $table->string('vet_name', 100)->nullable();
            $table->string('vet_phone', 20)->nullable();
        });
    }
};
