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
        Schema::table('banners', function (Blueprint $table) {
            // Add 'solid' and 'gradient-image' options
            DB::statement("ALTER TABLE banners MODIFY COLUMN background_type ENUM('gradient', 'solid', 'image', 'gradient-image') NOT NULL DEFAULT 'gradient'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
             DB::statement("ALTER TABLE banners MODIFY COLUMN background_type ENUM('gradient', 'image', 'color') NOT NULL DEFAULT 'gradient'");
        });
    }
};
