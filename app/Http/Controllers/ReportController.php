<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoqItem;
use App\Exports\ThirtyColumnReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private function getUserProjectIds()
    {
        $user = Auth::user();
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    private function buildFilteredQuery(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        
        $query = BoqItem::with([
            'costCategory', 'laborResources', 'materialResources', 'equipmentResources'
        ])->where('is_parent', false)
          ->whereIn('project_id', $userProjectIds);
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('cost_category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('planned_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('planned_start_date', '<=', $request->date_to);
        }
        
        return $query;
    }

    public function thirtyColumnReport(Request $request)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        $projects = $user->isAdmin() ? Project::all() : Project::whereIn('id', $userProjectIds)->get();
        $projectId = $request->get('project_id');
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $query = $this->buildFilteredQuery($request);
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $totalRevenue = $items->sum('revenue_amount');
        $totalBudget = $items->sum(fn($i) => $i->total_budget_cost);
        
        return view('reports.thirty-column', compact(
            'groupedItems', 'projects', 'projectId', 'selectedProject',
            'totalRevenue', 'totalBudget'
        ));
    }
    
    /**
     * Export filtered data to Excel
     */
    public function exportExcel(Request $request)
    {
        $projectId = $request->get('project_id');
        $filters = [
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];
        
        $projectName = 'All_Projects';
        if ($projectId) {
            $project = Project::find($projectId);
            $projectName = $project ? str_replace(' ', '_', $project->name) : 'Project';
        }
        
        $filename = '30_Column_Report_' . $projectName . '_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new ThirtyColumnReportExport($projectId, $filters), $filename);
    }
    
    /**
     * Export filtered data to PDF
     */
    public function downloadPdf(Request $request)
    {
        $projectId = $request->get('project_id');
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $query = $this->buildFilteredQuery($request);
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $totalRevenue = $items->sum('revenue_amount');
        $totalBudget = $items->sum(fn($i) => $i->total_budget_cost);
        
        $pdf = Pdf::loadView('reports.thirty-column-pdf', compact(
            'groupedItems', 'selectedProject', 'totalRevenue', 'totalBudget'
        ));
        $pdf->setPaper('A3', 'landscape');
        
        $projectName = $selectedProject ? str_replace(' ', '_', $selectedProject->name) : 'All_Projects';
        return $pdf->download('30_Column_Report_' . $projectName . '_' . now()->format('Y-m-d') . '.pdf');
    }
}
