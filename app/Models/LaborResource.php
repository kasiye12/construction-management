<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_item_id',
        'trade_name',
        'number_of_workers',
        'total_hours',
        'wage_per_day',
        'amount'
    ];

    protected $casts = [
        'number_of_workers' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'wage_per_day' => 'decimal:2',
        'amount' => 'decimal:2'
    ];

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }
}
