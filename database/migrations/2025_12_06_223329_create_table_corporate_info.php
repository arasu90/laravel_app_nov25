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
        Schema::create('s_stock_corporate_info', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->foreign('symbol')
                ->references('symbol')
                ->on('s_stock_symbols')
                ->onDelete('cascade');
            $table->string('actions_type');
            $table->date('actions_date');
            $table->text('actions_purpose');
            $table->timestamps();

            $table->index(['symbol', 'actions_type', 'actions_date'], 'idx_symbol_actions_data');
            $table->unique(['symbol', 'actions_type', 'actions_date'], 'unique_symbol_actions_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_stock_corporate_info');
    }
};
