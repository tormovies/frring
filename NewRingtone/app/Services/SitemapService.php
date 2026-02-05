<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Material;
use App\Models\Page;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public const CACHE_KEY = 'sitemap_xml';
    public const CACHE_AT_KEY = 'sitemap_cached_at';
    public const CACHE_TTL_SECONDS = 86400; // 24 часа

    /**
     * Количество URL по типам (для админки).
     */
    public function getCounts(): array
    {
        return [
            'main' => 1,
            'static' => 3, // popular, best, articles index
            'materials' => Material::active()->count(),
            'categories' => Category::active()->count(),
            'tags' => Tag::active()->count(),
            'articles' => Article::active()->count(),
            'pages' => Page::active()->count(),
        ];
    }

    /**
     * Общее количество URL в sitemap.
     */
    public function getTotalCount(): int
    {
        return array_sum($this->getCounts());
    }

    /**
     * Когда последний раз кешировали sitemap (timestamp или null).
     */
    public function getCachedAt(): ?\DateTimeInterface
    {
        $at = Cache::get(self::CACHE_AT_KEY);
        return $at instanceof \DateTimeInterface ? $at : null;
    }

    /**
     * Очистить кеш sitemap.
     */
    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_AT_KEY);
    }

    /**
     * Получить XML sitemap (из кеша или сгенерировать).
     */
    public function getXml(): string
    {
        $xml = Cache::get(self::CACHE_KEY);
        if ($xml !== null) {
            return $xml;
        }
        $xml = $this->buildXml();
        Cache::put(self::CACHE_KEY, $xml, self::CACHE_TTL_SECONDS);
        Cache::put(self::CACHE_AT_KEY, now(), self::CACHE_TTL_SECONDS);
        return $xml;
    }

    /**
     * Принудительно пересобрать и закешировать sitemap.
     */
    public function refreshCache(): string
    {
        $this->forgetCache();
        return $this->getXml();
    }

    private function buildXml(): string
    {
        $urls = [];

        $urls[] = $this->url(rtrim(config('app.url'), '/') . '/', now(), 'daily', '1.0');
        $urls[] = $this->url(route('materials.popular', ['offset' => 0]), now(), 'weekly', '0.8');
        $urls[] = $this->url(route('materials.best', ['offset' => 0]), now(), 'weekly', '0.8');
        $urls[] = $this->url(route('articles.index'), now(), 'weekly', '0.8');

        foreach (Material::active()->select('slug', 'updated_at')->cursor() as $material) {
            $urls[] = $this->url(route('materials.show', $material->slug), $material->updated_at, 'weekly', '0.7');
        }
        foreach (Category::active()->select('slug', 'updated_at')->cursor() as $item) {
            $urls[] = $this->url(route('categories.show', $item->slug), $item->updated_at, 'weekly', '0.7');
        }
        foreach (Tag::active()->select('slug', 'updated_at')->cursor() as $item) {
            $urls[] = $this->url(route('tags.show', $item->slug), $item->updated_at, 'weekly', '0.6');
        }
        foreach (Article::active()->select('slug', 'updated_at')->cursor() as $item) {
            $urls[] = $this->url(route('articles.show', $item->slug), $item->updated_at, 'weekly', '0.7');
        }
        foreach (Page::active()->select('slug', 'updated_at')->cursor() as $item) {
            $urls[] = $this->url(route('pages.show', $item->slug), $item->updated_at, 'monthly', '0.5');
        }

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $out .= '  <url>' . "\n";
            $out .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            $out .= '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            $out .= '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
            $out .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $out .= '  </url>' . "\n";
        }
        $out .= '</urlset>';
        return $out;
    }

    private function url(string $loc, $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod instanceof \DateTimeInterface ? $lastmod->format('Y-m-d') : now()->format('Y-m-d'),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
