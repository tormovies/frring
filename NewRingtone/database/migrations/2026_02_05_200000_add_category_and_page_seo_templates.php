<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Шаблоны SEO для категорий и статических страниц (как на старом сайте CAT и PAG, seo_item=0).
     */
    public function up(): void
    {
        $rows = [
            [
                'slug' => 'category',
                'name' => 'Страница категории',
                'title' => 'Рингтоны %cat_name% — скачать бесплатно на телефон',
                'description' => 'Скачать бесплатно рингтоны и мелодии категории «%cat_name%» на звонок телефона. Слушайте онлайн, %site_name%.',
                'h1' => 'Рингтоны %cat_name%',
            ],
            [
                'slug' => 'page',
                'name' => 'Статическая страница',
                'title' => '%page_name% — %site_name%',
                'description' => '%page_name%. %site_name%.',
                'h1' => '%page_name%',
            ],
        ];

        foreach ($rows as $row) {
            if (DB::table('seo_templates')->where('slug', $row['slug'])->exists()) {
                continue;
            }
            $row['created_at'] = $row['updated_at'] = now();
            DB::table('seo_templates')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('seo_templates')->whereIn('slug', ['category', 'page'])->delete();
    }
};
