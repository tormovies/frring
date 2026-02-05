<?php

namespace App\Filament\Widgets;

use App\Models\AuthorModeration;
use App\Models\AuthorRequest;
use App\Models\Material;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class ModerationStatusWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingMaterials = Material::where('moderation_status', 'pending')->count();
        $pendingAuthorRequests = AuthorRequest::where('status', 'pending')->count();
        $pendingAuthorModerations = AuthorModeration::where('status', 'pending')->count();
        $emailVerifiedUsers = User::where('status', 'email_verified')->count();
        
        $totalPending = $pendingMaterials + $pendingAuthorRequests + $pendingAuthorModerations;

        return [
            Stat::make('Материалы на модерации', $pendingMaterials)
                ->description('Требуют рассмотрения')
                ->descriptionIcon('heroicon-m-musical-note')
                ->color($pendingMaterials > 0 ? 'warning' : 'success')
                ->url($pendingMaterials > 0 ? '/admin/materials?moderation_status=pending' : null),
            
            Stat::make('Запросы на авторов', $pendingAuthorRequests)
                ->description('Ожидают модерации')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($pendingAuthorRequests > 0 ? 'warning' : 'success')
                ->url($pendingAuthorRequests > 0 ? '/admin/author-requests?status=pending' : null),
            
            Stat::make('Изменения авторов', $pendingAuthorModerations)
                ->description('На модерации')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($pendingAuthorModerations > 0 ? 'warning' : 'success')
                ->url($pendingAuthorModerations > 0 ? '/admin/author-moderations?status=pending' : null),
            
            Stat::make('Пользователи на активацию', $emailVerifiedUsers)
                ->description('Email подтвержден, ждут активации')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($emailVerifiedUsers > 0 ? 'warning' : 'success')
                ->url($emailVerifiedUsers > 0 ? '/admin/users?tableFilters[status][value]=email_verified' : null),
            
            Stat::make('Всего на модерации', $totalPending)
                ->description($totalPending > 0 ? 'Требуется ваше внимание' : 'Все рассмотрено')
                ->descriptionIcon($totalPending > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($totalPending > 0 ? 'danger' : 'success'),
        ];
    }
}
