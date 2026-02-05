<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Проверка локального (или любого) сайта на битые ссылки и картинки.
 * Список страниц берётся из sitemap.xml по base URL или из файла путей.
 */
class CheckBrokenLinksCommand extends Command
{
    protected $signature = 'check:broken-links
                            {--url= : Базовый URL (по умолчанию APP_URL, например http://127.0.0.1:3000)}
                            {--paths= : Файл со списком путей (по одному на строку), иначе из sitemap}
                            {--limit= : Макс. страниц для проверки (по умолчанию без лимита; укажите число, например 100, для быстрой проверки)}
                            {--external : Проверять также внешние ссылки и картинки (медленно)}
                            {--out= : Сохранить отчёт в файл (markdown)}';

    protected $description = 'Проверить сайт на битые ссылки и изображения (по sitemap или списку путей)';

    private string $baseUrl;
    private int $timeout = 10;
    private bool $checkExternal;
    private array $checked = [];
    private array $broken = [];
    private array $brokenFromPage = [];

    public function handle(): int
    {
        $this->baseUrl = rtrim($this->option('url') ?? config('app.url', 'http://127.0.0.1:3000'), '/');
        $this->checkExternal = (bool) $this->option('external');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : 0;

        $this->info('Базовый URL: ' . $this->baseUrl);
        $this->info('Проверка внешних: ' . ($this->checkExternal ? 'да' : 'нет (только свои)'));
        $this->newLine();

        $pages = $this->getPagesToCheck($limit);
        if (empty($pages)) {
            $this->warn('Нет страниц для проверки. Запустите сервер и убедитесь, что sitemap доступен, или укажите --paths=файл.txt');
            return self::FAILURE;
        }

        $this->info('Страниц к проверке: ' . count($pages));
        $bar = $this->output->createProgressBar(count($pages));
        $bar->start();

        foreach ($pages as $pageUrl) {
            $this->checkPage($pageUrl);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $this->report();
        $outFile = $this->option('out');
        if ($outFile) {
            file_put_contents($outFile, $this->reportMarkdown());
            $this->info("Отчёт сохранён: {$outFile}");
        }

        return empty($this->broken) ? self::SUCCESS : self::FAILURE;
    }

    private function getPagesToCheck(int $limit): array
    {
        $pathsFile = $this->option('paths');
        if ($pathsFile && is_readable($pathsFile)) {
            $lines = file($pathsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $paths = array_values(array_filter(array_map('trim', $lines), fn ($p) => $p !== '' && ($p[0] === '/' || preg_match('#^https?://#', $p))));
            $urls = [];
            foreach ($paths as $p) {
                $urls[] = str_starts_with($p, 'http') ? $p : $this->baseUrl . $p;
            }
            return $limit > 0 ? array_slice($urls, 0, $limit) : $urls;
        }

        $sitemapUrl = $this->baseUrl . '/sitemap.xml';
        try {
            $r = Http::timeout($this->timeout)->get($sitemapUrl);
        } catch (\Throwable $e) {
            $this->newLine();
            $this->warn('Sitemap недоступен: ' . $e->getMessage());
            return $this->getFallbackPaths($limit);
        }
        if (!$r->successful()) {
            $this->newLine();
            $this->warn('Sitemap вернул HTTP ' . $r->status() . '. Используется запасной список путей.');
            if ($r->status() >= 500) {
                $this->line('  Подсказка: откройте <comment>' . $sitemapUrl . '</comment> в браузере или проверьте <comment>storage/logs/laravel.log</comment> для деталей ошибки.');
            }
            return $this->getFallbackPaths($limit);
        }
        $xml = $r->body();
        preg_match_all('#<loc>\s*([^<]+)\s*</loc>#u', $xml, $m);
        $urls = isset($m[1]) ? array_map('trim', $m[1]) : [];
        $urls = array_values(array_filter($urls, fn ($u) => $u !== ''));
        return $limit > 0 ? array_slice($urls, 0, $limit) : $urls;
    }

    /**
     * Запасной список путей, если sitemap недоступен (500, таймаут и т.д.).
     */
    private function getFallbackPaths(int $limit): array
    {
        $defaultPaths = ['/', '/play/anzhela-katis.html', '/category/na-vraga.html', '/page/programma-dlja-sozdanija-ringtonov.html'];
        $exampleFile = base_path('docs/check-broken-links.example.txt');
        if (is_readable($exampleFile)) {
            $lines = file($exampleFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $paths = array_values(array_filter(array_map('trim', $lines), fn ($p) => $p !== '' && $p[0] !== '#' && ($p[0] === '/' || preg_match('#^https?://#', $p))));
            if ($paths !== []) {
                $defaultPaths = $paths;
            }
        }
        $urls = array_map(fn ($p) => str_starts_with($p, 'http') ? $p : $this->baseUrl . $p, $defaultPaths);
        return $limit > 0 ? array_slice($urls, 0, $limit) : $urls;
    }

    private function checkPage(string $pageUrl): void
    {
        try {
            $r = Http::timeout($this->timeout)->get($pageUrl);
        } catch (\Throwable $e) {
            $this->addBroken($pageUrl, $pageUrl, 'error: ' . $e->getMessage());
            return;
        }
        if (!$r->successful()) {
            $this->addBroken($pageUrl, $pageUrl, 'HTTP ' . $r->status());
            return;
        }
        $html = $r->body();
        $this->extractAndCheck($pageUrl, $html);
    }

    private function extractAndCheck(string $fromUrl, string $html): void
    {
        $links = $this->extractHrefs($html);
        $images = $this->extractImgSrc($html);
        foreach ($links as $href) {
            $this->checkUrl($fromUrl, $href, 'link');
        }
        foreach ($images as $src) {
            $this->checkUrl($fromUrl, $src, 'image');
        }
    }

    private function extractHrefs(string $html): array
    {
        $urls = [];
        if (preg_match_all('/<a\s[^>]*href\s*=\s*["\']([^"\']+)["\'][^>]*>/iu', $html, $m)) {
            foreach ($m[1] as $href) {
                $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($href === '' || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }
                if (str_starts_with($href, '#')) {
                    continue;
                }
                $urls[] = $href;
            }
        }
        return array_unique($urls);
    }

    private function extractImgSrc(string $html): array
    {
        $urls = [];
        if (preg_match_all('/<img\s[^>]*src\s*=\s*["\']([^"\']+)["\'][^>]*>/iu', $html, $m)) {
            foreach ($m[1] as $src) {
                $src = trim(html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($src === '' || str_starts_with($src, 'data:')) {
                    continue;
                }
                $urls[] = $src;
            }
        }
        return array_unique($urls);
    }

    private function resolveUrl(string $basePageUrl, string $href): string
    {
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        $base = preg_replace('#/[^/]*$#', '/', $basePageUrl);
        if (str_starts_with($href, '//')) {
            return 'https:' . $href;
        }
        if (str_starts_with($href, '/')) {
            $host = parse_url($basePageUrl, PHP_URL_SCHEME) . '://' . parse_url($basePageUrl, PHP_URL_HOST);
            return rtrim($host, '/') . $href;
        }
        return rtrim($base, '/') . '/' . ltrim($href, '/');
    }

    private function isInternal(string $url): bool
    {
        $baseHost = parse_url($this->baseUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        if ($baseHost === null || $urlHost === null) {
            return true;
        }
        return strtolower($baseHost) === strtolower($urlHost);
    }

    private function checkUrl(string $fromUrl, string $href, string $type): void
    {
        $resolved = $this->resolveUrl($fromUrl, $href);
        if (!$this->checkExternal && !$this->isInternal($resolved)) {
            return;
        }
        $key = $type . ':' . $resolved;
        if (isset($this->checked[$key])) {
            return;
        }
        $this->checked[$key] = true;

        try {
            $r = Http::timeout(8)->head($resolved);
        } catch (\Throwable $e) {
            $this->addBroken($fromUrl, $resolved, 'error: ' . $e->getMessage(), $type);
            return;
        }
        if ($type === 'image' && $r->status() === 405) {
            $r = Http::timeout(8)->get($resolved);
        }
        $status = $r->status();
        if ($status >= 400) {
            $this->addBroken($fromUrl, $resolved, 'HTTP ' . $status, $type);
        }
    }

    private function addBroken(string $fromPage, string $targetUrl, string $reason, string $type = 'link'): void
    {
        $key = $targetUrl;
        if (!isset($this->broken[$key])) {
            $this->broken[$key] = ['reason' => $reason, 'type' => $type];
            $this->brokenFromPage[$key] = [];
        }
        $this->brokenFromPage[$key][] = $fromPage;
    }

    private function report(): void
    {
        if (empty($this->broken)) {
            $this->info('Битых ссылок и картинок не найдено.');
            return;
        }
        $this->error('Найдено битых: ' . count($this->broken));
        $this->table(
            ['URL', 'Тип', 'Причина', 'Встречается на страницах'],
            array_map(function ($url) {
                $info = $this->broken[$url];
                $pages = array_slice(array_unique($this->brokenFromPage[$url]), 0, 3);
                $pagesStr = implode(', ', $pages);
                if (count(array_unique($this->brokenFromPage[$url])) > 3) {
                    $pagesStr .= ', …';
                }
                return [$url, $info['type'], $info['reason'], $pagesStr];
            }, array_keys($this->broken))
        );
    }

    private function reportMarkdown(): string
    {
        $lines = ["# Отчёт проверки битых ссылок и картинок\n", "Базовый URL: {$this->baseUrl}\n", "Всего битых: " . count($this->broken) . "\n"];
        foreach ($this->broken as $url => $info) {
            $pages = array_unique($this->brokenFromPage[$url]);
            $lines[] = "- **{$url}** ({$info['type']}) — {$info['reason']}";
            $lines[] = "  Найдено на: " . implode(', ', array_slice($pages, 0, 5)) . (count($pages) > 5 ? ' …' : '');
        }
        return implode("\n", $lines);
    }
}
