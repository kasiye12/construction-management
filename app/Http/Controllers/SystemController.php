<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function health()
    {
        $stats = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
        ];
        
        return view('system.health', compact('stats'));
    }
    
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'driver' => DB::connection()->getDriverName()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkStorage()
    {
        $path = storage_path('app');
        return [
            'status' => is_writable($path) ? 'writable' : 'not_writable',
            'free_space' => $this->formatBytes(disk_free_space($path)),
        ];
    }
    
    private function checkCache()
    {
        return [
            'driver' => config('cache.default'),
            'status' => 'active',
        ];
    }
    
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units)-1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
