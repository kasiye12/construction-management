#!/bin/bash

echo "📦 Creating all models..."

# Project Model
cat > app/Models/Project.php << 'MODEL'
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
MODEL

# Subcontractor Model
cat > app/Models/Subcontractor.php << 'MODEL'
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
MODEL

# CostCategory Model
cat > app/Models/CostCategory.php << 'MODEL'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'code', 'name', 'description', 'display_order'];

    public function project() { return $this->belongsTo(Project::class); }
    public function boqItems() { return $this->hasMany(BoqItem::class); }
}
MODEL

# BoqItem Model
cat > app/Models/BoqItem.php << 'MODEL'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoqItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id', 'cost_category_id', 'parent_id', 'item_number',
        'description', 'unit', 'quantity', 'unit_rate', 'revenue_amount',
        'duration_days', 'planned_start_date', 'planned_end_date',
        'display_order', 'is_parent', 'status'
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'quantity' => 'decimal:4',
        'unit_rate' => 'decimal:2',
        'revenue_amount' => 'decimal:2',
        'is_parent' => 'boolean'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function costCategory() { return $this->belongsTo(CostCategory::class); }
    public function parent() { return $this->belongsTo(BoqItem::class, 'parent_id'); }
    public function children() { return $this->hasMany(BoqItem::class, 'parent_id'); }
    public function laborResources() { return $this->hasMany(LaborResource::class); }
    public function materialResources() { return $this->hasMany(MaterialResource::class); }
    public function equipmentResources() { return $this->hasMany(EquipmentResource::class); }
    public function ipcItems() { return $this->hasMany(IpcItem::class); }

    public function getTotalBudgetCostAttribute() {
        return $this->laborResources->sum('amount') + 
               $this->materialResources->sum('amount') + 
               $this->equipmentResources->sum('amount');
    }

    public function getProfitLossAttribute() {
        return $this->revenue_amount - $this->total_budget_cost;
    }

    public function getProfitMarginPercentageAttribute() {
        return $this->revenue_amount > 0 ? ($this->profit_loss / $this->revenue_amount) * 100 : 0;
    }

    public function getProfitLossStatusAttribute() {
        return $this->profit_loss >= 0 ? 'PROFIT' : 'LOSS';
    }
}
MODEL

# LaborResource, MaterialResource, EquipmentResource, Ipc, IpcItem Models
for model in LaborResource MaterialResource EquipmentResource Ipc IpcItem; do
    cat > app/Models/${model}.php << 'MODEL'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MODEL_NAME extends Model
{
    use HasFactory;
    protected $fillable = [];
}
MODEL
    sed -i "s/MODEL_NAME/${model}/g" app/Models/${model}.php
done

echo "✅ All models created!"
