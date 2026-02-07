<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FULLTEXT индекс для быстрого поиска по имени автора.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE authors ADD FULLTEXT INDEX authors_name_fulltext (name)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE authors DROP INDEX authors_name_fulltext');
    }
};
