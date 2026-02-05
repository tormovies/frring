<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectOldRingtone extends Command
{
    protected $signature = 'import:inspect-old {slug : alias рингтона в старой БД (например yarik-bachok)}';

    protected $description = 'Показать сырые данные из старой БД (ringtone) по slug — чтобы проверить колонки скачиваний, длительности и т.д.';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $old = DB::connection('old');

        try {
            $old->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к старой БД: ' . $e->getMessage());
            return self::FAILURE;
        }

        $row = $old->table('ringtone')->where('alias', $slug)->first();
        if (! $row) {
            $this->error("В старой БД нет записи ringtone с alias = \"{$slug}\".");
            return self::FAILURE;
        }

        $this->info("Запись в старой БД (ringtone) для alias = \"{$slug}\":");
        $this->newLine();

        $cols = [
            'id', 'name', 'original_name', 'alias', 'description', 'hints',
            'plays', 'votes', 'votes_count', 'rating', 'datestamp', 'author',
            'file', 'type', 'image', 'cat', 'size', 'downloads', 'height', 'width',
            'uniq_text', 'created_at', 'updated_at',
        ];
        foreach ($cols as $col) {
            if (! property_exists($row, $col)) {
                continue;
            }
            $v = $row->{$col};
            if ($v === null || $v === '') {
                $this->line("  <comment>{$col}</comment>: (пусто)");
            } else {
                $display = is_string($v) && mb_strlen($v) > 80 ? mb_substr($v, 0, 80) . '…' : $v;
                $this->line("  <comment>{$col}</comment>: " . $display);
            }
        }

        $this->newLine();
        $this->info('Все колонки строки (как в БД):');
        $all = (array) $row;
        foreach ($all as $key => $val) {
            if (in_array($key, $cols, true)) {
                continue;
            }
            $this->line("  <comment>{$key}</comment>: " . (strlen((string) $val) > 60 ? substr((string) $val, 0, 60) . '…' : $val));
        }

        return self::SUCCESS;
    }
}
