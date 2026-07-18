<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = ['key', 'display_name', 'rate', 'type', 'description', 'is_active'];
    protected $casts = ['rate' => 'decimal:2', 'is_active' => 'boolean'];

    /**
     * Get active rate by key
     */
    public static function getRate(string $key): float
    {
        $setting = self::where('key', $key)->where('is_active', true)->first();
        return $setting ? (float) $setting->rate : 0;
    }

    /**
     * Get VAT rate
     */
    public static function vatRate(): float
    {
        return self::getRate('vat');
    }

    /**
     * Get Retention rate
     */
    public static function retentionRate(): float
    {
        return self::getRate('retention');
    }

    /**
     * Get Withholding Tax rate
     */
    public static function withholdingTaxRate(): float
    {
        return self::getRate('withholding_tax');
    }

    /**
     * Calculate tax amount
     */
    public static function calculateTax(float $amount, string $taxKey): float
    {
        $rate = self::getRate($taxKey);
        return $amount * ($rate / 100);
    }

    /**
     * Get all active settings
     */
    public static function getActiveSettings()
    {
        return self::where('is_active', true)->get();
    }
}
