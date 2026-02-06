@extends('layouts.dashboard')

@section('title', 'Мои материалы')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Мои материалы</h1>
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

        <div class="materials-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="materials-title" style="margin: 0;">Все материалы</h2>
            <div class="materials-buttons" style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="{{ route('dashboard') }}" 
                   class="dashboard-button"
                   style="display: none; padding: 0.75rem 1.5rem; background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none; border-radius: 6px; font-weight: 500; border: 1px solid var(--border-color);">
                    Личный кабинет
                </a>
                <a href="{{ route('account.materials.create') }}" 
                   style="padding: 0.75rem 1.5rem; background: var(--accent-primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">
                    Создать материал
                </a>
            </div>
        </div>

        @if($materials->count() > 0)
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); background: var(--bg-tertiary);">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Название</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Тип</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Авторы</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Статус модерации</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Дата</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-primary);">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materials as $material)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s;" 
                                onmouseover="this.style.background='var(--bg-tertiary)'" 
                                onmouseout="this.style.background='transparent'">
                                <td style="padding: 1rem; color: var(--text-primary);">
                                    @if($material->moderation_status === 'approved')
                                        <a href="{{ route('materials.show', $material->slug) }}" target="_blank" 
                                           style="color: var(--accent-primary); text-decoration: none;">
                                            {{ $material->name }}
                                        </a>
                                    @else
                                        {{ $material->name }}
                                    @endif
                                </td>
                                <td style="padding: 1rem; color: var(--text-primary);">{{ $material->type->name ?? '-' }}</td>
                                <td style="padding: 1rem; color: var(--text-primary);">
                                    {{ $material->authors->pluck('name')->join(', ') ?: '-' }}
                                </td>
                                <td style="padding: 1rem;">
                                    @if($material->moderation_status === 'approved')
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; background: rgba(var(--success-rgb, 34, 197, 94), 0.1); color: var(--text-success, #22c55e); border-radius: 12px; font-size: 0.875rem; font-weight: 500;">Одобрено</span>
                                    @elseif($material->moderation_status === 'pending')
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; background: rgba(var(--warning-rgb, 251, 191, 36), 0.1); color: var(--text-warning, #fbbf24); border-radius: 12px; font-size: 0.875rem; font-weight: 500;">На модерации</span>
                                    @elseif($material->moderation_status === 'rejected')
                                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                            <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; background: rgba(var(--danger-rgb, 239, 68, 68), 0.1); color: var(--text-danger, #ef4444); border-radius: 12px; font-size: 0.875rem; font-weight: 500;">Отклонено</span>
                                            @if($material->rejection_reason)
                                                <small style="color: var(--text-tertiary); font-size: 0.75rem;">{{ Str::limit($material->rejection_reason, 50) }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: var(--text-tertiary);">-</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; color: var(--text-primary); font-size: 0.875rem;">{{ $material->created_at->format('d.m.Y H:i') }}</td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="{{ route('account.materials.edit', $material) }}" 
                                           style="display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; background: var(--accent-primary); color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; transition: opacity 0.2s; min-width: 110px;"
                                           onmouseover="this.style.opacity='0.9'" 
                                           onmouseout="this.style.opacity='1'">
                                            Редактировать
                                        </a>
                                        <form action="{{ route('account.materials.destroy', $material) }}" method="POST" style="display: inline; margin: 0;" 
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот материал?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    style="display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; background: var(--text-danger, #ef4444); color: white; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: opacity 0.2s; min-width: 110px;"
                                                    onmouseover="this.style.opacity='0.9'" 
                                                    onmouseout="this.style.opacity='1'">
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 1.5rem;">
                    <div class="pagination">
                        {{ $materials->links('components.pagination') }}
                    </div>
                </div>
            </div>
        @else
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px; text-align: center;">
                <p style="color: var(--text-tertiary); margin-bottom: 1rem;">У вас пока нет материалов.</p>
                <a href="{{ route('account.materials.create') }}" 
                   style="padding: 0.75rem 1.5rem; background: var(--accent-primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-block;">
                    Создать первый материал
                </a>
            </div>
        @endif
    </div>
</main>
@endsection
