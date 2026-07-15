<?php
namespace App\Helpers;

class NumberToWordsHelper
{
    /**
     * Convert number to words in standard financial format
     * Example: 618750 → "Six Hundred Eighteen Thousand Seven Hundred Fifty Ethiopian Birr Only"
     */
    public static function convert($number)
    {
        if ($number <= 0) {
            return 'Zero Ethiopian Birr Only';
        }

        $whole = floor($number);
        $cents = round(($number - $whole) * 100);

        $words = self::convertWholeNumber($whole);
        
        if ($cents > 0) {
            $words .= ' and ' . self::convertWholeNumber($cents) . ' Cents';
        }

        return $words . ' Ethiopian Birr Only';
    }

    /**
     * Convert whole number to words (standard financial format - no "and")
     */
    private static function convertWholeNumber($number)
    {
        if ($number == 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];
        
        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];
        
        $scales = ['', 'Thousand', 'Million', 'Billion'];

        $words = '';
        $scaleIndex = 0;

        while ($number > 0) {
            $part = $number % 1000;
            
            if ($part > 0) {
                $partWords = self::convertHundreds($part, $ones, $tens);
                $words = $partWords . ($scaleIndex > 0 ? ' ' . $scales[$scaleIndex] : '') . 
                         ($words ? ' ' . $words : '');
            }
            
            $number = floor($number / 1000);
            $scaleIndex++;
        }

        return trim($words);
    }

    /**
     * Convert hundreds part (0-999) to words
     * Standard format: "Six Hundred Eighteen" (no "and")
     */
    private static function convertHundreds($number, $ones, $tens)
    {
        $words = '';
        
        $hundreds = floor($number / 100);
        $remainder = $number % 100;

        // Hundreds
        if ($hundreds > 0) {
            $words .= $ones[$hundreds] . ' Hundred';
        }

        // Tens and ones
        if ($remainder > 0) {
            if ($hundreds > 0) {
                $words .= ' '; // Space only, no "and"
            }
            
            if ($remainder < 20) {
                $words .= $ones[$remainder];
            } else {
                $tenDigit = floor($remainder / 10);
                $oneDigit = $remainder % 10;
                $words .= $tens[$tenDigit];
                if ($oneDigit > 0) {
                    $words .= ' ' . $ones[$oneDigit];
                }
            }
        }

        return trim($words);
    }
}
