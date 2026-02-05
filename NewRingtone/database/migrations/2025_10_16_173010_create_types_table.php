<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('types', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('img')->nullable();
            $table->string('title');
            $table->string('description', 500);
            $table->text('content')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Начальные типы
        DB::table('types')->insert([
            [
                'name' => 'Рингтоны',
                'slug' => Str::slug('Рингтоны'),
                'title' => 'Рингтоны',
                'description' => 'Подборка популярных рингтонов для вашего телефона.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Аудиоцитаты',
                'slug' => Str::slug('Аудиоцитаты'),
                'title' => 'Аудиоцитаты',
                'description' => 'Коллекция коротких аудиоцитат для вдохновения и мотивации.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Фоновые мелодии',
                'slug' => Str::slug('Фоновые мелодии'),
                'title' => 'Фоновые мелодии',
                'description' => 'Мелодии для видео, презентаций и подкастов.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Музыка для подкастов',
                'slug' => Str::slug('Музыка для подкастов'),
                'title' => 'Музыка для подкастов',
                'description' => 'Аудиофон и музыкальные вставки для подкастов.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types');
    }
};
