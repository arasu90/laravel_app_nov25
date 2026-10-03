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
        Schema::create('s_ipo_stock_lists', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('symbol_name');
            $table->string('security_type');
            $table->date('issue_start_date')->nullable();
            $table->date('issue_end_date')->nullable();
            $table->string('status')->nullable();
            $table->decimal('issue_price', 10, 2)->nullable();
            $table->string('issue_price_range')->nullable();
            $table->date('date_of_listing')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipo_stock_lists');
    }
};
