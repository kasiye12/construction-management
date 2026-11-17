<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\HasDocuments;

class EquipmentBreakdownReport extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDocuments;

    protected $table = 'equipment_breakdown_reports';

    public const DOCUMENT_CODE = 'OF/TNT/ EAMD/030';
    public const COMPANY_NAME_AMHARIC = 'ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች';
    public const COMPANY_NAME_ENGLISH = 'TNT CONSTRUCTION AND TRADING';
    public const TITLE = 'Maintenance Report';

    public const CATEGORIES = [
        'EARTH MOVING MACHINE AND PLANT' => 'Earth Moving Machine and Plant',
        'DUMP TRUCK' => 'Dump Truck',
        'LIGHT VEHICLES' => 'Light Vehicles',
        'AUXILIARY' => 'Auxiliary Equipment',
        'INSURANCE CASE' => 'Insurance Case',
    ];

    public const REPAIR_STATUSES = [
        'down' => 'Down',
        'under_maintenance' => 'Under Maintenance',
        'proforma' => 'Waiting Proforma',
        'purchase' => 'Waiting Purchase',
        'repaired' => 'Finished / Repaired',
    ];

    protected $fillable = [
        'report_code',
        'title',
        'document_code',
        'issue_no',
        'page_no',
        'report_date',
        'project_id',
        'location_name',
        'status',
        'prepared_by',
        'prepared_name',
        'prepared_at',
        'checked_by',
        'checked_name',
        'checked_at',
        'approved_by',
        'approved_name',
        'approved_at',
        'rejection_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(EquipmentBreakdownItem::class, 'breakdown_report_id')->orderBy('sort_order');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function preparedByUser()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function checkedByUser()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Status helpers
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isChecked(): bool { return $this->status === 'checked'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'submitted' => '<span class="badge bg-info text-dark">Submitted</span>',
            'checked' => '<span class="badge bg-warning text-dark">Checked</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getItemsGroupedByCategoryAttribute()
    {
        return $this->items->groupBy('category');
    }

    public function getDownCountAttribute(): int
    {
        return $this->items->where('repair_status', 'down')->count();
    }

    public function getUnderMaintenanceCountAttribute(): int
    {
        return $this->items->where('repair_status', 'under_maintenance')->count();
    }

    public function getRepairedCountAttribute(): int
    {
        return $this->items->where('repair_status', 'repaired')->count();
    }
}
