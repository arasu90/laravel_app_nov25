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
        Schema::create('s_stock_symbols', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['symbol'], 'idx_symbol');
            $table->index(['symbol', 'is_active'], 'idx_symbol_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_stock_symbols');
    }
};
