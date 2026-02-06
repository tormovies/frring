<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Приводит title и description главной в соответствие со старым сайтом
     * (формулировки из core.php: индекс, рингтоны по дате).
     */
    public function up(): void
    {
        DB::table('seo_templates')
            ->where('slug', 'home')
            ->update([
                'title' => 'Рингтоны звонки и мелодии на телефон',
                'description' => 'Скачать бесплатно рингтоны музыку и мелодии на звонок телефона с нашего сайта, перед загрузкой их можно прослушать онлайн, у нас собраны самые новые и громкие звуки для смартфона созданные из нарезок композиций %year% года',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $year = date('Y');
        DB::table('seo_templates')
            ->where('slug', 'home')
            ->update([
                'title' => 'Рингтоны на телефон бесплатно — скачать мелодии и звонки',
                'description' => 'Скачать бесплатно рингтоны и мелодии на звонок телефона. Слушайте онлайн перед загрузкой.',
                'updated_at' => now(),
            ]);
    }
};
