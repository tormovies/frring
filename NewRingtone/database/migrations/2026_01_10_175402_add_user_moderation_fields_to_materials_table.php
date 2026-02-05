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
        // Проверяем, какие поля уже существуют
        $columns = DB::select("SHOW COLUMNS FROM materials");
        $columnNames = array_column($columns, 'Field');
        
        // Добавляем user_id, если его нет (для связи с пользователем)
        if (!in_array('user_id', $columnNames)) {
            Schema::table('materials', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
        
        // Сначала добавляем moderation_status, если его нет
        if (!in_array('moderation_status', $columnNames)) {
            Schema::table('materials', function (Blueprint $table) {
                $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->nullable()->after('status');
            });
        } else {
            // Если moderation_status уже есть, меняем его default на NULL
            DB::statement("ALTER TABLE materials MODIFY COLUMN moderation_status ENUM('pending', 'approved', 'rejected') NULL DEFAULT NULL");
        }
        
        // Добавляем rejection_reason только если его нет
        if (!in_array('rejection_reason', $columnNames)) {
            Schema::table('materials', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('moderation_status');
            });
        }
        
        // Делаем status nullable
        DB::statement('ALTER TABLE materials MODIFY COLUMN status BOOLEAN NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = DB::select("SHOW COLUMNS FROM materials");
        $columnNames = array_column($columns, 'Field');
        
        // Удаляем только rejection_reason, так как user_id и moderation_status уже были до этой миграции
        if (in_array('rejection_reason', $columnNames)) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }
        
        // Возвращаем defaults обратно
        DB::statement("ALTER TABLE materials MODIFY COLUMN moderation_status ENUM('pending', 'approved', 'rejected') NULL DEFAULT 'pending'");
        DB::statement('ALTER TABLE materials MODIFY COLUMN status BOOLEAN NOT NULL DEFAULT 1');
    }
};
