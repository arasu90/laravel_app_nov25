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
        Schema::create('s_watch_list_master', function (Blueprint $table) {
            $table->id();
            $table->string('watch_list_name');
            $table->bigInteger('user_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // $table->index(['user_id'], 'idx_user_id');
            $table->index(['watch_list_name'], 'idx_watch_list_name');
            // $table->index(['user_id', 'watch_list_name'], 'idx_user_id_watch_list_name');
        });

        Schema::create('s_watch_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('watch_list_id');
            $table->foreign('watch_list_id')
                ->references('id')
                ->on('s_watch_list_master')
                ->onDelete('cascade');
            $table->string('symbol');
            $table->foreign('symbol')
                ->references('symbol')
                ->on('s_stock_symbols')
                ->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['watch_list_id'], 'idx_watch_list_id');
            $table->index(['symbol'], 'idx_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_watch_list_master');
        Schema::dropIfExists('s_watch_list_items');
    }
};
