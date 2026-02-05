<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * У материалов без персонального SEO убираем title/description (оставшиеся
     * неправильные данные после импорта). На сайте будут использоваться
     * автоподстановки: title = name, description из long_description или фолбек.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('description', 500)->nullable()->change();
        });

        DB::table('materials')->update([
            'title' => null,
            'description' => null,
            'h1' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('description', 500)->nullable(false)->change();
        });
        // В down не восстанавливаем старые значения — их уже нет
    }
};
