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

    public static function getIconForType(string $type): string
    {
        $icons = [
            'ipc_submitted' => 'file-invoice-dollar',
            'ipc_approved' => 'check-circle', 
            'ipc_rejected' => 'times-circle',
            'ipc_paid' => 'money-bill-wave',
            'contractual_report_submitted' => 'file-contract',
            'contractual_report_checked' => 'file-signature',
            'contractual_report_approved' => 'check-circle',
            'contractual_report_rejected' => 'times-circle',
            'contractual_report_created' => 'file-contract',
            'contractual_claim_logged' => 'exclamation-triangle',
            'takeoff_created' => 'ruler-combined',
            'takeoff_verified' => 'clipboard-check',
            'takeoff_approved' => 'check-double',
            'takeoff_sheet_created' => 'table',
            'delivery_received' => 'truck-loading',
            'delivery_inspected' => 'clipboard-check',
            'delivery_accepted' => 'check-circle',
            'delivery_confirmed' => 'truck-loading',
            'pm_overdue' => 'calendar-times',
            'pm_due_soon' => 'calendar-alt',
            'breakdown_reported' => 'tools',
            'breakdown_repaired' => 'wrench',
            'budget_exceeded' => 'exclamation-triangle',
            'deadline' => 'clock',
            'project_created' => 'hard-hat',
            'document_uploaded' => 'file-alt',
            'payment_due' => 'money-bill',
            'default' => 'bell',
        ];

        return $icons[$type] ?? $icons['default'];
    }

    public static function getColorForType(string $type): string
    {
        $colors = [
            'ipc_submitted' => 'warning',
            'ipc_approved' => 'success',
            'ipc_rejected' => 'danger',
            'ipc_paid' => 'primary',
            'contractual_report_submitted' => 'warning',
            'contractual_report_checked' => 'info',
            'contractual_report_approved' => 'success',
            'contractual_report_rejected' => 'danger',
            'contractual_report_created' => 'primary',
            'contractual_claim_logged' => 'warning',
            'takeoff_created' => 'primary',
            'takeoff_verified' => 'info',
            'takeoff_approved' => 'success',
            'takeoff_sheet_created' => 'primary',
            'delivery_received' => 'info',
            'delivery_inspected' => 'warning',
            'delivery_accepted' => 'success',
            'delivery_confirmed' => 'success',
            'pm_overdue' => 'danger',
            'pm_due_soon' => 'warning',
            'breakdown_reported' => 'danger',
            'breakdown_repaired' => 'success',
            'budget_exceeded' => 'danger',
            'deadline' => 'info',
            'project_created' => 'primary',
            'document_uploaded' => 'info',
            'payment_due' => 'warning',
            'default' => 'secondary',
        ];

        return $colors[$type] ?? $colors['default'];
    }

    // Quick create methods
    public static function sendToUser($userId, $type, $title, $message, $link = null, $sendEmail = false)
    {
        $notification = self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => self::getIconForType($type),
            'color' => self::getColorForType($type),
            'link' => $link,
        ]);

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
                             ->subject('[CMS News] ' . $title);
                    });
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification email: ' . $e->getMessage());
                }
            }
        }

        return $notification;
    }

    /**
     * Send or update recurring news notification to avoid flooding inbox
     */
    public static function sendOrUpdate($userId, $type, $title, $message, $link = null)
    {
        $existing = self::where('user_id', $userId)
            ->where('type', $type)
            ->latest()
            ->first();

        if ($existing) {
            // If already exists with identical title and message, do not recreate or alter read state
            if ($existing->title === $title && $existing->message === $message) {
                return $existing;
            }
            $existing->update([
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'icon' => self::getIconForType($type),
                'color' => self::getColorForType($type),
                'updated_at' => now(),
            ]);
            return $existing;
        }

        return self::sendToUser($userId, $type, $title, $message, $link);
    }

    /**
     * Send notification to all users who possess a specific permission
     */
    public static function sendToPermission($permission, $type, $title, $message, $link = null, $sendEmail = false)
    {
        $users = User::where('is_active', true)->get()->filter(function($user) use ($permission) {
            return $user->hasPermission($permission);
        });

        foreach ($users as $user) {
            self::sendOrUpdate($user->id, $type, $title, $message, $link);
        }
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
