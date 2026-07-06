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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->enum('type', ['fixed', 'percentage']);

            $table->decimal('value', 10, 2);

            $table->decimal('minimum_amount', 10, 2)->default(0);

            $table->decimal('maximum_discount', 10, 2)->nullable();

            $table->unsignedInteger('usage_limit')->nullable();

            $table->unsignedInteger('used_count')->default(0);

            $table->dateTime('starts_at')->nullable();

            $table->dateTime('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
