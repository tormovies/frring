<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'title',
        'description',
        'h1',
    ];

    /**
     * Получить шаблон по slug. Подстановки %query%, %year%, %page% применяются при вызове meta_replace() во view.
     */
    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Значения для подстановки %query% (например, на странице поиска).
     */
    public function replaceQuery(string $query): void
    {
        $this->title = str_replace('%query%', $query, $this->title ?? '');
        $this->description = str_replace('%query%', $query, $this->description ?? '');
        $this->h1 = $this->h1 ? str_replace('%query%', $query, $this->h1) : null;
    }
}
