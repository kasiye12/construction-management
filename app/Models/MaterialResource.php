<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_item_id',
        'description',
        'unit',
        'quantity',
        'unit_rate',
        'amount'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_rate' => 'decimal:2',
        'amount' => 'decimal:2'
    ];

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }
}
