<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('condicoes_especiais');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // Preenche pets existentes com a latitude e longitude do seu respectivo tutor
        DB::statement("
            UPDATE pets 
            SET latitude = (SELECT latitude FROM users WHERE users.id = pets.user_id),
                longitude = (SELECT longitude FROM users WHERE users.id = pets.user_id)
            WHERE latitude IS NULL AND longitude IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
