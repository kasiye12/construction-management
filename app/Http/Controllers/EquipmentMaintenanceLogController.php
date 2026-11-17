<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentMaintenanceLog;
use App\Models\Project;
use App\Services\EquipmentMaintenanceExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentMaintenanceLogController extends Controller
{
    protected EquipmentMaintenanceExcelService $excelService;

    public function __construct(EquipmentMaintenanceExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function index(Request $request)
    {
        $query = EquipmentMaintenanceLog::with(['equipment', 'project', 'loggedByUser']);

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('spare_part_name', 'like', "%{$s}%")
                  ->orWhere('po_no', 'like', "%{$s}%")
                  ->orWhere('equipment_plate', 'like', "%{$s}%")
                  ->orWhere('equipment_type', 'like', "%{$s}%")
                  ->orWhere('supplier', 'like', "%{$s}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->where('log_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('log_date', '<=', $request->date_to);
        }

        $totalCost = (clone $query)->sum('total_cost_with_vat');
        $totalLogsCount = (clone $query)->count();

        $logs = $query->latest('log_date')->paginate(25)->withQueryString();
        $equipments = Equipment::orderBy('equipment_type')->get();
        $projects = Project::orderBy('name')->get();

        return view('equipment-logs.index', compact(
            'logs',
            'totalCost',
            'totalLogsCount',
            'equipments',
            'projects'
        ));
    }

    public function create()
    {
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $projects = Project::orderBy('name')->get();

        return view('equipment-logs.create', compact('equipments', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'equipment_type' => 'required|string|max:100',
            'equipment_plate' => 'nullable|string|max:100',
            'spare_part_name' => 'required|string|max:255',
            'po_no' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'total_cost_with_vat' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'log_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        $validated['logged_by'] = Auth::id();

        if (empty($validated['total_cost_with_vat'])) {
            $validated['total_cost_with_vat'] = round($validated['quantity'] * $validated['unit_cost'] * 1.15, 2);
        }

        if (!empty($validated['equipment_id']) && empty($validated['equipment_plate'])) {
            $eq = Equipment::find($validated['equipment_id']);
            if ($eq) {
                $validated['equipment_plate'] = $eq->plate_serial_no;
                if (empty($validated['equipment_type'])) {
                    $validated['equipment_type'] = $eq->equipment_type;
                }
            }
        }

        $log = EquipmentMaintenanceLog::create($validated);

        return redirect()->route('equipment-logs.index')
            ->with('success', "Spare part / maintenance transaction recorded for {$log->equipment_plate}.");
    }

    public function edit(EquipmentMaintenanceLog $equipmentLog)
    {
        $equipments = Equipment::orderBy('category')->orderBy('equipment_type')->get();
        $projects = Project::orderBy('name')->get();

        return view('equipment-logs.edit', compact('equipmentLog', 'equipments', 'projects'));
    }

    public function update(Request $request, EquipmentMaintenanceLog $equipmentLog)
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'equipment_type' => 'required|string|max:100',
            'equipment_plate' => 'nullable|string|max:100',
            'spare_part_name' => 'required|string|max:255',
            'po_no' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'total_cost_with_vat' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'log_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        if (empty($validated['total_cost_with_vat'])) {
            $validated['total_cost_with_vat'] = round($validated['quantity'] * $validated['unit_cost'] * 1.15, 2);
        }

        $equipmentLog->update($validated);

        return redirect()->route('equipment-logs.index')
            ->with('success', 'Maintenance log record updated successfully.');
    }

    public function export(Request $request)
    {
        $query = EquipmentMaintenanceLog::with(['equipment', 'project']);

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->where('log_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('log_date', '<=', $request->date_to);
        }

        $logs = $query->latest('log_date')->get();
        $filePath = $this->excelService->exportDailyReport($logs);
        $fileName = 'TNT-EAMD-Daily-Maintenance-Logs-' . date('Y-m-d') . '.xlsx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        try {
            $result = $this->excelService->importDailyLogs(
                $request->file('excel_file'),
                $request->project_id ? (int)$request->project_id : null,
                Auth::id()
            );

            return redirect()->route('equipment-logs.index')
                ->with('success', "Import successful: {$result['imported_count']} log entries recorded with total " . number_format($result['total_cost'], 2) . " ETB.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function destroy(EquipmentMaintenanceLog $equipmentLog)
    {
        $equipmentLog->delete();
        return redirect()->route('equipment-logs.index')
            ->with('success', 'Maintenance log entry deleted.');
    }
}
