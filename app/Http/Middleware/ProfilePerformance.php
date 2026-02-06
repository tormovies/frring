<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProfilePerformance
{
    /**
     * Handle an incoming request.
     * Логирует время выполнения, количество запросов к БД и медленные запросы.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Включаем логирование запросов к БД
        DB::enableQueryLog();
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $executionTime = round(($endTime - $startTime) * 1000, 2); // в миллисекундах
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2); // в МБ
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Находим медленные запросы (> 100ms)
        $slowQueries = [];
        foreach ($queries as $query) {
            $time = $query['time'] ?? 0;
            if ($time > 100) {
                $slowQueries[] = [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'] ?? [],
                    'time' => $time . 'ms',
                ];
            }
        }
        
        // Логируем только если время выполнения > 500ms или много запросов (> 20)
        // Или если есть медленные запросы
        if ($executionTime > 500 || $queryCount > 20 || !empty($slowQueries)) {
            $logData = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => $executionTime,
                'memory_mb' => $memoryUsed,
                'query_count' => $queryCount,
                'slow_queries' => $slowQueries,
            ];
            
            Log::info('Performance Profile', $logData);
        }
        
        // Добавляем заголовки для отладки (только в dev)
        if (app()->environment('local')) {
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Memory-Used', $memoryUsed . 'MB');
            
            if (!empty($slowQueries)) {
                $response->headers->set('X-Slow-Queries', count($slowQueries));
            }
        }
        
        return $response;
    }
}
