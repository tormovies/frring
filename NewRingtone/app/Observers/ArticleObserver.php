<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;

class ArticleObserver
{
    /**
     * При сохранении модели
     */
    public function saving(Article $article): void
    {
        if ($article->isDirty('img') && $article->getOriginal('img')) {
            $oldFile = $article->getOriginal('img');
            if (Storage::disk('articles')->exists($oldFile)) {
                Storage::disk('articles')->delete($oldFile);
            }
        }
    }

    /**
     * При удалении модели
     */
    public function deleting(Article $article): void
    {
        if ($article->img && Storage::disk('articles')->exists($article->img)) {
            Storage::disk('articles')->delete($article->img);
        }
    }
}
