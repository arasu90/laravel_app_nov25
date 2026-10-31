<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('s_portfolio_stocks', function (Blueprint $table) {
            $table->foreignId('account_type')
                ->nullable()
                ->after('portfolio_type')
                ->constrained('accounts')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->after('account_type')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('s_portfolio_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('account_type');
        });
    }
};
