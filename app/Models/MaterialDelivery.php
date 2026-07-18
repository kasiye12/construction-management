<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class MaterialDelivery extends Model
{
    use Auditable;
    
    protected $fillable = [
        'project_id', 'subcontractor_id', 'boq_item_id',
        'item_description', 'unit', 'quantity', 'unit_multiplier',
        'converted_quantity', 'gate_pass_number', 'delivery_date',
        'source_location', 'remarks', 'created_by',
        'status', 'confirmed_by', 'confirmed_at'
    ];
    
    protected $casts = [
        'delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'quantity' => 'decimal:4',
        'unit_multiplier' => 'decimal:4',
        'converted_quantity' => 'decimal:4',
    ];
    
    public function project() { return $this->belongsTo(Project::class); }
    public function subcontractor() { return $this->belongsTo(Subcontractor::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
    
    // Auto-calculate converted quantity
    protected static function booted()
    {
        static::saving(function ($delivery) {
            $delivery->converted_quantity = $delivery->quantity * $delivery->unit_multiplier;
        });
    }
}
