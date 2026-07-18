<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());
        
        // Apply filters
        if ($request->filter == 'unread') {
            $query->where('is_read', false);
        } elseif (in_array($request->filter, ['ipc_submitted', 'ipc_approved', 'budget_exceeded', 'deadline', 'ipc_rejected', 'ipc_paid'])) {
            $query->where('type', $request->filter);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistics
        $stats = [
            'total' => Notification::where('user_id', Auth::id())->count(),
            'unread' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
            'read' => Notification::where('user_id', Auth::id())->where('is_read', true)->count(),
            'this_week' => Notification::where('user_id', Auth::id())->where('created_at', '>=', now()->subDays(7))->count(),
        ];
        
        return view('notifications.index', compact('notifications', 'stats'));
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
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::unreadCount(Auth::id()),
        ]);
    }
}
