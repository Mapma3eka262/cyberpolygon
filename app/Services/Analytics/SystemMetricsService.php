<?php

namespace App\Services\Analytics;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemMetricsService
{
    public function collectMetrics(): array
    {
        $metrics = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'database' => $this->getDatabaseMetrics(),
            'users' => $this->getUserMetrics(),
            'network' => $this->getNetworkMetrics(),
        ];

        // Сохраняем в базу
        SystemLog::create([
            'cpu_usage' => $metrics['cpu']['usage'],
            'memory_usage' => $metrics['memory']['used'],
            'memory_total' => $metrics['memory']['total'],
            'active_users' => $metrics['users']['active'],
            'active_teams' => $metrics['users']['active_teams'],
            'total_attempts' => $metrics['users']['total_attempts'],
        ]);

        return $metrics;
    }

    private function getCpuUsage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'usage' => $load[0],
                'load_1' => $load[0],
                'load_5' => $load[1],
                'load_15' => $load[2],
            ];
        }

        // Альтернативный метод для Windows
        $output = [];
        exec('wmic cpu get loadpercentage', $output);
        $usage = isset($output[1]) ? intval($output[1]) / 100 : 0;

        return [
            'usage' => $usage,
            'load_1' => $usage,
            'load_5' => $usage,
            'load_15' => $usage,
        ];
    }

    private function getMemoryUsage(): array
    {
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
            
            $totalMem = intval($total[1]) / 1024; // KB to MB
            $availableMem = intval($available[1]) / 1024;
            $usedMem = $totalMem - $availableMem;
            
            return [
                'total' => round($totalMem, 2),
                'used' => round($usedMem, 2),
                'available' => round($availableMem, 2),
                'percentage' => round(($usedMem / $totalMem) * 100, 2),
            ];
        }

        // Для Windows
        $output = [];
        exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory', $output);
        
        $total = isset($output[1]) ? intval($output[1]) / 1024 : 0;
        $free = isset($output[2]) ? intval($output[2]) / 1024 : 0;
        $used = $total - $free;

        return [
            'total' => round($total, 2),
            'used' => round($used, 2),
            'available' => round($free, 2),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    private function getDatabaseMetrics(): array
    {
        $connections = DB::select('SHOW STATUS LIKE "Threads_connected"');
        $running = DB::select('SHOW STATUS LIKE "Threads_running"');
        
        $slowQueries = Cache::remember('slow_queries_count', 300, function () {
            return DB::table('slow_query_log')->count();
        });

        return [
            'connections' => $connections[0]->Value ?? 0,
            'running' => $running[0]->Value ?? 0,
            'slow_queries' => $slowQueries,
        ];
    }

    private function getUserMetrics(): array
    {
        return Cache::remember('user_metrics', 60, function () {
            $activeTime = now()->subMinutes(5);
            
            return [
                'total' => DB::table('users')->count(),
                'active' => DB::table('users')->where('updated_at', '>', $activeTime)->count(),
                'active_teams' => DB::table('teams')->where('is_active', true)->count(),
                'total_attempts' => DB::table('flag_attempts')->where('created_at', '>', now()->subHour())->count(),
                'online' => DB::table('sessions')->where('last_activity', '>', now()->subMinutes(5))->count(),
            ];
        });
    }

    private function getNetworkMetrics(): array
    {
        // Упрощенная версия для Linux
        $rx = $tx = 0;
        
        if (is_readable('/proc/net/dev')) {
            $content = file_get_contents('/proc/net/dev');
            preg_match('/eth0:\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $content, $matches);
            
            if (count($matches) >= 3) {
                $rx = intval($matches[1]);
                $tx = intval($matches[2]);
            }
        }

        return [
            'rx_bytes' => $rx,
            'tx_bytes' => $tx,
        ];
    }

    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => round($total / (1024 * 1024 * 1024), 2), // GB
            'used' => round($used / (1024 * 1024 * 1024), 2),
            'free' => round($free / (1024 * 1024 * 1024), 2),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }
}