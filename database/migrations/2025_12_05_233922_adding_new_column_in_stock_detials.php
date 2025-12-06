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
            $table->string('surveillance_surv')->nullable()->after('trading_segment');
            $table->string('surveillance_desc')->nullable()->before('face_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('s_stock_detials', function (Blueprint $table) {
        //     //
        // });
    }
};
