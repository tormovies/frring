<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Меняем FULLTEXT индекс: name/title/description → name/long_description.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE materials DROP INDEX materials_search_fulltext');
        DB::statement('ALTER TABLE materials ADD FULLTEXT INDEX materials_search_fulltext (name, long_description)');
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
        DB::statement('ALTER TABLE materials ADD FULLTEXT INDEX materials_search_fulltext (name, title, description)');
    }
};
