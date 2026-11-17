<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\HasDocuments;

class Equipment extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDocuments;

    protected $table = 'equipment';

    protected $fillable = [
        'code',
        'name',
        'equipment_type',
        'category',
        'make',
        'model',
        'plate_serial_no',
        'serial_no',
        'project_id',
        'location_name',
        'reading_unit',
        'current_reading',
        'current_reading_date',
        'last_service_reading',
        'last_service_date',
        'last_service_type',
        'next_service_reading',
        'next_service_type',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'current_reading' => 'decimal:2',
        'last_service_reading' => 'decimal:2',
        'next_service_reading' => 'decimal:2',
        'current_reading_date' => 'date',
        'last_service_date' => 'date',
    ];

    public const CATEGORIES = [
        'EARTH MOVING MACHINE' => 'Earth Moving Machine',
        'DUMP TRUCK' => 'Heavy / Dump Trucks',
        'LIGHT VEHICLES' => 'Light Vehicles / Pickups',
        'AUXILIARY' => 'Auxiliary & Plant Equipment',
    ];

    public const SERVICE_TYPES_HEAVY = ['A', 'B', 'C', 'D'];
    public const SERVICE_TYPES_LIGHT = ['A2', 'B2', 'C2', 'D2'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pmScheduleItems()
    {
        return $this->hasMany(PmScheduleItem::class);
    }

    public function breakdownItems()
    {
        return $this->hasMany(EquipmentBreakdownItem::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(EquipmentMaintenanceLog::class);
    }

    // Computed Remain HR/KM
    public function getRemainReadingAttribute(): ?float
    {
        if ($this->next_service_reading === null || $this->current_reading === null) {
            return null;
        }
        return (float) ($this->next_service_reading - $this->current_reading);
    }

    // Computed Service Status
    public function getCalculatedServiceStatusAttribute(): string
    {
        $remain = $this->remain_reading;
        if ($remain === null) {
            return 'Normal';
        }
        if ($remain < 0) {
            return 'Overdue';
        }
        if ($remain <= 50) {
            return 'Due';
        }
        return 'Normal';
    }

    public function getServiceStatusBadgeAttribute(): string
    {
        $status = $this->calculated_service_status;
        return match($status) {
            'Overdue' => '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>',
            'Due' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Due Soon</span>',
            default => '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Normal</span>',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Active / Operational</span>',
            'due_service' => '<span class="badge bg-warning text-dark">Due For Service</span>',
            'under_maintenance' => '<span class="badge bg-info text-dark">Under Maintenance</span>',
            'breakdown' => '<span class="badge bg-danger">Down / Breakdown</span>',
            'idle' => '<span class="badge bg-secondary">Idle / Standby</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getFullDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->equipment_type,
            $this->make,
            $this->model,
            $this->plate_serial_no ? "({$this->plate_serial_no})" : null,
        ]);
        return implode(' ', $parts);
    }
}
