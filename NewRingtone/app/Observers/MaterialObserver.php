<?php

namespace App\Observers;

use App\Models\Material;
use getID3;
use Illuminate\Support\Facades\Storage;

class MaterialObserver
{
    /**
     * При сохранении модели.
     * Удаляем старые файлы, если загружены новые.
     * Пересчитываем битрейт и длительность для аудио.
     * Обнуляем данные, если файл был удалён.
     */
    public function saving(Material $material): void
    {
        // --- Фото ---
        if ($material->isDirty('img') && $material->getOriginal('img')) {
            $oldImg = $material->getOriginal('img');
            if (Storage::disk('materials')->exists($oldImg)) {
                Storage::disk('materials')->delete($oldImg);
            }
        }

        // --- Обработка аудио файлов ---
        $this->handleAudioField($material, 'mp4');
        $this->handleAudioField($material, 'm4r30');
        $this->handleAudioField($material, 'm4r40');
    }

    /**
     * При удалении модели.
     * Удаляем все связанные файлы.
     */
    public function deleting(Material $material): void
    {
        $this->deleteFileIfExists('materials', $material->img);
        $this->deleteFileIfExists('mp4', $material->mp4);
        $this->deleteFileIfExists('m4r30', $material->m4r30);
        $this->deleteFileIfExists('m4r40', $material->m4r40);
    }

    /**
     * Обрабатывает конкретное аудио-поле:
     * удаляет старый файл, пересчитывает данные или обнуляет.
     */
    protected function handleAudioField(Material $material, string $field): void
    {
        // если заменили файл — удалить старый
        if ($material->isDirty($field) && $material->getOriginal($field)) {
            $oldFile = $material->getOriginal($field);
            if (Storage::disk($field)->exists($oldFile)) {
                Storage::disk($field)->delete($oldFile);
            }
        }

        // если поле пустое — обнуляем данные
        if (empty($material->{$field})) {
            $material->{$field . '_bitrate'} = null;
            $material->{$field . '_duration'} = null;
            return;
        }

        // если файл существует — анализируем
        $path = Storage::disk($field)->path($material->{$field});
        if (file_exists($path)) {
            $this->analyzeAudio($material, $field);
        } else {
            // файла нет на диске (внешний CDN, импорт) — не обнуляем длительность/битрейт, чтобы сохранить данные из старой БД
        }
    }

    /**
     * Анализ аудиофайла и сохранение битрейта/длительности.
     */
    protected function analyzeAudio(Material $material, string $field): void
    {
        $path = Storage::disk($field)->path($material->{$field});
        $analyzer = new getID3();
        $info = $analyzer->analyze($path);

        $bitrate = $info['bitrate'] ?? null;
        $duration = $info['playtime_seconds'] ?? null;

        $material->{$field . '_bitrate'} = $bitrate ? (int) round($bitrate / 1000) : 0;
        $material->{$field . '_duration'} = $duration ? (int) round($duration) : 0;
    }

    /**
     * Универсальный метод для безопасного удаления файла.
     */
    protected function deleteFileIfExists(string $disk, ?string $path): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
