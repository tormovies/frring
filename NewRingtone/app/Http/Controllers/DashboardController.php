<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Показать dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Дополнительная проверка (на случай, если middleware не сработал)
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Необходима авторизация для доступа к личному кабинету.');
        }

        // Проверяем статус пользователя (только если колонка существует)
        if (isset($user->status) && $user->isBlocked()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Ваш аккаунт заблокирован.']);
        }
        
        // Проверяем, может ли пользователь создавать материалы (защита от ошибок)
        try {
            if (!$user->canCreateMaterials()) {
                // Пользователь с email_verified может видеть dashboard, но не может создавать материалы
            }
        } catch (\Exception $e) {
            // Игнорируем ошибку, продолжаем выполнение
        }

        // Статистика (с защитой от ошибок и кешированием на 5 минут)
        try {
            $stats = Cache::remember("user_stats_{$user->id}", 300, function() use ($user) {
                // Используем один запрос с группировкой для материалов
                $materialsStats = $user->materials()
                    ->selectRaw('moderation_status, count(*) as count')
                    ->groupBy('moderation_status')
                    ->pluck('count', 'moderation_status')
                    ->toArray();

                return [
                    'materials_total' => array_sum($materialsStats),
                    'materials_approved' => $materialsStats['approved'] ?? 0,
                    'materials_pending' => $materialsStats['pending'] ?? 0,
                    'materials_rejected' => $materialsStats['rejected'] ?? 0,
                    'authors_count' => $user->authors()->count(),
                ];
            });
        } catch (\Exception $e) {
            // Если возникла ошибка, устанавливаем нулевые значения
            $stats = [
                'materials_total' => 0,
                'materials_approved' => 0,
                'materials_pending' => 0,
                'materials_rejected' => 0,
                'authors_count' => 0,
            ];
        }

        // Последние материалы (с защитой от ошибок)
        try {
            $recentMaterials = $user->materials()
                ->with(['type', 'authors'])
                ->latest()
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            // Если возникла ошибка, используем пустую коллекцию
            $recentMaterials = collect([]);
        }

        return view('dashboard.index', compact('stats', 'recentMaterials'));
    }
}
