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
        Schema::table('s_portfolio_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->integer('portfolio_type')->default(1)->after('buy_date');
            $table->boolean('is_active')->default(true)->after('portfolio_type');

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
