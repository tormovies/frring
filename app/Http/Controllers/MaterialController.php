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

        $authors = $material->authors->pluck('name')->implode(', ');
        $filename = trim(($authors ? $authors . ' - ' : '') . $material->name);
        $extension = pathinfo($path ?? '', PATHINFO_EXTENSION) ?: ($field === 'mp4' ? 'mp3' : 'm4r');
        $safeName = Str::slug($filename, '-') . '.' . strtolower($extension);

        // Локальный файл — отдаём потоком с принудительным скачиванием
        if ($pathTrim && Storage::disk($field)->exists($pathTrim)) {
            $material->increment('downloads');
            return Storage::disk($field)->download($pathTrim, $safeName, [
                'Content-Disposition' => 'attachment; filename="' . str_replace('"', '\\"', $safeName) . '"',
            ]);
        }

        // Файл на CDN — проксируем потоком, чтобы браузер не открывал в той же вкладке
        if ($field === 'mp4' && $material->mp4 && $material->fileUrl()) {
            $material->increment('downloads');
            $url = $material->fileUrl();
            return $this->streamDownloadFromUrl($url, $safeName, 'audio/mpeg');
        }

        if (($field === 'm4r30' || $field === 'm4r40') && $material->m4rFileUrl()) {
            $material->increment('downloads');
            $url = $material->m4rFileUrl();
            return $this->streamDownloadFromUrl($url, $safeName, 'audio/x-m4r');
        }

        abort(404);
    }

    /**
     * Скачивание файла по URL потоком с заголовком attachment (чтобы не открывался в браузере).
     */
    private function streamDownloadFromUrl(string $url, string $filename, string $contentType): StreamedResponse
    {
        return response()->streamDownload(function () use ($url) {
            $client = new \GuzzleHttp\Client(['timeout' => 120, 'connect_timeout' => 15]);
            $response = $client->request('GET', $url, ['stream' => true]);
            $body = $response->getBody();
            while (!$body->eof()) {
                echo $body->read(65536);
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        }, $filename, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . str_replace('"', '\\"', $filename) . '"',
        ]);
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
