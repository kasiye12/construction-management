<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Ipc;
use App\Models\BoqItem;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Get projects based on user role
     */
    private function getUserProjects()
    {
        $user = Auth::user();
        
        // Admin sees all projects
        if ($user->isAdmin()) {
            return Project::query();
        }
        
        // Other users see only assigned projects
        return Project::whereHas('teamMembers', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('project_user.is_active', true);
        });
    }

    public function index()
    {
        $projects = $this->getUserProjects()->orderBy('created_at', 'desc')->paginate(10);
        return view('projects.index', compact('projects'));
    }

    public function create() { return view('projects.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'client_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled'
        ]);
        
        $project = Project::create($validated);
        
        // Auto-assign creator to the project as Project Manager
        $project->teamMembers()->attach(Auth::id(), [
            'role' => 'project_manager',
            'assigned_date' => now(),
            'is_active' => true,
        ]);
        
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        // Check access
        if (!$this->canAccessProject($project)) {
            abort(403, 'You are not assigned to this project.');
        }
        
        $project->load(['costCategories','boqItems' => function($q) { $q->with(['laborResources','materialResources','equipmentResources']); }, 'ipcs','subcontractors','documents']);
        $totalRevenue = $project->boqItems->sum('revenue_amount');
        $totalBudgetCost = $project->boqItems->sum(fn($i) => $i->total_budget_cost);
        $totalProfitLoss = $totalRevenue - $totalBudgetCost;
        return view('projects.show', compact('project','totalRevenue','totalBudgetCost','totalProfitLoss'));
    }

    public function edit(Project $project)
    {
        if (!$this->canAccessProject($project)) abort(403);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        if (!$this->canAccessProject($project)) abort(403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'client_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled'
        ]);
        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        if (!$this->canAccessProject($project)) abort(403);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        $role = $user->getRoleName();
        $projectQuery = $this->getUserProjects();
        
        $totalProjects = $projectQuery->count();
        $activeProjects = (clone $projectQuery)->where('status', 'active')->count();
        $totalContractValue = (clone $projectQuery)->sum('contract_amount');
        $recentProjects = (clone $projectQuery)->latest()->take(5)->get();
        
        // Get project IDs for filtering
        $projectIds = $user->isAdmin() ? Project::pluck('id')->toArray() : $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
        
        $totalRevenue = BoqItem::whereIn('project_id', $projectIds)->sum('revenue_amount') ?? 0;
        $totalBudget = DB::table('labor_resources')->whereIn('boq_item_id', BoqItem::whereIn('project_id', $projectIds)->pluck('id'))->sum('amount') + 
                       DB::table('material_resources')->whereIn('boq_item_id', BoqItem::whereIn('project_id', $projectIds)->pluck('id'))->sum('amount') + 
                       DB::table('equipment_resources')->whereIn('boq_item_id', BoqItem::whereIn('project_id', $projectIds)->pluck('id'))->sum('amount') ?? 0;
        
        $profitLoss = $totalRevenue - $totalBudget;
        $totalIpcs = Ipc::whereIn('project_id', $projectIds)->count();
        $pendingIpcs = Ipc::whereIn('project_id', $projectIds)->where('status', 'submitted')->count();
        $approvedIpcs = Ipc::whereIn('project_id', $projectIds)->where('status', 'approved')->count();
        $recentIpcs = Ipc::with(['project','subcontractor'])->whereIn('project_id', $projectIds)->latest()->take(5)->get();
        $recentNotifications = Notification::recent($user->id, 5);
        $unreadNotifications = Notification::unreadCount($user->id);
        
        $projectStatusData = [
            'active' => (clone $projectQuery)->where('status','active')->count(),
            'completed' => (clone $projectQuery)->where('status','completed')->count(),
            'on_hold' => (clone $projectQuery)->where('status','on_hold')->count(),
            'cancelled' => (clone $projectQuery)->where('status','cancelled')->count(),
        ];
        
        $totalUsers = \App\Models\User::count();
        $activeUsers = \App\Models\User::where('is_active', true)->count();
        $totalRoles = Role::count();
        $myBoqItems = BoqItem::whereIn('project_id', $projectIds)->where('is_parent',false)->count();
        $myIpcs = Ipc::whereIn('project_id', $projectIds)->count();
        $paidIpcs = Ipc::whereIn('project_id', $projectIds)->where('status','paid')->count();
        $totalPaidAmount = Ipc::whereIn('project_id', $projectIds)->where('status','paid')->sum('net_payment_amount') ?? 0;
        $totalPendingAmount = Ipc::whereIn('project_id', $projectIds)->where('status','submitted')->sum('net_payment_amount') ?? 0;
        
        return view('dashboard', compact(
            'user','role','totalProjects','activeProjects','totalContractValue','recentProjects',
            'totalRevenue','totalBudget','profitLoss','totalIpcs','pendingIpcs','approvedIpcs','recentIpcs',
            'recentNotifications','unreadNotifications','projectStatusData',
            'totalUsers','activeUsers','totalRoles',
            'myBoqItems','myIpcs','paidIpcs','totalPaidAmount','totalPendingAmount'
        ));
    }

    private function canAccessProject(Project $project): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;
        return $project->teamMembers()->where('user_id', $user->id)->where('project_user.is_active', true)->exists();
    }
}
