<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            try {
                $user = Auth::user();

                // Проверяем статус заблокированного пользователя (только если колонка существует)
                if (isset($user->status) && $user->isBlocked()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors(['email' => 'Ваш аккаунт заблокирован администратором. Пожалуйста, обратитесь к администратору для получения дополнительной информации.']);
                }
            } catch (\Exception $e) {
                // Если произошла ошибка при проверке статуса, пропускаем запрос дальше
                // Логируем ошибку, если логирование доступно
                \Log::warning('CheckUserStatus middleware error: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
