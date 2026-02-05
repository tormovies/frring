<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Существующие пользователи, у которых подтвержден email, получают статус email_verified
        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->whereNull('status')
            ->update(['status' => 'email_verified']);
        
        // Пользователи без подтвержденного email получают статус not_verified
        DB::table('users')
            ->whereNull('email_verified_at')
            ->whereNull('status')
            ->update(['status' => 'not_verified']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откат не требуется
    }
};
