<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{

    public function index(Request $request): Factory|View
    {
        $term = trim((string) ($request->input('query') ?? $request->input('q', '')));
        $term = mb_substr($term, 0, 80);

        // если пусто — отдаём “пустую” пагинацию
        if ($term === '') {
            $empty = new LengthAwarePaginator([], 0, sort_per_page(), 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);

            $seo = seo_template('search', '');

        return view('search.index', [
                'term' => $term,
                'materials' => $empty,
                'seo' => $seo,
            ]);
        }

        // основной поиск
        $materials = Material::query()
            ->with(['type', 'authors', 'categories', 'tags'])
            ->where('status', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('type', fn($t) => $t->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('authors', fn($a) => $a->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('categories', fn($c) => $c->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('tags', fn($t) => $t->where('name', 'like', "%{$term}%"));
            })
            ->orderByDesc('id')
            ->paginate(sort_per_page())
            ->appends(['query' => $term]);

        $seo = seo_template('search', $term);

        return view('search.index', compact('term', 'materials', 'seo'));
    }
}
