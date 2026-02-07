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

        // основной поиск: FULLTEXT по name/long_description
        $materials = $this->searchMaterials($term);

        $seo = seo_template('search', $term);

        return view('search.index', compact('term', 'materials', 'seo'));
    }

    private function searchMaterials(string $term)
    {
        $likeTerm = '%' . addcslashes($term, '%_\\') . '%';

        return Material::query()
            ->with(['type', 'authors', 'categories', 'tags'])
            ->where('status', true)
            ->where(function ($q) use ($term, $likeTerm) {
                if (mb_strlen($term) >= 3) {
                    $q->whereRaw(
                        'MATCH(name, long_description) AGAINST(? IN NATURAL LANGUAGE MODE)',
                        [$term]
                    );
                } else {
                    $q->where('name', 'like', $likeTerm)
                        ->orWhere('long_description', 'like', $likeTerm);
                }
            })
            ->orderByDesc('id')
            ->paginate(sort_per_page())
            ->appends(['query' => $term]);
    }
}
