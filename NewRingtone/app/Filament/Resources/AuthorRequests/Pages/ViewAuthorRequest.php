<?php

namespace App\Filament\Resources\AuthorRequests\Pages;

use App\Filament\Resources\AuthorRequests\AuthorRequestResource;
use App\Mail\AuthorRequestApproved;
use App\Mail\AuthorRequestRejected;
use App\Models\Author;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ViewAuthorRequest extends ViewRecord
{
    protected static string $resource = AuthorRequestResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_name'] = $this->record->user->name ?? 'Не указан';
        $data['user_email'] = $this->record->user->email ?? 'Не указан';
        $data['existing_author'] = $this->record->author->name ?? 'Автор еще не создан';
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Одобрить')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Одобрить запрос на автора')
                ->modalDescription('При одобрении будет создан автор (если его нет) и привязан к пользователю. Пользователь получит уведомление на email.')
                ->action(function () {
                    $request = $this->record;
                    
                    // Проверяем, существует ли автор
                    $author = $request->author;
                    
                    if (!$author) {
                        // Создаем нового автора
                        $author = Author::create([
                            'name' => $request->author_name,
                            'slug' => Str::slug($request->author_name),
                            'title' => $request->author_name,
                            'description' => '',
                            'status' => true,
                        ]);
                    }
                    
                    // Проверяем, не привязан ли автор к другому пользователю
                    if ($author->users()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Ошибка')
                            ->body('Автор уже привязан к другому пользователю!')
                            ->send();
                        return;
                    }
                    
                    // Привязываем автора к пользователю
                    $request->user->authors()->attach($author->id);
                    
                    // Обновляем запрос
                    $request->update([
                        'status' => 'approved',
                        'author_id' => $author->id,
                    ]);
                    
                    // Обновляем статус пользователя на active, если он был email_verified
                    if ($request->user->status === 'email_verified') {
                        $request->user->update(['status' => 'active']);
                    }
                    
                    // Отправляем уведомление
                    Mail::to($request->user->email)->send(new AuthorRequestApproved($request));
                    
                    Notification::make()
                        ->success()
                        ->title('Запрос одобрен')
                        ->body('Автор создан и привязан к пользователю. Уведомление отправлено.')
                        ->send();
                    
                    return redirect(static::getResource()::getUrl('index'));
                }),
                
            Action::make('reject')
                ->label('Отклонить')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Причина отклонения')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $request = $this->record;
                    
                    $request->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    // Отправляем уведомление
                    Mail::to($request->user->email)->send(new AuthorRequestRejected($request));
                    
                    Notification::make()
                        ->success()
                        ->title('Запрос отклонен')
                        ->body('Уведомление отправлено пользователю.')
                        ->send();
                    
                    return redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
