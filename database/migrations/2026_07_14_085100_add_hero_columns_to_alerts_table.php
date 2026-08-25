<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('hero_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hero_awarded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn(['hero_id', 'hero_awarded_at']);
        });
    }
};
