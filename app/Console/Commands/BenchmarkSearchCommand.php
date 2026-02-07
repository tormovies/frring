<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Замер скорости поиска по материалам (name, long_description).
 * Использует ту же логику, что и SearchController.
 */
class BenchmarkSearchCommand extends Command
{
    protected $signature = 'benchmark:search
                            {--term= : Термин для поиска (по умолчанию: "любовь", "Ярик")}
                            {--rounds=3 : Количество прогонов для усреднения}';

    protected $description = 'Замерить скорость поиска по материалам (name, long_description)';

    public function handle(): int
    {
        $rounds = (int) $this->option('rounds');
        $terms = $this->option('term') !== null
            ? [$this->option('term')]
            : ['любовь', 'Ярик'];

        $this->info('Замер скорости поиска');
        $this->info('Прогонов для усреднения: ' . $rounds);
        $this->newLine();

        foreach ($terms as $term) {
            $this->runBenchmark($term, $rounds);
        }

        return self::SUCCESS;
    }

    private function runBenchmark(string $term, int $rounds): void
    {
        $this->info("Термин: «{$term}» (длина: " . mb_strlen($term) . ')');
        $this->info('---');

        $times = [];
        $counts = [];

        for ($i = 0; $i < $rounds; $i++) {
            DB::connection()->enableQueryLog();
            $start = microtime(true);

            $materials = $this->searchMaterials($term);

            $elapsed = (microtime(true) - $start) * 1000;
            $times[] = $elapsed;
            $counts[] = $materials->total();

            $queries = DB::getQueryLog();
            if ($i === 0) {
                $this->table(
                    ['#', 'Время (ms)', 'Запросов', 'Результатов'],
                    [['1', round($elapsed, 2), count($queries), $materials->total()]]
                );
                // Выводим первый (основной) запрос
                $mainQuery = $queries[0] ?? null;
                if ($mainQuery) {
                    $this->line('  Основной запрос: ' . round($mainQuery['time'] ?? 0, 2) . ' ms');
                }
            }
        }

        $avg = array_sum($times) / count($times);
        $min = min($times);
        $max = max($times);

        $this->info(sprintf('  Среднее: %.0f ms | min: %.0f ms | max: %.0f ms', $avg, $min, $max));
        $this->newLine();
    }

    private function searchMaterials(string $term)
    {
        $likeTerm = '%' . addcslashes($term, '%_\\') . '%';

        return Material::query()
            ->with(['type', 'authors', 'categories', 'tags'])
            ->where('status', true)
            ->where(function ($q) use ($term, $likeTerm) {
                if (mb_strlen($term) >= 3) {
                    $q->whereRaw(
                        'MATCH(name, long_description) AGAINST(? IN NATURAL LANGUAGE MODE)',
                        [$term]
                    );
                } else {
                    $q->where('name', 'like', $likeTerm)
                        ->orWhere('long_description', 'like', $likeTerm);
                }
            })
            ->orderByDesc('id')
            ->paginate(sort_per_page());
    }
}
