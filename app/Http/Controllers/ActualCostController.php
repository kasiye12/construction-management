<?php
namespace App\Http\Controllers;

use App\Models\ActualCost;
use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActualCostController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        
        $query = ActualCost::with(['project', 'boqItem', 'creator']);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $costs = $query->orderBy('cost_date', 'desc')->paginate(20);
        
        // Summary
        $totalActual = ActualCost::when($projectId, fn($q) => $q->where('project_id', $projectId))->sum('amount');
        $totalBudget = BoqItem::when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->get()->sum(fn($i) => $i->total_budget_cost);
        $variance = $totalBudget - $totalActual;
        
        return view('actual-costs.index', compact('costs', 'projects', 'projectId', 'totalActual', 'totalBudget', 'variance'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $boqItems = $projectId ? BoqItem::where('project_id', $projectId)->where('is_parent', false)->get() : collect();
        
        return view('actual-costs.create', compact('projects', 'boqItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_item_id' => 'nullable|exists:boq_items,id',
            'cost_type' => 'required|in:labor,material,equipment,other',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = Auth::id();
        
        ActualCost::create($validated);

        return redirect()->route('actual-costs.index', ['project_id' => $validated['project_id']])
            ->with('success', 'Actual cost recorded successfully.');
    }

    public function show(ActualCost $actualCost)
    {
        $actualCost->load(['project', 'boqItem', 'creator']);
        return view('actual-costs.show', compact('actualCost'));
    }

    public function edit(ActualCost $actualCost)
    {
        $projects = Project::all();
        $boqItems = BoqItem::where('project_id', $actualCost->project_id)->where('is_parent', false)->get();
        return view('actual-costs.edit', compact('actualCost', 'projects', 'boqItems'));
    }

    public function update(Request $request, ActualCost $actualCost)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'boq_item_id' => 'nullable|exists:boq_items,id',
            'cost_type' => 'required|in:labor,material,equipment,other',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $actualCost->update($validated);

        return redirect()->route('actual-costs.index', ['project_id' => $actualCost->project_id])
            ->with('success', 'Actual cost updated.');
    }

    public function destroy(ActualCost $actualCost)
    {
        $projectId = $actualCost->project_id;
        $actualCost->delete();
        
        return redirect()->route('actual-costs.index', ['project_id' => $projectId])
            ->with('success', 'Cost record deleted.');
    }

    // Variance Report
    public function varianceReport(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $query = BoqItem::with(['actualCosts', 'laborResources', 'materialResources', 'equipmentResources'])
            ->where('is_parent', false);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        return view('actual-costs.variance', compact('groupedItems', 'projects', 'projectId', 'selectedProject'));
    }
}
