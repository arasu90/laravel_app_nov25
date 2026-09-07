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
        Schema::create('s_stock_details', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->foreign('symbol')
                ->references('symbol')
                ->on('s_stock_symbols')
                ->onDelete('cascade');
            $table->string('company_name');
            $table->string('macro')->nullable();
            $table->string('sector')->nullable();
            $table->string('basic_industry')->nullable();
            $table->string('industry')->nullable();
            $table->string('isin');
            $table->date('listing_date');
            $table->string('status');
            $table->string('series')->nullable();
            $table->string('market_type')->nullable();
            $table->datetime('last_update_time')->nullable();
            $table->string('sector_index')->nullable();
            $table->string('trading_status');
            $table->string('trading_segment');
            $table->string('surveillance_surv')->nullable();
            $table->string('surveillance_desc')->nullable();
            $table->string('face_value');
            $table->decimal('week_high_low_min', 10, 2);
            $table->date('week_high_low_min_date')->nullable();
            $table->decimal('week_high_low_max', 10, 2);
            $table->date('week_high_low_max_date')->nullable();
            $table->date('stock_date')->nullable();
            $table->decimal('stock_last_price', 10, 2)->nullable();
            $table->decimal('stock_change', 10, 2)->nullable();
            $table->decimal('stock_p_change', 10, 2)->nullable();
            $table->text('sector_index_all')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->index(['symbol', 'is_active'], 'idx_symbol_is_active');
            $table->index(['symbol'], 'idx_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_stock_details');
    }
};
