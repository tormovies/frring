<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TypeController extends Controller
{

    public function show(Request $request, string $slug): Factory|View
    {
        $type = Type::active()
            ->where('slug', $slug)
            ->firstOrFail();

        // Получаем тип сортировки из запроса
        $sort = $request->query('sort', 'new');

        $query = $type->materials()
            ->with(['authors', 'type'])
            ->where('status', true);

        // Применяем сортировку
        match ($sort) {
            'new' => $query->orderByDesc('id'),
            'alpha' => $query->orderBy('name'), // Используем 'name', так как именно это поле отображается в представлении
            'duration' => $query->orderByDesc('mp4_duration'),
            default => $query->orderByDesc('downloads'),
        };

        $materials = $query->paginate(20)->appends(['sort' => $sort]);

        return view('type.show', compact('type', 'materials', 'sort'));
    }
}
