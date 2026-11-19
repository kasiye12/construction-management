<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditTrailController extends Controller
{
    public const MODULE_NAMES = [
        'App\Models\ContractualReport' => 'Weekly Contractual Report',
        'App\Models\Equipment' => 'Equipment Inventory',
        'App\Models\EquipmentBreakdownReport' => 'Equipment Breakdown Report',
        'App\Models\EquipmentMaintenanceLog' => 'Maintenance Log',
        'App\Models\PmSchedule' => 'Preventive Maintenance Schedule',
        'App\Models\MaterialDelivery' => 'Material Delivery',
        'App\Models\QuantityTakeoff' => 'Quantity Takeoff',
        'App\Models\Project' => 'Project',
        'App\Models\User' => 'User Account',
        'App\Models\Ipc' => 'Payment Certificate (IPC)',
        'App\Models\BoqItem' => 'BOQ Item',
        'App\Models\ActualCost' => 'Actual Cost',
        'App\Models\Subcontractor' => 'Subcontractor',
    ];

    public function index(Request $request)
    {
        $query = AuditTrail::with('user');

        // 1. Search Query
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('field_name', 'LIKE', "%{$s}%")
                  ->orWhere('old_value', 'LIKE', "%{$s}%")
                  ->orWhere('new_value', 'LIKE', "%{$s}%")
                  ->orWhere('ip_address', 'LIKE', "%{$s}%")
                  ->orWhere('auditable_id', 'LIKE', "%{$s}%")
                  ->orWhere('auditable_type', 'LIKE', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('email', 'LIKE', "%{$s}%");
                  });
            });
        }

        // 2. User Filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 3. Module / Entity Filter
        if ($request->filled('module')) {
            $module = $request->module;
            $query->where(function ($q) use ($module) {
                $q->where('auditable_type', $module)
                  ->orWhere('auditable_type', 'LIKE', "%\\{$module}");
            });
        }

        // 4. Action Filter (created, updated, deleted)
        if ($request->filled('action')) {
            $query->where('action', strtolower($request->action));
        }

        // 5. Date From Filter
        if ($request->filled('date_from')) {
            try {
                $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            } catch (\Exception $e) {}
        }

        // 6. Date To Filter
        if ($request->filled('date_to')) {
            try {
                $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            } catch (\Exception $e) {}
        }

        // Stats summary calculation
        $totalLogs = AuditTrail::count();
        $filteredLogs = (clone $query)->count();
        $createdCount = (clone $query)->where('action', 'created')->count();
        $updatedCount = (clone $query)->where('action', 'updated')->count();
        $deletedCount = (clone $query)->where('action', 'deleted')->count();

        // Paginated records
        $logs = $query->latest('id')->paginate(30)->appends($request->query());

        // Available Filter Dropdown Options
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        $distinctModules = AuditTrail::distinct()
            ->pluck('auditable_type')
            ->filter()
            ->values()
            ->map(function ($class) {
                return [
                    'class' => $class,
                    'short' => class_basename($class),
                    'label' => self::MODULE_NAMES[$class] ?? class_basename($class),
                ];
            })
            ->sortBy('label')
            ->values();

        $actions = AuditTrail::distinct()->pluck('action')->filter()->values();

        return view('admin.audit.index', compact(
            'logs', 'users', 'distinctModules', 'actions',
            'totalLogs', 'filteredLogs', 'createdCount', 'updatedCount', 'deletedCount'
        ));
    }

    public function export(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('field_name', 'LIKE', "%{$s}%")
                  ->orWhere('old_value', 'LIKE', "%{$s}%")
                  ->orWhere('new_value', 'LIKE', "%{$s}%")
                  ->orWhere('ip_address', 'LIKE', "%{$s}%")
                  ->orWhere('auditable_id', 'LIKE', "%{$s}%")
                  ->orWhere('auditable_type', 'LIKE', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('email', 'LIKE', "%{$s}%");
                  });
            });
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('module')) {
            $module = $request->module;
            $query->where(function ($q) use ($module) {
                $q->where('auditable_type', $module)
                  ->orWhere('auditable_type', 'LIKE', "%\\{$module}");
            });
        }
        if ($request->filled('action')) {
            $query->where('action', strtolower($request->action));
        }
        if ($request->filled('date_from')) {
            try {
                $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            } catch (\Exception $e) {}
        }
        if ($request->filled('date_to')) {
            try {
                $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            } catch (\Exception $e) {}
        }

        $filename = 'Audit_Trail_' . date('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Log ID',
                'Date & Time',
                'User Name',
                'User Email',
                'Action',
                'Module Name',
                'Target Record ID',
                'Field Modified',
                'Old Value',
                'New Value',
                'IP Address',
                'User Agent'
            ], ',', '"', "\\");

            $query->latest('id')->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $log) {
                    $moduleName = self::MODULE_NAMES[$log->auditable_type] ?? class_basename($log->auditable_type);
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
                        $log->user?->name ?? 'System',
                        $log->user?->email ?? '',
                        strtoupper($log->action),
                        $moduleName,
                        $log->auditable_id,
                        $log->field_name ?? '-',
                        $log->old_value,
                        $log->new_value,
                        $log->ip_address,
                        $log->user_agent,
                    ], ',', '"', "\\");
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
