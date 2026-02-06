<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Шаблон SEO для страницы материала (как на старом сайте: seo_type=ITE, seo_item=0).
     * Подстановки: %item_name%, %author%, %category%, %year%.
     */
    public function up(): void
    {
        if (DB::table('seo_templates')->where('slug', 'material')->exists()) {
            return;
        }

        DB::table('seo_templates')->insert([
            'slug' => 'material',
            'name' => 'Страница материала (рингтон)',
            'title' => 'Рингтон %item_name% на звонок телефона — скачать бесплатно',
            'description' => 'Слушайте и скачайте рингтон «%item_name%» бесплатно на телефон. %author%, %category%.',
            'h1' => 'Рингтон %item_name%',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('seo_templates')->where('slug', 'material')->delete();
    }
};
