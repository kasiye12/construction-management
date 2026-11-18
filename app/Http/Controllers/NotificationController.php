<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Automatically synchronize live document news updates for user
        $this->notificationService->syncDocumentNews($user);

        // 2. Fetch live document counts across all necessary ERP documents
        $docStats = $this->notificationService->getDocumentStats($user);

        // 3. Query user notifications
        $query = Notification::where('user_id', $user->id);
        
        // Apply filters
        $filter = $request->filter;
        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'cad01') {
            $query->where('type', 'like', 'contractual_%');
        } elseif ($filter === 'ipc') {
            $query->where('type', 'like', 'ipc_%');
        } elseif ($filter === 'equipment') {
            $query->whereIn('type', ['pm_overdue', 'pm_due_soon', 'breakdown_reported', 'breakdown_repaired']);
        } elseif ($filter === 'deliveries') {
            $query->where('type', 'like', 'delivery_%');
        } elseif ($filter === 'takeoff') {
            $query->where('type', 'like', 'takeoff_%');
        } elseif ($filter === 'projects') {
            $query->whereIn('type', ['project_created', 'deadline', 'budget_exceeded', 'document_uploaded']);
        } elseif (!empty($filter) && in_array($filter, ['ipc_submitted', 'ipc_approved', 'budget_exceeded', 'deadline', 'ipc_rejected', 'ipc_paid', 'pm_overdue', 'breakdown_reported'])) {
            $query->where('type', $filter);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistics
        $stats = [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'read' => Notification::where('user_id', $user->id)->where('is_read', true)->count(),
            'this_week' => Notification::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(7))->count(),
            'action_required' => $docStats['action_required_count'],
            'urgent_alerts' => $docStats['urgent_alerts_count'],
            'total_monitored' => $docStats['total_monitored_documents'],
        ];

        // Specific filter counts for chips
        $filterCounts = [
            'cad01' => Notification::where('user_id', $user->id)->where('type', 'like', 'contractual_%')->count(),
            'ipc' => Notification::where('user_id', $user->id)->where('type', 'like', 'ipc_%')->count(),
            'equipment' => Notification::where('user_id', $user->id)->whereIn('type', ['pm_overdue', 'pm_due_soon', 'breakdown_reported', 'breakdown_repaired'])->count(),
            'deliveries' => Notification::where('user_id', $user->id)->where('type', 'like', 'delivery_%')->count(),
            'takeoff' => Notification::where('user_id', $user->id)->where('type', 'like', 'takeoff_%')->count(),
            'projects' => Notification::where('user_id', $user->id)->whereIn('type', ['project_created', 'deadline', 'budget_exceeded', 'document_uploaded'])->count(),
        ];
        
        return view('notifications.index', compact('notifications', 'stats', 'docStats', 'filterCounts'));
    }

    public function syncNews(Request $request)
    {
        $count = $this->notificationService->syncDocumentNews(Auth::user());
        return back()->with('success', "Operational document news updates and live counts synchronized successfully ({$count} active alerts updated).");
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        
        if ($notification->link) return redirect($notification->link);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function delete(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }

    public function deleteAllRead()
    {
        Notification::where('user_id', Auth::id())->where('is_read', true)->delete();
        return back()->with('success', 'All read notifications cleared.');
    }

    public function getUnreadCount()
    {
        return response()->json(['count' => Notification::unreadCount(Auth::id())]);
    }

    public function getRecent()
    {
        $notifications = Notification::recent(Auth::id(), 5);
        $docStats = $this->notificationService->getDocumentStats(Auth::user());

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::unreadCount(Auth::id()),
            'action_required_count' => $docStats['action_required_count'],
            'urgent_alerts_count' => $docStats['urgent_alerts_count'],
        ]);
    }
}
