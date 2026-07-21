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

    public static function canUserAct($userId, $step): bool
    {
        return self::where('user_id', $userId)
            ->where('workflow_step', $step)
            ->where('can_act', true)
            ->exists();
    }

    public static function syncForUser($userId, array $permissions)
    {
        foreach ($permissions as $step => $isAllowed) {
            if ($isAllowed) {
                self::updateOrCreate(
                    ['user_id' => $userId, 'workflow_step' => $step],
                    ['can_act' => true]
                );
            } else {
                self::where('user_id', $userId)
                    ->where('workflow_step', $step)
                    ->delete();
            }
        }
    }
}
