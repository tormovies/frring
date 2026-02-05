<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function index(Request $request): View
    {
        $seo = seo_template('home');
        $order = $request->get('order', 'date');

        $query = Material::with(['type', 'authors'])
            ->where('status', true);

        if ($order === 'plays') {
            $query->orderByDesc('downloads');
        } elseif ($order === 'rating') {
            $query->orderByDesc('likes');
        } else {
            $query->latest();
        }

        $materials = $query->paginate(sort_per_page())->withQueryString();

        return view('main.index', compact('materials', 'order', 'seo'));
    }
}
