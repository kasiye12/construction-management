<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseSyncLog;
use App\Services\SmartDatabaseSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartDatabaseSyncController extends Controller
{
    protected SmartDatabaseSyncService $syncService;

    public function __construct(SmartDatabaseSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Show the upload & sync interface along with history.
     */
    public function index()
    {
        $history = DatabaseSyncLog::latest()->paginate(15);
        return view('admin.database-sync.index', compact('history'));
    }

    /**
     * Handle SQL file upload and generate the comparison preview report.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400', // up to 100MB
        ]);

        try {
            $syncLog = $this->syncService->analyzeSqlFile($request->file('sql_file'), Auth::id());

            return redirect()->route('admin.database-sync.preview', $syncLog->batch_id)
                ->with('success', "Database comparison completed! Please review the preview report below before applying changes.");
        } catch (\Throwable $e) {
            return back()->with('error', "Database analysis failed: " . $e->getMessage());
        }
    }

    /**
     * Show detailed preview report with differences and confirmation form.
     */
    public function preview(string $batchId)
    {
        $syncLog = DatabaseSyncLog::where('batch_id', $batchId)->firstOrFail();

        return view('admin.database-sync.preview', compact('syncLog'));
    }

    /**
     * Apply approved changes incrementally to production.
     */
    public function apply(Request $request, string $batchId)
    {
        $syncLog = DatabaseSyncLog::where('batch_id', $batchId)->firstOrFail();

        $options = [
            'create_tables' => $request->boolean('create_tables', true),
            'add_columns' => $request->boolean('add_columns', true),
            'insert_records' => $request->boolean('insert_records', true),
            'update_records' => $request->boolean('update_records', true),
        ];

        try {
            $result = $this->syncService->applySync($syncLog, $options, Auth::id());

            $msg = "Database synchronization completed successfully! " .
                   "Created {$result['tables_created']} table(s), " .
                   "added {$result['columns_added']} column(s), " .
                   "inserted {$result['records_inserted']} record(s), and " .
                   "updated {$result['records_updated']} record(s). " .
                   "A safety pre-sync backup was created.";

            return redirect()->route('admin.database-sync.preview', $syncLog->batch_id)->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', "Database synchronization failed: " . $e->getMessage());
        }
    }

    /**
     * Rollback a synchronization to its pre-sync backup state.
     */
    public function rollback(Request $request, string $batchId)
    {
        $syncLog = DatabaseSyncLog::where('batch_id', $batchId)->firstOrFail();

        try {
            $this->syncService->rollbackSync($syncLog, Auth::id());

            return redirect()->route('admin.database-sync.preview', $syncLog->batch_id)
                ->with('success', "Database successfully rolled back to its pre-sync snapshot! Existing production state has been restored.");
        } catch (\Throwable $e) {
            return back()->with('error', "Rollback failed: " . $e->getMessage());
        }
    }
}
