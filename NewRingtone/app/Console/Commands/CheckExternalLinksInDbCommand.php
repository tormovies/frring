<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Services\ExternalLinksService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Сканирует материалы (materials) на внешние ссылки.
 * Ссылки на freeringtones.ru считаются внутренними и не показываются.
 */
class CheckExternalLinksInDbCommand extends Command
{
    protected $signature = 'check:external-links-db
                            {--out= : Сохранить отчёт в файл (markdown)}';

    protected $description = 'Проверить материалы на наличие внешних ссылок в контенте';

    private array $found = [];

    private ExternalLinksService $externalLinks;

    private const TEXT_COLUMNS = [
        Material::class => ['description', 'long_description', 'content'],
    ];

    public function handle(ExternalLinksService $externalLinks): int
    {
        $this->externalLinks = $externalLinks;
        $internalHosts = $externalLinks->getInternalHosts();

        $this->info('Внутренние домены (не показываются): ' . implode(', ', $internalHosts));
        $this->line('Проверяются только материалы (materials).');
        $this->newLine();

        foreach (self::TEXT_COLUMNS as $modelClass => $columns) {
            $this->scanTextColumns($modelClass, $columns);
        }

        $this->report();
        $outFile = $this->option('out');
        if ($outFile) {
            file_put_contents($outFile, $this->reportMarkdown());
            $this->info("Отчёт сохранён: {$outFile}");
        }

        return self::SUCCESS;
    }

    private function scanTextColumns(string $modelClass, array $columns): void
    {
        /** @var Model $modelClass */
        $query = $modelClass::query();
        $model = new $modelClass;
        $table = $model->getTable();
        $keyName = $model->getKeyName();

        $select = array_merge([$keyName], $columns);
        if (in_array('slug', $model->getFillable(), true) || $model->getKeyName() === 'slug') {
            $select[] = 'slug';
        }
        $query->select(array_unique($select));

        foreach ($query->cursor() as $row) {
            $id = $row->{$keyName};
            $slug = isset($row->slug) ? $row->slug : $id;

            foreach ($columns as $col) {
                $value = $row->{$col};
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $urls = $this->extractUrlsFromText($value);
                foreach ($urls as $url) {
                    if ($this->externalLinks->isExternal($url)) {
                        $this->add($table, $slug, $col, $url);
                    }
                }
            }
        }
    }

    private function extractUrlsFromText(string $text): array
    {
        $urls = [];
        // href="..." или href='...'
        if (preg_match_all('#\bhref\s*=\s*["\'](https?://[^"\']+)["\']#iu', $text, $m)) {
            foreach ($m[1] as $u) {
                $urls[] = trim(html_entity_decode($u, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }
        // src="..." для картинок/скриптов
        if (preg_match_all('#\bsrc\s*=\s*["\'](https?://[^"\']+)["\']#iu', $text, $m)) {
            foreach ($m[1] as $u) {
                $urls[] = trim(html_entity_decode($u, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }
        // голые URL в тексте
        if (preg_match_all('#https?://[^\s<>"\']+#u', $text, $m)) {
            foreach ($m[0] as $u) {
                $urls[] = trim($u);
            }
        }
        return array_values(array_unique($urls));
    }

    private function add(string $table, int|string $recordId, string $column, string $url): void
    {
        $key = $table . '|' . $recordId . '|' . $column . '|' . $url;
        if (!isset($this->found[$key])) {
            $this->found[$key] = [
                'table' => $table,
                'record' => $recordId,
                'column' => $column,
                'url' => $url,
            ];
        }
    }

    private function report(): void
    {
        if (empty($this->found)) {
            $this->info('Внешних ссылок в БД не найдено.');
            return;
        }

        $this->warn('Найдено внешних ссылок: ' . count($this->found));
        $this->newLine();

        $rows = [];
        foreach ($this->found as $f) {
            $rows[] = [$f['table'], $f['record'], $f['column'], $f['url']];
        }
        $this->table(['Таблица', 'Запись (id/slug)', 'Колонка', 'URL'], $rows);
    }

    private function reportMarkdown(): string
    {
        $internal = implode(', ', $this->externalLinks->getInternalHosts());
        $lines = ["# Внешние ссылки в материалах\n", "Внутренние домены: {$internal}\n", "Всего внешних: " . count($this->found) . "\n\n"];
        foreach ($this->found as $f) {
            $lines[] = "- **{$f['table']}** (запись: {$f['record']}, колонка: `{$f['column']}`): {$f['url']}\n";
        }
        return implode('', $lines);
    }
}
