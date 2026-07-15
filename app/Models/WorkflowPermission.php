<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowPermission extends Model
{
    protected $fillable = ['user_id', 'workflow_step', 'can_act'];
    protected $casts = ['can_act' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if a user can perform a specific workflow action
    public static function canUserAct($userId, $step): bool
    {
        return self::where('user_id', $userId)
            ->where('workflow_step', $step)
            ->where('can_act', true)
            ->exists();
    }

    // Get all users who can perform a specific step
    public static function getUsersForStep($step)
    {
        return User::whereHas('workflowPermissions', function($q) use ($step) {
            $q->where('workflow_step', $step)->where('can_act', true);
        })->get();
    }

    // Sync permissions for a user
    public static function syncForUser($userId, array $permissions)
    {
        self::where('user_id', $userId)->delete();
        
        foreach ($permissions as $step => $canAct) {
            if ($canAct) {
                self::create([
                    'user_id' => $userId,
                    'workflow_step' => $step,
                    'can_act' => true,
                ]);
            }
        }
    }
}
