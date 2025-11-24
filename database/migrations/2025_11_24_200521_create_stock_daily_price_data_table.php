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
        Schema::create('s_stock_daily_price_data', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->foreign('symbol')
                ->references('symbol')
                ->on('s_stock_symbols')
                ->onDelete('cascade');
            $table->date('date');
            $table->decimal('last_price', 10, 2);
            $table->decimal('change', 10, 2);
            $table->decimal('p_change', 10, 2);
            $table->decimal('previous_close', 10, 2);
            $table->decimal('open', 10, 2);
            $table->decimal('close', 10, 2);
            $table->decimal('lower_cp', 10, 2);
            $table->decimal('upper_cp', 10, 2);
            // $table->decimal('p_pr/ice_band', 10, 2);
            // $table->decimal('base_price', 10, 2);
            $table->decimal('intra_day_high_low_min', 10, 2);
            $table->decimal('intra_day_high_low_max', 10, 2);
            // $table->decimal('week_high_low_min', 10, 2);
            $table->json('day_reocrds');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->unique(['symbol', 'date'], 'unique_symbol_date');
            $table->index(['symbol', 'date'], 'idx_symbol_date');
            $table->index(['symbol'], 'idx_symbol');
            $table->index(['date'], 'idx_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_stock_daily_price_data');
    }
};
