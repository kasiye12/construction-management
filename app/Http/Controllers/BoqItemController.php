<?php
namespace App\Http\Controllers;

use App\Models\BoqItem;
use App\Models\Project;
use App\Models\CostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoqItemController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $query = BoqItem::with(['project','costCategory','laborResources','materialResources','equipmentResources']);
        if ($projectId) $query->where('project_id', $projectId);
        $boqItems = $query->orderBy('item_number')->paginate(20);
        return view('boq-items.index', compact('boqItems','projects','projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $costCategories = $projectId ? CostCategory::where('project_id', $projectId)->get() : CostCategory::all();
        return view('boq-items.create', compact('projects', 'costCategories', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'is_parent' => 'boolean',
            // Labor resources
            'labor' => 'nullable|array',
            'labor.*.trade_name' => 'nullable|string|max:255',
            'labor.*.number_of_workers' => 'nullable|numeric|min:0',
            'labor.*.total_hours' => 'nullable|numeric|min:0',
            'labor.*.wage_per_day' => 'nullable|numeric|min:0',
            // Material resources
            'materials' => 'nullable|array',
            'materials.*.description' => 'nullable|string|max:500',
            'materials.*.unit' => 'nullable|string|max:50',
            'materials.*.quantity' => 'nullable|numeric|min:0',
            'materials.*.unit_rate' => 'nullable|numeric|min:0',
            // Equipment resources
            'equipment' => 'nullable|array',
            'equipment.*.description' => 'nullable|string|max:500',
            'equipment.*.duration_days' => 'nullable|numeric|min:0',
            'equipment.*.number_of_units' => 'nullable|integer|min:1',
            'equipment.*.total_hours' => 'nullable|numeric|min:0',
            'equipment.*.rate_per_hour' => 'nullable|numeric|min:0',
        ]);

        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];

        DB::beginTransaction();
        try {
            $boqItem = BoqItem::create($validated);

            // Save Labor Resources
            if ($request->has('labor')) {
                foreach ($request->labor as $labor) {
                    if (!empty($labor['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name' => $labor['trade_name'],
                            'number_of_workers' => $labor['number_of_workers'] ?? 0,
                            'total_hours' => $labor['total_hours'] ?? 0,
                            'wage_per_day' => $labor['wage_per_day'] ?? 0,
                            'amount' => ($labor['number_of_workers'] ?? 0) * ($labor['total_hours'] ?? 0) * ($labor['wage_per_day'] ?? 0) / 8,
                        ]);
                    }
                }
            }

            // Save Material Resources
            if ($request->has('materials')) {
                foreach ($request->materials as $material) {
                    if (!empty($material['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $material['description'],
                            'unit' => $material['unit'] ?? '',
                            'quantity' => $material['quantity'] ?? 0,
                            'unit_rate' => $material['unit_rate'] ?? 0,
                            'amount' => ($material['quantity'] ?? 0) * ($material['unit_rate'] ?? 0),
                        ]);
                    }
                }
            }

            // Save Equipment Resources
            if ($request->has('equipment')) {
                foreach ($request->equipment as $equipment) {
                    if (!empty($equipment['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description' => $equipment['description'],
                            'duration_days' => $equipment['duration_days'] ?? 0,
                            'number_of_units' => $equipment['number_of_units'] ?? 1,
                            'total_hours' => $equipment['total_hours'] ?? 0,
                            'rate_per_hour' => $equipment['rate_per_hour'] ?? 0,
                            'amount' => ($equipment['total_hours'] ?? 0) * ($equipment['rate_per_hour'] ?? 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item created with resources.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(BoqItem $boqItem)
    {
        $boqItem->load(['project','costCategory','parent','children','laborResources','materialResources','equipmentResources','ipcItems.ipc']);
        return view('boq-items.show', compact('boqItem'));
    }

    public function edit(BoqItem $boqItem)
    {
        $projects = Project::all();
        $costCategories = CostCategory::where('project_id', $boqItem->project_id)->get();
        $boqItem->load(['laborResources','materialResources','equipmentResources']);
        return view('boq-items.edit', compact('boqItem','projects','costCategories'));
    }

    public function update(Request $request, BoqItem $boqItem)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'is_parent' => 'boolean',
            'status' => 'required|in:pending,in_progress,completed',
        ]);
        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];
        
        DB::beginTransaction();
        try {
            $boqItem->update($validated);

            // Update resources if provided
            if ($request->has('labor')) {
                $boqItem->laborResources()->delete();
                foreach ($request->labor as $labor) {
                    if (!empty($labor['trade_name'])) {
                        $boqItem->laborResources()->create([
                            'trade_name' => $labor['trade_name'],
                            'number_of_workers' => $labor['number_of_workers'] ?? 0,
                            'total_hours' => $labor['total_hours'] ?? 0,
                            'wage_per_day' => $labor['wage_per_day'] ?? 0,
                            'amount' => ($labor['number_of_workers'] ?? 0) * ($labor['total_hours'] ?? 0) * ($labor['wage_per_day'] ?? 0) / 8,
                        ]);
                    }
                }
            }

            if ($request->has('materials')) {
                $boqItem->materialResources()->delete();
                foreach ($request->materials as $material) {
                    if (!empty($material['description'])) {
                        $boqItem->materialResources()->create([
                            'description' => $material['description'],
                            'unit' => $material['unit'] ?? '',
                            'quantity' => $material['quantity'] ?? 0,
                            'unit_rate' => $material['unit_rate'] ?? 0,
                            'amount' => ($material['quantity'] ?? 0) * ($material['unit_rate'] ?? 0),
                        ]);
                    }
                }
            }

            if ($request->has('equipment')) {
                $boqItem->equipmentResources()->delete();
                foreach ($request->equipment as $equipment) {
                    if (!empty($equipment['description'])) {
                        $boqItem->equipmentResources()->create([
                            'description' => $equipment['description'],
                            'duration_days' => $equipment['duration_days'] ?? 0,
                            'number_of_units' => $equipment['number_of_units'] ?? 1,
                            'total_hours' => $equipment['total_hours'] ?? 0,
                            'rate_per_hour' => $equipment['rate_per_hour'] ?? 0,
                            'amount' => ($equipment['total_hours'] ?? 0) * ($equipment['rate_per_hour'] ?? 0),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('boq-items.show', $boqItem)->with('success', 'BOQ Item updated.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(BoqItem $boqItem) { $boqItem->delete(); return redirect()->route('boq-items.index')->with('success', 'BOQ Item deleted.'); }
}
