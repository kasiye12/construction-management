<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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
    public static function sendToUser($userId, $type, $title, $message, $link = null, $sendEmail = false)
    {
        $icons = [
            'ipc_submitted' => 'file-invoice',
            'ipc_approved' => 'check-circle', 
            'ipc_rejected' => 'times-circle',
            'ipc_paid' => 'money-bill-wave',
            'budget_exceeded' => 'exclamation-triangle',
            'deadline' => 'clock',
            'project_created' => 'plus-circle',
            'payment_due' => 'money-bill',
            'takeoff_verified' => 'ruler-combined',
            'takeoff_approved' => 'check-double',
            'delivery_confirmed' => 'truck-loading',
            'default' => 'bell',
        ];

        $colors = [
            'ipc_submitted' => 'warning',
            'ipc_approved' => 'success',
            'ipc_rejected' => 'danger',
            'ipc_paid' => 'primary',
            'budget_exceeded' => 'danger',
            'deadline' => 'info',
            'project_created' => 'primary',
            'payment_due' => 'warning',
            'takeoff_verified' => 'info',
            'takeoff_approved' => 'success',
            'delivery_confirmed' => 'success',
            'default' => 'secondary',
        ];

        $notification = self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icons[$type] ?? $icons['default'],
            'color' => $colors[$type] ?? $colors['default'],
            'link' => $link,
        ]);

        // Send email if requested
        if ($sendEmail) {
            $user = User::find($userId);
            if ($user && $user->email) {
                try {
                    Mail::send('emails.notification', [
                        'user' => $user,
                        'title' => $title,
                        'message' => $message,
                        'link' => $link,
                    ], function($mail) use ($user, $title) {
                        $mail->to($user->email)
                             ->subject('[CMS] ' . $title);
                    });
                } catch (\Exception $e) {
                    // Log error but don't stop execution
                    \Log::error('Failed to send notification email: ' . $e->getMessage());
                }
            }
        }

        return $notification;
    }

    public static function sendToRole($role, $type, $title, $message, $link = null, $sendEmail = false)
    {
        $users = User::where('role', $role)
            ->orWhereHas('roleRelation', function($q) use ($role) {
                $q->where('name', $role);
            })->get();

        foreach ($users as $user) {
            self::sendToUser($user->id, $type, $title, $message, $link, $sendEmail);
        }
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public static function unreadCount($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return self::where('user_id', $userId)->where('is_read', false)->count();
    }

    public static function recent($userId = null, $limit = 10)
    {
        $userId = $userId ?? auth()->id();
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
