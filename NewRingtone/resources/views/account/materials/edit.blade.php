@extends('layouts.dashboard')

@section('title', 'Редактировать материал')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Редактировать материал</h1>
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

            @if($material->moderation_status === 'pending')
                <div style="background: var(--bg-warning); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-warning);">
                    ⚠️ Материал находится на модерации. При редактировании изменения также будут отправлены на модерацию.
                </div>
            @endif

            <form method="POST" action="{{ route('account.materials.update', $material) }}" enctype="multipart/form-data" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                @csrf
                @method('POST')

                <!-- Основные поля -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem;">Основная информация</h2>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Название <span style="color: var(--text-danger);">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $material->name) }}" required readonly
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-tertiary); color: var(--text-primary); cursor: not-allowed;">
                        <small style="color: var(--text-tertiary); font-size: 0.85rem;">Название материала нельзя изменить после создания</small>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="type_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Тип <span style="color: var(--text-danger);">*</span></label>
                        <select id="type_id" name="type_id" required
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                            <option value="">Выберите тип</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('type_id', $material->type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="authors" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Авторы <span style="color: var(--text-danger);">*</span></label>
                        <select id="authors" name="authors[]" multiple required style="display: none;">
                            @foreach($activeAuthors as $author)
                                <option value="{{ $author->id }}" {{ in_array($author->id, old('authors', $material->authors->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $author->name }}</option>
                            @endforeach
                        </select>
                        <div id="authors-chips" class="multi-select-chips" data-select-id="authors"></div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="categories" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Категории</label>
                        <select id="categories" name="categories[]" multiple style="display: none;">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ in_array($category->id, old('categories', $material->categories->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div id="categories-chips" class="multi-select-chips" data-select-id="categories"></div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="tags" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Теги</label>
                        <select id="tags" name="tags[]" multiple style="display: none;">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $material->tags->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div id="tags-chips" class="multi-select-chips" data-select-id="tags"></div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Текст песни</label>
                        <textarea id="content" name="content" rows="15"
                                  placeholder="Введите текст песни..."
                                  style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); font-family: monospace; white-space: pre-wrap;">{{ old('content', $material->content) }}</textarea>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="mp4" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">MP4 файл</label>
                        @if($material->mp4 && \Illuminate\Support\Facades\Storage::disk('mp4')->exists($material->mp4))
                            <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: var(--bg-tertiary); border-radius: 6px;">
                                Текущий файл: {{ $material->mp4 }}
                                @if($material->mp4_duration)
                                    ({{ gmdate('i:s', $material->mp4_duration) }})
                                @endif
                            </div>
                        @endif
                        <input type="file" id="mp4" name="mp4" accept="audio/mp3,audio/mp4,audio/mpeg,audio/x-m4a"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary);">
                        <small style="color: var(--text-tertiary); font-size: 0.85rem;">Оставьте пустым, чтобы не менять файл. Форматы: MP3, MP4, M4A. Максимум 10MB</small>
                    </div>
                </div>

                <!-- SEO поля под спойлером -->
                <details style="margin-bottom: 2rem; background: var(--bg-tertiary); padding: 1rem; border-radius: 8px;" {{ (old('seo_title') || old('seo_description') || old('seo_h1') || $material->title !== $material->name || $material->h1 !== $material->name) ? 'open' : '' }}>
                    <summary style="cursor: pointer; font-weight: 500; padding: 0.5rem;">SEO настройки (опционально)</summary>
                    <div style="margin-top: 1.5rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_title" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">SEO Title</label>
                            <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $material->title) }}"
                                   placeholder="Если не заполнено, будет использовано название"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">SEO Description</label>
                            <textarea id="seo_description" name="seo_description" rows="3"
                                      placeholder="Если не заполнено, будет использовано название"
                                      style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">{{ old('seo_description', $material->description) }}</textarea>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="seo_h1" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">H1</label>
                            <input type="text" id="seo_h1" name="seo_h1" value="{{ old('seo_h1', $material->h1) }}"
                                   placeholder="Если не заполнено, будет использовано название"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="long_description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Длинное описание</label>
                            <textarea id="long_description" name="long_description" rows="5"
                                      style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">{{ old('long_description', $material->long_description) }}</textarea>
                        </div>
                    </div>
                </details>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="padding: 0.75rem 2rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                        Сохранить изменения
                    </button>
                    <a href="{{ route('account.materials.index') }}" 
                       style="padding: 0.75rem 2rem; background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block;">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.multi-select-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    min-height: 42px;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-primary);
    cursor: pointer;
    position: relative;
}

