@extends('layouts.dashboard')

@section('title', 'Мои авторы')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Мои авторы</h1>
        </div>
    </div>
</div>

<main class="main-cloud">
    <div class="container">
        @if (session('success'))
            <div style="background: var(--bg-success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-success);">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-danger);">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Список авторов -->
        <div class="account-authors-block" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem;">Привязанные авторы</h2>

            @if($authors->count() > 0)
                <div style="display: grid; gap: 1rem;">
                    @foreach($authors as $author)
                        <div class="account-author-card" style="padding: 1.5rem; background: var(--bg-primary); border-radius: 8px; border: 1px solid var(--border-color);">
                            <div class="account-author-card-header" style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0;">
                                        <a href="{{ route('search', ['query' => $author->name]) }}" style="color: var(--accent-primary); text-decoration: none;">
                                            {{ $author->name }}
                                        </a>
                                    </h3>
                                    @if($author->hasPendingModerations())
                                        <span style="color: var(--text-warning); font-size: 0.85rem;">⚠️ Есть изменения на модерации</span>
                                    @endif
                                </div>
                                <div class="account-author-actions" style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('account.authors.edit', $author) }}" 
                                       style="padding: 0.5rem 1rem; background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                                        Редактировать
                                    </a>
                                    <button type="button" 
                                            onclick="showDetachModal({{ $author->id }}, '{{ addslashes($author->name) }}')"
                                            style="padding: 0.5rem 1rem; background: #dc3545; color: white; border: none; border-radius: 6px; font-size: 0.9rem; cursor: pointer; font-weight: 500;">
                                        Отвязать автора
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-tertiary); text-align: center; padding: 2rem;">
                    У вас нет привязанных авторов. Запросите доступ к автору ниже.
                </p>
            @endif
        </div>

        <!-- Форма запроса на автора -->
        <div class="account-authors-block" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem;">Запросить доступ к автору</h2>
            
            @if($pendingRequests->count() > 0)
                <div style="background: var(--bg-warning); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-warning);">
                    ⚠️ У вас есть активный запрос на автора, который находится на модерации. Дождитесь рассмотрения.
                </div>
            @else
                <form method="POST" action="{{ route('account.authors.request') }}">
                    @csrf

                    <div class="account-authors-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label for="author_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Имя Автора <span style="color: var(--text-danger);">*</span></label>
                            <input type="text" id="author_name" name="author_name" value="{{ old('author_name') }}" required
                                   placeholder="Имя артиста"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>

                        <div>
                            <label for="author_card_url" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Ссылка на карточку артиста <span style="color: var(--text-danger);">*</span></label>
                            <input type="url" id="author_card_url" name="author_card_url" value="{{ old('author_card_url') }}" required
                                   placeholder="https://vk.com/artist"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                    </div>

                    <button type="submit" style="padding: 0.75rem 1.5rem; background: var(--accent-primary); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                        Отправить запрос
                    </button>
                </form>
            @endif
        </div>
    </div>
</main>

<!-- Модальное окно для подтверждения отвязки -->
<div id="detachModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; margin: auto;">
        <h2 style="margin-top: 0; color: var(--text-danger); margin-bottom: 1rem;">⚠️ Внимание! Необратимое действие</h2>
        <p style="margin-bottom: 1rem; line-height: 1.6;">
            Вы собираетесь отвязать автора <strong id="detachAuthorName"></strong> от вашего аккаунта.
        </p>
        <div style="background: var(--bg-warning); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid var(--text-danger);">
            <p style="margin: 0; font-weight: 500; color: var(--text-danger); margin-bottom: 0.5rem;">Это действие необратимо и приведет к:</p>
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-primary);">
                <li>Потере доступа к управлению этим автором</li>
                <li>Потере возможности редактировать материалы этого автора</li>
                <li>Потере возможности создавать новые материалы для этого автора</li>
                <li>Материалы автора останутся на сайте, но будут недоступны вам для редактирования</li>
            </ul>
        </div>
        <form id="detachForm" method="POST">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: start; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="confirmDetach" name="confirm" value="yes" required 
                           style="margin-top: 0.25rem; width: auto;">
                    <span style="color: var(--text-primary);">
                        Я понимаю последствия и подтверждаю, что хочу отвязать этого автора от моего аккаунта
                    </span>
                </label>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" 
                        onclick="hideDetachModal()"
                        style="padding: 0.75rem 1.5rem; background: var(--bg-tertiary); color: var(--text-primary); border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    Отмена
                </button>
                <button type="submit" 
                        id="confirmDetachBtn"
                        disabled
                        style="padding: 0.75rem 1.5rem; background: #dc3545; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; opacity: 0.5; transition: opacity 0.2s;">
                    Да, отвязать автора
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showDetachModal(authorId, authorName) {
    const modal = document.getElementById('detachModal');
    const form = document.getElementById('detachForm');
    const nameField = document.getElementById('detachAuthorName');
    const confirmCheckbox = document.getElementById('confirmDetach');
    const confirmBtn = document.getElementById('confirmDetachBtn');
    
    // Сброс формы
    confirmCheckbox.checked = false;
    confirmBtn.disabled = true;
    confirmBtn.style.opacity = '0.5';
    confirmBtn.style.cursor = 'not-allowed';
    
    nameField.textContent = authorName;
    form.action = '{{ route("account.authors.detach", ":id") }}'.replace(':id', authorId);
    modal.style.display = 'flex';
}

function hideDetachModal() {
    document.getElementById('detachModal').style.display = 'none';
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detachModal');
    const confirmCheckbox = document.getElementById('confirmDetach');
    
    // Включение кнопки подтверждения при отметке checkbox
    if (confirmCheckbox) {
        confirmCheckbox.addEventListener('change', function() {
            const confirmBtn = document.getElementById('confirmDetachBtn');
            if (this.checked) {
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = '1';
                confirmBtn.style.cursor = 'pointer';
            } else {
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = '0.5';
                confirmBtn.style.cursor = 'not-allowed';
            }
        });
    }
    
    // Закрытие по клику вне модального окна
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideDetachModal();
            }
        });
    }
});
</script>
@endsection
