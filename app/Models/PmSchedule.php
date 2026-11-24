<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\HasDocuments;

class PmSchedule extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDocuments;

    protected $table = 'pm_schedules';

    public const DOCUMENT_CODE = 'OF/EAMD/074';
    public const COMPANY_NAME_AMHARIC = 'ቲ ኤን ቲ ኮንስትራክሽንና ንግድ ሥራዎች';
    public const COMPANY_NAME_ENGLISH = 'TNT CONSTRUCTION AND TRADING';
    public const TITLE = 'Preventive Maintenance Schedule';

    protected $fillable = [
        'schedule_code',
        'title',
        'document_code',
        'issue_no',
        'page_no',
        'project_id',
        'project_location',
        'schedule_date',
        'period_start',
        'period_end',
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
        'schedule_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PmScheduleItem::class)->orderBy('sort_order');
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

    // Category grouping helper
    public function getItemsGroupedByCategoryAttribute()
    {
        return $this->items->groupBy('category');
    }

    public function getOverdueCountAttribute(): int
    {
        return $this->items->where('service_status', 'Overdue')->count();
    }

    public function getDueCountAttribute(): int
    {
        return $this->items->where('service_status', 'Due')->count();
    }

    public function getNormalCountAttribute(): int
    {
        return $this->items->where('service_status', 'Normal')->count();
    }

    /**
     * Generate guaranteed collision-free sequence code (e.g. PMS-2026-001)
     */
    public static function generateNextScheduleCode(?string $year = null): string
    {
        $year = $year ?: date('Y');
        $prefix = "PMS-{$year}-";

        // Fetch all existing schedule codes for the given year (including soft-deleted)
        $existing = self::withTrashed()
            ->where('schedule_code', 'LIKE', "{$prefix}%")
            ->pluck('schedule_code')
            ->toArray();

        $maxNum = 0;
        foreach ($existing as $code) {
            if (preg_match('/^PMS-\d{4}-(\d+)$/', $code, $matches)) {
                $val = (int)$matches[1];
                if ($val > $maxNum) {
                    $maxNum = $val;
                }
            }
        }

        $nextNum = $maxNum + 1;
        do {
            $candidate = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            $collision = in_array($candidate, $existing) || self::withTrashed()->where('schedule_code', $candidate)->exists();
            if ($collision) {
                $nextNum++;
            }
        } while ($collision);

        return $candidate;
    }
}
