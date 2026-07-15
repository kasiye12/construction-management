<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoqItem;
use App\Exports\ThirtyColumnReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function thirtyColumnReport(Request $request)
    {
        $projects = Project::all();
        $projectId = $request->get('project_id');
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        // Build query with filters
        $query = BoqItem::with([
            'costCategory',
            'laborResources',
            'materialResources',
            'equipmentResources',
            'project'
        ])->where('is_parent', false);
        
        // Project filter
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        // Cost Category filter
        if ($request->filled('category_id')) {
            $query->where('cost_category_id', $request->category_id);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('planned_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('planned_start_date', '<=', $request->date_to);
        }
        
        $items = $query->orderBy('cost_category_id')
                      ->orderBy('item_number')
                      ->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $totalRevenue = $items->sum('revenue_amount');
        $totalBudget = $items->sum(function($item) {
            return $item->total_budget_cost;
        });
        $totalProfitLoss = $totalRevenue - $totalBudget;
        $totalProfitMargin = $totalRevenue > 0 ? ($totalProfitLoss / $totalRevenue) * 100 : 0;
        
        return view('reports.thirty-column', compact(
            'groupedItems', 'projects', 'projectId', 'selectedProject',
            'totalRevenue', 'totalBudget', 'totalProfitLoss', 'totalProfitMargin'
        ));
    }
    
    public function exportExcel(Request $request)
    {
        $projectId = $request->get('project_id');
        $projectName = 'All_Projects';
        
        if ($projectId) {
            $project = Project::find($projectId);
            $projectName = $project ? str_replace(' ', '_', $project->name) : 'Project';
        }
        
        $filename = '30_Column_Report_' . $projectName . '_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new ThirtyColumnReportExport($projectId), $filename);
    }
    
    public function downloadPdf(Request $request)
    {
        $projectId = $request->get('project_id');
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $query = BoqItem::with([
            'costCategory', 'laborResources', 'materialResources', 'equipmentResources'
        ])->where('is_parent', false);
        
        if ($projectId) $query->where('project_id', $projectId);
        
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $totalRevenue = $items->sum('revenue_amount');
        $totalBudget = $items->sum(fn($i) => $i->total_budget_cost);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.thirty-column-pdf', compact(
            'groupedItems', 'selectedProject', 'totalRevenue', 'totalBudget'
        ));
        $pdf->setPaper('A3', 'landscape');
        return $pdf->download('30-column-report.pdf');
    }
}
