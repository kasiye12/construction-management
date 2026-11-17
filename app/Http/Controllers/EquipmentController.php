<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Project;
use App\Models\PmScheduleItem;
use App\Models\EquipmentBreakdownItem;
use App\Models\EquipmentMaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with('project');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('plate_serial_no', 'like', "%{$s}%")
                  ->orWhere('equipment_type', 'like', "%{$s}%")
                  ->orWhere('make', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%");
            });
        }

        $equipments = $query->orderBy('category')->orderBy('equipment_type')->paginate(20)->withQueryString();

        // Calculate KPI Stats
        $allEquipments = Equipment::all();
        $totalCount = $allEquipments->count();
        $activeCount = $allEquipments->where('status', 'active')->count();
        $downCount = $allEquipments->whereIn('status', ['breakdown', 'under_maintenance'])->count();
        
        $dueCount = $allEquipments->filter(function($e) {
            $rem = $e->remain_reading;
            return $rem !== null && $rem >= 0 && $rem <= 50;
        })->count();

        $overdueCount = $allEquipments->filter(function($e) {
            $rem = $e->remain_reading;
            return $rem !== null && $rem < 0;
        })->count();

        $projects = Project::orderBy('name')->get();
        $categories = Equipment::CATEGORIES;

        return view('equipment.index', compact(
            'equipments',
            'totalCount',
            'activeCount',
            'downCount',
            'dueCount',
            'overdueCount',
            'projects',
            'categories'
        ));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $categories = Equipment::CATEGORIES;
        return view('equipment.create', compact('projects', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'plate_serial_no' => 'required|string|max:100|unique:equipment,plate_serial_no',
            'serial_no' => 'nullable|string|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'reading_unit' => 'required|in:hr,km',
            'current_reading' => 'required|numeric|min:0',
            'current_reading_date' => 'nullable|date',
            'last_service_reading' => 'nullable|numeric|min:0',
            'last_service_date' => 'nullable|date',
            'last_service_type' => 'nullable|string|max:50',
            'next_service_reading' => 'nullable|numeric|min:0',
            'next_service_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,due_service,under_maintenance,breakdown,idle',
            'remarks' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['code'] = $validated['plate_serial_no'] ?: ('EQ-' . rand(1000, 9999));
        $validated['current_reading_date'] = $validated['current_reading_date'] ?? now()->toDateString();

        $equipment = Equipment::create($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', "Equipment '{$equipment->full_display_name}' created successfully.");
    }

    public function show(Equipment $equipment)
    {
        $equipment->load('project', 'creator');
        
        $pmItems = PmScheduleItem::with('schedule')
            ->where('equipment_id', $equipment->id)
            ->latest()
            ->take(10)
            ->get();

        $breakdowns = EquipmentBreakdownItem::with('report')
            ->where('equipment_id', $equipment->id)
            ->latest()
            ->take(10)
            ->get();

        $maintenanceLogs = EquipmentMaintenanceLog::where('equipment_id', $equipment->id)
            ->orWhere('equipment_plate', $equipment->plate_serial_no)
            ->latest('log_date')
            ->take(15)
            ->get();

        $totalSpent = EquipmentMaintenanceLog::where('equipment_id', $equipment->id)
            ->orWhere('equipment_plate', $equipment->plate_serial_no)
            ->sum('total_cost_with_vat');

        return view('equipment.show', compact('equipment', 'pmItems', 'breakdowns', 'maintenanceLogs', 'totalSpent'));
    }

    public function edit(Equipment $equipment)
    {
        $projects = Project::orderBy('name')->get();
        $categories = Equipment::CATEGORIES;
        return view('equipment.edit', compact('equipment', 'projects', 'categories'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'equipment_type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'plate_serial_no' => 'required|string|max:100|unique:equipment,plate_serial_no,' . $equipment->id,
            'serial_no' => 'nullable|string|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'location_name' => 'nullable|string|max:150',
            'reading_unit' => 'required|in:hr,km',
            'current_reading' => 'required|numeric|min:0',
            'current_reading_date' => 'nullable|date',
            'last_service_reading' => 'nullable|numeric|min:0',
            'last_service_date' => 'nullable|date',
            'last_service_type' => 'nullable|string|max:50',
            'next_service_reading' => 'nullable|numeric|min:0',
            'next_service_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,due_service,under_maintenance,breakdown,idle',
            'remarks' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return redirect()->route('equipment.show', $equipment)
            ->with('success', "Equipment '{$equipment->full_display_name}' updated successfully.");
    }

    public function updateReading(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'current_reading' => 'required|numeric|min:' . (float)$equipment->current_reading,
            'current_reading_date' => 'required|date',
            'location_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return back()->with('success', "Current meter reading updated to {$equipment->current_reading} {$equipment->reading_unit}.");
    }

    public function destroy(Equipment $equipment)
    {
        $name = $equipment->full_display_name;
        $equipment->delete();
        return redirect()->route('equipment.index')->with('success', "Equipment '{$name}' removed from active fleet.");
    }
}
