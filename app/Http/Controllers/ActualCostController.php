<?php
namespace App\Http\Controllers;

use App\Models\ActualCost;
use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActualCostController extends Controller
{
    private function getUserProjectIds() {
        $user = Auth::user();
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $user = Auth::user();
        $projects = $user->isAdmin() ? Project::all() : Project::whereIn('id', $userProjectIds)->get();
        
        $query = ActualCost::with(['project', 'boqItem', 'creator'])->whereIn('project_id', $userProjectIds);
        
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('cost_type')) $query->where('cost_type', $request->cost_type);
        if ($request->filled('search')) $query->where('description', 'LIKE', "%{$request->search}%");
        if ($request->filled('date_from')) $query->where('cost_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('cost_date', '<=', $request->date_to);
        
        $costs = $query->orderBy('cost_date', 'desc')->paginate(20)->appends($request->query());
        
        $totalActual = (clone $query)->sum('amount');
        $totalBudget = BoqItem::whereIn('project_id', $userProjectIds)->where('is_parent', false)->get()->sum(fn($i) => $i->total_budget_cost);
        $variance = $totalBudget - $totalActual;
        $percentUsed = $totalBudget > 0 ? round(($totalActual/$totalBudget)*100, 1) : 0;
        
        return view('actual-costs.index', compact('costs','projects','totalActual','totalBudget','variance','percentUsed'));
    }

    public function create(Request $request) {
        $userProjectIds = $this->getUserProjectIds();
        $projects = Auth::user()->isAdmin() ? Project::all() : Project::whereIn('id', $userProjectIds)->get();
        $projectId = $request->get('project_id');
        $boqItems = $projectId ? BoqItem::where('project_id', $projectId)->where('is_parent', false)->get() : collect();
        return view('actual-costs.create', compact('projects', 'boqItems', 'projectId'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id', 'cost_type' => 'required|in:labor,material,equipment,other',
            'description' => 'required|string|max:500', 'amount' => 'required|numeric|min:0',
            'cost_date' => 'required|date', 'vendor' => 'nullable|string', 'invoice_number' => 'nullable|string',
        ]);
        $validated['created_by'] = Auth::id();
        ActualCost::create($validated);
        return redirect()->route('actual-costs.index', ['project_id' => $validated['project_id']])->with('success', 'Cost recorded.');
    }

    public function edit(ActualCost $actualCost) {
        $projects = Project::all();
        $boqItems = BoqItem::where('project_id', $actualCost->project_id)->where('is_parent', false)->get();
        return view('actual-costs.edit', compact('actualCost','projects','boqItems'));
    }

    public function update(Request $request, ActualCost $actualCost) {
        $actualCost->update($request->validate([
            'project_id' => 'required|exists:projects,id', 'cost_type' => 'required|in:labor,material,equipment,other',
            'description' => 'required|string', 'amount' => 'required|numeric|min:0', 'cost_date' => 'required|date',
        ]));
        return redirect()->route('actual-costs.index')->with('success', 'Updated.');
    }

    public function destroy(ActualCost $actualCost) { 
        $actualCost->delete(); 
        return redirect()->route('actual-costs.index')->with('success', 'Deleted.'); 
    }

    public function varianceReport(Request $request)
    {
        $projectId = $request->get('project_id');
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        
        $projects = $user->isAdmin() ? Project::all() : $user->projects()->where('project_user.is_active', true)->get();
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $query = BoqItem::with(['actualCosts', 'costCategory'])
            ->where('is_parent', false)
            ->whereIn('project_id', $userProjectIds);
        
        if ($projectId) $query->where('project_id', $projectId);
        
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        return view('actual-costs.variance', compact('groupedItems', 'projects', 'projectId', 'selectedProject'));
    }
}
