<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\PmSchedule;
use App\Models\PmScheduleItem;
use App\Models\EquipmentBreakdownReport;
use App\Models\EquipmentBreakdownItem;
use App\Models\EquipmentMaintenanceLog;
use App\Models\Project;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EquipmentMaintenanceExcelService
{
    protected string $templatePath;

    public function __construct()
    {
        $this->templatePath = storage_path('app/templates/TNT-OF-EAMD-074-Preventive-Maintenance-Schedule.xlsx');
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Export PM Schedule (OF/EAMD/074)
     */
    public function exportPmSchedule(PmSchedule $schedule): string
    {
        $spreadsheet = file_exists($this->templatePath) 
            ? IOFactory::load($this->templatePath) 
            : new Spreadsheet();

        // Keep or set active sheet 0 (PM SCHEDULE)
        $ws = $spreadsheet->getSheetByName('PM SCHEDULE') ?? $spreadsheet->getActiveSheet();
        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($ws));

        // Document Info
        $ws->setCellValue('O2', $schedule->document_code ?: 'OF/EAMD/074');
        $ws->setCellValue('O5', $schedule->issue_no ?: '1');
        $ws->setCellValue('Q5', $schedule->page_no ?: 'Page 1 of 1');
        
        // Optional location/period in header
        if ($schedule->project_location) {
            $ws->setCellValue('H4', 'Location: ' . $schedule->project_location);
        }

        // Write Schedule Items starting from row 8
        $row = 8;
        $itemsGrouped = $schedule->items->groupBy('category');
        
        $categoriesOrder = [
            'EARTH MOVING MACHINE' => 'EARTH MOVING MACHINE',
            'DUMP TRUCK' => 'HEAVY TRUCKS / DUMP TRUCK',
            'LIGHT VEHICLES' => 'LIGHT VEHICLES',
            'AUXILIARY' => 'AUXILIARY & PLANT EQUIPMENT',
        ];

        $categories = array_unique(array_merge(array_keys($categoriesOrder), $itemsGrouped->keys()->toArray()));

        foreach ($categories as $catKey) {
            $catItems = $itemsGrouped[$catKey] ?? collect();
            $catTitle = $categoriesOrder[$catKey] ?? $catKey;

            // Category header row
            $ws->setCellValue("B{$row}", $catTitle);
            $ws->getStyle("B{$row}:R{$row}")->getFont()->setBold(true);
            $ws->getStyle("B{$row}:R{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
            $row++;

            if ($catItems->isEmpty()) {
                // Add empty placeholder row
                $ws->setCellValue("B{$row}", '-');
                $ws->setCellValue("C{$row}", 'None recorded');
                $row++;
                continue;
            }

            $no = 1;
            foreach ($catItems as $item) {
                $ws->setCellValue("B{$row}", $no++);
                $ws->setCellValue("C{$row}", $item->equipment_type);
                $ws->setCellValue("D{$row}", $item->make);
                $ws->setCellValue("E{$row}", $item->model);
                $ws->setCellValue("F{$row}", $item->plate_serial_no);
                $ws->setCellValue("G{$row}", $item->serial_no);
                $ws->setCellValue("H{$row}", $item->location);

                // Last Service
                if ($item->last_service_reading !== null) $ws->setCellValue("I{$row}", (float)$item->last_service_reading);
                if ($item->last_service_date) $ws->setCellValue("J{$row}", $item->last_service_date->format('d/m/Y'));
                $ws->setCellValue("K{$row}", $item->last_service_type);

                // Next Service
                if ($item->next_service_reading !== null) $ws->setCellValue("L{$row}", (float)$item->next_service_reading);
                $ws->setCellValue("M{$row}", $item->next_service_type);

                // Current Reading
                if ($item->current_reading !== null) $ws->setCellValue("N{$row}", (float)$item->current_reading);
                if ($item->current_reading_date) $ws->setCellValue("O{$row}", $item->current_reading_date->format('d/m/Y'));

                // Remain Formula
                $ws->setCellValue("P{$row}", "=L{$row}-N{$row}");
                
                // Status
                $ws->setCellValue("Q{$row}", $item->service_status);
                $ws->setCellValue("R{$row}", $item->remark);

                // Highlight Overdue / Due
                if ($item->service_status === 'Overdue') {
                    $ws->getStyle("Q{$row}")->getFont()->getColor()->setARGB('FFFF0000');
                    $ws->getStyle("Q{$row}")->getFont()->setBold(true);
                } elseif ($item->service_status === 'Due') {
                    $ws->getStyle("Q{$row}")->getFont()->getColor()->setARGB('FFB8860B');
                    $ws->getStyle("Q{$row}")->getFont()->setBold(true);
                }

                $row++;
            }
        }

        // Add service type reference legend table at bottom
        $row += 2;
        $ws->setCellValue("L{$row}", "Service Type Reference Legend");
        $ws->getStyle("L{$row}")->getFont()->setBold(true);
        $row++;
        $ws->setCellValue("L{$row}", "Earth Moving / Heavy: Type A (250 hr), Type B (500 hr), Type C (1,000 hr), Type D (2,000 hr)");
        $row++;
        $ws->setCellValue("L{$row}", "Light Vehicles: Type A2 (5,000 km), Type B2 (10,000 km), Type C2 (20,000 km), Type D2 (40,000 km)");
        
        // Sign-offs
        $row += 2;
        $prep = "Prepared By: " . ($schedule->prepared_name ?: ($schedule->preparedByUser?->name ?: '_____________'));
        if ($schedule->prepared_at) $prep .= " (" . $schedule->prepared_at->format('d/m/Y') . ")";

        $chk = "Checked By: " . ($schedule->checked_name ?: ($schedule->checkedByUser?->name ?: '_____________'));
        if ($schedule->checked_at) $chk .= " (" . $schedule->checked_at->format('d/m/Y') . ")";

        $appr = "Approved By: " . ($schedule->approved_name ?: ($schedule->approvedByUser?->name ?: '_____________'));
        if ($schedule->approved_at) $appr .= " (" . $schedule->approved_at->format('d/m/Y') . ")";

        $ws->setCellValue("B{$row}", $prep);
        $ws->setCellValue("H{$row}", $chk);
        $ws->setCellValue("N{$row}", $appr);
        $ws->getStyle("B{$row}:R{$row}")->getFont()->setBold(true);

        $tempPath = tempnam(sys_get_temp_dir(), 'PMS_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Export Maintenance Breakdown Report (OF/TNT/ EAMD/030)
     */
    public function exportBreakdownReport(EquipmentBreakdownReport $report): string
    {
        $spreadsheet = file_exists($this->templatePath) 
            ? IOFactory::load($this->templatePath) 
            : new Spreadsheet();

        $ws = $spreadsheet->getSheetByName('MAINTENANCE REPORT') ?? $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($ws));

        $ws->setCellValue('J2', $report->document_code ?: 'OF/TNT/ EAMD/030');
        $ws->setCellValue('J4', $report->issue_no ?: '1');
        $ws->setCellValue('K4', $report->page_no ?: 'Page 1 of 1');
        $ws->setCellValue('A5', $report->report_date ? $report->report_date->format('d/m/Y') : date('d/m/Y'));

        $row = 9;
        $grouped = $report->items->groupBy('category');

        $categories = [
            'EARTH MOVING MACHINE AND PLANT' => 'EARTH MOVING MACHINE AND PLANT',
            'DUMP TRUCK' => 'DUMP TRUCK',
            'LIGHT VEHICLES' => 'LIGHT VEHICLES',
            'AUXILIARY' => 'AUXILIARY',
            'INSURANCE CASE' => 'INSURANCE CASE',
        ];

        foreach ($categories as $catKey => $catTitle) {
            $catItems = $grouped[$catKey] ?? collect();

            $ws->setCellValue("A{$row}", $catTitle);
            $ws->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);
            $ws->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $row++;

            if ($catItems->isEmpty()) {
                $ws->setCellValue("A{$row}", '-');
                $ws->setCellValue("B{$row}", 'None recorded');
                $row++;
                continue;
            }

            $no = 1;
            foreach ($catItems as $item) {
                $ws->setCellValue("A{$row}", $no++);
                $ws->setCellValue("B{$row}", $item->equipment_type);
                $ws->setCellValue("C{$row}", $item->quantity ?: 1);
                $ws->setCellValue("D{$row}", $item->plate_no_sn);
                $ws->setCellValue("E{$row}", $item->down_date ? $item->down_date->format('d/m/Y') : '');
                $ws->setCellValue("F{$row}", $item->reason_of_down);
                $ws->setCellValue("H{$row}", $item->action_taken);
                $ws->setCellValue("I{$row}", $item->location);
                $ws->setCellValue("J{$row}", $item->repair_status);
                $ws->setCellValue("K{$row}", $item->finished_date ? $item->finished_date->format('d/m/Y') : '');
                $row++;
            }
        }

        $row++;
        // Sign-offs
        $prep = "PREPARED BY:- " . ($report->prepared_name ?: ($report->preparedByUser?->name ?: '_____________'));
        if ($report->prepared_at) $prep .= " (" . $report->prepared_at->format('d/m/Y') . ")";

        $chk = "CHECKED BY:- " . ($report->checked_name ?: ($report->checkedByUser?->name ?: '_____________'));
        if ($report->checked_at) $chk .= " (" . $report->checked_at->format('d/m/Y') . ")";

        $appr = "APPROVED BY:- " . ($report->approved_name ?: ($report->approvedByUser?->name ?: '_____________'));
        if ($report->approved_at) $appr .= " (" . $report->approved_at->format('d/m/Y') . ")";

        $ws->setCellValue("A{$row}", $prep);
        $ws->setCellValue("F{$row}", $chk);
        $ws->setCellValue("I{$row}", $appr);
        $ws->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);

        $tempPath = tempnam(sys_get_temp_dir(), 'MR_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Export Daily Maintenance & Spare Parts Log
     */
    public function exportDailyReport(Collection $logs, ?string $title = 'Daily Equipment Maintenance & Cost Report'): string
    {
        $spreadsheet = file_exists($this->templatePath) 
            ? IOFactory::load($this->templatePath) 
            : new Spreadsheet();

        $ws = $spreadsheet->getSheetByName('DAILY REPORT') ?? $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($ws));

        $ws->setCellValue('A1', $title ?: 'Daily Record');
        $ws->setCellValue('A5', 'Daily Report generated ' . date('d/m/Y H:i'));

        $row = 10;
        $no = 1;
        $startRow = $row;

        foreach ($logs as $log) {
            $ws->setCellValue("A{$row}", $no++);
            $ws->setCellValue("B{$row}", $log->equipment_type);
            $ws->setCellValue("C{$row}", $log->equipment_plate);
            $ws->setCellValue("D{$row}", $log->spare_part_name);
            $ws->setCellValue("F{$row}", $log->po_no);
            $ws->setCellValue("G{$row}", $log->unit);
            $ws->setCellValue("H{$row}", (float)$log->quantity);
            $ws->setCellValue("I{$row}", (float)$log->unit_cost);
            $ws->setCellValue("J{$row}", (float)$log->total_cost_with_vat);
            $ws->setCellValue("K{$row}", $log->supplier);
            $ws->setCellValue("L{$row}", $log->log_date ? $log->log_date->format('d/m/Y') : '');
            $row++;
        }

        // Total
        $endRow = $row - 1;
        $ws->setCellValue("I{$row}", "TOTAL");
        $ws->setCellValue("J{$row}", "=SUM(J{$startRow}:J{$endRow})");
        $ws->getStyle("I{$row}:J{$row}")->getFont()->setBold(true);

        $tempPath = tempnam(sys_get_temp_dir(), 'DMR_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Import and populate fleet from PM Schedule / FORMAT 1
     */
    public function importPmSchedule(UploadedFile $file, ?int $projectId, int $userId): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $ws = $spreadsheet->getSheetByName('PM SCHEDULE') ?? $spreadsheet->getSheet(0);

        $scheduleCode = 'PMS-' . date('Ymd-His');
        $schedule = PmSchedule::create([
            'schedule_code' => $scheduleCode,
            'title' => 'Preventive Maintenance Schedule',
            'document_code' => trim((string)$ws->getCell('O2')->getValue()) ?: 'OF/EAMD/074',
            'issue_no' => trim((string)$ws->getCell('O5')->getValue()) ?: '1',
            'page_no' => trim((string)$ws->getCell('Q5')->getValue()) ?: 'Page 1 of 1',
            'project_id' => $projectId,
            'schedule_date' => now()->toDateString(),
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        $highestRow = $ws->getHighestRow();
        $currentCategory = 'EARTH MOVING MACHINE';
        $importedCount = 0;
        $equipmentCreatedOrUpdated = 0;

        for ($r = 8; $r <= $highestRow; $r++) {
            $colB = trim((string)$ws->getCell("B{$r}")->getValue());
            $colC = trim((string)$ws->getCell("C{$r}")->getValue());
            $colD = trim((string)$ws->getCell("D{$r}")->getValue());
            $colF = trim((string)$ws->getCell("F{$r}")->getValue());

            // Check if this row is a category header
            if (!empty($colB) && empty($colC) && empty($colD) && empty($colF)) {
                $upper = strtoupper($colB);
                if (str_contains($upper, 'EARTH')) $currentCategory = 'EARTH MOVING MACHINE';
                elseif (str_contains($upper, 'TRUCK')) $currentCategory = 'DUMP TRUCK';
                elseif (str_contains($upper, 'VEHICLE') || str_contains($upper, 'PICKUP')) $currentCategory = 'LIGHT VEHICLES';
                elseif (str_contains($upper, 'AUX') || str_contains($upper, 'PLANT')) $currentCategory = 'AUXILIARY';
                continue;
            }

            // Stop if reached footer/legend
            if (str_contains(strtoupper($colB), 'SERVICE TYPE') || str_contains(strtoupper((string)$ws->getCell("M{$r}")->getValue()), 'SERVICE TYPE')) {
                break;
            }

            if (empty($colC) && empty($colD) && empty($colF)) {
                continue;
            }

            $type = $colC;
            $make = $colD;
            $model = trim((string)$ws->getCell("E{$r}")->getValue());
            $plate = $colF;
            $serial = trim((string)$ws->getCell("G{$r}")->getValue());
            $loc = trim((string)$ws->getCell("H{$r}")->getValue());

            $lastReading = is_numeric($ws->getCell("I{$r}")->getValue()) ? (float)$ws->getCell("I{$r}")->getValue() : null;
            $lastDate = $this->parseDateValue($ws->getCell("J{$r}")->getValue());
            $lastType = trim((string)$ws->getCell("K{$r}")->getValue());

            $nextReading = is_numeric($ws->getCell("L{$r}")->getValue()) ? (float)$ws->getCell("L{$r}")->getValue() : null;
            $nextType = trim((string)$ws->getCell("M{$r}")->getValue());

            $currReading = is_numeric($ws->getCell("N{$r}")->getValue()) ? (float)$ws->getCell("N{$r}")->getValue() : null;
            $currDate = $this->parseDateValue($ws->getCell("O{$r}")->getValue());

            $remark = trim((string)$ws->getCell("R{$r}")->getValue());

            // Match or create Equipment fleet record
            $equipment = null;
            if (!empty($plate)) {
                $equipment = Equipment::where('plate_serial_no', $plate)->first();
            }
            if (!$equipment && !empty($type)) {
                $equipment = Equipment::where('equipment_type', $type)
                    ->where('make', $make)
                    ->where('model', $model)
                    ->first();
            }

            $readingUnit = ($currentCategory === 'LIGHT VEHICLES') ? 'km' : 'hr';

            if (!$equipment) {
                $equipment = Equipment::create([
                    'code' => $plate ?: ('EQ-' . strtoupper(substr($type, 0, 3)) . '-' . rand(100, 999)),
                    'equipment_type' => $type ?: 'EQUIPMENT',
                    'category' => $currentCategory,
                    'make' => $make,
                    'model' => $model,
                    'plate_serial_no' => $plate,
                    'serial_no' => $serial,
                    'project_id' => $projectId,
                    'location_name' => $loc,
                    'reading_unit' => $readingUnit,
                    'current_reading' => $currReading ?? 0,
                    'current_reading_date' => $currDate ?? now()->toDateString(),
                    'last_service_reading' => $lastReading,
                    'last_service_date' => $lastDate,
                    'last_service_type' => $lastType,
                    'next_service_reading' => $nextReading,
                    'next_service_type' => $nextType,
                    'status' => 'active',
                    'created_by' => $userId,
                ]);
                $equipmentCreatedOrUpdated++;
            } else {
                // Update readings if present
                if ($currReading !== null) {
                    $equipment->update([
                        'current_reading' => $currReading,
                        'current_reading_date' => $currDate ?? $equipment->current_reading_date,
                        'last_service_reading' => $lastReading ?? $equipment->last_service_reading,
                        'last_service_date' => $lastDate ?? $equipment->last_service_date,
                        'last_service_type' => $lastType ?: $equipment->last_service_type,
                        'next_service_reading' => $nextReading ?? $equipment->next_service_reading,
                        'next_service_type' => $nextType ?: $equipment->next_service_type,
                    ]);
                    $equipmentCreatedOrUpdated++;
                }
            }

            // Create PM Schedule Item
            PmScheduleItem::create([
                'pm_schedule_id' => $schedule->id,
                'equipment_id' => $equipment->id,
                'category' => $currentCategory,
                'equipment_type' => $type ?: $equipment->equipment_type,
                'make' => $make ?: $equipment->make,
                'model' => $model ?: $equipment->model,
                'plate_serial_no' => $plate ?: $equipment->plate_serial_no,
                'serial_no' => $serial ?: $equipment->serial_no,
                'location' => $loc ?: $equipment->location_name,
                'last_service_reading' => $lastReading,
                'last_service_date' => $lastDate,
                'last_service_type' => $lastType,
                'next_service_reading' => $nextReading,
                'next_service_type' => $nextType,
                'current_reading' => $currReading,
                'current_reading_date' => $currDate,
                'remark' => $remark,
                'sort_order' => $importedCount,
            ]);

            $importedCount++;
        }

        return [
            'schedule' => $schedule,
            'items_count' => $importedCount,
            'equipment_count' => $equipmentCreatedOrUpdated,
        ];
    }

    /**
     * Import Maintenance Breakdown Report (OF/TNT/ EAMD/030)
     */
    public function importBreakdownReport(UploadedFile $file, ?int $projectId, int $userId): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $ws = $spreadsheet->getSheetByName('MAINTENANCE REPORT') ?? $spreadsheet->getSheet(1);

        $reportCode = 'MR-' . date('Ymd-His');
        $reportDate = $this->parseDateValue($ws->getCell('A5')->getValue()) ?: now()->toDateString();

        $report = EquipmentBreakdownReport::create([
            'report_code' => $reportCode,
            'title' => 'Maintenance Report',
            'document_code' => trim((string)$ws->getCell('J2')->getValue()) ?: 'OF/TNT/ EAMD/030',
            'issue_no' => trim((string)$ws->getCell('J4')->getValue()) ?: '1',
            'page_no' => trim((string)$ws->getCell('K4')->getValue()) ?: 'Page 1 of 1',
            'report_date' => $reportDate,
            'project_id' => $projectId,
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        $highestRow = $ws->getHighestRow();
        $currentCategory = 'EARTH MOVING MACHINE AND PLANT';
        $itemsCount = 0;

        for ($r = 8; $r <= $highestRow; $r++) {
            $colA = trim((string)$ws->getCell("A{$r}")->getValue());
            $colB = trim((string)$ws->getCell("B{$r}")->getValue());
            $colD = trim((string)$ws->getCell("D{$r}")->getValue());

            // Category row
            if (!empty($colA) && empty($colB) && empty($colD)) {
                $upper = strtoupper($colA);
                if (str_contains($upper, 'PREPARED BY')) break; // Sign-off row reached
                if (str_contains($upper, 'EARTH')) $currentCategory = 'EARTH MOVING MACHINE AND PLANT';
                elseif (str_contains($upper, 'DUMP')) $currentCategory = 'DUMP TRUCK';
                elseif (str_contains($upper, 'LIGHT')) $currentCategory = 'LIGHT VEHICLES';
                elseif (str_contains($upper, 'AUX')) $currentCategory = 'AUXILIARY';
                elseif (str_contains($upper, 'INSURANCE')) $currentCategory = 'INSURANCE CASE';
                continue;
            }

            // Table header row repeated
            if (strtolower($colA) === 'no' || strtolower($colB) === 'equipment type') {
                continue;
            }

            if (empty($colB) && empty($colD)) {
                continue;
            }

            $type = $colB;
            $qty = (int)$ws->getCell("C{$r}")->getValue() ?: 1;
            $plate = $colD;
            $downDate = $this->parseDateValue($ws->getCell("E{$r}")->getValue());
            $reason = trim((string)$ws->getCell("F{$r}")->getValue());
            $action = trim((string)$ws->getCell("H{$r}")->getValue());
            $loc = trim((string)$ws->getCell("I{$r}")->getValue());
            $repairStatus = strtolower(trim((string)$ws->getCell("J{$r}")->getValue())) ?: 'down';
            $finishedDate = $this->parseDateValue($ws->getCell("K{$r}")->getValue());

            $equipment = null;
            if (!empty($plate)) {
                $equipment = Equipment::where('plate_serial_no', $plate)->first();
            }

            EquipmentBreakdownItem::create([
                'breakdown_report_id' => $report->id,
                'equipment_id' => $equipment?->id,
                'category' => $currentCategory,
                'equipment_type' => $type,
                'quantity' => $qty,
                'plate_no_sn' => $plate,
                'down_date' => $downDate,
                'reason_of_down' => $reason,
                'action_taken' => $action,
                'location' => $loc,
                'repair_status' => $repairStatus,
                'finished_date' => $finishedDate,
                'sort_order' => $itemsCount,
            ]);

            // Update equipment status if linked
            if ($equipment) {
                if ($repairStatus === 'repaired' || $repairStatus === 'completed') {
                    $equipment->update(['status' => 'active']);
                } else {
                    $equipment->update(['status' => 'breakdown']);
                }
            }

            $itemsCount++;
        }

        return [
            'report' => $report,
            'items_count' => $itemsCount,
        ];
    }

    /**
     * Import Daily Logs & Spare Parts from Sheet 3
     */
    public function importDailyLogs(UploadedFile $file, ?int $projectId, int $userId): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $ws = $spreadsheet->getSheetByName('DAILY REPORT') ?? $spreadsheet->getSheet(2);

        $highestRow = $ws->getHighestRow();
        $importedCount = 0;
        $totalCost = 0;

        for ($r = 8; $r <= $highestRow; $r++) {
            $colB = trim((string)$ws->getCell("B{$r}")->getValue());
            $colC = trim((string)$ws->getCell("C{$r}")->getValue());
            $colD = trim((string)$ws->getCell("D{$r}")->getValue());

            if (empty($colD) && empty($colB)) {
                continue;
            }

            // Stop at total row
            if (str_contains(strtoupper($colB), 'TOTAL') || str_contains(strtoupper((string)$ws->getCell("I{$r}")->getValue()), 'TOTAL')) {
                break;
            }

            $type = $colB ?: 'EQUIPMENT';
            $plate = $colC;
            $partName = $colD;
            $poNo = trim((string)$ws->getCell("F{$r}")->getValue());
            $unit = trim((string)$ws->getCell("G{$r}")->getValue()) ?: 'PCS';
            $qty = (float)$ws->getCell("H{$r}")->getValue() ?: 1;
            $unitCost = (float)$ws->getCell("I{$r}")->getValue();
            $totCost = (float)$ws->getCell("J{$r}")->getValue();
            $supplier = trim((string)$ws->getCell("K{$r}")->getValue());
            $logDate = $this->parseDateValue($ws->getCell("L{$r}")->getValue()) ?: now()->toDateString();

            if ($totCost == 0 && $qty && $unitCost) {
                $totCost = round($qty * $unitCost * 1.15, 2);
            }

            $equipment = null;
            if (!empty($plate)) {
                $equipment = Equipment::where('plate_serial_no', $plate)->first();
            }

            EquipmentMaintenanceLog::create([
                'equipment_id' => $equipment?->id,
                'equipment_type' => $type,
                'equipment_plate' => $plate,
                'spare_part_name' => $partName,
                'po_no' => $poNo,
                'unit' => $unit,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_cost_with_vat' => $totCost,
                'supplier' => $supplier,
                'log_date' => $logDate,
                'project_id' => $projectId,
                'logged_by' => $userId,
            ]);

            $importedCount++;
            $totalCost += $totCost;
        }

        return [
            'imported_count' => $importedCount,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Helper to parse Excel dates (numeric serials, DMY, YMD strings)
     */
    protected function parseDateValue(mixed $val): ?string
    {
        if (empty($val)) return null;

        if (is_numeric($val)) {
            // Excel serial date to PHP date
            $unixDate = ($val - 25569) * 86400;
            return gmdate('Y-m-d', (int)$unixDate);
        }

        $str = trim((string)$val);
        try {
            // Try DD/MM/YYYY
            if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $str, $m)) {
                $day = (int)$m[1];
                $month = (int)$m[2];
                $year = (int)$m[3];
                if ($year < 100) $year += 2000;
                // If month > 12, might be MM/DD/YYYY
                if ($month > 12 && $day <= 12) {
                    return Carbon::createFromDate($year, $day, $month)->toDateString();
                }
                return Carbon::createFromDate($year, $month, $day)->toDateString();
            }
            return Carbon::parse($str)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
