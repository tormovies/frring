<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Material;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectSeoMigration extends Command
{
    protected $signature = 'import:inspect-seo';

    protected $description = 'Найти в старой БД материал, категорию и страницу со своими SEO и показать их сравнение с новой БД';

    public function handle(): int
    {
        $old = DB::connection('old');

        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (! $old->getSchemaBuilder()->hasTable('seo')) {
            $this->error('В старой БД нет таблицы seo.');
            return self::FAILURE;
        }

        $this->info('Ищем в старой БД записи с собственным SEO (не дефолтным)...');
        $this->newLine();

        // Материал (ITE): свой SEO = seo_item не 0 (не дефолт)
        $seoItem = $old->table('seo')
            ->where('seo_type', 'ITE')
            ->where('seo_item', '!=', '0')
            ->whereNotNull('seo_title')
            ->where('seo_title', '!=', '')
            ->first();

        if ($seoItem) {
            $ringtone = $old->table('ringtone')->where('id', $seoItem->seo_item)->first();
            if ($ringtone) {
                $this->line('<fg=cyan>═══ МАТЕРИАЛ (рингтон) с собственным SEO ═══</>');
                $this->line('Старая БД: alias = <comment>' . ($ringtone->alias ?? '') . '</comment>');
                $this->line('  seo_title: ' . ($seoItem->seo_title ?? '(пусто)'));
                $this->line('  seo_description: ' . mb_substr(strip_tags($seoItem->seo_description ?? ''), 0, 120) . (mb_strlen($seoItem->seo_description ?? '') > 120 ? '…' : ''));
                $this->line('  seo_h1: ' . ($seoItem->seo_h1 ?? '(пусто)'));
                $slug = $ringtone->alias ?? null;
                if ($slug) {
                    $m = Material::where('slug', $slug)->first();
                    if ($m) {
                        $this->line('Новая БД (materials):');
                        $this->line('  title: ' . ($m->title ?? '(пусто)'));
                        $this->line('  description: ' . mb_substr(strip_tags($m->description ?? ''), 0, 120) . (mb_strlen($m->description ?? '') > 120 ? '…' : ''));
                        $this->line('  h1: ' . ($m->h1 ?? '(пусто)'));
                    } else {
                        $this->warn("  В новой БД нет материала с slug = \"{$slug}\".");
                    }
                }
                $this->newLine();
                $this->line('Проверить на сайте: <info>/play/' . ($slug ?? '') . '.html</info>');
                $this->newLine();
            }
        } else {
            $this->warn('В старой БД нет ни одного материала (ITE) с собственным SEO.');
            $this->newLine();
        }

        // Категория (CAT): свой SEO = seo_item не 0
        $seoCat = $old->table('seo')
            ->where('seo_type', 'CAT')
            ->where('seo_item', '!=', '0')
            ->whereNotNull('seo_title')
            ->where('seo_title', '!=', '')
            ->first();

        if ($seoCat) {
            $cat = $old->table('cats')->where('cat_id', $seoCat->seo_item)->first();
            if ($cat) {
                $this->line('<fg=cyan>═══ КАТЕГОРИЯ с собственным SEO ═══</>');
                $this->line('Старая БД: cat_alias = <comment>' . ($cat->cat_alias ?? '') . '</comment>, cat_name = ' . ($cat->cat_name ?? ''));
                $this->line('  seo_title: ' . ($seoCat->seo_title ?? '(пусто)'));
                $this->line('  seo_description: ' . mb_substr(strip_tags($seoCat->seo_description ?? ''), 0, 120) . (mb_strlen($seoCat->seo_description ?? '') > 120 ? '…' : ''));
                $this->line('  seo_h1: ' . ($seoCat->seo_h1 ?? '(пусто)'));
                $slug = $cat->cat_alias ?? null;
                if ($slug) {
                    $c = Category::where('slug', $slug)->first();
                    if ($c) {
                        $this->line('Новая БД (categories):');
                        $this->line('  title: ' . ($c->title ?? '(пусто)'));
                        $this->line('  description: ' . mb_substr(strip_tags($c->description ?? ''), 0, 120) . (mb_strlen($c->description ?? '') > 120 ? '…' : ''));
                        $this->line('  h1: ' . ($c->h1 ?? '(пусто)'));
                    } else {
                        $this->warn("  В новой БД нет категории с slug = \"{$slug}\".");
                    }
                }
                $this->newLine();
                $this->line('Проверить на сайте: <info>/category/' . ($slug ?? '') . '.html</info>');
                $this->newLine();
            }
        } else {
            $this->warn('В старой БД нет ни одной категории (CAT) с собственным SEO.');
            $this->newLine();
        }

        // Страница (PAG)
        if ($old->getSchemaBuilder()->hasTable('pages')) {
            $seoPag = $old->table('seo')
                ->where('seo_type', 'PAG')
                ->where('seo_item', '!=', '0')
                ->whereNotNull('seo_title')
                ->where('seo_title', '!=', '')
                ->first();

            if ($seoPag) {
                $page = $old->table('pages')->where('page_id', $seoPag->seo_item)->first();
                if (! $page && $old->getSchemaBuilder()->hasColumn('pages', 'id')) {
                    $page = $old->table('pages')->where('id', $seoPag->seo_item)->first();
                }
                if ($page) {
                    $alias = $page->page_alias ?? $page->alias ?? null;
                    $this->line('<fg=cyan>═══ СТРАНИЦА с собственным SEO ═══</>');
                    $this->line('Старая БД: page_alias = <comment>' . ($alias ?? '') . '</comment>');
                    $this->line('  seo_title: ' . ($seoPag->seo_title ?? '(пусто)'));
                    $this->line('  seo_description: ' . mb_substr(strip_tags($seoPag->seo_description ?? ''), 0, 120) . (mb_strlen($seoPag->seo_description ?? '') > 120 ? '…' : ''));
                    $this->line('  seo_h1: ' . ($seoPag->seo_h1 ?? '(пусто)'));
                    if ($alias) {
                        $p = Page::where('slug', $alias)->first();
                        if ($p) {
                            $this->line('Новая БД (pages):');
                            $this->line('  title: ' . ($p->title ?? '(пусто)'));
                            $this->line('  description: ' . mb_substr(strip_tags($p->description ?? ''), 0, 120) . (mb_strlen($p->description ?? '') > 120 ? '…' : ''));
                            $this->line('  h1: ' . ($p->h1 ?? '(пусто)'));
                        } else {
                            $this->warn("  В новой БД нет страницы с slug = \"{$alias}\".");
                        }
                    }
                    $this->newLine();
                    $this->line('Проверить на сайте: <info>/page/' . ($alias ?? '') . '.html</info>');
                }
            } else {
                $this->warn('В старой БД нет ни одной страницы (PAG) с собственным SEO.');
            }
        }

        return self::SUCCESS;
    }
}
