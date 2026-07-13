<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_item_id',
        'description',
        'duration_days',
        'number_of_units',
        'total_hours',
        'rate_per_hour',
        'amount'
    ];

    protected $casts = [
        'duration_days' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
        'amount' => 'decimal:2'
    ];

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }
}
