<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Как на старом сайте: ссылки на автора ведут на поиск по имени.
     * /author/yarik.html → /search.php?query=Ярик (301)
     */
    public function show(Request $request, string $slug): RedirectResponse
    {
        $author = Author::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return redirect()->route('search', ['query' => $author->name], 301);
    }

}