.multi-select-chips::after {
    content: '⌄';
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    font-size: 1.2rem;
    pointer-events: none;
}

.multi-select-chips.has-selection::after {
    right: 0.75rem;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    background: var(--accent-primary);
    color: white;
    border-radius: 16px;
    font-size: 0.875rem;
    font-weight: 500;
    max-width: 200px;
}

.chip-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.chip-remove {
    cursor: pointer;
    font-weight: bold;
    font-size: 1.1rem;
    line-height: 1;
    padding: 0;
    margin: 0;
    background: none;
    border: none;
    color: white;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.chip-remove:hover {
    opacity: 1;
}

.chip-remove::before {
    content: '×';
}

.multi-select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 6px 6px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    margin-top: -1px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: none;
}

.multi-select-dropdown.open {
    display: block;
}

.multi-select-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background 0.2s;
}

.multi-select-option:hover {
    background: var(--bg-secondary);
}

.multi-select-option.selected {
    background: var(--bg-tertiary);
    font-weight: 500;
}

.multi-select-wrapper {
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initMultiSelect(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        const chipsContainer = document.getElementById(selectId + '-chips');
        if (!chipsContainer) return;
        
        // Создаем wrapper для позиционирования dropdown
        const wrapper = document.createElement('div');
        wrapper.className = 'multi-select-wrapper';
        wrapper.style.position = 'relative';
        
        // Перемещаем chipsContainer в wrapper
        chipsContainer.parentNode.insertBefore(wrapper, chipsContainer);
        wrapper.appendChild(chipsContainer);
        
        // Создаем dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'multi-select-dropdown';
        wrapper.appendChild(dropdown);
        
        function updateChips() {
            chipsContainer.innerHTML = '';
            const selected = Array.from(select.selectedOptions);
            
            selected.forEach(option => {
                const chip = document.createElement('span');
                chip.className = 'chip';
                
                const chipText = document.createElement('span');
                chipText.className = 'chip-text';
                chipText.textContent = option.textContent;
                chip.appendChild(chipText);
                
                const chipRemove = document.createElement('button');
                chipRemove.className = 'chip-remove';
                chipRemove.type = 'button';
                chipRemove.onclick = function(e) {
                    e.stopPropagation();
                    option.selected = false;
                    updateChips();
                    updateDropdown();
                };
                chip.appendChild(chipRemove);
                
                chipsContainer.appendChild(chip);
            });
            
            if (selected.length > 0) {
                chipsContainer.classList.add('has-selection');
            } else {
                chipsContainer.classList.remove('has-selection');
            }
        }
        
        function updateDropdown() {
            dropdown.innerHTML = '';
            
            Array.from(select.options).forEach(option => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'multi-select-option';
                if (option.selected) {
                    optionDiv.classList.add('selected');
                }
                optionDiv.textContent = option.textContent;
                optionDiv.onclick = function(e) {
                    e.stopPropagation();
                    option.selected = !option.selected;
                    updateChips();
                    updateDropdown();
                };
                dropdown.appendChild(optionDiv);
            });
        }
        
        chipsContainer.onclick = function(e) {
            if (e.target.closest('.chip-remove')) return;
            e.stopPropagation();
            dropdown.classList.toggle('open');
        };
        
        // Закрытие dropdown при клике вне его
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
        
        // Инициализация
        updateChips();
        updateDropdown();
    }
    
    // Инициализируем все поля
    initMultiSelect('authors');
    initMultiSelect('categories');
    initMultiSelect('tags');
});
</script>
@endsection
