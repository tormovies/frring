@extends('layouts.app-old')

@section('title', $seo['title'] ?? $page->name)
@section('description', $seo['description'] ?? '')

@section('content')
    <div class="col-12 text_ringtones">
        <h1>{{ ($seo['h1'] ?? '') !== '' ? $seo['h1'] : ($page->h1 ?? $page->name) }}</h1>
        @if(trim((string) ($page->content ?? '')) !== '')
            @php
                $pageContent = $page->content ?? '';
                // Картинка со старого сайта — в public/img/pages/
                $audacityImgUrl = asset('img/pages/audacity-200.jpg');
                $pageContent = preg_replace('#src=(["\'])([^"\']*audacity-200\.jpg)\1#iu', 'src=$1' . $audacityImgUrl . '$1', $pageContent);
                // Файлы для скачивания (программы) — в public/distr/
                $distrFiles = [
                    'audacity-win-2.0.5.exe',
                    'Lame_v3.99.3_for_Windows.exe',
                    'FFmpeg_v0.6.2_for_Audacity_on_Windows.exe',
                ];
                foreach ($distrFiles as $file) {
                    $url = asset('distr/' . $file);
                    $escaped = preg_quote($file, '#');
                    $pageContent = preg_replace('#href=(["\'])([^"\']*distr/' . $escaped . ')\1#iu', 'href=$1' . $url . '$1', $pageContent);
                }
            @endphp
            <div class="page-content">
                {!! $pageContent !!}
            </div>
        @endif
    </div>
@endsection
