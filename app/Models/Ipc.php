<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ipc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'subcontractor_id',
        'ipc_number',
        'issue_number',
        'ipc_date',
        'period_start_date',
        'period_end_date',
        'total_previous_amount',
        'total_current_amount',
        'total_to_date_amount',
        'retention_percentage',
        'retention_amount',
        'net_payment_amount',
        'remarks',
        'status'
    ];

    protected $casts = [
        'ipc_date' => 'date',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'total_previous_amount' => 'decimal:2',
        'total_current_amount' => 'decimal:2',
        'total_to_date_amount' => 'decimal:2',
        'retention_percentage' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_payment_amount' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function ipcItems()
    {
        return $this->hasMany(IpcItem::class);
    }

    public function calculateTotals()
    {
        $this->total_current_amount = $this->ipcItems->sum('current_amount');
        $this->total_previous_amount = $this->ipcItems->sum('previous_amount');
        $this->total_to_date_amount = $this->total_previous_amount + $this->total_current_amount;
        $this->retention_amount = $this->total_to_date_amount * ($this->retention_percentage / 100);
        $this->net_payment_amount = $this->total_to_date_amount - $this->retention_amount;
        $this->save();
    }
}
