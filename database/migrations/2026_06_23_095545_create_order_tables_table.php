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
        Schema::create('order_tables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('order_number')->unique();

            $table->string('first_name');
            $table->string('last_name');

            $table->string('email');
            $table->string('phone');

            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');

            $table->decimal('total_amount', 10, 2);

            $table->string('status')
                ->default('pending');

            $table->string('pyment')
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tables');
    }
};
