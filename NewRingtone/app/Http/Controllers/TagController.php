<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Парсинг path из URL: slug, slug-24, slug-48-plays, slug-0-rating.
     *
     * @return array{slug: string, offset: int, sort: string}
     */
    private function parseTagPath(string $path): array
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
        } elseif (preg_match('/^(.+)-(\d+)$/', $path, $m)) {
            $slug = $m[1];
            $offset = (int) $m[2];
            $sort = 'new';
        }

        return ['slug' => $slug, 'offset' => $offset, 'sort' => $sort];
    }

    public function show(Request $request, string $path): Factory|View
    {
        $parsed = $this->parseTagPath($path);
        $slug = $parsed['slug'];
        $offset = $parsed['offset'];
        $sort = $parsed['sort'];

        $tag = Tag::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $perPage = sort_per_page();
        $page = $offset > 0 ? (int) (($offset / $perPage) + 1) : 1;
        if ($page < 1) {
            $page = 1;
        }

        $query = $tag->materials()
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

        return view('tag.show', compact('tag', 'materials', 'sort', 'offset'));
    }
}
