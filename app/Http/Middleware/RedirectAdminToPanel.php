<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToPanel
{
    /**
     * Handle an incoming request.
     * Перенаправляет админов на панель админки вместо пользовательского кабинета
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Если пользователь не авторизован, пропускаем дальше
        if (!$user) {
            return $next($request);
        }
        
        // Проверяем является ли пользователь админом
        // Используем строгую проверку - только если есть роль Admin
        try {
            // Проверяем роли напрямую через getRoleNames()
            $roles = $user->getRoleNames();
            
            // Если ролей нет (пустая коллекция), точно не админ - пропускаем дальше
            if ($roles->isEmpty() || $roles->count() === 0) {
                return $next($request);
            }
            
            // Проверяем наличие роли Admin (строгая проверка - только точные совпадения)
            $hasAdminRole = false;
            foreach ($roles as $role) {
                $roleName = trim((string)$role);
                // Только точные совпадения (без приведения к нижнему регистру для строгости)
                if ($roleName === 'Admin' || $roleName === 'admin' || $roleName === 'super_admin') {
                    $hasAdminRole = true;
                    break;
                }
            }
            
            // Только если есть роль Admin, перенаправляем на админку
            if ($hasAdminRole === true) {
                return redirect('/admin');
            }
        } catch (\Exception $e) {
            // Если возникла ошибка при проверке админа, пропускаем запрос дальше
            // Не логируем, чтобы не засорять логи
        }
        
        // Если не админ - пропускаем дальше
        return $next($request);
    }
}
