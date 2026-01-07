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
            $table->date('stock_date')->nullable()->after('surveillance_desc');
            $table->decimal('stock_last_price', 10, 2)->nullable()->after('date');
            $table->decimal('stock_change', 10, 2)->nullable()->after('last_price');
            $table->decimal('stock_p_change', 10, 2)->nullable()->after('change');
        });
    }
};
