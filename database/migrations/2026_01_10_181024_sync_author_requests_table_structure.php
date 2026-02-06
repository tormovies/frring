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
        
        // Синхронизируем author_card_url
        if (in_array('author_link', $columnNames) && !in_array('author_card_url', $columnNames)) {
            // Добавляем новую колонку как nullable
            Schema::table('author_requests', function (Blueprint $table) {
                $table->text('author_card_url')->nullable()->after('author_name');
            });
            // Копируем данные (обрабатываем NULL)
            DB::statement("UPDATE author_requests SET author_card_url = COALESCE(author_link, '')");
            // Если поле должно быть обязательным, делаем его NOT NULL после копирования
            // Но по требованиям оно может быть пустым, оставляем nullable
            // Удаляем старую колонку
            Schema::table('author_requests', function (Blueprint $table) {
                $table->dropColumn('author_link');
            });
        } elseif (!in_array('author_card_url', $columnNames)) {
            // Если нет ни того, ни другого, создаем author_card_url как обязательное (по требованиям)
            Schema::table('author_requests', function (Blueprint $table) {
                $table->text('author_card_url')->after('author_name');
            });
        }
        
        // Синхронизируем rejection_reason
        if (in_array('admin_comment', $columnNames) && !in_array('rejection_reason', $columnNames)) {
            // Добавляем новую колонку
            Schema::table('author_requests', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
            // Копируем данные
            DB::statement("UPDATE author_requests SET rejection_reason = admin_comment");
            // Удаляем старую колонку
            Schema::table('author_requests', function (Blueprint $table) {
                $table->dropColumn('admin_comment');
            });
        } elseif (!in_array('rejection_reason', $columnNames)) {
            // Если нет ни того, ни другого, создаем rejection_reason
            Schema::table('author_requests', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = DB::select("SHOW COLUMNS FROM author_requests");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('author_card_url', $columnNames) && !in_array('author_link', $columnNames)) {
            DB::statement("ALTER TABLE author_requests CHANGE COLUMN author_card_url author_link TEXT NOT NULL");
        }
        if (in_array('rejection_reason', $columnNames) && !in_array('admin_comment', $columnNames)) {
            DB::statement("ALTER TABLE author_requests CHANGE COLUMN rejection_reason admin_comment TEXT NULL");
        }
    }
};
