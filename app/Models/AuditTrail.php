<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = [
        'auditable_type', 'auditable_id', 'user_id', 'action',
        'field_name', 'old_value', 'new_value', 'ip_address', 'user_agent'
    ];

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionBadgeAttribute(): string
    {
        return match (strtolower((string)$this->action)) {
            'created' => '<span class="badge bg-success rounded-pill px-2 py-1"><i class="fas fa-plus me-1"></i>Created</span>',
            'updated' => '<span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fas fa-pen me-1"></i>Updated</span>',
            'deleted' => '<span class="badge bg-danger rounded-pill px-2 py-1"><i class="fas fa-trash-alt me-1"></i>Deleted</span>',
            default => '<span class="badge bg-secondary rounded-pill px-2 py-1">' . ucfirst((string)$this->action) . '</span>',
        };
    }

    public function getModuleTitleAttribute(): string
    {
        $map = [
            'App\Models\ContractualReport' => 'Weekly Contractual Report',
            'App\Models\Equipment' => 'Equipment',
            'App\Models\EquipmentBreakdownReport' => 'Equipment Breakdown',
            'App\Models\EquipmentMaintenanceLog' => 'Maintenance Log',
            'App\Models\PmSchedule' => 'PM Schedule',
            'App\Models\MaterialDelivery' => 'Material Delivery',
            'App\Models\QuantityTakeoff' => 'Quantity Takeoff',
            'App\Models\Project' => 'Project',
            'App\Models\User' => 'User Account',
            'App\Models\Ipc' => 'IPC Payment Certificate',
        ];

        return $map[$this->auditable_type] ?? class_basename($this->auditable_type);
    }
}
