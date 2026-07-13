<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

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
}
