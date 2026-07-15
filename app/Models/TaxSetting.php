<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = ['key', 'display_name', 'rate', 'type', 'description', 'is_active'];
    protected $casts = ['rate' => 'decimal:2', 'is_active' => 'boolean'];

    public static function getRate(string $key): float
    {
        return (float) (self::where('key', $key)->where('is_active', true)->first()->rate ?? 0);
    }
    public static function vatRate(): float { return self::getRate('vat'); }
    public static function retentionRate(): float { return self::getRate('retention'); }
}
