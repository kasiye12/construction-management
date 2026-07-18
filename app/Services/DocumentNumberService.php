<?php
namespace App\Services;

use App\Models\DocumentNumber;

class DocumentNumberService
{
    /**
     * Generate next document number
     * Format: OF/TNT/ECD/012
     */
    public static function generate(string $type, int $projectId): string
    {
        $prefix = config('construction.document_prefix', 'OF/TNT/ECD');
        $year = date('Y');
        
        $docNumber = DocumentNumber::firstOrCreate(
            ['prefix' => $prefix, 'type' => $type, 'project_id' => $projectId, 'year' => $year],
            ['sequence_number' => 0]
        );
        
        $docNumber->increment('sequence_number');
        
        return $prefix . '/' . str_pad($docNumber->sequence_number, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get current sequence without incrementing
     */
    public static function preview(string $type, int $projectId): string
    {
        $prefix = config('construction.document_prefix', 'OF/TNT/ECD');
        $year = date('Y');
        
        $docNumber = DocumentNumber::where('prefix', $prefix)
            ->where('type', $type)
            ->where('project_id', $projectId)
            ->where('year', $year)
            ->first();
        
        $next = $docNumber ? $docNumber->sequence_number + 1 : 1;
        
        return $prefix . '/' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
