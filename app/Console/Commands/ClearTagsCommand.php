<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTagsCommand extends Command
{
    protected $signature = 'tags:clear';

    protected $description = 'Удалить все теги и связи материал–тег (keywords не используем)';

    public function handle(): int
    {
        if (!$this->confirm('Удалить все теги и связи material_tag?', true)) {
            return self::SUCCESS;
        }

        $pivot = DB::table('material_tag')->count();
        $articleTag = DB::table('article_tag')->count();
        $tags = DB::table('tags')->count();

        DB::table('material_tag')->delete();
        if (Schema::hasTable('article_tag')) {
            DB::table('article_tag')->delete();
        }
        DB::table('tags')->delete();

        $this->info("Удалено: связей material_tag — {$pivot}, article_tag — {$articleTag}, тегов — {$tags}.");
        return self::SUCCESS;
    }
}
