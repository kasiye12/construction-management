<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractualClaimCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractual_report_id',
        'category',
        'category_title',
        'item_code',
        'item_title',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'sort_order',
    ];

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
    ];

    public function contractualReport()
    {
        return $this->belongsTo(ContractualReport::class);
    }

    public function hasAnyYes(): bool
    {
        return $this->monday || $this->tuesday || $this->wednesday ||
               $this->thursday || $this->friday || $this->saturday || $this->sunday;
    }

    public function getActiveDaysAttribute(): array
    {
        $days = [];
        if ($this->monday) $days[] = 'Mon';
        if ($this->tuesday) $days[] = 'Tue';
        if ($this->wednesday) $days[] = 'Wed';
        if ($this->thursday) $days[] = 'Thu';
        if ($this->friday) $days[] = 'Fri';
        if ($this->saturday) $days[] = 'Sat';
        if ($this->sunday) $days[] = 'Sun';
        return $days;
    }
}
