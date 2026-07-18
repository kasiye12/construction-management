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

    /**
     * Check if a user can perform a specific workflow action
     */
    public static function canUserAct($userId, $step): bool
    {
        return self::where('user_id', $userId)
            ->where('workflow_step', $step)
            ->where('can_act', true)
            ->exists();
    }

    /**
     * Sync permissions for a user - only update submitted steps
     */
    public static function syncForUser($userId, array $permissions)
    {
        foreach ($permissions as $step => $canAct) {
            if ($canAct) {
                self::updateOrCreate(
                    ['user_id' => $userId, 'workflow_step' => $step],
                    ['can_act' => true]
                );
            } else {
                // If unchecked, delete the permission record
                self::where('user_id', $userId)
                    ->where('workflow_step', $step)
                    ->delete();
            }
        }
    }
}
