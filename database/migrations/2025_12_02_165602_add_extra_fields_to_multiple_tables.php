<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'authors',
        'categories',
        'tags',
        'pages',
        'articles',
        'types',
        'materials',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, static function (Blueprint $table) {
                $table->string('h1')->nullable()->after('description');
                $table->string('long_description', 5000)->nullable()->after('h1');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['h1', 'long_description']);
            });
        }
    }
};
