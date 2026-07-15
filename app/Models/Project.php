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
        'name', 'client_name', 'contractor_name', 'start_date',
        'end_date', 'contract_amount', 'description', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_amount' => 'decimal:2'
    ];

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
