<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function show(string $slug): Factory|View
    {
        $material = Material::with(['type', 'authors', 'categories', 'tags'])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Material::with(['type', 'authors'])
            ->active()
            ->where('type_id', $material->type_id)
            ->where('id', '!=', $material->id)
            ->limit(6)
            ->get();

        // увеличим просмотры
        $material->increment('views');

        $hasPersonalTitle = trim((string) ($material->title ?? '')) !== '';
        $hasPersonalDesc = trim(strip_tags($material->description ?? '')) !== '';
        $hasPersonalH1 = trim((string) ($material->h1 ?? '')) !== '';
        if ($hasPersonalTitle && $hasPersonalDesc) {
            $seo = [
                'title' => trim((string) $material->title),
                'description' => trim(Str::limit(strip_tags($material->description), 250)),
                'h1' => $hasPersonalH1 ? trim((string) $material->h1) : '',
            ];
        } else {
            $seo = seo_template_material($material);
            if ($hasPersonalTitle) {
                $seo['title'] = trim((string) $material->title);
            }
            if ($hasPersonalDesc) {
                $seo['description'] = trim(Str::limit(strip_tags($material->description), 250));
            }
            if ($hasPersonalH1) {
                $seo['h1'] = trim((string) $material->h1);
            }
        }

        return view('material.show', compact('material', 'related', 'seo'));
    }

    /**
     * Скачивание: если файл локальный — отдаём потоком; иначе редирект на CDN (cp1.freeringtones.ru).
     */
    public function download(string $slug, string $format): StreamedResponse|RedirectResponse
    {
        $material = Material::with('authors')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
        $field = match ($format) {
            'mp4' => 'mp4',
            'm4r30' => 'm4r30',
            'm4r40' => 'm4r40',
            default => abort(404),
        };

        $path = $material->{$field};
        $pathTrim = $path ? ltrim($path, '/') : '';

        $authors = $material->authors?->pluck('name')->implode(', ') ?? '';
        $filename = trim(($authors ? $authors . ' - ' : '') . $material->name);
        $extension = pathinfo($path ?? '', PATHINFO_EXTENSION) ?: ($field === 'mp4' ? 'mp3' : 'm4r');
        $safeName = Str::slug($filename, '-') . '.' . strtolower($extension);

        // Локальный файл — отдаём потоком с принудительным скачиванием
        if ($pathTrim) {
            try {
                if (Storage::disk($field)->exists($pathTrim)) {
                    $material->increment('downloads');
                    return Storage::disk($field)->download($pathTrim, $safeName, [
                        'Content-Disposition' => 'attachment; filename="' . str_replace('"', '\\"', $safeName) . '"',
                    ]);
                }
            } catch (\Throwable $e) {
                // диск недоступен или сломан — идём в ветку CDN
            }
        }

        // Файл на CDN — редирект с ?download=1 (cp1 Apache добавит Content-Disposition: attachment)
        if ($field === 'mp4' && $material->mp4) {
            $url = $material->fileUrl();
            if (!$url) {
                $url = $this->buildCdnUrlForMp4($material->mp4);
            }
            if ($url) {
                $material->increment('downloads');
                return redirect()->away($this->appendDownloadParam($url));
            }
        }

        if (($field === 'm4r30' || $field === 'm4r40') && $material->m4rFileUrl()) {
            $material->increment('downloads');
            return redirect()->away($this->appendDownloadParam($material->m4rFileUrl()));
        }

        abort(404);
    }

    /** Добавить ?download=1 к URL для принудительного скачивания (cp1 Apache отдаст Content-Disposition). */
    private function appendDownloadParam(string $url): string
    {
        return str_contains($url, '?') ? $url . '&download=1' : $url . '?download=1';
    }

    /** URL для mp3 на CDN (если fileUrl() вернул null из‑за кеша/конфига). */
    private function buildCdnUrlForMp4(?string $mp4Path): ?string
    {
        if (!$mp4Path) {
            return null;
        }
        $base = rtrim((string) config('services.ringtone_cdn.url'), '/');
        if ($base === '') {
            return null;
        }
        $path = ltrim($mp4Path, '/');
        $path = str_starts_with($path, 'mp3/') || str_starts_with($path, 'm4r/') ? $path : 'mp3/' . $path;
        return $base . '/' . $path;
    }

    public function like(string $slug): JsonResponse|RedirectResponse
    {
        $material = Material::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $key = 'liked_' . $material->id;
        if (!session()->has($key)) {
            $material->increment('likes');
            session([$key => true]);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'likes' => $material->fresh()->likes]);
        }
        return redirect()->back();
    }

    public function dislike(string $slug): JsonResponse|RedirectResponse
    {
        $material = Material::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $key = 'liked_' . $material->id;

        if (session()->has($key)) {
            $material->decrement('likes');
            session()->forget($key);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'likes' => $material->fresh()->likes]);
        }
        return redirect()->back();
    }

    /**
     * Популярные (по скачиваниям). URL: /category/index-0-plays.html, /category/index-24-plays.html, ...
     */
    public function popular(int $offset = 0): View
    {
        $perPage = sort_per_page();
        $page = $offset > 0 ? (int) (($offset / $perPage) + 1) : 1;
        if ($page < 1) {
            $page = 1;
        }

        $materials = Material::with(['type', 'authors'])
            ->active()
            ->orderByDesc('downloads')
            ->paginate($perPage, ['*'], 'page', $page);

        $seo = seo_template('popular');

        return view('material.popular', compact('materials', 'seo'));
    }

    /**
     * Лучшие (по лайкам). URL: /category/index-0-rating.html, /category/index-24-rating.html, ...
     */
    public function best(int $offset = 0): View
    {
        $perPage = sort_per_page();
        $page = $offset > 0 ? (int) (($offset / $perPage) + 1) : 1;
        if ($page < 1) {
            $page = 1;
        }

        $materials = Material::with(['type', 'authors'])
            ->active()
            ->orderByDesc('likes')
            ->paginate($perPage, ['*'], 'page', $page);

        $seo = seo_template('best');

        return view('material.best', compact('materials', 'seo'));
    }
}
