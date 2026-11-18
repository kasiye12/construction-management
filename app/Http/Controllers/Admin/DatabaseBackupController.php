<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display database statistics and list of backups.
     */
    public function index()
    {
        $stats = $this->backupService->getDatabaseStats();
        $backups = $this->backupService->getBackupsList();

        return view('admin.database-backups.index', compact('stats', 'backups'));
    }

    /**
     * Create an on-demand manual database backup.
     */
    public function createBackup(Request $request)
    {
        $compress = $request->boolean('compress', false);

        try {
            $result = $this->backupService->createBackup('manual', $compress, Auth::id());

            return redirect()->route('admin.database-backups.index')
                ->with('success', "Database backup created successfully: {$result['filename']} ({$result['filesize_formatted']}). Master canonical files updated.");
        } catch (\Throwable $e) {
            return back()->with('error', "Backup failed: " . $e->getMessage());
        }
    }

    /**
     * Trigger the daily automated backup and update canonical master files now.
     */
    public function runDailySync(Request $request)
    {
        try {
            $result = $this->backupService->createBackup('daily', false, Auth::id());

            return redirect()->route('admin.database-backups.index')
                ->with('success', "Daily auto-backup executed successfully! Canonical backup synchronized at {$result['canonical_doc_dump']}.");
        } catch (\Throwable $e) {
            return back()->with('error', "Daily sync failed: " . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $filename)
    {
        try {
            return $this->backupService->downloadBackup($filename);
        } catch (\Throwable $e) {
            return back()->with('error', "Download failed: " . $e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(string $filename)
    {
        try {
            $this->backupService->deleteBackup($filename);
            return redirect()->route('admin.database-backups.index')
                ->with('success', "Backup file '{$filename}' deleted successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', "Delete failed: " . $e->getMessage());
        }
    }
}
