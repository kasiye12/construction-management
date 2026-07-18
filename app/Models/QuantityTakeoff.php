<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class QuantityTakeoff extends Model
{
    use Auditable;
    
    protected $fillable = [
        'project_id', 'boq_item_id', 'structure_type', 'element_id',
        'location_axis', 'quantity_count', 'length', 'width', 'height_depth',
        'total_area_volume', 'measurement_date', 'measured_by', 'verified_by',
        'status', 'remarks'
    ];
    
    protected $casts = [
        'measurement_date' => 'date',
        'quantity_count' => 'integer',
        'length' => 'decimal:4',
        'width' => 'decimal:4',
        'height_depth' => 'decimal:4',
        'total_area_volume' => 'decimal:4',
    ];
    
    public function project() { return $this->belongsTo(Project::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
    
    // Auto-calculate total
    protected static function booted()
    {
        static::saving(function ($takeoff) {
            $takeoff->total_area_volume = $takeoff->quantity_count * 
                ($takeoff->length ?? 0) * ($takeoff->width ?? 1) * ($takeoff->height_depth ?? 1);
        });
    }
}
