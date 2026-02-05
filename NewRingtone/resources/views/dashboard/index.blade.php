@extends('layouts.dashboard')

@section('title', 'Личный кабинет')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-title">Личный кабинет</h1>
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

        @if (session('error'))
            <div style="background: var(--bg-danger); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: var(--text-danger);">
                {{ session('error') }}
            </div>
        @endif

        <!-- Статистика -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <a href="{{ route('account.materials.index') }}" style="text-decoration: none; display: block; color: inherit;">
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; transition: background 0.2s; cursor: pointer; height: 100%;" 
                     onmouseover="this.style.background='var(--bg-tertiary)'" 
                     onmouseout="this.style.background='var(--bg-secondary)'">
                    <div style="font-size: 2rem; font-weight: bold; color: var(--accent-primary); margin-bottom: 0.5rem; line-height: 1.2;">{{ $stats['materials_total'] }}</div>
                    <div style="color: var(--text-tertiary); font-size: 0.9rem; line-height: 1.4;">Материалов</div>
                </div>
            </a>
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; height: 100%;">
                <div style="font-size: 2rem; font-weight: bold; color: var(--text-success); margin-bottom: 0.5rem; line-height: 1.2;">{{ $stats['materials_approved'] }}</div>
                <div style="color: var(--text-tertiary); font-size: 0.9rem; line-height: 1.4;">Одобрено</div>
            </div>
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; height: 100%;">
                <div style="font-size: 2rem; font-weight: bold; color: var(--text-warning); margin-bottom: 0.5rem; line-height: 1.2;">{{ $stats['materials_pending'] }}</div>
                <div style="color: var(--text-tertiary); font-size: 0.9rem; line-height: 1.4;">Ожидают</div>
            </div>
            <a href="{{ route('account.authors.index') }}" style="text-decoration: none; display: block; color: inherit;">
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; transition: background 0.2s; cursor: pointer; height: 100%;" 
                     onmouseover="this.style.background='var(--bg-tertiary)'" 
                     onmouseout="this.style.background='var(--bg-secondary)'">
                    <div style="font-size: 2rem; font-weight: bold; color: var(--accent-primary); margin-bottom: 0.5rem; line-height: 1.2;">{{ $stats['authors_count'] }}</div>
                    <div style="color: var(--text-tertiary); font-size: 0.9rem; line-height: 1.4;">Авторов</div>
                </div>
            </a>
        </div>

        <!-- Быстрые действия -->
        <div class="quick-actions-block" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem;">Быстрые действия</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('account.materials.create') }}" 
                   style="padding: 0.75rem 1.5rem; background: var(--accent-primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 500;">
                    Создать материал
                </a>
                <a href="{{ route('account.authors.index') }}" 
                   style="padding: 0.75rem 1.5rem; background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none; border-radius: 6px; font-weight: 500;">
                    Запросить автора
                </a>
            </div>
        </div>

        <!-- Последние материалы -->
        <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="margin: 0;">Последние материалы</h2>
                <a href="{{ route('account.materials.index') }}" style="color: var(--accent-primary); text-decoration: none;">Все материалы →</a>
            </div>

            @if($recentMaterials->count() > 0)
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 0.75rem; text-align: left;">Название</th>
                                <th style="padding: 0.75rem; text-align: left;">Тип</th>
                                <th style="padding: 0.75rem; text-align: left;">Статус</th>
                                <th style="padding: 0.75rem; text-align: left;">Дата</th>
                                <th style="padding: 0.75rem; text-align: left;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMaterials as $material)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem;">
                                    @if($material->moderation_status === 'approved')
                                        <a href="{{ route('materials.show', $material->slug) }}" target="_blank" 
                                           style="color: var(--accent-primary); text-decoration: none;">
                                            {{ $material->name }}
                                        </a>
                                    @else
                                        {{ $material->name }}
                                    @endif
                                </td>
                                    <td style="padding: 0.75rem;">{{ $material->type->name ?? '-' }}</td>
                                    <td style="padding: 0.75rem;">
                                        @if($material->moderation_status === 'approved')
                                            <span style="color: var(--text-success);">Одобрено</span>
                                        @elseif($material->moderation_status === 'pending')
                                            <span style="color: var(--text-warning);">На модерации</span>
                                        @elseif($material->moderation_status === 'rejected')
                                            <span style="color: var(--text-danger);">Отклонено</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem;">{{ $material->created_at->format('d.m.Y') }}</td>
                                    <td style="padding: 0.75rem;">
                                        <a href="{{ route('account.materials.edit', $material) }}" style="color: var(--accent-primary); text-decoration: none; margin-right: 0.5rem;">Редактировать</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="color: var(--text-tertiary);">У вас пока нет материалов. <a href="{{ route('account.materials.create') }}" style="color: var(--accent-primary);">Создайте первый материал</a></p>
            @endif
        </div>
    </div>
</main>
@endsection
