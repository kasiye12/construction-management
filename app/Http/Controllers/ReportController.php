<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function thirtyColumnReport(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        
        $query = BoqItem::with([
            'costCategory',
            'laborResources',
            'materialResources',
            'equipmentResources',
            'project'
        ]);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        // Get all items ordered by cost category and item number
        $items = $query->where('is_parent', false)
                      ->orderBy('cost_category_id')
                      ->orderBy('item_number')
                      ->get();
        
        // Group by cost category
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        // Calculate totals
        $totalRevenue = $items->sum('revenue_amount');
        $totalBudget = $items->sum(function($item) {
            return $item->total_budget_cost;
        });
        $totalProfitLoss = $totalRevenue - $totalBudget;
        $totalProfitMargin = $totalRevenue > 0 ? ($totalProfitLoss / $totalRevenue) * 100 : 0;
        
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        return view('reports.thirty-column', compact(
            'groupedItems', 'projects', 'projectId', 'selectedProject',
            'totalRevenue', 'totalBudget', 'totalProfitLoss', 'totalProfitMargin'
        ));
    }
    
    public function downloadPdf(Request $request)
    {
        $projectId = $request->get('project_id');
        
        $query = BoqItem::with([
            'costCategory',
            'laborResources',
            'materialResources',
            'equipmentResources',
            'project'
        ]);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $items = $query->where('is_parent', false)
                      ->orderBy('cost_category_id')
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
        
        $selectedProject = $projectId ? Project::find($projectId) : null;
        
        $pdf = Pdf::loadView('reports.thirty-column-pdf', compact(
            'groupedItems', 'selectedProject',
            'totalRevenue', 'totalBudget', 'totalProfitLoss', 'totalProfitMargin'
        ));
        
        $pdf->setPaper('A2', 'landscape'); // Large paper for 30 columns
        
        return $pdf->download('30-column-report.pdf');
    }
}
