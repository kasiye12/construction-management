<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'code', 'name', 'description', 'display_order'];

    public function project() { return $this->belongsTo(Project::class); }
    public function boqItems() { return $this->hasMany(BoqItem::class); }
}
