<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class EquipmentMaintenanceLog extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'equipment_maintenance_logs';

    protected $fillable = [
        'equipment_id',
        'equipment_type',
        'equipment_plate',
        'spare_part_name',
        'po_no',
        'unit',
        'quantity',
        'unit_cost',
        'total_cost_with_vat',
        'supplier',
        'log_date',
        'project_id',
        'location_name',
        'logged_by',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost_with_vat' => 'decimal:2',
        'log_date' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function loggedByUser()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    protected static function booted()
    {
        static::saving(function ($log) {
            if (empty($log->total_cost_with_vat) || (float)$log->total_cost_with_vat === 0.0) {
                if ($log->quantity && $log->unit_cost) {
                    // With 15% Ethiopian VAT default if not provided
                    $log->total_cost_with_vat = round((float)$log->quantity * (float)$log->unit_cost * 1.15, 2);
                }
            }
        });
    }
}
