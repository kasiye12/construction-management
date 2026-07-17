<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GanttController extends Controller
{
    private function getUserProjectIds()
    {
        $user = Auth::user();
        if ($user->isAdmin()) return Project::pluck('id')->toArray();
        return $user->projects()->where('project_user.is_active', true)->pluck('projects.id')->toArray();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        
        $projects = $user->isAdmin() ? Project::all() : $user->projects()->where('project_user.is_active', true)->get();
        $projectId = $request->get('project_id');
        $selectedProject = null;
        $tasks = collect();
        
        if ($projectId && in_array($projectId, $userProjectIds)) {
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
                    $duration = max($start->diffInDays($end), 1);
                    $progress = $item->status == 'completed' ? 100 : ($item->status == 'in_progress' ? 50 : 0);
                    
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
        
        $minDate = $tasks->min('start') ?? date('Y-m-d');
        $maxDate = $tasks->max('end') ?? date('Y-m-d', strtotime('+30 days'));
        $totalDays = max(Carbon::parse($minDate)->diffInDays(Carbon::parse($maxDate)), 30);
        
        return view('gantt.index', compact('projects', 'projectId', 'selectedProject', 'tasks', 'minDate', 'maxDate', 'totalDays'));
    }
}
