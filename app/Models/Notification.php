<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon',
        'color', 'link', 'is_read', 'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quick create methods
    public static function sendToUser($userId, $type, $title, $message, $link = null)
    {
        $icons = [
            'ipc_submitted' => 'file-invoice',
            'ipc_approved' => 'check-circle', 
            'budget_exceeded' => 'exclamation-triangle',
            'deadline' => 'clock',
            'project_created' => 'plus-circle',
            'payment_due' => 'money-bill',
            'default' => 'bell',
        ];

        $colors = [
            'ipc_submitted' => 'warning',
            'ipc_approved' => 'success',
            'budget_exceeded' => 'danger',
            'deadline' => 'info',
            'project_created' => 'primary',
            'payment_due' => 'warning',
            'default' => 'secondary',
        ];

        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icons[$type] ?? $icons['default'],
            'color' => $colors[$type] ?? $colors['default'],
            'link' => $link,
        ]);
    }

    public static function sendToRole($role, $type, $title, $message, $link = null)
    {
        $users = User::where('role', $role)->orWhereHas('roleRelation', function($q) use ($role) {
            $q->where('name', $role);
        })->get();

        foreach ($users as $user) {
            self::sendToUser($user->id, $type, $title, $message, $link);
        }
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    // Get unread count for user
    public static function unreadCount($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return self::where('user_id', $userId)->where('is_read', false)->count();
    }

    // Get recent notifications
    public static function recent($userId = null, $limit = 10)
    {
        $userId = $userId ?? auth()->id();
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
