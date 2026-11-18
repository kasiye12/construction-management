<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupService
{
    protected string $backupDir;
    protected string $canonicalDocDump;
    protected string $canonicalRepoDump;
    protected string $canonicalStorageDump;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }

        $this->canonicalDocDump = '/home/ks/Documents/construction_management.sql';
        $this->canonicalRepoDump = base_path('database/construction_management_full_dump.sql');
        $this->canonicalStorageDump = $this->backupDir . '/latest_backup.sql';
    }

    /**
     * Create a database backup dump and update the canonical files.
     *
     * @param string $type 'manual', 'daily', or 'auto'
     * @param bool $compress Whether to compress with gzip
     * @param int|null $userId
     * @return array
     */
    public function createBackup(string $type = 'manual', bool $compress = false, ?int $userId = null): array
    {
        $dbConfig = config('database.connections.mysql');
        $dbName = $dbConfig['database'] ?? 'construction_management';
        $dbUser = $dbConfig['username'] ?? 'root';
        $dbPass = $dbConfig['password'] ?? '';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? '3306';

        $timestamp = date('Y-m-d_His');
        $sqlFilename = "backup-{$dbName}-{$type}-{$timestamp}.sql";
        $sqlPath = "{$this->backupDir}/{$sqlFilename}";

        // Write a secure temporary credentials file to eliminate password exposure and warnings
        $tempCnf = tempnam(sys_get_temp_dir(), 'my_cnf_');
        $cnfContent = "[client]\n" .
            "user = \"{$dbUser}\"\n" .
            "password = \"{$dbPass}\"\n" .
            "host = \"{$dbHost}\"\n" .
            "port = \"{$dbPort}\"\n";
        file_put_contents($tempCnf, $cnfContent);
        chmod($tempCnf, 0600);

        $startTime = microtime(true);

        try {
            // Build mysqldump command
            $cmd = sprintf(
                'mysqldump --defaults-extra-file=%s --routines --triggers --single-transaction --quick %s > %s',
                escapeshellarg($tempCnf),
                escapeshellarg($dbName),
                escapeshellarg($sqlPath)
            );

            $output = [];
            $exitCode = 0;
            exec($cmd . ' 2>&1', $output, $exitCode);

            // If mysqldump failed or produced empty file, try direct fallback
            if ($exitCode !== 0 || !file_exists($sqlPath) || filesize($sqlPath) === 0) {
                // Direct fallback command
                $fallbackCmd = sprintf(
                    'mysqldump -u %s -p%s -h %s -P %s --routines --triggers --single-transaction %s > %s',
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbName),
                    escapeshellarg($sqlPath)
                );
                exec($fallbackCmd . ' 2>&1', $output, $exitCode);
            }

            if (!file_exists($sqlPath) || filesize($sqlPath) === 0) {
                $err = implode("\n", $output);
                throw new \Exception("Database backup failed: " . ($err ?: "mysqldump produced an empty file."));
            }

            $rawFilesize = filesize($sqlPath);

            // Update Canonical Backup Files
            // 1. In Documents/
            @copy($sqlPath, $this->canonicalDocDump);
            // 2. In Repo database/
            @copy($sqlPath, $this->canonicalRepoDump);
            // 3. In Storage backups/
            @copy($sqlPath, $this->canonicalStorageDump);

            $finalFilename = $sqlFilename;
            $finalPath = $sqlPath;

            // Optional Compression
            if ($compress) {
                $gzPath = "{$sqlPath}.gz";
                $fpOut = gzopen($gzPath, 'wb9');
                $fpIn = fopen($sqlPath, 'rb');
                if ($fpOut && $fpIn) {
                    while (!feof($fpIn)) {
                        gzwrite($fpOut, fread($fpIn, 1024 * 512));
                    }
                    fclose($fpIn);
                    gzclose($fpOut);
                    unlink($sqlPath); // remove uncompressed copy in backups folder
                    $finalPath = $gzPath;
                    $finalFilename = "{$sqlFilename}.gz";
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $finalFilesize = filesize($finalPath);

            // Record metadata in cache/activity
            $effectiveUserId = $userId ?: Auth::id();
            if ($effectiveUserId) {
                ActivityLog::create([
                    'user_id' => $effectiveUserId,
                    'action' => 'backup_created',
                    'module' => 'DatabaseBackup',
                    'description' => "Created {$type} database backup ({$finalFilename}, " . $this->formatBytes($finalFilesize) . ") in {$duration}s",
                    'new_values' => json_encode([
                        'filename' => $finalFilename,
                        'type' => $type,
                        'size' => $finalFilesize,
                        'duration_seconds' => $duration,
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                ]);
            }

            // Prune old backups (keep last 30 days)
            $prunedCount = 0;
            if ($type === 'daily' || $type === 'auto') {
                $prunedCount = $this->cleanOldBackups(30);
            }

            return [
                'success' => true,
                'filename' => $finalFilename,
                'path' => $finalPath,
                'filesize' => $finalFilesize,
                'filesize_formatted' => $this->formatBytes($finalFilesize),
                'type' => $type,
                'duration' => $duration,
                'canonical_doc_dump' => $this->canonicalDocDump,
                'canonical_repo_dump' => $this->canonicalRepoDump,
                'pruned_count' => $prunedCount,
            ];
        } finally {
            if (file_exists($tempCnf)) {
                @unlink($tempCnf);
            }
        }
    }

    /**
     * Get database stats, table counts, sizes, and health information.
     */
    public function getDatabaseStats(): array
    {
        $dbConfig = config('database.connections.mysql');
        $dbName = $dbConfig['database'] ?? 'construction_management';

        // Table statistics from information_schema
        $tables = DB::select("
            SELECT 
                TABLE_NAME AS table_name,
                ENGINE AS engine,
                COALESCE(TABLE_ROWS, 0) AS row_count,
                COALESCE(DATA_LENGTH, 0) AS data_bytes,
                COALESCE(INDEX_LENGTH, 0) AS index_bytes,
                (COALESCE(DATA_LENGTH, 0) + COALESCE(INDEX_LENGTH, 0)) AS total_bytes
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
            ORDER BY total_bytes DESC
        ", [$dbName]);

        $totalTables = count($tables);
        $totalRows = 0;
        $totalDataBytes = 0;
        $totalIndexBytes = 0;

        $tableDetails = [];
        foreach ($tables as $t) {
            $tArr = array_change_key_case((array) $t, CASE_LOWER);
            $rows = (int) ($tArr['row_count'] ?? $tArr['table_rows'] ?? 0);
            $dataBytes = (int) ($tArr['data_bytes'] ?? $tArr['data_length'] ?? 0);
            $indexBytes = (int) ($tArr['index_bytes'] ?? $tArr['index_length'] ?? 0);
            $totalBytes = (int) ($tArr['total_bytes'] ?? ($dataBytes + $indexBytes));
            $name = (string) ($tArr['table_name'] ?? '');
            $engine = (string) ($tArr['engine'] ?? 'InnoDB');

            $totalRows += $rows;
            $totalDataBytes += $dataBytes;
            $totalIndexBytes += $indexBytes;

            $tableDetails[] = [
                'name' => $name,
                'engine' => $engine,
                'rows' => $rows,
                'size_formatted' => $this->formatBytes($totalBytes),
                'data_formatted' => $this->formatBytes($dataBytes),
                'index_formatted' => $this->formatBytes($indexBytes),
            ];
        }

        $totalDbBytes = $totalDataBytes + $totalIndexBytes;

        // MySQL version
        $version = 'Unknown';
        try {
            $versionRow = DB::select('SELECT VERSION() as v');
            $version = $versionRow[0]->v ?? 'Unknown';
        } catch (\Throwable $e) {}

        // Backups overview
        $backups = $this->getBackupsList();
        $totalBackupsSize = array_sum(array_column($backups, 'size'));
        $lastBackup = !empty($backups) ? $backups[0] : null;

        // Check canonical files
        $canonicalDocExists = file_exists($this->canonicalDocDump);
        $canonicalDocMtime = $canonicalDocExists ? Carbon::createFromTimestamp(filemtime($this->canonicalDocDump)) : null;
        $canonicalDocSize = $canonicalDocExists ? $this->formatBytes(filesize($this->canonicalDocDump)) : 'N/A';

        return [
            'database_name' => $dbName,
            'mysql_version' => $version,
            'tables_count' => $totalTables,
            'total_rows' => $totalRows,
            'data_size_formatted' => $this->formatBytes($totalDataBytes),
            'index_size_formatted' => $this->formatBytes($totalIndexBytes),
            'total_size_formatted' => $this->formatBytes($totalDbBytes),
            'total_size_bytes' => $totalDbBytes,
            'tables' => $tableDetails,
            'backups_count' => count($backups),
            'backups_total_size' => $this->formatBytes($totalBackupsSize),
            'last_backup' => $lastBackup,
            'canonical_doc_exists' => $canonicalDocExists,
            'canonical_doc_path' => $this->canonicalDocDump,
            'canonical_doc_size' => $canonicalDocSize,
            'canonical_doc_mtime' => $canonicalDocMtime,
            'canonical_repo_path' => $this->canonicalRepoDump,
        ];
    }

    /**
     * Get list of all available backup files.
     */
    public function getBackupsList(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['sql', 'gz', 'zip'])) {
                continue;
            }

            $type = 'manual';
            if (str_contains($filename, '-daily-')) {
                $type = 'daily';
            } elseif (str_contains($filename, '-auto-')) {
                $type = 'auto';
            } elseif ($filename === 'latest_backup.sql') {
                $type = 'canonical';
            }

            $backups[] = [
                'filename' => $filename,
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'size_formatted' => $this->formatBytes($file->getSize()),
                'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                'age' => Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
                'type' => $type,
                'is_compressed' => str_ends_with($filename, '.gz') || str_ends_with($filename, '.zip'),
                'is_canonical' => ($filename === 'latest_backup.sql'),
            ];
        }

        // Sort latest first
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Download a specific backup file.
     */
    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $safeName = basename($filename);
        $path = "{$this->backupDir}/{$safeName}";

        // If requesting canonical file
        if ($safeName === 'construction_management.sql' && file_exists($this->canonicalDocDump)) {
            $path = $this->canonicalDocDump;
        }

        if (!file_exists($path)) {
            throw new \Exception("Backup file '{$safeName}' not found.");
        }

        return response()->download($path, $safeName, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $safeName . '"'
        ]);
    }

    /**
     * Delete a specific backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $safeName = basename($filename);
        if ($safeName === 'latest_backup.sql') {
            throw new \Exception("Cannot delete the active canonical backup file.");
        }

        $path = "{$this->backupDir}/{$safeName}";
        if (file_exists($path)) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * Delete backups older than specified days, keeping latest.
     */
    public function cleanOldBackups(int $keepDays = 30): int
    {
        $backups = $this->getBackupsList();
        $cutoff = Carbon::now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach ($backups as $b) {
            if ($b['is_canonical']) continue;

            if ($b['created_at']->timestamp < $cutoff) {
                if (@unlink($b['path'])) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Helper to format bytes into readable units.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
