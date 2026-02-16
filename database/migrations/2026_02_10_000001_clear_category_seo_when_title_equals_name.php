<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Обнуляем title, description, h1 у категорий, где title совпадает с name
     * (при импорте туда вбивалось название вместо SEO) — тогда применяется SEO-шаблон.
     */
    public function up(): void
    {
        $updated = DB::table('categories')
            ->whereRaw('TRIM(COALESCE(title, "")) = TRIM(COALESCE(name, ""))')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->update([
                'title' => '',
                'description' => '',
                'h1' => null,
                'updated_at' => now(),
            ]);

        if ($updated > 0) {
            \Illuminate\Support\Facades\Log::info("clear_category_seo_when_title_equals_name: обновлено {$updated} категорий");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Нельзя восстановить старые значения — откат пустой
    }
};
