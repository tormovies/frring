<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixMaterialFieldsCommand extends Command
{
    protected $signature = 'materials:fix-description-fields
                            {--dry-run : Показать, что будет сделано, без изменений}';

    protected $description = 'Исправить перепутанные при импорте поля: description→long_description, long_description→content, description=краткое из long_description';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Режим dry-run: изменения не сохраняются.');
        }

        $count = 0;
        Material::query()->orderBy('id')->chunk(500, function ($materials) use (&$count, $dryRun) {
            foreach ($materials as $m) {
                $currentDesc = (string) $m->description;
                $currentLong = (string) $m->long_description;
                $currentContent = (string) $m->content;

                // При импорте перепутали: в description попал текст для long_description, в long_description — для content.
                // Переносим: content <- long_description, long_description <- description, description <- краткое из long_description.
                $newContent = $currentLong !== '' ? $currentLong : $currentContent;
                $newLongDescription = $currentDesc !== '' ? $currentDesc : $currentLong;
                $newDescription = Str::limit(strip_tags($newLongDescription), 250, '');

                if (!$dryRun) {
                    $m->update([
                        'content' => $newContent,
                        'long_description' => $newLongDescription,
                        'description' => $newDescription,
                    ]);
                }
                $count++;
            }
        });

        $this->info('Обработано материалов: ' . $count . ($dryRun ? ' (dry-run)' : '. Поля обновлены.'));
        return self::SUCCESS;
    }
}
