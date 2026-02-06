<?php

namespace App\Observers;

use App\Models\Author;
use Illuminate\Support\Facades\Storage;

class AuthorObserver
{
    /**
     * При сохранении модели
     */
    public function saving(Author $author): void
    {
        if ($author->isDirty('img') && $author->getOriginal('img')) {
            $oldFile = $author->getOriginal('img');
            if (Storage::disk('authors')->exists($oldFile)) {
                Storage::disk('authors')->delete($oldFile);
            }
        }
    }

    /**
     * При удалении модели
     */
    public function deleting(Author $author): void
    {
        if ($author->img && Storage::disk('authors')->exists($author->img)) {
            Storage::disk('authors')->delete($author->img);
        }
    }
}
