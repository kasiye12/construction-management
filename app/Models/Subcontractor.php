<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcontractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'address', 'tax_id', 'is_active'
    ];

    public function projects() {
        return $this->belongsToMany(Project::class, 'project_subcontractor')
                    ->withPivot(['contract_amount', 'contract_start_date', 'contract_end_date', 'scope_of_work'])
                    ->withTimestamps();
    }
    public function ipcs() { return $this->hasMany(Ipc::class); }
}
