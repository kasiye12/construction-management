<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProjectImportExportService
{
    /**
     * Import projects from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param UploadedFile|string $file
     * @param bool $updateExisting
     * @param int|null $userId
     * @return array
     */
    public function importFromFile($file, bool $updateExisting = true, ?int $userId = null): array
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getRealPath() : $file;

        if (!file_exists($filePath)) {
            throw new \Exception("File not found at {$filePath}");
        }

        // Use PhpSpreadsheet reader to load CSV or Excel seamlessly
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, false);

        if (empty($rawRows)) {
            throw new \Exception("The uploaded file appears to be completely empty.");
        }

        // 1. Locate the header row
        $headerRowIndex = null;
        $columnMap = [];

        foreach ($rawRows as $rIndex => $row) {
            $normalizedRow = array_map(function ($col) {
                return strtolower(trim((string) $col));
            }, $row);

            // Look for row containing "project name" or "name"
            foreach ($normalizedRow as $cIndex => $val) {
                if (in_array($val, ['project name', 'name', 'project', 'project_name'])) {
                    $headerRowIndex = $rIndex;
                    break 2;
                }
            }
        }

        if ($headerRowIndex === null) {
            // Fallback: check if row 0, 1, or 2 has headers
            $headerRowIndex = 0;
            if (isset($rawRows[2]) && count(array_filter($rawRows[2])) > 3) {
                $headerRowIndex = 2;
            } elseif (isset($rawRows[1]) && count(array_filter($rawRows[1])) > 3) {
                $headerRowIndex = 1;
            }
        }

        // 2. Build column mapping from header row
        $headerRow = $rawRows[$headerRowIndex];
        foreach ($headerRow as $cIndex => $headerText) {
            $headerClean = strtolower(trim((string) $headerText));
            if (empty($headerClean)) continue;

            if (in_array($headerClean, ['no', 'no.', '#', 'item', 'item no', 'sn', 's/n', 'project code', 'code'])) {
                $columnMap['code'] = $cIndex;
            } elseif (in_array($headerClean, ['project name', 'project', 'name', 'project_name', 'project title'])) {
                $columnMap['name'] = $cIndex;
            } elseif (str_contains($headerClean, 'contract amount') || str_contains($headerClean, 'amount') || str_contains($headerClean, 'budget') || str_contains($headerClean, 'value')) {
                $columnMap['contract_amount'] = $cIndex;
            } elseif (str_contains($headerClean, 'tnt share') || str_contains($headerClean, 'share') || str_contains($headerClean, 'percentage')) {
                $columnMap['tnt_share'] = $cIndex;
            } elseif (str_contains($headerClean, 'signing date') || str_contains($headerClean, 'contract date') || str_contains($headerClean, 'start date') || str_contains($headerClean, 'signing')) {
                $columnMap['contract_signing_date'] = $cIndex;
            } elseif (str_contains($headerClean, 'client') || str_contains($headerClean, 'employer') || str_contains($headerClean, 'owner')) {
                $columnMap['client_name'] = $cIndex;
            } elseif (str_contains($headerClean, 'consultant') || str_contains($headerClean, 'engineer') || str_contains($headerClean, 'supervisor')) {
                $columnMap['consultant_name'] = $cIndex;
            } elseif (str_contains($headerClean, 'location') || str_contains($headerClean, 'site') || str_contains($headerClean, 'place') || str_contains($headerClean, 'city')) {
                $columnMap['location'] = $cIndex;
            } elseif (str_contains($headerClean, 'contractor')) {
                $columnMap['contractor_name'] = $cIndex;
            } elseif (str_contains($headerClean, 'status')) {
                $columnMap['status'] = $cIndex;
            } elseif (str_contains($headerClean, 'description') || str_contains($headerClean, 'remarks') || str_contains($headerClean, 'scope')) {
                $columnMap['description'] = $cIndex;
            }
        }

        // If name wasn't mapped, try positional default or first non-empty column after No
        if (!isset($columnMap['name'])) {
            foreach ($headerRow as $cIndex => $val) {
                if ($cIndex !== ($columnMap['code'] ?? -1) && !empty($val)) {
                    $columnMap['name'] = $cIndex;
                    break;
                }
            }
        }

        if (!isset($columnMap['name'])) {
            throw new \Exception("Could not detect the 'Project Name' column in the uploaded file header row ({$headerRowIndex}).");
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $processedProjects = [];

        $effectiveUserId = $userId ?: Auth::id();

        // 3. Process data rows starting immediately after headerRowIndex
        DB::beginTransaction();
        try {
            for ($i = $headerRowIndex + 1; $i < count($rawRows); $i++) {
                $row = $rawRows[$i];

                // Check for empty row
                if (empty(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                    continue;
                }

                $nameVal = isset($columnMap['name']) ? trim((string)($row[$columnMap['name']] ?? '')) : '';

                // Skip summary or total rows
                if (empty($nameVal)) {
                    continue;
                }
                $lowerName = strtolower($nameVal);
                if (str_starts_with($lowerName, 'total') || str_contains($lowerName, 'total contract value') || str_contains($lowerName, 'summary')) {
                    continue;
                }

                // Parse Code
                $codeVal = null;
                if (isset($columnMap['code']) && isset($row[$columnMap['code']])) {
                    $rawCode = trim((string)$row[$columnMap['code']]);
                    if (!empty($rawCode) && !str_starts_with(strtolower($rawCode), 'total')) {
                        $codeVal = is_numeric($rawCode) ? "PRJ-" . str_pad($rawCode, 3, '0', STR_PAD_LEFT) : $rawCode;
                    }
                }

                // Parse Contract Amount
                $contractAmount = 0.00;
                if (isset($columnMap['contract_amount']) && isset($row[$columnMap['contract_amount']])) {
                    $rawAmount = (string)$row[$columnMap['contract_amount']];
                    // Strip commas, spaces, currency symbols
                    $cleanedAmount = preg_replace('/[^\d.]/', '', str_replace(',', '', $rawAmount));
                    if (is_numeric($cleanedAmount)) {
                        $contractAmount = (float)$cleanedAmount;
                    }
                }

                // Parse TNT Share (%)
                $tntShare = 100.00;
                if (isset($columnMap['tnt_share']) && isset($row[$columnMap['tnt_share']])) {
                    $rawShare = trim((string)$row[$columnMap['tnt_share']]);
                    if ($rawShare !== '') {
                        $cleanShare = str_replace(['%', ' '], '', $rawShare);
                        if (is_numeric($cleanShare)) {
                            $floatShare = (float)$cleanShare;
                            // If entered as 1 or 0.95 or 0.33, convert to percentage 100, 95, 33
                            if ($floatShare <= 1.0 && $floatShare > 0) {
                                $tntShare = $floatShare * 100.0;
                            } else {
                                $tntShare = $floatShare;
                            }
                        }
                    }
                }

                // Parse Signing Date & Start Date
                $signingDate = null;
                if (isset($columnMap['contract_signing_date']) && isset($row[$columnMap['contract_signing_date']])) {
                    $rawDate = trim((string)$row[$columnMap['contract_signing_date']]);
                    if (!empty($rawDate)) {
                        try {
                            if (is_numeric($rawDate) && (int)$rawDate > 1000) {
                                // Excel timestamp serial number
                                $dateTimeObj = ExcelDate::excelToDateTimeObject((int)$rawDate);
                                $signingDate = Carbon::instance($dateTimeObj)->toDateString();
                            } else {
                                $signingDate = Carbon::parse($rawDate)->toDateString();
                            }
                        } catch (\Throwable $e) {
                            $signingDate = null;
                        }
                    }
                }

                // Parse Client, Consultant, Location, Contractor
                $clientName = isset($columnMap['client_name']) ? trim((string)($row[$columnMap['client_name']] ?? '')) : null;
                $consultantName = isset($columnMap['consultant_name']) ? trim((string)($row[$columnMap['consultant_name']] ?? '')) : null;
                $location = isset($columnMap['location']) ? trim((string)($row[$columnMap['location']] ?? '')) : null;
                $contractorName = isset($columnMap['contractor_name']) ? trim((string)($row[$columnMap['contractor_name']] ?? '')) : null;
                if (empty($contractorName)) {
                    $contractorName = 'TNT Construction and Trading';
                }

                $status = 'active';
                if (isset($columnMap['status']) && isset($row[$columnMap['status']])) {
                    $rawStatus = strtolower(trim((string)$row[$columnMap['status']]));
                    if (in_array($rawStatus, ['active', 'completed', 'on_hold', 'cancelled'])) {
                        $status = $rawStatus;
                    }
                }

                $description = isset($columnMap['description']) ? trim((string)($row[$columnMap['description']] ?? '')) : null;

                // Check existing project by exact name or code
                $existingProject = Project::where('name', $nameVal)
                    ->orWhere(function ($q) use ($codeVal) {
                        if ($codeVal) $q->where('project_code', $codeVal);
                    })
                    ->first();

                $projectData = [
                    'project_code' => $codeVal ?: ($existingProject?->project_code),
                    'name' => $nameVal,
                    'location' => $location ?: ($existingProject?->location),
                    'client_name' => $clientName ?: ($existingProject?->client_name),
                    'consultant_name' => $consultantName ?: ($existingProject?->consultant_name),
                    'contractor_name' => $contractorName ?: ($existingProject?->contractor_name ?? 'TNT Construction and Trading'),
                    'contract_signing_date' => $signingDate ?: ($existingProject?->contract_signing_date),
                    'start_date' => $signingDate ?: ($existingProject?->start_date),
                    'contract_amount' => $contractAmount > 0 ? $contractAmount : ($existingProject?->contract_amount ?? 0),
                    'tnt_share' => $tntShare,
                    'status' => $status,
                    'description' => $description ?: ($existingProject?->description),
                ];

                if ($existingProject) {
                    if ($updateExisting) {
                        $existingProject->update($projectData);
                        $updatedCount++;
                        $processedProjects[] = $existingProject;
                    } else {
                        $skippedCount++;
                    }
                    $targetProject = $existingProject;
                } else {
                    $targetProject = Project::create($projectData);
                    $importedCount++;
                    $processedProjects[] = $targetProject;
                }

                // Assign importing user to project if not already assigned
                if ($effectiveUserId && $targetProject) {
                    $isAssigned = DB::table('project_user')
                        ->where('project_id', $targetProject->id)
                        ->where('user_id', $effectiveUserId)
                        ->exists();

                    if (!$isAssigned) {
                        $targetProject->teamMembers()->attach($effectiveUserId, [
                            'role' => 'project_manager',
                            'assigned_date' => now(),
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // Log activity
            if ($effectiveUserId) {
                ActivityLog::create([
                    'user_id' => $effectiveUserId,
                    'action' => 'imported',
                    'module' => 'Project',
                    'description' => "Imported {$importedCount} new projects, updated {$updatedCount}, skipped {$skippedCount}.",
                    'new_values' => json_encode(['imported' => $importedCount, 'updated' => $updatedCount, 'skipped' => $skippedCount]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'total_processed' => $importedCount + $updatedCount + $skippedCount,
            'imported_count' => $importedCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
            'projects' => $processedProjects,
            'errors' => $errors,
        ];
    }

    /**
     * Generate an Excel or CSV template file for downloading.
     *
     * @param string $format 'xlsx' or 'csv'
     * @return string Absolute file path
     */
    public function generateTemplate(string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Basic Project Data');

        if ($format === 'xlsx') {
            // Title Banner
            $sheet->setCellValue('A1', 'TNT CONSTRUCTION AND TRADING - MASTER PROJECT DATA IMPORT');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(1)->setRowHeight(32);

            // Subtitle
            $sheet->setCellValue('A2', 'Official import template for ERP Projects Master Registry (Accepts .xlsx, .xls, and .csv)');
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('475569');
            $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Headers on Row 3
            $headers = [
                'A3' => 'No',
                'B3' => 'Project Name',
                'C3' => 'Contract Amount (ETB)',
                'D3' => 'TNT Share (%)',
                'E3' => 'Contract Signing date',
                'F3' => 'Client',
                'G3' => 'Consultant',
                'H3' => 'Project Location',
                'I3' => 'Status'
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A3:I3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A3:I3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
            $sheet->getStyle('A3:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(3)->setRowHeight(26);

            // Sample rows based on real company projects
            $sampleData = [
                ['1', 'Bole Lemhi Phase - I Water Supply Line', 123898313.93, '100%', '2018-02-05', 'Industrial Parks Development Corporation', 'EEC - Water & Energy Sector', 'Addis Ababa', 'active'],
                ['2', 'Construction of Commercial Complex Building (Lot 4)', 490179128.52, '100%', '2018-05-09', 'Addis Ababa Science and Technology University', 'Acute Engineering PLC', 'Addis Ababa', 'active'],
                ['3', 'Construction of Ambo University Teaching and Referral Hospital', 1633436050.80, '100%', '2018-09-25', 'Ambo University', 'EEC - Building & Urban Sector', 'Ambo', 'active'],
                ['4', 'Construction of Debre Markos University Teaching Hospital', 2437794972.80, '95%', '2021-04-12', 'Debre Markos University', 'EEC - Building & Urban Sector', 'Debre Markos', 'active'],
                ['5', 'EWP1 – Earthworks Package for Bishoftu International Airport (Central Area)', 40957667635.73, '33%', '2025-10-20', 'Ethiopian Airlines Group', 'Dar Al-Handasah Consultants', 'Bishoftu', 'active'],
            ];

            $rowIdx = 4;
            foreach ($sampleData as $row) {
                $colIdx = 1;
                foreach ($row as $val) {
                    $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $val);
                    $colIdx++;
                }
                $rowIdx++;
            }

            // Formatting columns
            $sheet->getStyle('C4:C8')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A4:A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D4:D8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E4:E8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I4:I8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $tempPath = storage_path('app/templates/TNT_Project_Import_Template.xlsx');
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0777, true);
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($tempPath);

            return $tempPath;
        } else {
            // Generate standard CSV matching user's format
            $csvHeaders = ['No', 'Project Name', 'Contract Amount', 'TNT Share (%)', 'Contract Signing date', 'Client', 'Consultant', 'Project Location', 'Status'];
            $sampleRows = [
                ['1', 'Bole Lemhi Phase - I Water Supply Line', '123898313.93', '1', '2018-02-05', 'Industrial Parks Development Corporation', 'EEC - Water & Energy Sector', 'Addis Ababa', 'active'],
                ['2', 'Construction of Commercial Complex Building (Lot 4)', '490179128.52', '1', '2018-05-09', 'Addis Ababa Science and Technology University', 'Acute Engineering PLC', 'Addis Ababa', 'active'],
                ['3', 'Construction of Debre Markos University Teaching Hospital', '2437794972.8', '0.95', '2021-04-12', 'Debre Markos University', 'EEC - Building & Urban Sector', 'Debre Markos', 'active'],
                ['4', 'EWP1 – Earthworks Package for Bishoftu International Airport (Central Area)', '40957667635.73', '0.33', '2025-10-20', 'Ethiopian Airlines Group', 'Dar Al-Handasah Consultants', 'Bishoftu', 'active'],
            ];

            $tempPath = storage_path('app/templates/TNT_Project_Import_Template.csv');
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0777, true);
            }

            $fp = fopen($tempPath, 'w');
            fputcsv($fp, ['Basic Project Data', '', '', '', '', '', '', '', '']);
            fputcsv($fp, ['', '', '', '', '', '', '', '', '']);
            fputcsv($fp, $csvHeaders);
            foreach ($sampleRows as $r) {
                fputcsv($fp, $r);
            }
            fclose($fp);

            return $tempPath;
        }
    }
}
