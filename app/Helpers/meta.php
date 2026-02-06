<?php

use App\Models\SeoTemplate;

if (!function_exists('seo_template')) {
    /**
     * SEO для общих разделов (главная, поиск, популярные, лучшие, статьи).
     * Возвращает ['title' => ..., 'description' => ..., 'h1' => ...] с подстановками %year%, %page%, %query%.
     */
    function seo_template(string $slug, ?string $query = null): array
    {
        $t = SeoTemplate::getBySlug($slug);
        $title = $t->title ?? '';
        $description = $t->description ?? '';
        $h1 = $t->h1 ?? '';

        if ($query !== null) {
            $title = str_replace('%query%', $query, $title);
            $description = str_replace('%query%', $query, $description);
            $h1 = $h1 !== '' ? str_replace('%query%', $query, $h1) : '';
        }

        return [
            'title' => trim(meta_replace($title)),
            'description' => trim(meta_replace($description)),
            'h1' => trim(meta_replace($h1)),
        ];
    }
}

if (!function_exists('seo_template_material')) {
    /**
     * SEO для страницы материала по шаблону (slug=material).
     * Подстановки: %item_name%, %author%, %category%, %year%, %site_name%.
     */
    function seo_template_material(\App\Models\Material $material): array
    {
        $t = \App\Models\SeoTemplate::getBySlug('material');
        $title = $t?->title ?? 'Рингтон %item_name% — скачать бесплатно';
        $description = $t?->description ?? 'Слушайте и скачайте рингтон «%item_name%» на ' . config('app.name');
        $h1 = $t?->h1 ?? '';

        return [
            'title' => trim(meta_replace($title, $material)),
            'description' => trim(\Illuminate\Support\Str::limit(meta_replace($description, $material), 250)),
            'h1' => $h1 !== '' ? trim(meta_replace($h1, $material)) : '',
        ];
    }
}

if (!function_exists('seo_template_category')) {
    /**
     * SEO для страницы категории по шаблону (slug=category).
     * Подстановки: %cat_name%, %year%, %site_name%.
     */
    function seo_template_category(\App\Models\Category $category): array
    {
        $t = \App\Models\SeoTemplate::getBySlug('category');
        $title = $t?->title ?? 'Рингтоны %cat_name% — скачать бесплатно';
        $description = $t?->description ?? 'Скачать бесплатно рингтоны категории «%cat_name%» на телефон.';
        $h1 = $t?->h1 ?? '';

        return [
            'title' => trim(meta_replace($title, $category)),
            'description' => trim(\Illuminate\Support\Str::limit(meta_replace($description, $category), 250)),
            'h1' => $h1 !== '' ? trim(meta_replace($h1, $category)) : '',
        ];
    }
}

if (!function_exists('seo_template_page')) {
    /**
     * SEO для статической страницы по шаблону (slug=page).
     * Подстановки: %page_name%, %year%, %site_name%.
     */
    function seo_template_page(\App\Models\Page $page): array
    {
        $t = \App\Models\SeoTemplate::getBySlug('page');
        $title = $t?->title ?? '%page_name% — ' . config('app.name');
        $description = $t?->description ?? '%page_name%. ' . config('app.name');
        $h1 = $t?->h1 ?? '';

        return [
            'title' => trim(meta_replace($title, $page)),
            'description' => trim(\Illuminate\Support\Str::limit(meta_replace($description, $page), 250)),
            'h1' => $h1 !== '' ? trim(meta_replace($h1, $page)) : '',
        ];
    }
}

if (!function_exists('meta_replace')) {
    /**
     * Подстановки: %year%, %page%, %site_name%;
     * при передаче Material: %item_name%, %item_name_lower%, %author%, %category%, %bitrate%, %duration%;
     * при передаче Category: %cat_name%;
     * при передаче Page: %page_name%.
     */
    function meta_replace(string $text, $context = null): string
    {
        $text = str_replace('%year%', date('Y'), $text);
        $text = str_replace('%site_name%', config('app.name', ''), $text);

        $pageNumber = request()->integer('page', 1);
        $text = str_replace('%page%', $pageNumber > 1 ? 'Страница ' . $pageNumber : '', $text);

        if ($context instanceof \App\Models\Category) {
            $text = str_replace('%cat_name%', $context->name ?? '', $text);
        } elseif ($context instanceof \App\Models\Page) {
            $text = str_replace('%page_name%', $context->name ?? $context->title ?? '', $text);
        } elseif ($context instanceof \App\Models\Material) {
            $name = $context->name ?? '';
            $text = str_replace('%item_name%', $name, $text);
            $text = str_replace('%item_name_lower%', mb_strtolower($name, 'UTF-8'), $text);
            $text = str_replace('%author%', optional($context->authors->first())->name ?? config('app.name', 'AI'), $text);
            $text = str_replace('%bitrate%', $context->mp4_bitrate ?? 128, $text);
            $text = str_replace('%duration%', $context->mp4_duration ? gmdate('i:s', (int) $context->mp4_duration) : '—', $text);
            $text = str_replace('%category%', $context->type->name ?? '', $text);
        }

        return trim($text);
    }
}

if (!function_exists('duration_iso8601')) {
    /**
     * Преобразует длительность в секундах в формат ISO 8601 (например PT1M30S).
     */
    function duration_iso8601(?int $seconds): ?string
    {
        if ($seconds === null || $seconds < 0) {
            return null;
        }
        $m = (int) floor($seconds / 60);
        $s = $seconds % 60;
        return 'PT' . $m . 'M' . $s . 'S';
    }
}
