<?php
namespace App\Helpers;

use Carbon\Carbon;

class ConstructionHelper
{
    public static function calculateRebarWeight(float $diameter, float $length, int $numberOfBars): float
    {
        // Standard formula: W = (d²/162) × L × N
        return round(($diameter * $diameter / 162) * $length * $numberOfBars, 2);
    }

    public static function calculateConcreteVolume(float $length, float $width, float $height): float
    {
        return round($length * $width * $height, 3);
    }

    public static function calculateWaterproofingArea(float $length, float $width): float
    {
        return round($length * $width, 2);
    }

    public static function calculateProfitMargin(float $revenue, float $cost): float
    {
        if ($revenue <= 0) return 0;
        return round((($revenue - $cost) / $revenue) * 100, 2);
    }

    public static function formatCurrency(float $amount, string $currency = 'ETB'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    public static function generateCertificateNumber(string $prefix = 'IPC', int $projectId, int $sequence): string
    {
        return sprintf('%s-%03d-%04d', $prefix, $projectId, $sequence);
    }

    public static function amountToWords(float $number): string
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number)) . ' Ethiopian Birr Only';
    }

    public static function calculateRetention(float $amount, float $percentage = 5): float
    {
        return round($amount * ($percentage / 100), 2);
    }

    public static function calculateVAT(float $amount, float $percentage = 15): float
    {
        return round($amount * ($percentage / 100), 2);
    }
}
