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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('subtitle')->nullable();

            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();

            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_link')->nullable();

            $table->string('desktop_image')->nullable();
            $table->string('desktop_image_id')->nullable();

            $table->string('mobile_image')->nullable();
            $table->string('mobile_image_id')->nullable();

            $table->string('background_image')->nullable();
            $table->string('background_image_id')->nullable();

            $table->enum('background_type', [
                'gradient',
                'image',
                'color'
            ])->default('gradient');
            $table->string('background_color')->nullable();

            $table->enum('position', [
                'hero',
                'middle',
                'bottom'
            ])->default('hero');

            $table->integer('sort_order')->default(1);

            $table->boolean('is_active')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
