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
        // Все существующие материалы автоматически становятся одобренными
        DB::table('materials')
            ->whereNull('moderation_status')
            ->update([
                'moderation_status' => 'approved',
                'status' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откат не требуется
    }
};
