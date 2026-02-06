<?php

namespace App\Observers;

use App\Models\Type;
use Illuminate\Support\Facades\Storage;

class TypeObserver
{
    /**
     * При сохранении модели
     */
    public function saving(Type $type): void
    {
        if ($type->isDirty('img') && $type->getOriginal('img')) {
            $oldFile = $type->getOriginal('img');
            if (Storage::disk('types')->exists($oldFile)) {
                Storage::disk('types')->delete($oldFile);
            }
        }
    }

    /**
     * При удалении модели
     */
    public function deleting(Type $type): void
    {
        if ($type->img && Storage::disk('types')->exists($type->img)) {
            Storage::disk('types')->delete($type->img);
        }
    }
}
