<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->query('sort', 'new');

        $query = Article::query()
            ->with('tags')
            ->active();

        match ($sort) {
            'alpha'  => $query->orderBy('title')->orderBy('name'),
            'views'  => $query->orderByDesc('views'),
            'new'    => $query->latest(),
            default  => $query->orderByDesc('likes'),
        };

        $articles = $query->paginate(20)
            ->appends(['sort' => $sort]);

        $seo = seo_template('articles_index');

        return view('article.index', compact('articles', 'sort', 'seo'));
    }

    public function show(string $slug): View
    {
        $article = Article::with('tags')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $article->increment('views');

        $tagIds = $article->tags()->pluck('tags.id');

        $related = Material::with(['type', 'authors'])
            ->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
            ->active()
            ->latest()
            ->limit(10)
            ->get();

        return view('article.show', compact('article', 'related'));
    }

    public function like(string $slug): RedirectResponse
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $key = 'liked_article_' . $article->id;

        if (!session()->has($key)) {
            $article->increment('likes');
            session([$key => true]);
        }

        return redirect()->back();
    }

    public function dislike(string $slug): RedirectResponse
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $key = 'liked_article_' . $article->id;

        if (session()->has($key)) {
            $article->decrement('likes');
            session()->forget($key);
        }

        return redirect()->back();
    }
}
