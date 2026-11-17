<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentBreakdownItem extends Model
{
    use HasFactory;

    protected $table = 'equipment_breakdown_items';

    protected $fillable = [
        'breakdown_report_id',
        'equipment_id',
        'category',
        'equipment_type',
        'quantity',
        'plate_no_sn',
        'down_date',
        'reason_of_down',
        'action_taken',
        'location',
        'repair_status',
        'finished_date',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'down_date' => 'date',
        'finished_date' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(EquipmentBreakdownReport::class, 'breakdown_report_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function getRepairStatusBadgeAttribute(): string
    {
        $status = strtolower($this->repair_status ?? 'down');
        return match($status) {
            'down' => '<span class="badge bg-danger">Down</span>',
            'under maintenance', 'under_maintenance' => '<span class="badge bg-info text-dark">Under Maintenance</span>',
            'proforma' => '<span class="badge bg-warning text-dark">Waiting Proforma</span>',
            'purchase' => '<span class="badge bg-primary">Waiting Purchase</span>',
            'repaired', 'completed' => '<span class="badge bg-success">Finished / Repaired</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->repair_status) . '</span>',
        };
    }
}
