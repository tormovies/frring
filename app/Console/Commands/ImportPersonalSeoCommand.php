<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Material;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Подтягивает персональный SEO (title, description, h1) из старой БД
 * только для тех записей, у которых в старой таблице seo заданы свои значения.
 * Совпадение: по slug (материалы — ringtone.alias, категории — cats.cat_alias, страницы — pages.page_alias).
 */
class ImportPersonalSeoCommand extends Command
{
    protected $signature = 'import:personal-seo
                            {--materials : Только материалы (ITE)}
                            {--categories : Только категории (CAT)}
                            {--pages : Только страницы (PAG)}
                            {--dry-run : Без записи в БД}';

    protected $description = 'Вставить персональный SEO из старой БД в материалы, категории и страницы (где в старом seo есть свои title/description)';

    public function handle(): int
    {
        $old = DB::connection('old');
        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            return self::FAILURE;
        }

        $doMaterials = $this->option('materials') || (!$this->option('materials') && !$this->option('categories') && !$this->option('pages'));
        $doCategories = $this->option('categories') || (!$this->option('materials') && !$this->option('categories') && !$this->option('pages'));
        $doPages = $this->option('pages') || (!$this->option('materials') && !$this->option('categories') && !$this->option('pages'));

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Режим dry-run: изменения не сохраняются.');
        }

        $total = 0;
        if ($doMaterials) {
            $total += $this->syncMaterialsSeo($old, $dryRun);
        }
        if ($doCategories) {
            $total += $this->syncCategoriesSeo($old, $dryRun);
        }
        if ($doPages) {
            $total += $this->syncPagesSeo($old, $dryRun);
        }

        $this->info('Обновлено записей: ' . $total . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }

    private function syncMaterialsSeo(\Illuminate\Database\Connection $old, bool $dryRun): int
    {
        $this->info('Материалы (ITE)...');
        $rows = $old->table('seo')
            ->where('seo_type', 'ITE')
            ->where(function ($q) {
                $q->whereNotNull('seo_title')->where('seo_title', '!=', '')
                    ->orWhereNotNull('seo_description')->where('seo_description', '!=', '')
                    ->orWhereNotNull('seo_h1')->where('seo_h1', '!=', '');
            })
            ->get();

        $ringtoneIds = $rows->pluck('seo_item')->unique()->filter()->values()->all();
        if (empty($ringtoneIds)) {
            $this->line('  Нет записей с персональным SEO.');
            return 0;
        }

        $aliases = $old->table('ringtone')->whereIn('id', $ringtoneIds)->pluck('alias', 'id')->all();
        $seoByItem = $rows->keyBy('seo_item');
        $updated = 0;

        foreach ($aliases as $oldId => $alias) {
            $slug = Str::limit((string) $alias, 255, '');
            if ($slug === '') {
                continue;
            }
            $material = Material::where('slug', $slug)->first();
            if (!$material) {
                continue;
            }
            $seo = $seoByItem->get($oldId);
            $title = ($seo && trim((string) ($seo->seo_title ?? '')) !== '') ? Str::limit($seo->seo_title, 255, '') : null;
            $description = ($seo && trim((string) ($seo->seo_description ?? '')) !== '') ? Str::limit(strip_tags($seo->seo_description), 250, '') : null;
            $h1 = ($seo && trim((string) ($seo->seo_h1 ?? '')) !== '') ? Str::limit($seo->seo_h1, 255, '') : null;

            if (!$dryRun) {
                $material->update(['title' => $title, 'description' => $description, 'h1' => $h1]);
            }
            $updated++;
        }
        $this->line('  Материалов: ' . $updated);
        return $updated;
    }

    private function syncCategoriesSeo(\Illuminate\Database\Connection $old, bool $dryRun): int
    {
        $this->info('Категории (CAT)...');
        $rows = $old->table('seo')
            ->where('seo_type', 'CAT')
            ->where(function ($q) {
                $q->whereNotNull('seo_title')->where('seo_title', '!=', '')
                    ->orWhereNotNull('seo_description')->where('seo_description', '!=', '')
                    ->orWhereNotNull('seo_h1')->where('seo_h1', '!=', '');
            })
            ->get();

        $seoByItem = $rows->keyBy('seo_item');
        $catIds = $rows->pluck('seo_item')->unique()->filter()->values()->all();
        if (empty($catIds)) {
            $this->line('  Нет записей с персональным SEO.');
            return 0;
        }

        $cats = $old->table('cats')->whereIn('cat_id', $catIds)->get()->keyBy('cat_id');
        $updated = 0;

        foreach ($cats as $oldId => $c) {
            $slug = Str::limit((string) ($c->cat_alias ?? ''), 255, '');
            if ($slug === '') {
                continue;
            }
            $category = Category::where('slug', $slug)->first();
            if (!$category) {
                continue;
            }
            $seo = $seoByItem->get($oldId);
            $title = ($seo && trim((string) ($seo->seo_title ?? '')) !== '') ? Str::limit($seo->seo_title, 255, '') : null;
            $description = ($seo && trim((string) ($seo->seo_description ?? '')) !== '') ? Str::limit(strip_tags($seo->seo_description), 500, '') : null;
            $h1 = ($seo && trim((string) ($seo->seo_h1 ?? '')) !== '') ? Str::limit($seo->seo_h1, 255, '') : null;
            $upd = array_filter(['title' => $title, 'description' => $description, 'h1' => $h1], fn ($v) => $v !== null && $v !== '');

            if ($upd !== []) {
                if (!$dryRun) {
                    $category->update($upd);
                }
                $updated++;
            }
        }
        $this->line('  Категорий: ' . $updated);
        return $updated;
    }

    private function syncPagesSeo(\Illuminate\Database\Connection $old, bool $dryRun): int
    {
        $this->info('Страницы (PAG)...');
        if (!$old->getSchemaBuilder()->hasTable('pages')) {
            $this->line('  В старой БД нет таблицы pages.');
            return 0;
        }

        $rows = $old->table('seo')
            ->where('seo_type', 'PAG')
            ->where(function ($q) {
                $q->whereNotNull('seo_title')->where('seo_title', '!=', '')
                    ->orWhereNotNull('seo_description')->where('seo_description', '!=', '')
                    ->orWhereNotNull('seo_h1')->where('seo_h1', '!=', '');
            })
            ->get();

        $seoByItem = $rows->keyBy('seo_item');
        $pageIds = $rows->pluck('seo_item')->unique()->filter()->values()->all();
        if (empty($pageIds)) {
            $this->line('  Нет записей с персональным SEO.');
            return 0;
        }

        $hasPageId = $old->getSchemaBuilder()->hasColumn('pages', 'page_id');
        $pages = $hasPageId
            ? $old->table('pages')->whereIn('page_id', $pageIds)->get()->keyBy('page_id')
            : $old->table('pages')->whereIn('id', $pageIds)->get()->keyBy('id');
        $updated = 0;

        foreach ($pages as $oldId => $p) {
            $slug = Str::limit((string) ($p->page_alias ?? Str::slug($p->page_title ?? $p->title ?? '')), 255, '');
            if ($slug === '') {
                $slug = 'page-' . ($p->page_id ?? $p->id ?? $oldId);
            }
            $page = Page::where('slug', $slug)->first();
            if (!$page) {
                continue;
            }
            $seo = $seoByItem->get($oldId);
            $title = ($seo && trim((string) ($seo->seo_title ?? '')) !== '') ? Str::limit($seo->seo_title, 255, '') : null;
            $description = ($seo && trim((string) ($seo->seo_description ?? '')) !== '') ? Str::limit(strip_tags($seo->seo_description), 500, '') : null;
            $h1 = ($seo && trim((string) ($seo->seo_h1 ?? '')) !== '') ? Str::limit($seo->seo_h1, 255, '') : null;
            $upd = array_filter(['title' => $title, 'description' => $description, 'h1' => $h1], fn ($v) => $v !== null && $v !== '');

            if ($upd !== []) {
                if (!$dryRun) {
                    $page->update($upd);
                }
                $updated++;
            }
        }
        $this->line('  Страниц: ' . $updated);
        return $updated;
    }
}
