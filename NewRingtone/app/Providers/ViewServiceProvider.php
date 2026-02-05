<?php

namespace App\Providers;

use App\Models\Material;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.sidebar-left', function ($view) {
            $popularMaterials = Material::active()
                ->orderByDesc('views')
                ->limit(15)
                ->get();

            $topMaterials = Material::active()
                ->orderByDesc('likes')
                ->limit(15)
                ->get();

            $view->with([
                'popularMaterials' => $popularMaterials,
                'topMaterials'     => $topMaterials,
            ]);
        });
    }
}
