<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Запрос на автора одобрен</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Ваш запрос на автора одобрен!</h2>
        
        <p>Здравствуйте, {{ $authorRequest->user->name }}!</p>
        
        <p>Ваш запрос на автора <strong>{{ $authorRequest->author_name }}</strong> был одобрен администратором.</p>
        
        @if($authorRequest->author)
            <p>Автор <strong>{{ $authorRequest->author->name }}</strong> теперь привязан к вашему аккаунту. Вы можете:</p>
            <ul>
                <li>Создавать материалы для этого автора</li>
                <li>Редактировать страницу автора</li>
                <li>Управлять материалами автора</li>
            </ul>
        @endif
        
        <p>
            <a href="{{ route('account.authors.index') }}" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                Перейти в личный кабинет
            </a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #999;">
            С уважением,<br>
            Команда {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
