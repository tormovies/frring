<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Services\ExternalLinksService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Удаляет внешние ссылки только из материалов (materials).
 * Ссылки на https://freeringtones.ru/ не удаляются.
 * В тексте: удаляется только ссылка (<a> или голый URL), текст внутри ссылки остаётся.
 * Сначала запустите с --dry-run, затем без него (с подтверждением или --force).
 */
class RemoveExternalLinksInDbCommand extends Command
{
    protected $signature = 'remove:external-links-db
                            {--dry-run : Только показать, что будет изменено, не сохранять}
                            {--force : Выполнить без запроса подтверждения}';

    protected $description = 'Удалить внешние ссылки из материалов (materials)';

    private ExternalLinksService $externalLinks;

    private const TEXT_COLUMNS = [
        Material::class => ['description', 'long_description', 'content'],
    ];

    public function handle(ExternalLinksService $externalLinks): int
    {
        $this->externalLinks = $externalLinks;
        $internalHosts = $externalLinks->getInternalHosts();
        $this->info('Внутренние домены (не удаляем): ' . implode(', ', $internalHosts));
        $this->line('Обрабатываются только материалы (materials). В тексте удаляется только ссылка, текст внутри сохраняется.');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('Режим --dry-run: изменения не сохраняются.');
            $this->newLine();
        } elseif (!$force && !$this->confirm('Удалить внешние ссылки из БД? Изменения сохранятся.', false)) {
            $this->info('Отменено.');
            return self::SUCCESS;
        }

        $updated = 0;

        foreach (self::TEXT_COLUMNS as $modelClass => $columns) {
            $updated += $this->processTextColumns($modelClass, $columns, $dryRun);
        }

        $this->newLine();
        $this->info($dryRun
            ? "Будет обновлено записей (только там, где есть внешние ссылки): {$updated}. Запустите без --dry-run для применения."
            : "Обновлено записей: {$updated}.");

        return self::SUCCESS;
    }

    private function processTextColumns(string $modelClass, array $columns, bool $dryRun): int
    {
        /** @var Model $modelClass */
        $query = $modelClass::query();
        $model = new $modelClass;
        $keyName = $model->getKeyName();
        $select = array_merge([$keyName], $columns);
        if (in_array('slug', $model->getFillable(), true)) {
            $select[] = 'slug';
        }
        $query->select(array_unique($select));

        $updated = 0;
        foreach ($query->cursor() as $row) {
            $changes = [];
            foreach ($columns as $col) {
                $value = $row->{$col};
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $newValue = $this->externalLinks->stripExternalLinksFromText($value);
                if ($newValue !== $value) {
                    $changes[$col] = $newValue;
                }
            }
            if ($changes !== []) {
                if (!$dryRun) {
                    $modelClass::where($keyName, $row->{$keyName})->update($changes);
                }
                $updated++;
                $slug = $row->slug ?? $row->{$keyName};
                $this->line("  {$model->getTable()} #{$row->{$keyName}} ({$slug}): " . implode(', ', array_keys($changes)));
            }
        }
        return $updated;
    }
}
