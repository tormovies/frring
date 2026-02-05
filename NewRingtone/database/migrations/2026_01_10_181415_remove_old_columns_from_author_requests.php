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
        $columns = DB::select("SHOW COLUMNS FROM author_requests");
        $columnNames = array_column($columns, 'Field');
        
        Schema::table('author_requests', function (Blueprint $table) use ($columnNames) {
            // Удаляем старые колонки, если они еще существуют
            if (in_array('author_link', $columnNames) && in_array('author_card_url', $columnNames)) {
                $table->dropColumn('author_link');
            }
            if (in_array('admin_comment', $columnNames) && in_array('rejection_reason', $columnNames)) {
                $table->dropColumn('admin_comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откат не требуется - это очистка старых колонок
    }
};
