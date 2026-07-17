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
        'status',
        // Workflow fields
        'prepared_by', 'prepared_at',
        'checked_by', 'checked_at',
        'submitted_by', 'submitted_at',
        'approved_by', 'approved_at',
        'rejected_by', 'rejected_at',
        'paid_by', 'paid_at',
    ];

    protected $casts = [
        'ipc_date' => 'date',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'prepared_at' => 'datetime',
        'checked_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_previous_amount' => 'decimal:2',
        'total_current_amount' => 'decimal:2',
        'total_to_date_amount' => 'decimal:2',
        'retention_percentage' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_payment_amount' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function subcontractor() { return $this->belongsTo(Subcontractor::class); }
    public function ipcItems() { return $this->hasMany(IpcItem::class); }
}
