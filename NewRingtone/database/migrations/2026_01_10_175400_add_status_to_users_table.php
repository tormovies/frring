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
        // Проверяем, существует ли колонка status
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['not_verified', 'email_verified', 'active', 'blocked', 'inactive'])
                    ->default('not_verified')
                    ->after('email_verified_at');
            });
            
            // Устанавливаем статус для существующих пользователей
            // Если email подтвержден - email_verified, иначе not_verified
            \DB::table('users')
                ->whereNotNull('email_verified_at')
                ->update(['status' => 'email_verified']);
            
            \DB::table('users')
                ->whereNull('email_verified_at')
                ->update(['status' => 'not_verified']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
