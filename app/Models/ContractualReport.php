<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\HasDocuments;

class ContractualReport extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDocuments;

    protected $fillable = [
        'project_id',
        'report_number',
        'document_code',
        'issue_no',
        'project_location',
        'client_name',
        'consultant_name',
        'contractor_name',
        'week_start_date',
        'week_end_date',
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
        'page1_notes',
        'page2_remark',
        'page3_remark',
        'created_by',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const CLAIM_CATEGORIES = [
        'design_issues' => [
            'title' => 'Design related issues',
            'items' => [
                '1.1' => 'Miss drawing',
                '1.2' => 'Design discrepancies',
                '1.3' => 'Design modification at site level',
            ],
        ],
        'work_orders' => [
            'title' => 'Work Orders',
            'items' => [
                '2.1' => 'Works indicated on drawing and missed in BOQ or vice versa',
                '2.2' => 'Additional work order given',
                '2.3' => 'Change in work item (quality, or shape /dimension or quantity from BOQ)',
            ],
        ],
        'suspension_of_works' => [
            'title' => 'Suspension of works',
            'items' => [
                '3.1' => 'Bad Weather conditions and natural hazards',
                '3.2' => 'Delay due to inspection / approval',
                '3.3' => 'Force majeure issues',
                '3.4' => 'Artificial conditions or Physical obstructions',
                '3.5' => 'Other external factors',
            ],
        ],
    ];

    public const CORRESPONDENCE_CATEGORIES = [
        'incoming' => [
            'title' => 'In coming Letters',
            'items' => [
                '1.0' => 'From Client',
                '2.0' => 'From Consultant',
                '3.0' => 'Others',
            ],
        ],
        'outgoing' => [
            'title' => 'Out going Letters',
            'items' => [
                '1.0' => 'To Client',
                '2.0' => 'To Consultant',
                '3.0' => 'Others',
            ],
        ],
    ];

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
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

    public function claimChecks()
    {
        return $this->hasMany(ContractualClaimCheck::class)->orderBy('sort_order');
    }

    public function claimEvents()
    {
        return $this->hasMany(ContractualClaimEvent::class)->orderBy('sort_order');
    }

    public function correspondenceChecks()
    {
        return $this->hasMany(ContractualCorrespondenceCheck::class)->orderBy('sort_order');
    }

    /**
     * Generate the next globally unique report number for a given year.
     * Scans all records including soft-deleted ones to avoid unique constraint collisions.
     * Format: WCR-YYYY-XXXX (e.g. WCR-2026-0004)
     */
    public static function generateNextReportNumber(?string $year = null): string
    {
        $year = $year ?: date('Y');
        $prefix = "WCR-{$year}-";

        // Fetch all existing numbers for the given year (including soft-deleted)
        $existing = self::withTrashed()
            ->where('report_number', 'LIKE', "{$prefix}%")
            ->pluck('report_number')
            ->toArray();

        $maxNum = 0;
        foreach ($existing as $num) {
            if (preg_match('/^WCR-\d{4}-(\d+)$/', $num, $matches)) {
                $val = (int)$matches[1];
                if ($val > $maxNum) {
                    $maxNum = $val;
                }
            }
        }

        $nextNum = $maxNum + 1;
        do {
            $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            $collision = in_array($candidate, $existing) || self::withTrashed()->where('report_number', $candidate)->exists();
            if ($collision) {
                $nextNum++;
            }
        } while ($collision);

        return $candidate;
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

    public function getActiveClaimsCountAttribute(): int
    {
        return $this->claimChecks()->get()->filter(fn($c) => $c->hasAnyYes())->count();
    }

    public function getActiveCorrespondencesCountAttribute(): int
    {
        return $this->correspondenceChecks()->get()->filter(fn($c) => $c->hasAnyYes())->count();
    }
}
