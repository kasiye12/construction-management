<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeoffDescription extends Model
{
    protected $fillable = [
        'takeoff_item_id', 'side', 'description', 'display_order'
    ];

    public function takeoffItem() { return $this->belongsTo(TakeoffItem::class); }
    public function measurements() { return $this->hasMany(TakeoffMeasurement::class)->orderBy('display_order'); }
    
    public function getTotalAreaAttribute()
    {
        return $this->measurements->sum('total_area_volume');
    }
}
