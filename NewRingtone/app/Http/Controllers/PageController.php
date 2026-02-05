<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(string $slug): Factory|View
    {
        $page = Page::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $hasPersonalTitle = trim((string) ($page->title ?? '')) !== '';
        $hasPersonalDesc = trim(strip_tags($page->description ?? '')) !== '';
        $hasPersonalH1 = trim((string) ($page->h1 ?? '')) !== '';
        if ($hasPersonalTitle && $hasPersonalDesc) {
            $seo = [
                'title' => trim((string) $page->title),
                'description' => trim(\Illuminate\Support\Str::limit(strip_tags($page->description), 250)),
                'h1' => $hasPersonalH1 ? trim((string) $page->h1) : '',
            ];
        } else {
            $seo = seo_template_page($page);
            if ($hasPersonalTitle) {
                $seo['title'] = trim((string) $page->title);
            }
            if ($hasPersonalDesc) {
                $seo['description'] = trim(\Illuminate\Support\Str::limit(strip_tags($page->description), 250));
            }
            if ($hasPersonalH1) {
                $seo['h1'] = trim((string) $page->h1);
            }
        }

        return view('page.show', compact('page', 'seo'));
    }
}
