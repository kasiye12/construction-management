<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $fillable = [
        'auditable_type', 'auditable_id', 'user_id', 'action',
        'field_name', 'old_value', 'new_value', 'ip_address', 'user_agent'
    ];

    public function auditable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }
}
