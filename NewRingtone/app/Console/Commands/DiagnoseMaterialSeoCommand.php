<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Диагностика: почему у материала на новом сайте не совпадает SEO со старым.
 * Показывает данные в новой БД и в старой (seo + ringtone по alias).
 */
class DiagnoseMaterialSeoCommand extends Command
{
    protected $signature = 'seo:diagnose-material
                            {slug : Slug материала (например anzhela-katis)}';

    protected $description = 'Проверить материал: есть ли персональный SEO в старой БД и что записано в новой';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $slug = trim(preg_replace('/\.html$/', '', $slug), '/');

        $this->info("Материал slug: {$slug}");
        $this->newLine();

        // Новая БД
        $material = Material::where('slug', $slug)->first();
        if (!$material) {
            $this->warn('В новой БД нет материала с таким slug.');
        } else {
            $this->line('<fg=cyan>Новая БД (materials):</>');
            $this->line('  id: ' . $material->id);
            $this->line('  name: ' . ($material->name ?? '(пусто)'));
            $this->line('  title: ' . ($material->title !== null && $material->title !== '' ? $material->title : '<comment>(пусто — будет шаблон)</comment>'));
            $this->line('  description: ' . ($material->description !== null && trim(strip_tags($material->description ?? '')) !== '' ? mb_substr(strip_tags($material->description), 0, 80) . '…' : '<comment>(пусто — будет шаблон)</comment>'));
            $this->line('  h1: ' . ($material->h1 !== null && trim((string) $material->h1) !== '' ? $material->h1 : '(пусто)'));
            $this->newLine();
        }

        $old = DB::connection('old');
        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$old->getSchemaBuilder()->hasTable('ringtone') || !$old->getSchemaBuilder()->hasTable('seo')) {
            $this->error('В старой БД нет таблиц ringtone или seo.');
            return self::FAILURE;
        }

        $ringtone = $old->table('ringtone')->where('alias', $slug)->first();
        if (!$ringtone) {
            $this->warn("В старой БД нет рингтона с alias = \"{$slug}\".");
            $this->line('Проверьте точное написание alias в таблице ringtone.');
            return self::SUCCESS;
        }

        $this->line('<fg=cyan>Старая БД (ringtone):</>');
        $this->line('  id: ' . $ringtone->id);
        $this->line('  alias: ' . ($ringtone->alias ?? '(пусто)'));
        $this->line('  name: ' . ($ringtone->name ?? '') . ' - ' . ($ringtone->original_name ?? ''));
        $this->newLine();

        $seo = $old->table('seo')->where('seo_type', 'ITE')->where('seo_item', $ringtone->id)->first();
        if (!$seo) {
            $this->warn('В старой БД нет записи seo (seo_type=ITE, seo_item=' . $ringtone->id . ').');
            $this->line('На старом сайте для этого рингтона используется дефолтный шаблон.');
            return self::SUCCESS;
        }

        $hasTitle = trim((string) ($seo->seo_title ?? '')) !== '';
        $hasDesc = trim((string) ($seo->seo_description ?? '')) !== '';
        $hasH1 = trim((string) ($seo->seo_h1 ?? '')) !== '';

        $this->line('<fg=cyan>Старая БД (seo для этого рингтона):</>');
        $this->line('  seo_title: ' . ($hasTitle ? $seo->seo_title : '(пусто)'));
        $this->line('  seo_description: ' . ($hasDesc ? mb_substr(strip_tags($seo->seo_description ?? ''), 0, 80) . '…' : '(пусто)'));
        $this->line('  seo_h1: ' . ($hasH1 ? $seo->seo_h1 : '(пусто)'));
        $this->newLine();

        if ($hasTitle || $hasDesc || $hasH1) {
            if (!$material) {
                $this->line('<fg=yellow>Вывод:</> В новой БД нет материала с slug «' . $slug . '», импорт персонального SEO для него невозможен. Добавьте материал или проверьте slug.');
            } elseif (trim((string) ($material->title ?? '')) === '' && trim(strip_tags($material->description ?? '')) === '') {
                $this->line('<fg=green>Вывод:</> В старой БД есть персональный SEO, в новой — пусто. Запустите:');
                $this->line('  <info>php artisan import:personal-seo --materials</info>');
                $this->line('После этого обновите страницу материала на новом сайте.');
            } else {
                $this->line('<fg=green>Вывод:</> В новой БД уже есть title/description. Если на сайте всё ещё показывается шаблон — очистите кэш или проверьте вывод в шаблоне.');
            }
        } else {
            $this->line('На старом сайте для этого рингтона используются дефолтные подстановки (шаблон), не персональный SEO.');
        }

        return self::SUCCESS;
    }
}
