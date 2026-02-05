<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Storage;

class TagObserver
{
    /**
     * При сохранении модели
     */
    public function saving(Tag $tag): void
    {
        if ($tag->isDirty('img') && $tag->getOriginal('img')) {
            $oldFile = $tag->getOriginal('img');
            if (Storage::disk('tags')->exists($oldFile)) {
                Storage::disk('tags')->delete($oldFile);
            }
        }
    }

    /**
     * При удалении модели
     */
    public function deleting(Tag $tag): void
    {
        if ($tag->img && Storage::disk('tags')->exists($tag->img)) {
            Storage::disk('tags')->delete($tag->img);
        }
    }
}
