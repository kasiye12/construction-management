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
        $projects = Project::orderBy('name')->get();
        $projectId = $request->get('project_id');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Get BOQ items with dates
        $query = BoqItem::whereNotNull('planned_start_date')
            ->whereNotNull('planned_end_date')
            ->where('is_parent', false);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($status) {
            if ($status == 'completed') {
                $query->whereRaw('planned_end_date < ?', [now()]);
            } elseif ($status == 'in_progress') {
                $query->where('planned_start_date', '<=', now())
                    ->where('planned_end_date', '>=', now());
            } elseif ($status == 'pending') {
                $query->where('planned_start_date', '>', now());
            }
        }

        if ($dateFrom) {
            $query->where('planned_end_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('planned_start_date', '<=', $dateTo);
        }

        $boqItems = $query->orderBy('planned_start_date')->get();

        // Build tasks array
        $tasks = [];
        foreach ($boqItems as $item) {
            $start = Carbon::parse($item->planned_start_date);
            $end = Carbon::parse($item->planned_end_date);
            $duration = (int) $start->diffInDays($end) + 1;
            
            // Determine status
            if ($end->lt(now())) {
                $itemStatus = 'completed';
                $progress = 100;
            } elseif ($start->lte(now()) && $end->gte(now())) {
                $itemStatus = 'in_progress';
                $totalDays = $start->diffInDays($end) + 1;
                $daysDone = $start->diffInDays(min(now(), $end)) + 1;
                $progress = min(round(($daysDone / max($totalDays, 1)) * 100), 100);
            } else {
                $itemStatus = 'pending';
                $progress = 0;
            }

            $tasks[] = [
                'id' => $item->id,
                'name' => $item->description ?? $item->item_number,
                'category' => $item->costCategory->name ?? 'BOQ Item',
                'start' => $item->planned_start_date,
                'end' => $item->planned_end_date,
                'duration' => $duration,
                'status' => $itemStatus,
                'progress' => $progress,
            ];
        }

        // Calculate date range
        if ($tasks) {
            $minDate = collect($tasks)->min('start');
            $maxDate = collect($tasks)->max('end');
            $totalDays = Carbon::parse($minDate)->diffInDays(Carbon::parse($maxDate)) + 1;
        } else {
            $minDate = now()->subDays(30)->format('Y-m-d');
            $maxDate = now()->addDays(60)->format('Y-m-d');
            $totalDays = 90;
        }

        $selectedProject = $projectId ? Project::find($projectId) : null;

        return view('gantt.index', compact(
            'tasks', 'projects', 'projectId', 'minDate', 'maxDate', 'totalDays', 'selectedProject'
        ));
    }

    public function projectTimeline($projectId)
    {
        return redirect()->route('gantt.index', ['project_id' => $projectId]);
    }
}
