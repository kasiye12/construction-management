<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmScheduleItem extends Model
{
    use HasFactory;

    protected $table = 'pm_schedule_items';

    protected $fillable = [
        'pm_schedule_id',
        'equipment_id',
        'category',
        'equipment_type',
        'make',
        'model',
        'plate_serial_no',
        'serial_no',
        'location',
        'last_service_reading',
        'last_service_date',
        'last_service_type',
        'next_service_reading',
        'next_service_type',
        'current_reading',
        'current_reading_date',
        'remain_reading',
        'service_status',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'last_service_reading' => 'decimal:2',
        'next_service_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'remain_reading' => 'decimal:2',
        'last_service_date' => 'date',
        'current_reading_date' => 'date',
    ];

    public function schedule()
    {
        return $this->belongsTo(PmSchedule::class, 'pm_schedule_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            // Auto compute remain_reading
            if ($item->next_service_reading !== null && $item->current_reading !== null) {
                $item->remain_reading = (float) ($item->next_service_reading - $item->current_reading);
                
                // Auto compute service_status if not explicitly forced
                if ($item->remain_reading < 0) {
                    $item->service_status = 'Overdue';
                } elseif ($item->remain_reading <= 50) {
                    $item->service_status = 'Due';
                } else {
                    $item->service_status = 'Normal';
                }
            }
        });
    }

    public function getServiceStatusBadgeAttribute(): string
    {
        return match($this->service_status) {
            'Overdue' => '<span class="badge bg-danger">Overdue</span>',
            'Due' => '<span class="badge bg-warning text-dark">Due</span>',
            'Completed' => '<span class="badge bg-info text-dark">Completed</span>',
            default => '<span class="badge bg-success">Normal</span>',
        };
    }
}
