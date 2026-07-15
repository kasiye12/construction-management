<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::all();
        $selectedProject = null;
        $tasks = collect();
        
        if ($projectId) {
            $selectedProject = Project::find($projectId);
            $tasks = BoqItem::where('project_id', $projectId)
                ->where('is_parent', false)
                ->whereNotNull('planned_start_date')
                ->whereNotNull('planned_end_date')
                ->orderBy('planned_start_date')
                ->get()
                ->map(function($item) {
                    $start = Carbon::parse($item->planned_start_date);
                    $end = Carbon::parse($item->planned_end_date);
                    $duration = $start->diffInDays($end) ?: 1;
                    $progress = $item->status == 'completed' ? 100 : 
                               ($item->status == 'in_progress' ? 50 : 0);
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->item_number . ' - ' . \Str::limit($item->description, 50),
                        'start' => $start->format('Y-m-d'),
                        'end' => $end->format('Y-m-d'),
                        'duration' => $duration,
                        'progress' => $progress,
                        'status' => $item->status,
                        'category' => $item->costCategory->name ?? 'General',
                    ];
                });
        }
        
        // Calculate date range
        $minDate = $tasks->min('start') ?? date('Y-m-d');
        $maxDate = $tasks->max('end') ?? date('Y-m-d', strtotime('+30 days'));
        $totalDays = Carbon::parse($minDate)->diffInDays(Carbon::parse($maxDate)) ?: 30;
        
        return view('gantt.index', compact(
            'projects', 'projectId', 'selectedProject', 
            'tasks', 'minDate', 'maxDate', 'totalDays'
        ));
    }
    
    public function projectTimeline($projectId)
    {
        return redirect()->route('gantt.index', ['project_id' => $projectId]);
    }
}
