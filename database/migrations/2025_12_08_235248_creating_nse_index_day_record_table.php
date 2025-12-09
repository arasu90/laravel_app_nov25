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

        Schema::table('s_stock_index_name', function (Blueprint $table) {
            $table->unique('index_symbol');
        });

        Schema::create('s_nes_index_day_records', function (Blueprint $table) {
            $table->id();
            $table->string('index_symbol');
            $table->date('trade_date');
            $table->decimal('last_value', 10, 2);
            $table->decimal('value_change', 10, 2);
            $table->decimal('value_p_change', 10, 2);
            $table->decimal('value_open', 10, 2);
            $table->decimal('day_high', 10, 2);
            $table->decimal('day_low', 10, 2);
            $table->decimal('previous_close', 10, 2);
            $table->decimal('year_high', 10, 2);
            $table->decimal('year_low', 10, 2);
            $table->smallInteger('declines');
            $table->smallInteger('advances');
            $table->smallInteger('unchanged');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->index(['index_symbol', 'trade_date'], 'idx_index_symbol_date');
            $table->index(['index_symbol'], 'idx_index_symbol');

            $table->unique(['index_symbol', 'trade_date'], 'unique_index_symbol_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_nes_index_day_records');
    }
};
