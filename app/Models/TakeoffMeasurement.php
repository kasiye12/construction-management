<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeoffMeasurement extends Model
{
    protected $fillable = [
        'takeoff_description_id', 'quantity_count', 'length', 'width',
        'height_depth', 'description', 'total_area_volume', 'remarks', 'display_order'
    ];

    protected $casts = [
        'length' => 'decimal:4',
        'width' => 'decimal:4',
        'height_depth' => 'decimal:4',
        'total_area_volume' => 'decimal:4',
    ];

    public function takeoffDescription()
    {
        return $this->belongsTo(TakeoffDescription::class);
    }

    protected static function booted()
    {
        static::saving(function ($m) {
            $m->total_area_volume = $m->quantity_count * $m->length * ($m->width ?? 1) * ($m->height_depth ?? 1);
        });
    }
}
