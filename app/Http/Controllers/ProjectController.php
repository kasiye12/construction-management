<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Ipc;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->paginate(10);
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'contract_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled'
        ]);

        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load(['boqItems' => function($query) {
            $query->with(['laborResources', 'materialResources', 'equipmentResources']);
        }, 'ipcs', 'subcontractors']);

        $totalRevenue = $project->boqItems->sum('revenue_amount');
        $totalBudgetCost = $project->boqItems->sum(function($item) {
            return $item->total_budget_cost;
        });
        $totalProfitLoss = $totalRevenue - $totalBudgetCost;

        return view('projects.show', compact('project', 'totalRevenue', 'totalBudgetCost', 'totalProfitLoss'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'contract_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled'
        ]);

        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function dashboard()
    {
        // Project Statistics
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalContractValue = Project::sum('contract_amount');
        
        // Financial Overview
        $totalRevenue = BoqItem::sum('revenue_amount');
        
        // Calculate total budget from all resources
        $totalBudget = DB::table('labor_resources')->sum('amount') + 
                       DB::table('material_resources')->sum('amount') + 
                       DB::table('equipment_resources')->sum('amount');
        
        $totalRevenue = $totalRevenue ?? 0;
        $totalBudget = $totalBudget ?? 0;
        $profitLoss = $totalRevenue - $totalBudget;
        $profitMargin = $totalRevenue > 0 ? ($profitLoss / $totalRevenue) * 100 : 0;
        
        // IPC Statistics
        $totalIpcs = Ipc::count();
        $pendingIpcs = Ipc::where('status', 'submitted')->count();
        $approvedIpcs = Ipc::where('status', 'approved')->count();
        $totalPaidAmount = Ipc::where('status', 'paid')->sum('net_payment_amount');
        
        // Recent data
        $recentProjects = Project::latest()->take(5)->get();
        $recentIpcs = Ipc::with(['project', 'subcontractor'])->latest()->take(5)->get();
        
        // Monthly data for charts
        $monthlyRevenue = [];
        $projectStatusData = [
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'cancelled' => Project::where('status', 'cancelled')->count(),
        ];
        
        // Cost category summary
        $costCategorySummary = DB::table('cost_categories')
            ->leftJoin('boq_items', 'cost_categories.id', '=', 'boq_items.cost_category_id')
            ->select('cost_categories.name', DB::raw('COALESCE(SUM(boq_items.revenue_amount), 0) as total'))
            ->groupBy('cost_categories.id', 'cost_categories.name')
            ->get();
        
        // Top subcontractors
        $subcontractorPerformance = Ipc::with('subcontractor')
            ->select('subcontractor_id', DB::raw('SUM(net_payment_amount) as total_paid'))
            ->groupBy('subcontractor_id')
            ->orderByDesc('total_paid')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalProjects', 'activeProjects', 'completedProjects', 'totalContractValue',
            'totalRevenue', 'totalBudget', 'profitLoss', 'profitMargin',
            'totalIpcs', 'pendingIpcs', 'approvedIpcs', 'totalPaidAmount',
            'recentProjects', 'recentIpcs', 'monthlyRevenue', 'projectStatusData',
            'costCategorySummary', 'subcontractorPerformance'
        ));
    }
}
