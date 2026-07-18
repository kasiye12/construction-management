<?php
namespace App\Http\Controllers;

use App\Models\MaterialDelivery;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialDeliveryController extends Controller
{
    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (!$user) return [];
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        return $user->isAdmin() ? Project::all() : Project::whereIn('id', $this->getUserProjectIds())->get();
    }

    // ------------------------------------------------------------------
    // INDEX
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();
        $projectId = $request->get('project_id');
        
        $query = MaterialDelivery::with(['project', 'subcontractor', 'boqItem', 'creator'])
            ->whereIn('project_id', $userProjectIds);
        
        if ($projectId) $query->where('project_id', $projectId);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->where('delivery_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('delivery_date', '<=', $request->date_to);
        if ($request->filled('search')) $query->where('item_description', 'LIKE', "%{$request->search}%");
        
        $deliveries = $query->orderBy('delivery_date', 'desc')->paginate(20)->appends($request->query());
        $totalDelivered = (clone $query)->sum('converted_quantity');
        
        return view('material-deliveries.index', compact('deliveries', 'projects', 'projectId', 'totalDelivered'));
    }

    // ------------------------------------------------------------------
    // CREATE / STORE
    // ------------------------------------------------------------------
    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = $this->getUserProjects();
        $subcontractors = $projectId ? Project::find($projectId)?->subcontractors : collect();
        $boqItems = $projectId ? BoqItem::where('project_id', $projectId)->where('is_parent', false)->get() : collect();
        return view('material-deliveries.create', compact('projects', 'subcontractors', 'boqItems', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'item_description' => 'required|string|max:500',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_multiplier' => 'nullable|numeric|min:0',
            'gate_pass_number' => 'nullable|string|max:100',
            'delivery_date' => 'required|date',
            'source_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'recorded';
        $delivery = MaterialDelivery::create($validated);

        return redirect()->route('material-deliveries.index', ['project_id' => $validated['project_id']])
            ->with('success', 'Material delivery recorded successfully.');
    }

    // ------------------------------------------------------------------
    // SHOW
    // ------------------------------------------------------------------
    public function show(MaterialDelivery $materialDelivery)
    {
        $materialDelivery->load(['project', 'subcontractor', 'boqItem', 'creator', 'confirmedBy']);
        $canConfirm = $this->canConfirm();
        return view('material-deliveries.show', compact('materialDelivery', 'canConfirm'));
    }

    // ------------------------------------------------------------------
    // EDIT / UPDATE (locked after confirmation)
    // ------------------------------------------------------------------
    public function edit(MaterialDelivery $materialDelivery)
    {
        if ($materialDelivery->status === 'confirmed' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot edit confirmed delivery. Only admin can edit.');
        }
        $projects = Project::all();
        $subcontractors = Subcontractor::all();
        $boqItems = BoqItem::where('project_id', $materialDelivery->project_id)->where('is_parent', false)->get();
        return view('material-deliveries.edit', compact('materialDelivery', 'projects', 'subcontractors', 'boqItems'));
    }

    public function update(Request $request, MaterialDelivery $materialDelivery)
    {
        if ($materialDelivery->status === 'confirmed' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot update confirmed delivery.');
        }
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'item_description' => 'required|string|max:500',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_multiplier' => 'nullable|numeric|min:0',
            'delivery_date' => 'required|date',
            'gate_pass_number' => 'nullable|string',
            'source_location' => 'nullable|string',
            'status' => 'nullable|in:recorded,confirmed',
        ]);
        
        $materialDelivery->update($validated);
        return redirect()->route('material-deliveries.index')->with('success', 'Delivery updated.');
    }

    // ------------------------------------------------------------------
    // DELETE (locked after confirmation)
    // ------------------------------------------------------------------
    public function destroy(MaterialDelivery $materialDelivery)
    {
        if ($materialDelivery->status === 'confirmed' && !Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Cannot delete confirmed delivery. Only admin can delete confirmed records.');
        }
        
        $materialDelivery->delete();
        return redirect()->route('material-deliveries.index')->with('success', 'Delivery deleted.');
    }

    // ==================================================================
    // WORKFLOW ACTIONS
    // ==================================================================
    
    private function canConfirm(): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;
        return \App\Models\WorkflowPermission::canUserAct($user->id, 'confirm_delivery');
    }

    /**
     * Confirm material delivery
     */
    public function confirm(MaterialDelivery $materialDelivery)
    {
        if (!$this->canConfirm()) {
            return back()->with('error', '❌ You do not have permission to confirm deliveries.');
        }
        
        if ($materialDelivery->status === 'confirmed') {
            return back()->with('error', '❌ Delivery is already confirmed.');
        }
        
        $materialDelivery->update([
            'status' => 'confirmed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);
        
        return back()->with('success', '✅ Material delivery confirmed by ' . Auth::user()->name);
    }

    /**
     * Revert to recorded
     */
    public function revertToRecorded(MaterialDelivery $materialDelivery)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', '❌ Only admin can revert deliveries.');
        }
        
        $materialDelivery->update([
            'status' => 'recorded',
            'confirmed_by' => null,
            'confirmed_at' => null,
        ]);
        
        return back()->with('success', 'Delivery reverted to recorded.');
    }
}
