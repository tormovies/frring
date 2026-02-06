<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Парсинг path из URL: na-vraga, na-vraga-48-date, na-vraga-48-plays, na-vraga-48-rating.
     * Формат slug-offset (без -date) не поддерживается — на него делается редирект.
     *
     * @return array{slug: string, offset: int, sort: string}
     */
    private function parseCategoryPath(string $path): array
    {
        $sort = 'new';
        $offset = 0;
        $slug = $path;

        if (preg_match('/^(.+)-(\d+)-plays$/', $path, $m)) {
            $slug = $m[1];
            $offset = (int) $m[2];
            $sort = 'plays';
        } elseif (preg_match('/^(.+)-(\d+)-rating$/', $path, $m)) {
            $slug = $m[1];
            $offset = (int) $m[2];
            $sort = 'rating';
        } elseif (preg_match('/^(.+)-(\d+)-date$/', $path, $m)) {
            $slug = $m[1];
            $offset = (int) $m[2];
            $sort = 'new';
        }

        return ['slug' => $slug, 'offset' => $offset, 'sort' => $sort];
    }

    public function show(Request $request, string $path): Factory|View|RedirectResponse
    {
        // Неправильный формат slug-offset без -date → редирект на правильный slug-offset-date
        if (preg_match('/^(.+)-(\d+)$/', $path, $m) && !str_ends_with($path, '-date')) {
            return redirect()->to(url("/category/{$path}-date.html"), 301);
        }

        $parsed = $this->parseCategoryPath($path);
        $slug = $parsed['slug'];
        $offset = $parsed['offset'];
        $sort = $parsed['sort'];

        $category = Category::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $perPage = sort_per_page();
        $page = $offset > 0 ? (int) (($offset / $perPage) + 1) : 1;
        if ($page < 1) {
            $page = 1;
        }

        $query = $category->materials()
            ->with(['type', 'authors'])
            ->where('status', true);

        match ($sort) {
            'new' => $query->orderByDesc('id'),
            'plays' => $query->orderByDesc('downloads'),
            'rating' => $query->orderByDesc('likes'),
            'alpha' => $query->orderBy('title'),
            'duration' => $query->orderByDesc('mp4_duration'),
            default => $query->orderByDesc('id'),
        };

        $materials = $query->paginate($perPage, ['*'], 'page', $page);

        $hasPersonalTitle = trim((string) ($category->title ?? '')) !== '';
        $hasPersonalDesc = trim(strip_tags($category->description ?? '')) !== '';
        $hasPersonalH1 = trim((string) ($category->h1 ?? '')) !== '';
        if ($hasPersonalTitle && $hasPersonalDesc) {
            $seo = [
                'title' => $category->title,
                'description' => \Illuminate\Support\Str::limit(strip_tags($category->description), 250),
                'h1' => $hasPersonalH1 ? $category->h1 : '',
            ];
        } else {
            $seo = seo_template_category($category);
            if ($hasPersonalTitle) {
                $seo['title'] = $category->title;
            }
            if ($hasPersonalDesc) {
                $seo['description'] = \Illuminate\Support\Str::limit(strip_tags($category->description), 250);
            }
            if ($hasPersonalH1) {
                $seo['h1'] = $category->h1;
            }
        }

        return view('category.show', compact('category', 'materials', 'sort', 'offset', 'seo'));
    }
}
