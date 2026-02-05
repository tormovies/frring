<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Category;
use App\Models\Material;
use App\Models\Page;
use App\Models\Tag;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportOldSite extends Command
{
    protected $signature = 'import:old-site
                            {--fresh : Очистить материалы и категории перед импортом (не трогает users, types)}
                            {--limit= : Лимит материалов (для теста)}';

    protected $description = 'Импорт категорий и рингтонов из старой БД FreeRingtones (admin_freeringtones)';

    private array $catIdMap = []; // old cat_id => new category id
    private array $materialIdMap = []; // old ringtone id => new material id

    public function handle(): int
    {
        @ini_set('memory_limit', '512M');
        $old = DB::connection('old');

        if (!$this->confirm('Подключиться к старой БД (admin_freeringtones) и импортировать данные?', true)) {
            return self::FAILURE;
        }

        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            $this->line('Проверь .env: DB_OLD_* или задай DB_OLD_DATABASE=admin_freeringtones, DB_OLD_USERNAME, DB_OLD_PASSWORD');
            return self::FAILURE;
        }

        $typeRingtones = Type::where('slug', 'ringtony')->orWhere('name', 'Рингтоны')->first();
        if (!$typeRingtones) {
            $typeRingtones = Type::first();
        }
        if (!$typeRingtones) {
            $this->error('В новой БД нет ни одного типа (types). Выполни миграции и сидеры.');
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (!$this->confirm('Удалить все материалы и категории в новой БД?', false)) {
                return self::SUCCESS;
            }
            DB::table('material_category')->delete();
            DB::table('material_tag')->delete();
            DB::table('material_author')->delete();
            Material::query()->delete();
            Category::query()->delete();
            $this->info('Очищено.');
        }

        $this->importCategories($old);
        $this->importMaterials($old, $typeRingtones->id);
        $this->importMaterialCategories($old);
        // Теги из seo_keywords не используем — не создаём при импорте
        // $this->importMaterialTags($old);
        $this->syncMaterialFileStats($old);
        $this->importPages($old);

        $this->info('Импорт завершён.');
        return self::SUCCESS;
    }

    private function importCategories(\Illuminate\Database\Connection $old): void
    {
        $this->info('Импорт категорий...');
        $seoByCat = $old->table('seo')->where('seo_type', 'CAT')->get()->keyBy('seo_item');

        $cats = $old->table('cats')->orderBy('cat_id')->get();
        $bar = $this->output->createProgressBar($cats->count());
        $bar->start();

        foreach ($cats as $c) {
            $seo = $seoByCat->get($c->cat_id);
            Category::withoutEvents(function () use ($c, $seo) {
                $name = $c->cat_name;
                // в новой БД name уникален; при повторе добавляем alias
                if (Category::where('name', $name)->exists()) {
                    $name = $name . ' (' . $c->cat_alias . ')';
                }
                $cat = Category::firstOrCreate(
                    ['slug' => $c->cat_alias],
                    [
                        'name' => $name,
                        'title' => $seo?->seo_title ?? $c->cat_name,
                        'description' => $seo?->seo_description ?? mb_substr($c->cat_description ?? '', 0, 500),
                        'h1' => $seo?->seo_h1 ?? null,
                        'long_description' => $c->cat_description ?? null,
                        'status' => true,
                    ]
                );
                $this->catIdMap[(int) $c->cat_id] = $cat->id;
            });
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Категорий: ' . count($this->catIdMap));
    }

    private function importMaterials(\Illuminate\Database\Connection $old, int $typeId): void
    {
        $this->info('Импорт материалов (рингтоны)...');
        $seoByItem = $old->table('seo')->where('seo_type', 'ITE')->get()->keyBy('seo_item');

        $query = $old->table('ringtone')->orderBy('id');
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }
        $rows = $query->get();
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();
        $created = 0;

        foreach ($rows as $r) {
            $seo = $seoByItem->get($r->id);
            // Персональный SEO только если в старой БД есть свои seo_title / seo_description
            $hasPersonalTitle = $seo && trim((string) ($seo->seo_title ?? '')) !== '';
            $hasPersonalDesc = $seo && trim((string) ($seo->seo_description ?? '')) !== '';
            $title = $hasPersonalTitle ? Str::limit($seo->seo_title, 255, '') : null;
            $h1 = ($seo && trim((string) ($seo->seo_h1 ?? '')) !== '') ? Str::limit($seo->seo_h1, 255, '') : null;

            // long_description = полный текст из SEO (или из ringtone), content = тело из ringtone.description
            $longDescRaw = $seo?->seo_description ?? strip_tags($r->description ?? '');
            $longDesc = $longDescRaw !== '' ? Str::limit($longDescRaw, 5000, '') : null;
            $contentRaw = isset($r->description) ? (string) $r->description : null;
            $content = $contentRaw !== '' ? Str::limit($contentRaw, 10000, '') : null;
            $desc = $hasPersonalDesc ? Str::limit(strip_tags($seo->seo_description), 250, '') : null;

            // Путь к файлу сохраняем как есть (аудио на cp1.freeringtones.ru не трогаем)
            $mp4Path = $r->file ? Str::limit($r->file, 250, '') : null;

            Material::withoutEvents(function () use ($r, $typeId, $title, $desc, $h1, $mp4Path, $longDesc, $content, &$created) {
                $nameRaw = $r->name . ' - ' . $r->original_name;
                $name = Str::limit($nameRaw, 255, '');
                $existing = Material::where('slug', $r->alias)->first();
                $createdAt = isset($r->datestamp) && (int) $r->datestamp > 0
                    ? Carbon::createFromTimestamp((int) $r->datestamp) : now();

                if ($existing) {
                    $this->materialIdMap[(int) $r->id] = $existing->id;
                    $this->attachAuthorToMaterial($existing, $r->name ?? '');
                    $upd = [
                        'views' => (int) ($r->plays ?? 0),
                        'likes' => (int) ($r->votes_count ?? 0),
                        'downloads' => (int) ($r->plays ?? $r->downloads ?? 0),
                        'title' => $title,
                        'description' => $desc,
                        'h1' => $h1,
                        'long_description' => $longDesc,
                        'content' => $content,
                        'created_at' => $createdAt,
                    ];
                    if (isset($r->size) && (int) $r->size > 0) {
                        $upd['mp4_size'] = (int) $r->size;
                    }
                    $dur = $this->parsePlaytimeToSeconds($r->width ?? null);
                    if ($dur !== null && $dur > 0) {
                        $upd['mp4_duration'] = $dur;
                    }
                    Material::withoutEvents(fn () => $existing->update($upd));
                    return;
                }
                if (Material::where('name', $name)->exists()) {
                    $name = Str::limit($nameRaw, 245, '') . ' [' . $r->id . ']';
                }
                $m = Material::create([
                    'name' => $name,
                    'slug' => Str::limit($r->alias, 255, ''),
                    'type_id' => $typeId,
                    'img' => $r->image ? Str::limit($r->image, 250, '') : null,
                    'title' => $title,
                    'description' => $desc,
                    'h1' => $h1,
                    'long_description' => $longDesc,
                    'content' => $content ?? $r->hints ?? null,
                    'copyright' => $r->original_name ? Str::limit($r->original_name, 255, '') : null,
                    'mp4' => $mp4Path,
                    'mp4_bitrate' => !empty($r->height) ? (int) $r->height : null,
                    'mp4_duration' => $this->parsePlaytimeToSeconds($r->width ?? null),
                    'mp4_size' => isset($r->size) ? (int) $r->size : null,
                    'views' => (int) ($r->plays ?? 0),
                    'likes' => (int) ($r->votes_count ?? 0),
                    'downloads' => (int) ($r->plays ?? $r->downloads ?? 0),
                    'status' => true,
                    'moderation_status' => 'approved',
                    'user_id' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $this->materialIdMap[(int) $r->id] = $m->id;
                $this->attachAuthorToMaterial($m, $r->name ?? '');
                $created++;
            });
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Материалов в карте: ' . count($this->materialIdMap) . ' (создано новых: ' . $created . ')');
    }

    private function importMaterialCategories(\Illuminate\Database\Connection $old): void
    {
        $this->info('Связи материал ↔ категория...');
        $total = 0;
        $now = now();
        $old->table('ringtone_gat')->orderBy('id')->chunk(2000, function ($rows) use (&$total, $now) {
            $inserts = [];
            foreach ($rows as $r) {
                $newMaterialId = $this->materialIdMap[(int) $r->id] ?? null;
                $newCategoryId = $this->catIdMap[(int) $r->catid] ?? null;
                if ($newMaterialId && $newCategoryId) {
                    $inserts[] = [
                        'material_id' => $newMaterialId,
                        'category_id' => $newCategoryId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if ($inserts) {
                foreach (array_chunk($inserts, 500) as $chunk) {
                    DB::table('material_category')->insertOrIgnore($chunk);
                }
                $total += count($inserts);
            }
        });
        $this->info('Связей: ' . $total);
    }

    private function importMaterialTags(\Illuminate\Database\Connection $old): void
    {
        $this->info('Теги из SEO keywords...');
        $seoRows = $old->table('seo')->where('seo_type', 'ITE')->whereNotNull('seo_keywords')->get();
        if ($seoRows->isEmpty()) {
            $this->info('Тегов привязано: 0 (нет записей SEO keywords для материалов)');
            return;
        }
        $oldIds = $seoRows->pluck('seo_item')->unique()->filter()->values()->all();
        $aliasById = $old->table('ringtone')->whereIn('id', $oldIds)->pluck('alias', 'id');
        $bar = $this->output->createProgressBar($seoRows->count());
        $bar->start();
        $attached = 0;
        foreach ($seoRows as $seo) {
            $newMaterialId = $this->materialIdMap[(int) $seo->seo_item] ?? null;
            if ($newMaterialId) {
                $material = Material::find($newMaterialId);
            } else {
                $alias = $aliasById[(int) $seo->seo_item] ?? null;
                $material = $alias ? Material::where('slug', $alias)->first() : null;
            }
            if (!$material) {
                $bar->advance();
                continue;
            }
            $keywords = preg_split('/[\s,;]+/u', (string) $seo->seo_keywords, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw === '' || mb_strlen($kw) > 255) {
                    continue;
                }
                $slug = Str::slug($kw) ?: 'tag-' . substr(md5($kw), 0, 8);
                $tag = Tag::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::limit($kw, 255, ''),
                        'title' => Str::limit($kw, 255, ''),
                        'description' => '',
                        'status' => true,
                    ]
                );
                if (!$material->tags()->where('tag_id', $tag->id)->exists()) {
                    $material->tags()->attach($tag->id);
                    $attached++;
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Тегов привязано: ' . $attached);
    }

    /** Обновить размер, длительность и статистику (просмотры, лайки, скачивания) из старой БД */
    private function syncMaterialFileStats(\Illuminate\Database\Connection $old): void
    {
        $this->info('Синхронизация размера/длительности/статистики/даты/описания/авторов из старой БД...');
        $updated = 0;
        $old->table('ringtone')->select('alias', 'size', 'width', 'plays', 'votes_count', 'downloads', 'description', 'datestamp', 'name')->orderBy('id')->chunk(1000, function ($rows) use (&$updated) {
            foreach ($rows as $r) {
                $m = Material::where('slug', $r->alias)->first();
                if (! $m) {
                    continue;
                }
                $data = [
                    'views' => (int) ($r->plays ?? 0),
                    'likes' => (int) ($r->votes_count ?? 0),
                    'downloads' => (int) ($r->plays ?? $r->downloads ?? 0),
                ];
                if (isset($r->size) && (int) $r->size > 0) {
                    $data['mp4_size'] = (int) $r->size;
                }
                $duration = $this->parsePlaytimeToSeconds($r->width ?? null);
                if ($duration !== null && $duration > 0) {
                    $data['mp4_duration'] = $duration;
                }
                if (isset($r->datestamp) && (int) $r->datestamp > 0) {
                    $data['created_at'] = Carbon::createFromTimestamp((int) $r->datestamp);
                }
                if (isset($r->description) && (string) $r->description !== '') {
                    $data['content'] = Str::limit((string) $r->description, 10000, '');
                }
                Material::withoutEvents(fn () => $m->update($data));
                $this->attachAuthorToMaterial($m, $r->name ?? '');
                $updated++;
            }
        });
        $this->info('Обновлено записей: ' . $updated);
    }

    /** Импорт статических страниц из старой БД (pages + seo PAG) */
    private function importPages(\Illuminate\Database\Connection $old): void
    {
        if (!$old->getSchemaBuilder()->hasTable('pages')) {
            $this->warn('В старой БД нет таблицы pages, пропуск.');
            return;
        }

        $this->info('Импорт страниц...');
        $seoByPage = $old->table('seo')->where('seo_type', 'PAG')->get()->keyBy('seo_item');

        $rows = $old->table('pages')->get();
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();
        $created = 0;

        foreach ($rows as $p) {
            $seo = $seoByPage->get($p->page_id ?? $p->id ?? 0);
            $name = Str::limit($p->page_title ?? $p->title ?? 'Page ' . ($p->page_id ?? $p->id), 255, '');
            $slug = Str::limit($p->page_alias ?? Str::slug($name), 255, '');
            if ($slug === '') {
                $slug = 'page-' . ($p->page_id ?? $p->id);
            }
            $title = Str::limit($seo?->seo_title ?? $p->page_title ?? $name, 255, '');
            $description = Str::limit(strip_tags($seo?->seo_description ?? $p->page_text ?? $p->page_title ?? ''), 500, '');
            $h1 = !empty($seo?->seo_h1) ? Str::limit($seo->seo_h1, 255, '') : null;
            $content = isset($p->page_text) ? (string) $p->page_text : null;
            $longDescription = isset($p->page_text) ? Str::limit(strip_tags((string) $p->page_text), 5000, '') : null;

            $page = Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'title' => $title,
                    'description' => $description ?: Str::limit($name, 500, ''),
                    'h1' => $h1,
                    'long_description' => $longDescription,
                    'content' => $content,
                    'status' => true,
                ]
            );

            if ($page->wasRecentlyCreated) {
                $created++;
            } else {
                $page->update([
                    'name' => $name,
                    'title' => $title,
                    'description' => $description ?: Str::limit($name, 500, ''),
                    'h1' => $h1,
                    'long_description' => $longDescription,
                    'content' => $content,
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Страниц: ' . $rows->count() . ' (создано новых: ' . $created . ')');
    }

    /** Исполнитель из старой БД (ringtone.name) → Author, связь material_author */
    private function attachAuthorToMaterial(Material $material, string $artistName): void
    {
        $artistName = trim($artistName);
        if ($artistName === '') {
            return;
        }
        $slug = Str::slug($artistName) ?: 'author-' . substr(md5($artistName), 0, 8);
        $author = Author::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => Str::limit($artistName, 255, ''),
                'title' => Str::limit($artistName, 255, ''),
                'description' => '',
                'status' => true,
            ]
        );
        if (! $material->authors()->where('author_id', $author->id)->exists()) {
            $material->authors()->attach($author->id);
        }
    }

    /** В старой БД длительность в колонке width: playtime_string getID3 "0:32", "1:35" или секунды */
    private function parsePlaytimeToSeconds(mixed $width): ?int
    {
        if ($width === null || $width === '') {
            return null;
        }
        $width = trim((string) $width);
        $width = preg_replace('/\s+/', ' ', $width);
        if ($width === '') {
            return null;
        }
        // Минуты:секунды (1:05, 0:32, 1:35) — допускаем пробелы вокруг двоеточия
        if (preg_match('/^(\d+)\s*:\s*(\d{1,2})\s*$/', $width, $m)) {
            $sec = (int) $m[1] * 60 + (int) $m[2];
            return $sec > 0 ? $sec : null;
        }
        // Часы:минуты:секунды (1:05:30)
        if (preg_match('/^(\d+)\s*:\s*(\d{1,2})\s*:\s*(\d{1,2})\s*$/', $width, $m)) {
            $sec = (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3];
            return $sec > 0 ? $sec : null;
        }
        // Число секунд
        if (is_numeric($width)) {
            $sec = (int) $width;
            return $sec > 0 ? $sec : null;
        }
        return null;
    }
}
