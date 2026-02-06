<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class MainController extends Controller
{
    /**
     * Главная страница: новинки с пагинацией. URL страниц: / (1), /index-24-date.html (2), /index-48-date.html (3)...
     */
    public function index(Request $request): View
    {
        $seo = seo_template('home');
        $perPage = sort_per_page();

        $query = Material::with(['type', 'authors'])
            ->where('status', true)
            ->latest();

        $materials = $query->paginate($perPage);

        return view('main.index', [
            'materials' => $materials,
            'order' => 'date',
            'seo' => $seo,
            'sortContext' => 'main',
            'sortType' => 'new',
            'slug' => null,
        ]);
    }

    /**
     * Новинки со смещением: /index-24-date.html, /index-48-date.html — те же данные, правильные URL пагинации.
     */
    public function indexWithOffset(Request $request, int $offset): View
    {
        $seo = seo_template('home');
        $perPage = sort_per_page();
        $page = $offset > 0 ? (int) (($offset / $perPage) + 1) : 1;
        if ($page < 1) {
            $page = 1;
        }

        $query = Material::with(['type', 'authors'])
            ->where('status', true)
            ->latest();

        $total = $query->count();
        $items = $query->offset($offset)->limit($perPage)->get();

        $materials = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('main.index', [
            'materials' => $materials,
            'order' => 'date',
            'seo' => $seo,
            'sortContext' => 'main',
            'sortType' => 'new',
            'slug' => null,
        ]);
    }
}
