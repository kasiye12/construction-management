<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractualClaimEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractual_report_id',
        'event_date',
        'issue_number',
        'cause_description',
        'effect_on_progress',
        'action_taken',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function contractualReport()
    {
        return $this->belongsTo(ContractualReport::class);
    }
}
