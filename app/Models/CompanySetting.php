<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getLogoUrl()
    {
        $logo = self::get('company_logo');
        if ($logo && \Storage::disk('public')->exists($logo)) {
            return asset('storage/' . $logo);
        }
        return null;
    }

    public static function getAllGrouped()
    {
        return self::all()->groupBy('group');
    }
}
