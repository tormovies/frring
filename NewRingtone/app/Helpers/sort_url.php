<?php

/**
 * URL для блока «Новинки / Популярные / Лучшие» и пагинации в стиле старого сайта.
 * Offset в пути: 0, 24, 48, ... (24 материала на страницу).
 */

if (!function_exists('sort_filter_url')) {
    /**
     * URL первой страницы выбранной сортировки (для ссылок Новинки / Популярные / Лучшие).
     *
     * @param string $context 'main' | 'category' | 'tag'
     * @param string $sort    'new' | 'plays' | 'rating'
     * @param string|null $slug slug категории или тега (для context category/tag)
     */
    function sort_filter_url(string $context, string $sort, ?string $slug = null): string
    {
        return sort_page_url($context, $sort, $slug, 1);
    }
}

if (!function_exists('sort_page_url')) {
    /**
     * URL страницы с offset в пути (страница 1 = offset 0, страница 2 = offset 24, ...).
     *
     * @param string $context 'main' | 'category' | 'tag'
     * @param string $sort    'new' | 'plays' | 'rating'
     * @param string|null $slug slug категории/тега
     * @param int $page номер страницы (1-based)
     */
    function sort_page_url(string $context, string $sort, ?string $slug, int $page): string
    {
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        if ($context === 'main') {
            if ($sort === 'new') {
                return $page <= 1 ? url('/') : url('/?' . http_build_query(['page' => $page]));
            }
            if ($sort === 'plays') {
                return url("/category/index-{$offset}-plays.html");
            }
            if ($sort === 'rating') {
                return url("/category/index-{$offset}-rating.html");
            }
        }

        if ($context === 'category' && $slug !== null) {
            if ($sort === 'new') {
                return $offset === 0
                    ? url("/category/{$slug}.html")
                    : url("/category/{$slug}-{$offset}.html");
            }
            if ($sort === 'plays') {
                return url("/category/{$slug}-{$offset}-plays.html");
            }
            if ($sort === 'rating') {
                return url("/category/{$slug}-{$offset}-rating.html");
            }
        }

        if ($context === 'tag' && $slug !== null) {
            if ($sort === 'new') {
                return $offset === 0
                    ? url("/tag/{$slug}.html")
                    : url("/tag/{$slug}-{$offset}.html");
            }
            if ($sort === 'plays') {
                return url("/tag/{$slug}-{$offset}-plays.html");
            }
            if ($sort === 'rating') {
                return url("/tag/{$slug}-{$offset}-rating.html");
            }
        }

        return url('/');
    }
}

if (!function_exists('sort_per_page')) {
    function sort_per_page(): int
    {
        return 24;
    }
}
