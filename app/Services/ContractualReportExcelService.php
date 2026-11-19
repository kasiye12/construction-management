<?php

namespace App\Services;

use App\Models\ContractualReport;
use App\Models\ContractualClaimCheck;
use App\Models\ContractualClaimEvent;
use App\Models\ContractualCorrespondenceCheck;
use App\Models\Project;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContractualReportExcelService
{
    protected string $templatePath;

    public function __construct()
    {
        $this->templatePath = storage_path('app/templates/TNT-OF-CAD-01-Weekly-Contractual-Follow-up-Reporting-Form.xlsx');
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Export a ContractualReport to the official 3-page Excel format
     */
    public function export(ContractualReport $report): string
    {
        if (!file_exists($this->templatePath)) {
            throw new \Exception("Master template not found at {$this->templatePath}");
        }

        $spreadsheet = IOFactory::load($this->templatePath);
        $project = $report->project;

        $startDateStr = $report->week_start_date ? $report->week_start_date->format('d/m/Y') : '___________';
        $endDateStr = $report->week_end_date ? $report->week_end_date->format('d/m/Y') : '___________';
        $dateRangeText = "Date: From {$startDateStr}  To {$endDateStr}";

        $preparedText = "Prepared By: " . ($report->prepared_name ?: ($report->preparedByUser?->name ?: '_______________'));
        if ($report->prepared_at) {
            $preparedText .= " (" . $report->prepared_at->format('d/m/Y') . ")";
        }

        $checkedText = "Checked By: " . ($report->checked_name ?: ($report->checkedByUser?->name ?: '_______________'));
        if ($report->checked_at) {
            $checkedText .= " (" . $report->checked_at->format('d/m/Y') . ")";
        }

        $approvedText = "Approved By: " . ($report->approved_name ?: ($report->approvedByUser?->name ?: '_______________'));
        if ($report->approved_at) {
            $approvedText .= " (" . $report->approved_at->format('d/m/Y') . ")";
        }

        // ==========================================
        // PAGE 1: Causes of Claim - Follow up Form
        // ==========================================
        $sheet1 = $spreadsheet->getSheetByName('TNT-CAD-01-Page 01');
        if ($sheet1) {
            $sheet1->setCellValue('A7', 'Project: ' . ($project?->name ?: ''));
            $sheet1->setCellValue('A8', 'Project Location: ' . ($report->project_location ?: ($project?->location ?: 'Site')));
            $sheet1->setCellValue('A9', 'Client: ' . ($report->client_name ?: ($project?->client_name ?: '')));
            $sheet1->setCellValue('A10', 'Consultant: ' . ($report->consultant_name ?: ($project?->consultant_name ?: '')));
            $sheet1->setCellValue('A11', 'Contractor: ' . ($report->contractor_name ?: 'TNT CONSTRUCTION AND TRADING'));
            $sheet1->setCellValue('F13', $dateRangeText);

            // Row mapping for Claim Items
            $claimRowMap = [
                '1.1' => 19,
                '1.2' => 20,
                '1.3' => 21,
                '2.1' => 23,
                '2.2' => 24,
                '2.3' => 25,
                '3.1' => 27,
                '3.2' => 28,
                '3.3' => 29,
                '3.4' => 30,
                '3.5' => 31,
            ];

            // Days columns: Monday (C/D), Tuesday (E/F), Wednesday (G/H), Thursday (I/J), Friday (K/L), Saturday (M/N), Sunday (O/P)
            $dayCols = [
                'monday'    => ['yes' => 'C', 'no' => 'D'],
                'tuesday'   => ['yes' => 'E', 'no' => 'F'],
                'wednesday' => ['yes' => 'G', 'no' => 'H'],
                'thursday'  => ['yes' => 'I', 'no' => 'J'],
                'friday'    => ['yes' => 'K', 'no' => 'L'],
                'saturday'  => ['yes' => 'M', 'no' => 'N'],
                'sunday'    => ['yes' => 'O', 'no' => 'P'],
            ];

            $checks = $report->claimChecks()->get()->keyBy('item_code');
            foreach ($claimRowMap as $code => $row) {
                $check = $checks->get($code);
                foreach ($dayCols as $day => $cols) {
                    $isYes = $check ? (bool)$check->$day : false;
                    $sheet1->setCellValue($cols['yes'] . $row, $isYes ? '✓' : '');
                    $sheet1->setCellValue($cols['no'] . $row, $isYes ? '' : '✓');
                }
            }

            // Custom Claim Checks
            $customChecks = $report->claimChecks()->whereNotIn('item_code', array_keys($claimRowMap))->get();
            if ($customChecks->isNotEmpty()) {
                $insertAt = 32;
                $sheet1->insertNewRowBefore($insertAt, $customChecks->count());
                foreach ($customChecks as $cIdx => $cCheck) {
                    $cRow = $insertAt + $cIdx;
                    $sheet1->setCellValue('A' . $cRow, $cCheck->item_code . ' ' . $cCheck->item_title);
                    foreach ($dayCols as $day => $cols) {
                        $isYes = (bool)$cCheck->$day;
                        $sheet1->setCellValue($cols['yes'] . $cRow, $isYes ? '✓' : '');
                        $sheet1->setCellValue($cols['no'] . $cRow, $isYes ? '' : '✓');
                    }
                }
                $sigRow = 33 + $customChecks->count();
                $sheet1->setCellValue('A' . $sigRow, $preparedText);
                $sheet1->setCellValue('D' . $sigRow, $checkedText);
                $sheet1->setCellValue('K' . $sigRow, $approvedText);
            } else {
                // Signatures
                $sheet1->setCellValue('A33', $preparedText);
                $sheet1->setCellValue('D33', $checkedText);
                $sheet1->setCellValue('K33', $approvedText);
            }
        }

        // ==========================================
        // PAGE 2: Detailed Information of Claim Event
        // ==========================================
        $sheet2 = $spreadsheet->getSheetByName('TNT-CAD-01 Page 02.');
        if ($sheet2) {
            $sheet2->setCellValue('A7', 'Project: ' . ($project?->name ?: ''));
            $sheet2->setCellValue('A8', 'Project Location: ' . ($report->project_location ?: ($project?->location ?: 'Site')));
            $sheet2->setCellValue('A9', 'Client: ' . ($report->client_name ?: ($project?->client_name ?: '')));
            $sheet2->setCellValue('A10', 'Consultant: ' . ($report->consultant_name ?: ($project?->consultant_name ?: '')));
            $sheet2->setCellValue('A11', 'Contractor: ' . ($report->contractor_name ?: 'TNT CONSTRUCTION AND TRADING'));
            $sheet2->setCellValue('D13', $dateRangeText);

            $events = $report->claimEvents()->orderBy('sort_order')->get();
            $startRow = 17;
            $rowIndex = $startRow;

            foreach ($events as $event) {
                // If we exceed row 21, insert a new row before row 22 (Remark row)
                if ($rowIndex > 21) {
                    $sheet2->insertNewRowBefore(22, 1);
                }
                $sheet2->setCellValue('A' . $rowIndex, $event->event_date ? $event->event_date->format('d/m/Y') : '');
                $sheet2->setCellValue('B' . $rowIndex, $event->issue_number ?: '');
                $sheet2->setCellValue('C' . $rowIndex, $event->cause_description ?: '');
                $sheet2->setCellValue('D' . $rowIndex, $event->effect_on_progress ?: '');
                $sheet2->setCellValue('E' . $rowIndex, $event->action_taken ?: '');
                $sheet2->setCellValue('F' . $rowIndex, $event->remark ?: '');
                $rowIndex++;
            }

            // Find remark row (which might have shifted if rows were inserted)
            $remarkRow = 22 + max(0, $events->count() - 5);
            $sheet2->setCellValue('A' . $remarkRow, 'Remark: ' . ($report->page2_remark ?: ''));

            $sigRow = $remarkRow + 1;
            $sheet2->setCellValue('A' . $sigRow, "{$preparedText}         {$checkedText}         {$approvedText}");
        }

        // ==========================================
        // PAGE 3: Site Correspondence - Follow up Form
        // ==========================================
        $sheet3 = $spreadsheet->getSheetByName('TNT-CAD-01 Page 03');
        if ($sheet3) {
            $sheet3->setCellValue('A6', 'Project Location: ' . ($report->project_location ?: ($project?->location ?: 'Site')));
            $sheet3->setCellValue('A7', 'Client: ' . ($report->client_name ?: ($project?->client_name ?: '')));
            $sheet3->setCellValue('A8', 'Consultant: ' . ($report->consultant_name ?: ($project?->consultant_name ?: '')));
            $sheet3->setCellValue('A9', 'Contractor: ' . ($report->contractor_name ?: 'TNT CONSTRUCTION AND TRADING'));
            $sheet3->setCellValue('F11', $dateRangeText);

            $correspRowMap = [
                'incoming_1.0' => 17, // From Client
                'incoming_2.0' => 18, // From Consultant
                'incoming_3.0' => 19, // Others
                'outgoing_1.0' => 21, // To Client
                'outgoing_2.0' => 22, // To Consultant
                'outgoing_3.0' => 23, // Others
            ];

            $correspChecks = $report->correspondenceChecks()->get()->keyBy(function($item) {
                return $item->direction . '_' . $item->item_code;
            });

            $dayCols = [
                'monday'    => ['yes' => 'C', 'no' => 'D'],
                'tuesday'   => ['yes' => 'E', 'no' => 'F'],
                'wednesday' => ['yes' => 'G', 'no' => 'H'],
                'thursday'  => ['yes' => 'I', 'no' => 'J'],
                'friday'    => ['yes' => 'K', 'no' => 'L'],
                'saturday'  => ['yes' => 'M', 'no' => 'N'],
                'sunday'    => ['yes' => 'O', 'no' => 'P'],
            ];

            foreach ($correspRowMap as $key => $row) {
                $check = $correspChecks->get($key);
                foreach ($dayCols as $day => $cols) {
                    $isYes = $check ? (bool)$check->$day : false;
                    $sheet3->setCellValue($cols['yes'] . $row, $isYes ? '✓' : '');
                    $sheet3->setCellValue($cols['no'] . $row, $isYes ? '' : '✓');
                }
            }

            $sheet3->setCellValue('A26', 'Remark: ' . ($report->page3_remark ?: ''));
            $sheet3->setCellValue('A28', "{$preparedText}         {$checkedText}         {$approvedText}");
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'TNT_CAD01_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Import a filled Excel file into a ContractualReport
     */
    public function import(UploadedFile $file, int $projectId, int $userId): ContractualReport
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $project = Project::findOrFail($projectId);

        $sheet1 = $spreadsheet->getSheetByName('TNT-CAD-01-Page 01') ?? $spreadsheet->getSheet(0);

        // Parse Date Range from F13: "Date: From 01/10/2026 To 07/10/2026"
        $dateText = $sheet1->getCell('F13')->getValue() ?? '';
        $startDate = now()->startOfWeek(Carbon::MONDAY);
        $endDate = now()->endOfWeek(Carbon::SUNDAY);

        if (preg_match('/From\s*[:\s]*([0-9\/\-\.]+)\s*To\s*([0-9\/\-\.]+)/i', $dateText, $matches)) {
            try {
                $startDate = Carbon::parse(str_replace('/', '-', $matches[1]));
                $endDate = Carbon::parse(str_replace('/', '-', $matches[2]));
            } catch (\Exception $e) {
                // Keep default
            }
        }

        // Generate report number
        $reportCount = ContractualReport::where('project_id', $projectId)->count() + 1;
        $reportNumber = 'WCR-' . date('Y') . '-' . str_pad($reportCount, 4, '0', STR_PAD_LEFT);

        $report = ContractualReport::create([
            'project_id' => $projectId,
            'report_number' => $reportNumber,
            'document_code' => 'OF/TNT/CAD/01',
            'issue_no' => '1',
            'project_location' => $project->location ?? 'Site',
            'client_name' => $project->client_name,
            'consultant_name' => $project->consultant_name,
            'contractor_name' => 'TNT CONSTRUCTION AND TRADING',
            'week_start_date' => $startDate,
            'week_end_date' => $endDate,
            'status' => 'draft',
            'prepared_by' => $userId,
            'prepared_name' => auth()->user()?->name ?? 'Site Engineer',
            'prepared_at' => now(),
            'created_by' => $userId,
        ]);

        // Map Sheet 1 Checks
        $claimRowMap = [
            '1.1' => ['row' => 19, 'cat' => 'design_issues', 'cat_title' => 'Design related issues', 'title' => 'Miss drawing'],
            '1.2' => ['row' => 20, 'cat' => 'design_issues', 'cat_title' => 'Design discrepancies', 'title' => 'Design discrepancies'],
            '1.3' => ['row' => 21, 'cat' => 'design_issues', 'cat_title' => 'Design related issues', 'title' => 'Design modification at site level'],
            '2.1' => ['row' => 23, 'cat' => 'work_orders', 'cat_title' => 'Work Orders', 'title' => 'Works indicated on drawing and missed in BOQ or vice versa'],
            '2.2' => ['row' => 24, 'cat' => 'work_orders', 'cat_title' => 'Work Orders', 'title' => 'Additional work order given'],
            '2.3' => ['row' => 25, 'cat' => 'work_orders', 'cat_title' => 'Work Orders', 'title' => 'Change in work item (quality, or shape /dimension or quantity from BOQ)'],
            '3.1' => ['row' => 27, 'cat' => 'suspension_of_works', 'cat_title' => 'Suspension of works', 'title' => 'Bad Weather conditions and natural hazards'],
            '3.2' => ['row' => 28, 'cat' => 'suspension_of_works', 'cat_title' => 'Suspension of works', 'title' => 'Delay due to inspection / approval'],
            '3.3' => ['row' => 29, 'cat' => 'suspension_of_works', 'cat_title' => 'Suspension of works', 'title' => 'Force majeure issues'],
            '3.4' => ['row' => 30, 'cat' => 'suspension_of_works', 'cat_title' => 'Suspension of works', 'title' => 'Artificial conditions or Physical obstructions'],
            '3.5' => ['row' => 31, 'cat' => 'suspension_of_works', 'cat_title' => 'Suspension of works', 'title' => 'Other external factors'],
        ];

        $dayCols = [
            'monday'    => 'C',
            'tuesday'   => 'E',
            'wednesday' => 'G',
            'thursday'  => 'I',
            'friday'    => 'K',
            'saturday'  => 'M',
            'sunday'    => 'O',
        ];

        $order = 1;
        foreach ($claimRowMap as $code => $info) {
            $data = [
                'contractual_report_id' => $report->id,
                'category' => $info['cat'],
                'category_title' => $info['cat_title'],
                'item_code' => $code,
                'item_title' => $info['title'],
                'sort_order' => $order++,
            ];
            foreach ($dayCols as $day => $col) {
                $val = trim((string)$sheet1->getCell($col . $info['row'])->getValue());
                $data[$day] = !empty($val) && (stripos($val, 'y') !== false || $val === '✓' || $val === 'x' || $val === 'X');
            }
            ContractualClaimCheck::create($data);
        }

        // Sheet 2: Claim Events
        $sheet2 = $spreadsheet->getSheetByName('TNT-CAD-01 Page 02.') ?? $spreadsheet->getSheet(1);
        if ($sheet2) {
            $order = 1;
            for ($r = 17; $r <= 21; $r++) {
                $dateVal = $sheet2->getCell('A' . $r)->getValue();
                $issueNo = trim((string)$sheet2->getCell('B' . $r)->getValue());
                $desc = trim((string)$sheet2->getCell('C' . $r)->getValue());
                $effect = trim((string)$sheet2->getCell('D' . $r)->getValue());
                $action = trim((string)$sheet2->getCell('E' . $r)->getValue());
                $remark = trim((string)$sheet2->getCell('F' . $r)->getValue());

                if (!empty($issueNo) || !empty($desc)) {
                    $eventDate = null;
                    if ($dateVal) {
                        try {
                            $eventDate = Carbon::parse(str_replace('/', '-', $dateVal));
                        } catch (\Exception $e) {}
                    }
                    ContractualClaimEvent::create([
                        'contractual_report_id' => $report->id,
                        'event_date' => $eventDate ?? $startDate,
                        'issue_number' => $issueNo,
                        'cause_description' => $desc,
                        'effect_on_progress' => $effect,
                        'action_taken' => $action,
                        'remark' => $remark,
                        'sort_order' => $order++,
                    ]);
                }
            }
            $remarkVal = $sheet2->getCell('A22')->getValue();
            if ($remarkVal) {
                $report->update(['page2_remark' => trim(str_ireplace('Remark:', '', $remarkVal))]);
            }
        }

        // Sheet 3: Correspondence
        $sheet3 = $spreadsheet->getSheetByName('TNT-CAD-01 Page 03') ?? $spreadsheet->getSheet(2);
        if ($sheet3) {
            $correspMap = [
                '1.0_in' => ['row' => 17, 'dir' => 'incoming', 'dir_title' => 'In coming Letters', 'code' => '1.0', 'party' => 'From Client'],
                '2.0_in' => ['row' => 18, 'dir' => 'incoming', 'dir_title' => 'In coming Letters', 'code' => '2.0', 'party' => 'From Consultant'],
                '3.0_in' => ['row' => 19, 'dir' => 'incoming', 'dir_title' => 'In coming Letters', 'code' => '3.0', 'party' => 'Others'],
                '1.0_out' => ['row' => 21, 'dir' => 'outgoing', 'dir_title' => 'Out going Letters', 'code' => '1.0', 'party' => 'To Client'],
                '2.0_out' => ['row' => 22, 'dir' => 'outgoing', 'dir_title' => 'Out going Letters', 'code' => '2.0', 'party' => 'To Consultant'],
                '3.0_out' => ['row' => 23, 'dir' => 'outgoing', 'dir_title' => 'Out going Letters', 'code' => '3.0', 'party' => 'Others'],
            ];

            $order = 1;
            foreach ($correspMap as $info) {
                $data = [
                    'contractual_report_id' => $report->id,
                    'direction' => $info['dir'],
                    'direction_title' => $info['dir_title'],
                    'item_code' => $info['code'],
                    'party_title' => $info['party'],
                    'sort_order' => $order++,
                ];
                foreach ($dayCols as $day => $col) {
                    $val = trim((string)$sheet3->getCell($col . $info['row'])->getValue());
                    $data[$day] = !empty($val) && (stripos($val, 'y') !== false || $val === '✓' || $val === 'x' || $val === 'X');
                }
                ContractualCorrespondenceCheck::create($data);
            }

            $remarkVal = $sheet3->getCell('A26')->getValue();
            if ($remarkVal) {
                $report->update(['page3_remark' => trim(str_ireplace('Remark:', '', $remarkVal))]);
            }
        }

        return $report;
    }
}
