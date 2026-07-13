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
