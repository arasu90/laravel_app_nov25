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
        Schema::table('s_stock_daily_price_data', function (Blueprint $table) {
            $table->decimal('is_52_week_high_value', 10, 2)->after('intra_day_high_low_max');
            $table->integer('is_52_week_high')->default(0)->after('intra_day_high_low_max');
            $table->decimal('is_52_week_low_value', 10, 2)->after('intra_day_high_low_max');
            $table->integer('is_52_week_low')->default(0)->after('intra_day_high_low_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
