<?php
namespace App\Helpers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

class LogoHelper
{
    /**
     * Get logo as base64 encoded string for PDF rendering
     */
    public static function getLogoBase64(): ?string
    {
        $logoPath = CompanySetting::get('company_logo');
        
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $fullPath = Storage::disk('public')->path($logoPath);
            $mimeType = mime_content_type($fullPath);
            $base64 = base64_encode(file_get_contents($fullPath));
            return "data:{$mimeType};base64,{$base64}";
        }
        
        return null;
    }
    
    /**
     * Get logo URL for web display
     */
    public static function getLogoUrl(): ?string
    {
        $logoPath = CompanySetting::get('company_logo');
        
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return asset('storage/' . $logoPath);
        }
        
        return null;
    }
}
