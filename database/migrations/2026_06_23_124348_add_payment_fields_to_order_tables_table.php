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
        Schema::table('order_tables', function (Blueprint $table) {
             $table->string('cashfree_order_id')
                ->nullable();

            $table->string('cf_payment_id')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tables', function (Blueprint $table) {
            //
        });
    }
};
