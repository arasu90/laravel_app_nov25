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
        Schema::create('s_watchlist_master', function (Blueprint $table) {
            $table->id();
            $table->string('watchlist_name');
            $table->bigInteger('user_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // $table->index(['user_id'], 'idx_user_id');
            $table->index(['watchlist_name'], 'idx_watchlist_name');
            // $table->index(['user_id', 'watchlist_name'], 'idx_user_id_watchlist_name');
        });

        Schema::create('s_watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('watchlist_id');
            $table->foreign('watchlist_id')
                ->references('id')
                ->on('s_watchlist_master')
                ->onDelete('cascade');
            $table->string('symbol');
            $table->foreign('symbol')
                ->references('symbol')
                ->on('s_stock_symbols')
                ->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['watchlist_id'], 'idx_watchlist_id');
            $table->index(['symbol'], 'idx_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_watchlist_master');
        Schema::dropIfExists('s_watchlist_items');
    }
};
