<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeoffSheet extends Model
{
    protected $fillable = [
        'project_id', 'sheet_number', 'division', 'page_no',
        'measurement_date', 'measured_by', 'verified_by', 'approved_by',
        'status', 'remarks'
    ];

    protected $casts = [
        'measurement_date' => 'date',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function items() { return $this->hasMany(TakeoffItem::class)->orderBy('display_order'); }
}
