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
            $table->string('macro');
            $table->string('sector');
            $table->string('basic_industry');
            $table->string('industry');
            $table->string('isin');
            $table->date('listing_date');
            $table->string('status');
            $table->datetime('last_update_time')->nullable();
            $table->string('pdsectorind');
            $table->string('trading_status');
            $table->string('trading_segment');
            $table->string('face_value');
            $table->decimal('week_high_low_min', 10, 2);
            $table->date('week_high_low_min_date');
            $table->decimal('week_high_low_max', 10, 2);
            $table->date('week_high_low_max_date');
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
