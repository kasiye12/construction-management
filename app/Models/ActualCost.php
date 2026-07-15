<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActualCost extends Model
{
    protected $fillable = [
        'project_id', 'boq_item_id', 'cost_type', 'description',
        'amount', 'cost_date', 'vendor', 'invoice_number', 'remarks', 'created_by'
    ];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function boqItem() { return $this->belongsTo(BoqItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
