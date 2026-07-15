<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        $notification->markAsRead();
        
        if ($notification->link) {
            return redirect($notification->link);
        }
        
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function getUnreadCount()
    {
        $count = Notification::unreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }

    public function getRecent()
    {
        $notifications = Notification::recent(Auth::id(), 5);
        $count = Notification::unreadCount(Auth::id());
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $count,
        ]);
    }
}
