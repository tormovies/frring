<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Запрос на автора отклонен</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #f44336;">Запрос на автора отклонен</h2>
        
        <p>Здравствуйте, {{ $authorRequest->user->name }}!</p>
        
        <p>К сожалению, ваш запрос на автора <strong>{{ $authorRequest->author_name }}</strong> был отклонен администратором.</p>
        
        @if($authorRequest->rejection_reason)
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                <strong>Причина отклонения:</strong><br>
                {{ $authorRequest->rejection_reason }}
            </div>
        @endif
        
        <p>Если у вас есть вопросы, пожалуйста, обратитесь к администратору: <a href="{{ route('authors.show', 'neurozvuk') }}">{{ route('authors.show', 'neurozvuk') }}</a></p>
        
        <p>
            <a href="{{ route('account.authors.index') }}" style="display: inline-block; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;">
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
