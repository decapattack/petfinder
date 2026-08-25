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
        Schema::create('pet_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->string('path');
            $table->string('type'); // 'image' or 'video'
            $table->timestamps();
        });

        // Migrate existing foto data
        $pets = DB::table('pets')->whereNotNull('foto')->get();
        foreach ($pets as $pet) {
            DB::table('pet_media')->insert([
                'pet_id' => $pet->id,
                'path' => $pet->foto,
                'type' => 'image',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the foto column from pets table
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create foto column
        Schema::table('pets', function (Blueprint $table) {
            $table->string('foto')->nullable();
        });

        // Migrate back the first media item to foto column
        $mediaList = DB::table('pet_media')->orderBy('id', 'asc')->get();
        $migratedPets = [];
        foreach ($mediaList as $media) {
            if (!in_array($media->pet_id, $migratedPets)) {
                DB::table('pets')->where('id', $media->pet_id)->update([
                    'foto' => $media->path
                ]);
                $migratedPets[] = $media->pet_id;
            }
        }

        Schema::dropIfExists('pet_media');
    }
};
