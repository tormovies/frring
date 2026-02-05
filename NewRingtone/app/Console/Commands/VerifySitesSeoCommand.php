<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerifySitesSeoCommand extends Command
{
    protected $signature = 'verify:sites-seo
                            {--old= : Базовый URL старого сайта (например https://freeringtones.ru)}
                            {--new= : Базовый URL нового сайта (например http://127.0.0.1:3000)}
                            {--paths= : Файл со списком путей (по одному на строку), иначе используется встроенный список}
                            {--out= : Сохранить отчёт в файл (markdown)}';

    protected $description = 'Парсит живой старый и новый сайты, собирает SEO (title, description, h1 и др.) и выводит сравнение';

    /** Пути для проверки по умолчанию */
    private const DEFAULT_PATHS = [
        '/',
        '/category/na-vraga.html',
        '/category/klubnye.html',
        '/category/bez-slov.html',
        '/category/iz-filmov.html',
        '/play/anzhela-katis.html',
        '/play/super-rington-klassika-privet-morrikone.html',
        '/page/programma-dlja-sozdanija-ringtonov.html',
    ];

    public function handle(): int
    {
        $oldBase = rtrim($this->option('old') ?? config('verify.old_site_url') ?? 'https://freeringtones.ru', '/');
        $newBase = rtrim($this->option('new') ?? config('verify.new_site_url') ?? 'http://127.0.0.1:3000', '/');

        $paths = $this->getPaths();
        if ($paths === []) {
            $this->warn('Нет путей для проверки.');
            return self::FAILURE;
        }

        $this->info("Старый сайт: {$oldBase}");
        $this->info("Новый сайт: {$newBase}");
        $this->info('Путей: ' . count($paths));
        $this->newLine();

        $rows = [];
        $headers = ['Путь', 'Источник', 'Title', 'Description', 'H1', 'Canonical', 'Совпадение'];

        foreach ($paths as $path) {
            $oldUrl = $oldBase . $path;
            $newUrl = $newBase . $path;

            $oldData = $this->fetchSeo($oldUrl);
            $newData = $this->fetchSeo($newUrl);

            $titleOk = $this->normalize($oldData['title']) === $this->normalize($newData['title']);
            $descOk = $this->normalize($oldData['description']) === $this->normalize($newData['description']);
            $h1Ok = $this->normalize($oldData['h1']) === $this->normalize($newData['h1']);
            $allOk = $titleOk && $descOk && $h1Ok;

            $matchStr = $allOk ? '✓' : ($titleOk ? '' : 'title') . ($descOk ? '' : ' desc') . ($h1Ok ? '' : ' h1');
            if ($matchStr !== '✓') {
                $matchStr = trim($matchStr) ?: '—';
            }

            $rows[] = [
                'path' => $path,
                'old' => $oldData,
                'new' => $newData,
                'match' => $matchStr,
            ];
        }

        $this->printTable($rows);
        $reportMd = $this->buildMarkdownReport($oldBase, $newBase, $rows);

        if ($outFile = $this->option('out')) {
            \Illuminate\Support\Facades\File::put($outFile, $reportMd);
            $this->newLine();
            $this->info("Отчёт сохранён: {$outFile}");
        }

        return self::SUCCESS;
    }

    private function getPaths(): array
    {
        $pathsFile = $this->option('paths');
        if ($pathsFile && is_readable($pathsFile)) {
            $lines = file($pathsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $paths = array_map('trim', $lines);
            return array_values(array_filter($paths, fn ($p) => $p !== '' && (mb_substr($p, 0, 1) !== '#')));
        }
        return self::DEFAULT_PATHS;
    }

    private function fetchSeo(string $url): array
    {
        $out = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'h1' => '',
            'canonical' => '',
            'og_title' => '',
            'og_description' => '',
            'error' => null,
        ];

        try {
            $response = Http::timeout(15)->withOptions(['verify' => false])->get($url);
        } catch (\Throwable $e) {
            $out['error'] = $e->getMessage();
            return $out;
        }

        if (!$response->successful()) {
            $out['error'] = 'HTTP ' . $response->status();
            return $out;
        }

        $html = $response->body();

        if (preg_match('/<title[^>]*>\s*([^<]+)\s*<\/title>/is', $html, $m)) {
            $out['title'] = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']*)["\']/is', $html, $m)) {
            $out['description'] = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<meta\s+name=["\']keywords["\']\s+content=["\']([^"\']*)["\']/is', $html, $m)) {
            $out['keywords'] = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<h1[^>]*>\s*([^<]+)\s*<\/h1>/is', $html, $m)) {
            $out['h1'] = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\']([^"\']*)["\']/is', $html, $m)) {
            $out['canonical'] = trim($m[1]);
        }
        if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']*)["\']/is', $html, $m)) {
            $out['og_title'] = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\']([^"\']*)["\']/is', $html, $m)) {
            $out['og_description'] = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $out;
    }

    private function normalize(string $s): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        return mb_strtolower($s, 'UTF-8');
    }

    private function printTable(array $rows): void
    {
        foreach ($rows as $r) {
            $path = $r['path'];
            $old = $r['old'];
            $new = $r['new'];
            $match = $r['match'];

            $this->line('<fg=cyan>▶ ' . $path . '</>');
            if ($old['error']) {
                $this->line('  Старый: <fg=red>' . $old['error'] . '</>');
            } else {
                $this->line('  Старый title: ' . mb_substr($old['title'], 0, 70) . (mb_strlen($old['title']) > 70 ? '…' : ''));
                $this->line('  Старый desc:  ' . mb_substr($old['description'], 0, 70) . (mb_strlen($old['description']) > 70 ? '…' : ''));
                $this->line('  Старый h1:    ' . mb_substr($old['h1'], 0, 70) . (mb_strlen($old['h1']) > 70 ? '…' : ''));
            }
            if ($new['error']) {
                $this->line('  Новый:  <fg=red>' . $new['error'] . '</>');
            } else {
                $this->line('  Новый title:  ' . mb_substr($new['title'], 0, 70) . (mb_strlen($new['title']) > 70 ? '…' : ''));
                $this->line('  Новый desc:   ' . mb_substr($new['description'], 0, 70) . (mb_strlen($new['description']) > 70 ? '…' : ''));
                $this->line('  Новый h1:     ' . mb_substr($new['h1'], 0, 70) . (mb_strlen($new['h1']) > 70 ? '…' : ''));
            }
            $color = $match === '✓' ? 'green' : 'yellow';
            $this->line('  <fg=' . $color . '>Совпадение: ' . $match . '</>');
            $this->newLine();
        }
    }

    private function buildMarkdownReport(string $oldBase, string $newBase, array $rows): string
    {
        $lines = [
            '# Сравнение SEO: старый vs новый сайт',
            '',
            '- **Старый:** ' . $oldBase,
            '- **Новый:** ' . $newBase,
            '- **Дата:** ' . now()->format('Y-m-d H:i'),
            '',
            '| Путь | | Старый Title | Новый Title | Старый Desc | Новый Desc | H1 совп. |',
            '|------|---|--------------|-------------|-------------|------------|----------|',
        ];

        foreach ($rows as $r) {
            $path = $r['path'];
            $old = $r['old'];
            $new = $r['new'];
            $match = $r['match'];
            $titleOk = $this->normalize($old['title']) === $this->normalize($new['title']);
            $descOk = $this->normalize($old['description']) === $this->normalize($new['description']);
            $h1Ok = $this->normalize($old['h1']) === $this->normalize($new['h1']);

            $st = fn ($s, int $max = 50) => str_replace(['|', "\n", "\r"], [' ', ' ', ''], mb_substr($s, 0, $max)) . (mb_strlen($s) > $max ? '…' : '');
            $err = $old['error'] ?: $new['error'];
            $status = $err ? '⚠ ' . $err : ($match === '✓' ? '✓' : '✗');
            $h1Col = $h1Ok ? '✓' : '✗';
            $lines[] = '| ' . $path . ' | ' . $status . ' | ' . $st($old['title']) . ' | ' . $st($new['title']) . ' | ' . $st($old['description']) . ' | ' . $st($new['description']) . ' | ' . $h1Col . ' |';
        }

        $lines[] = '';
        $lines[] = '## Детали по каждому пути';
        $lines[] = '';

        foreach ($rows as $r) {
            $path = $r['path'];
            $old = $r['old'];
            $new = $r['new'];
            $lines[] = '### ' . $path;
            $lines[] = '';
            $esc = fn ($s) => str_replace(["\r", "\n"], ' ', (string) $s);
            $lines[] = '**Старый:**';
            $lines[] = '- title: ' . $esc($old['title']);
            $lines[] = '- description: ' . $esc($old['description']);
            $lines[] = '- h1: ' . $esc($old['h1']);
            $lines[] = '- canonical: ' . $old['canonical'];
            if ($old['error']) {
                $lines[] = '- error: ' . $old['error'];
            }
            $lines[] = '';
            $lines[] = '**Новый:**';
            $lines[] = '- title: ' . $esc($new['title']);
            $lines[] = '- description: ' . $esc($new['description']);
            $lines[] = '- h1: ' . $esc($new['h1']);
            $lines[] = '- canonical: ' . $esc($new['canonical']);
            if ($new['error']) {
                $lines[] = '- error: ' . $new['error'];
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
