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
            $cacheKey = 'sidebar_materials';
            $cacheTtl = now()->addMinutes(10);

            $data = cache()->remember($cacheKey, $cacheTtl, function () {
                return [
                    'popularMaterials' => Material::active()
                        ->with(['type', 'authors'])
                        ->orderByDesc('views')
                        ->limit(15)
                        ->get(),
                    'topMaterials' => Material::active()
                        ->with(['type', 'authors'])
                        ->orderByDesc('likes')
                        ->limit(15)
                        ->get(),
                ];
            });

            $view->with($data);
        });
    }
}
