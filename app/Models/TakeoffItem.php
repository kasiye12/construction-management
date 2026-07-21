<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeoffItem extends Model
{
    protected $fillable = [
        'takeoff_sheet_id', 'boq_item_id', 'item_number', 'description', 'display_order'
    ];

    public function takeoffSheet()
    {
        return $this->belongsTo(TakeoffSheet::class);
    }

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }

    public function descriptions()
    {
        return $this->hasMany(TakeoffDescription::class)->orderBy('side')->orderBy('display_order');
    }
}
