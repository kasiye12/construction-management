<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpcItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ipc_id',
        'boq_item_id',
        'contract_quantity',
        'contract_amount',
        'previous_quantity',
        'previous_amount',
        'current_quantity',
        'current_amount',
        'to_date_quantity',
        'to_date_amount',
        'percentage_complete',
        'remark'
    ];

    protected $casts = [
        'contract_quantity' => 'decimal:4',
        'contract_amount' => 'decimal:2',
        'previous_quantity' => 'decimal:4',
        'previous_amount' => 'decimal:2',
        'current_quantity' => 'decimal:4',
        'current_amount' => 'decimal:2',
        'to_date_quantity' => 'decimal:4',
        'to_date_amount' => 'decimal:2',
        'percentage_complete' => 'decimal:2'
    ];

    public function ipc()
    {
        return $this->belongsTo(Ipc::class);
    }

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }

    public function calculateAmount()
    {
        $boqItem = $this->boqItem;
        if ($boqItem) {
            $this->current_amount = $this->current_quantity * $boqItem->unit_rate;
            $this->to_date_quantity = $this->previous_quantity + $this->current_quantity;
            $this->to_date_amount = $this->previous_amount + $this->current_amount;
            
            if ($this->contract_quantity > 0) {
                $this->percentage_complete = ($this->to_date_quantity / $this->contract_quantity) * 100;
            }
        }
    }
}
