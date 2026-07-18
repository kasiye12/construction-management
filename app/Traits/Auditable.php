<?php
namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            foreach ($model->getDirty() as $field => $newValue) {
                if (!in_array($field, ['updated_at'])) {
                    $model->logAudit('updated', $field, $model->getOriginal($field), $newValue);
                }
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', null, $model->getOriginal());
        });
    }

    protected function logAudit($action, $field, $oldValue = null, $newValue = null)
    {
        AuditTrail::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'field_name' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
