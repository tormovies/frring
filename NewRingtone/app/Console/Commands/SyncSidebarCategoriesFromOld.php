<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSidebarCategoriesFromOld extends Command
{
    protected $signature = 'sidebar:sync-from-old';

    protected $description = 'Взять список категорий (parent_cat=0, on_main=1) из старой БД и записать в статичный шаблон правого блока';

    public function handle(): int
    {
        $old = DB::connection('old');

        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            return self::FAILURE;
        }

        $sql = "SELECT cats.cat_alias, cats.cat_name, COUNT(*) AS summ
                FROM cats, ringtone_gat
                WHERE cats.parent_cat = '0' AND cats.on_main = '1'
                  AND cats.cat_id = ringtone_gat.catid
                GROUP BY cats.cat_id
                ORDER BY cats.cat_name ASC";

        $rows = $old->select($sql);

        if (empty($rows)) {
            $this->warn('В старой БД нет категорий с parent_cat=0 и on_main=1. Шаблон не изменён.');
            return self::SUCCESS;
        }

        $lines = [];
        foreach ($rows as $r) {
            $alias = e($r->cat_alias ?? '');
            $name = e($r->cat_name ?? '');
            $summ = (int) ($r->summ ?? 0);
            $lines[] = "            <li><a href=\"{{ url('/category/{$alias}.html') }}\" title=\"{$name}\"><span>{$name}</span> <span class=\"nums\">{$summ}</span></a></li>";
        }
        $listBody = implode("\n", $lines);

        $content = <<<BLADE
{{--
  Правый блок категорий — как на старом сайте (aside_right, leftcats.tpl).
  Обновить: php artisan sidebar:sync-from-old
--}}
<div class="aside_right popup" id="category">
    <nav class="nav_aside">
        <ul>
{$listBody}
        </ul>
    </nav>
</div>
BLADE;

        $path = resource_path('views/partials/sidebar-categories.blade.php');
        file_put_contents($path, $content);

        $this->info('Записано категорий: ' . count($rows));
        $this->info('Файл: ' . $path);

        return self::SUCCESS;
    }
}
