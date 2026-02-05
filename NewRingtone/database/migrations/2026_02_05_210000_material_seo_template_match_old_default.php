<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Привести шаблон материала (slug=material) к дефолту старого сайта (seo ITE seo_item=0).
     * На старом сайте %item_name% подставляется как mb_strtolower(name - original_name);
     * у нас для совпадения используем %item_name_lower% в шаблоне.
     */
    public function up(): void
    {
        DB::table('seo_templates')
            ->where('slug', 'material')
            ->update([
                'title' => 'Скачать рингтон %item_name_lower% 😜',
                'description' => 'Скачать рингтон из мелодии %item_name_lower% на звонок телефона бесплатно, можно перед этим его прослушать онлайн',
                'h1' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('seo_templates')
            ->where('slug', 'material')
            ->update([
                'title' => 'Рингтон %item_name% на звонок телефона — скачать бесплатно',
                'description' => 'Слушайте и скачайте рингтон «%item_name%» бесплатно на телефон. %author%, %category%.',
                'h1' => 'Рингтон %item_name%',
                'updated_at' => now(),
            ]);
    }
};
