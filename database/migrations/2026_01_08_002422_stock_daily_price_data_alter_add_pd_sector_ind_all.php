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
        Schema::table('s_stock_details', function (Blueprint $table) {
            $table->text('pd_sector_ind_all')->nullable()->after('surveillance_desc');
        });

        Schema::table('s_stock_daily_price_data', function (Blueprint $table) {
            $table->text('pd_sector_ind_all')->nullable()->after('is_52_week_high_value');
        });
    }
};
