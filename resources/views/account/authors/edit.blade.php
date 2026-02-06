@extends('layouts.dashboard')

@section('title', 'Редактировать автора')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Редактировать автора: {{ $author->name }}</h1>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        <div style="max-width: 800px; margin: 2rem auto;">
            @if ($errors->any())
                <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-danger);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div style="background: var(--bg-success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-success);">
                    {{ session('success') }}
                </div>
            @endif

            @if($hasPendingModerations)
                <div style="background: var(--bg-warning); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-warning);">
                    ⚠️ Есть изменения на модерации. Новые изменения можно будет внести после рассмотрения текущих.
                </div>
            @endif

            <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <strong>Имя автора:</strong> {{ $author->name }} (редактирование недоступно)
            </div>

            <form method="POST" action="{{ route('account.authors.update', $author) }}" enctype="multipart/form-data" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                @csrf
                @method('POST')

                <div style="margin-bottom: 1.5rem;">
                    <label for="img" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Фото автора</label>
                    @if($author->img)
                        <div style="margin-bottom: 0.5rem;">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('authors')->url($author->img) }}" alt="{{ $author->name }}" 
                                 style="max-width: 200px; border-radius: 8px;">
                        </div>
                    @endif
                    <input type="file" id="img" name="img" accept="image/*"
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary);">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="long_description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Текст под заголовком (необязательно)</label>
                    <textarea id="long_description" name="long_description" rows="8"
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">{{ old('long_description', $author->long_description) }}</textarea>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Об Авторе</label>
                    <div id="content-editor" style="min-height: 300px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);"></div>
                    <textarea id="content" name="content" style="display: none;">{{ old('content', $author->content) }}</textarea>
                </div>

                <!-- SEO поля под спойлером -->
                <details style="margin-bottom: 2rem; background: var(--bg-tertiary); padding: 1rem; border-radius: 8px;">
                    <summary style="cursor: pointer; font-weight: 500; padding: 0.5rem;">SEO настройки</summary>
                    <div style="margin-top: 1.5rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_title" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">SEO Title</label>
                            <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $author->title) }}"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">SEO Description</label>
                            <textarea id="seo_description" name="seo_description" rows="3"
                                      style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">{{ old('seo_description', $author->description) }}</textarea>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_h1" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">H1</label>
                            <input type="text" id="seo_h1" name="seo_h1" value="{{ old('seo_h1', $author->h1) }}"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                    </div>
                </details>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="padding: 0.75rem 2rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;" 
                            {{ $hasPendingModerations ? 'disabled' : '' }}>
                        Сохранить изменения
                    </button>
                    <a href="{{ route('account.authors.index') }}" 
                       style="padding: 0.75rem 2rem; background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block;">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<link href="/js/quill/quill.snow.css" rel="stylesheet">
<script src="/js/quill/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var quill = new Quill('#content-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Введите текст об авторе...'
    });
    
    // Загружаем существующий контент в редактор
    var contentTextarea = document.getElementById('content');
    if (contentTextarea.value && contentTextarea.value.trim()) {
        quill.root.innerHTML = contentTextarea.value;
    }
    
    // Синхронизируем содержимое редактора с textarea при каждом изменении
    quill.on('text-change', function() {
        contentTextarea.value = quill.root.innerHTML;
    });
    
    // Также синхронизируем перед отправкой формы (на всякий случай)
    var form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Синхронизируем содержимое редактора с textarea
        contentTextarea.value = quill.root.innerHTML;
    });
});
</script>
@endpush
