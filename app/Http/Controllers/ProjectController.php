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
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->paginate(10);
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
        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load(['costCategories','boqItems' => function($q) { $q->with(['laborResources','materialResources','equipmentResources']); }, 'ipcs','subcontractors','documents']);
        $totalRevenue = $project->boqItems->sum('revenue_amount');
        $totalBudgetCost = $project->boqItems->sum(fn($i) => $i->total_budget_cost);
        $totalProfitLoss = $totalRevenue - $totalBudgetCost;
        return view('projects.show', compact('project','totalRevenue','totalBudgetCost','totalProfitLoss'));
    }

    public function edit(Project $project) { return view('projects.edit', compact('project')); }

    public function update(Request $request, Project $project)
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
        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project) { $project->delete(); return redirect()->route('projects.index')->with('success', 'Project deleted.'); }

    public function dashboard()
    {
        $user = Auth::user();
        
        // If not logged in, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user->getRoleName();
        
        // Common data
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $totalContractValue = Project::sum('contract_amount');
        $recentProjects = Project::latest()->take(5)->get();
        $myProjects = auth()->user()->projects()->where('status', 'active')->get();
        $totalRevenue = BoqItem::sum('revenue_amount') ?? 0;
        $totalBudget = DB::table('labor_resources')->sum('amount') + DB::table('material_resources')->sum('amount') + DB::table('equipment_resources')->sum('amount') ?? 0;
        $profitLoss = $totalRevenue - $totalBudget;
        $totalIpcs = Ipc::count();
        $pendingIpcs = Ipc::where('status', 'submitted')->count();
        $approvedIpcs = Ipc::where('status', 'approved')->count();
        $recentIpcs = Ipc::with(['project','subcontractor'])->latest()->take(5)->get();
        $recentNotifications = Notification::recent($user->id, 5);
        $unreadNotifications = Notification::unreadCount($user->id);
        
        $projectStatusData = [
            'active' => Project::where('status','active')->count(),
            'completed' => Project::where('status','completed')->count(),
            'on_hold' => Project::where('status','on_hold')->count(),
            'cancelled' => Project::where('status','cancelled')->count(),
        ];
        
        // Role-specific extras
        $totalUsers = \App\Models\User::count();
        $activeUsers = \App\Models\User::where('is_active', true)->count();
        $totalRoles = Role::count();
        $myBoqItems = BoqItem::where('is_parent',false)->count();
        $myIpcs = Ipc::count();
        $paidIpcs = Ipc::where('status','paid')->count();
        $totalPaidAmount = Ipc::where('status','paid')->sum('net_payment_amount') ?? 0;
        $totalPendingAmount = Ipc::where('status','submitted')->sum('net_payment_amount') ?? 0;
        
        return view('dashboard', compact(
            'user','role',
            'totalProjects','activeProjects','totalContractValue','recentProjects',
            'totalRevenue','totalBudget','profitLoss',
            'totalIpcs','pendingIpcs','approvedIpcs','recentIpcs',
            'recentNotifications','unreadNotifications','projectStatusData',
            'totalUsers','activeUsers','totalRoles',
            'myBoqItems','myIpcs','paidIpcs','totalPaidAmount','totalPendingAmount'
        ));
    }
}
