<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name', 255)->nullable()->comment('Название раздела для админки');
            $table->string('title', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('h1', 255)->nullable();
            $table->timestamps();
        });

        $year = date('Y');
        $defaults = [
            ['slug' => 'home', 'name' => 'Главная', 'title' => 'Рингтоны на телефон бесплатно — скачать мелодии и звонки', 'description' => 'Скачать бесплатно рингтоны и мелодии на звонок телефона. Слушайте онлайн перед загрузкой.', 'h1' => 'Рингтоны на телефон'],
            ['slug' => 'search', 'name' => 'Поиск', 'title' => 'Рингтоны на телефон %query% бесплатно', 'description' => 'Скачать бесплатно рингтоны и звонки по запросу «%query%» на телефон с нашего сайта без регистрации.', 'h1' => null],
            ['slug' => 'popular', 'name' => 'Популярные', 'title' => 'Популярные рингтоны и мелодии на звонок телефона', 'description' => 'Выбрать и скачать бесплатно популярные рингтоны и мелодии на звонок телефона за ' . $year . ' год, прослушать онлайн в плеере.', 'h1' => 'Популярные рингтоны'],
            ['slug' => 'best', 'name' => 'Лучшие (хиты)', 'title' => 'Лучшие рингтоны и мелодии на звонок телефона', 'description' => 'Выбрать и скачать бесплатно лучшие рингтоны и мелодии на телефон за ' . $year . ' год, предварительно прослушав без регистрации.', 'h1' => 'Лучшие рингтоны'],
            ['slug' => 'articles_index', 'name' => 'Раздел статей', 'title' => 'Статьи о рингтонах и звуках', 'description' => 'Статьи и полезные материалы о рингтонах, мелодиях и звуках для телефона.', 'h1' => 'Статьи'],
        ];

        foreach ($defaults as $row) {
            $row['created_at'] = $row['updated_at'] = now();
            \DB::table('seo_templates')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_templates');
    }
};
