<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FULLTEXT индекс для быстрого поиска по name, title, description.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE materials ADD FULLTEXT INDEX materials_search_fulltext (name, title, description)');
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

        DB::statement('ALTER TABLE materials DROP INDEX materials_search_fulltext');
    }
};
