<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryObserver
{
    /**
     * При сохранении модели
     */
    public function saving(Category $category): void
    {
        if ($category->isDirty('img') && $category->getOriginal('img')) {
            $oldFile = $category->getOriginal('img');
            if (Storage::disk('categories')->exists($oldFile)) {
                Storage::disk('categories')->delete($oldFile);
            }
        }
    }

    /**
     * При удалении модели
     */
    public function deleting(Category $category): void
    {
        if ($category->img && Storage::disk('categories')->exists($category->img)) {
            Storage::disk('categories')->delete($category->img);
        }
    }
}
