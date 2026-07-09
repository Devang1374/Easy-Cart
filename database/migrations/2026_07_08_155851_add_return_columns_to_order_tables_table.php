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

        $table->boolean('return_requested')
            ->default(false);
    
        $table->enum('return_status', [
            'none',
            'requested',
            'approved',
            'rejected',
            'received',
            'refunded'
        ])->default('none');
    
        $table->text('return_reason')
            ->nullable();
    
        $table->text('return_admin_note')
            ->nullable();
    
        $table->timestamp('return_requested_at')
            ->nullable();
    
        $table->timestamp('return_completed_at')
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
