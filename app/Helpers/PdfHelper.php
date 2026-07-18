<?php
namespace App\Helpers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

class PdfHelper
{
    /**
     * Get logo as base64 encoded string for PDF rendering
     */
    public static function getLogoBase64(): ?string
    {
        $logoPath = CompanySetting::get('company_logo');
        
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $fullPath = Storage::disk('public')->path($logoPath);
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $mime = $type == 'svg' ? 'image/svg+xml' : ('image/' . ($type == 'jpg' ? 'jpeg' : $type));
            $base64 = base64_encode(file_get_contents($fullPath));
            return "data:{$mime};base64,{$base64}";
        }
        
        return null;
    }

    /**
     * Get company settings for PDF views
     */
    public static function getCompanySettings(): array
    {
        return [
            'logo_base64' => self::getLogoBase64(),
            'name' => CompanySetting::get('company_name', 'TNT Construction and Trading'),
            'tagline' => CompanySetting::get('company_tagline', 'General Contractor & Engineering Services'),
            'phone' => CompanySetting::get('company_phone', '+251-000-000000'),
            'email' => CompanySetting::get('company_email', 'info@tnt-constructions.com'),
            'address' => CompanySetting::get('company_address', 'Addis Ababa, Ethiopia'),
            'tin' => CompanySetting::get('company_tin', '000000000'),
        ];
    }
}
