<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasDocuments;

class Project extends Model
{
    use HasFactory, SoftDeletes, HasDocuments;

    protected $fillable = [
        'project_code', 'name', 'location', 'client_name', 'consultant_name', 'contractor_name',
        'contract_signing_date', 'start_date', 'end_date', 'contract_amount', 'tnt_share',
        'description', 'status'
    ];

    protected $casts = [
        'contract_signing_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_amount' => 'decimal:2',
        'tnt_share' => 'decimal:2',
    ];

    public function getFormattedTntShareAttribute(): string
    {
        if ($this->tnt_share === null) {
            return '100%';
        }
        $val = (float) $this->tnt_share;
        // If stored as 1.00 or <= 1, convert to percentage representation
        if ($val <= 1.0 && $val > 0) {
            $val = $val * 100;
        }
        return (fmod($val, 1) === 0.0 ? (int)$val : number_format($val, 2)) . '%';
    }

    public function contractualReports() { return $this->hasMany(ContractualReport::class); }
    public function equipment() { return $this->hasMany(Equipment::class); }
    public function pmSchedules() { return $this->hasMany(PmSchedule::class); }
    public function breakdownReports() { return $this->hasMany(EquipmentBreakdownReport::class); }
    public function maintenanceLogs() { return $this->hasMany(EquipmentMaintenanceLog::class); }
    public function costCategories() { return $this->hasMany(CostCategory::class); }
    public function boqItems() { return $this->hasMany(BoqItem::class); }
    
    public function subcontractors() {
        return $this->belongsToMany(Subcontractor::class, 'project_subcontractor')
                    ->withPivot(['contract_amount', 'contract_start_date', 'contract_end_date', 'scope_of_work'])
                    ->withTimestamps();
    }
    
    public function ipcs() { return $this->hasMany(Ipc::class); }

    // Team Members
    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot(['role', 'assigned_date', 'end_date', 'is_active', 'responsibilities'])
                    ->withTimestamps()
                    ->orderBy('name');
    }

    // Get project manager
    public function projectManager()
    {
        return $this->teamMembers()->wherePivot('role', 'project_manager')->first();
    }

    // Get site engineers
    public function siteEngineers()
    {
        return $this->teamMembers()->wherePivot('role', 'site_engineer');
    }

    // Get quantity surveyors
    public function quantitySurveyors()
    {
        return $this->teamMembers()->wherePivot('role', 'quantity_surveyor');
    }

    // Check if user is assigned to project
    public function hasMember($userId): bool
    {
        return $this->teamMembers()->where('user_id', $userId)->exists();
    }
}
