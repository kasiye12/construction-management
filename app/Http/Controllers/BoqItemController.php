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
        
        $query = BoqItem::with(['project', 'costCategory', 'laborResources', 'materialResources', 'equipmentResources']);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $boqItems = $query->orderBy('item_number')->paginate(20);
        
        return view('boq-items.index', compact('boqItems', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $costCategories = [];
        $parentItems = [];
        
        if ($projectId) {
            $costCategories = CostCategory::where('project_id', $projectId)->get();
            $parentItems = BoqItem::where('project_id', $projectId)
                                 ->where('is_parent', true)
                                 ->get();
        }
        
        return view('boq-items.create', compact('projects', 'costCategories', 'parentItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'parent_id' => 'nullable|exists:boq_items,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'is_parent' => 'boolean'
        ]);

        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];

        $boqItem = BoqItem::create($validated);

        return redirect()->route('boq-items.show', $boqItem)
                        ->with('success', 'BOQ Item created successfully.');
    }

    public function show(BoqItem $boqItem)
    {
        $boqItem->load([
            'project', 
            'costCategory', 
            'parent', 
            'children',
            'laborResources', 
            'materialResources', 
            'equipmentResources',
            'ipcItems.ipc'
        ]);
        
        $totalBudgetCost = $boqItem->total_budget_cost;
        $profitLoss = $boqItem->profit_loss;
        $profitMargin = $boqItem->profit_margin_percentage;
        $profitLossStatus = $boqItem->profit_loss_status;

        return view('boq-items.show', compact('boqItem', 'totalBudgetCost', 'profitLoss', 'profitMargin', 'profitLossStatus'));
    }

    public function edit(BoqItem $boqItem)
    {
        $projects = Project::all();
        $costCategories = CostCategory::where('project_id', $boqItem->project_id)->get();
        $parentItems = BoqItem::where('project_id', $boqItem->project_id)
                             ->where('id', '!=', $boqItem->id)
                             ->where('is_parent', true)
                             ->get();
        
        $boqItem->load(['laborResources', 'materialResources', 'equipmentResources']);
        
        return view('boq-items.edit', compact('boqItem', 'projects', 'costCategories', 'parentItems'));
    }

    public function update(Request $request, BoqItem $boqItem)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'parent_id' => 'nullable|exists:boq_items,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_rate' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:0',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'is_parent' => 'boolean',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $validated['revenue_amount'] = $validated['quantity'] * $validated['unit_rate'];
        $boqItem->update($validated);

        return redirect()->route('boq-items.show', $boqItem)
                        ->with('success', 'BOQ Item updated successfully.');
    }

    public function destroy(BoqItem $boqItem)
    {
        $boqItem->delete();

        return redirect()->route('boq-items.index')
                        ->with('success', 'BOQ Item deleted successfully.');
    }
}
