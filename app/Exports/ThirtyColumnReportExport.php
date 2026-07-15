<?php
namespace App\Exports;

use App\Models\BoqItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;

class ThirtyColumnReportExport implements 
    FromCollection, 
    WithHeadings, 
    WithTitle,
    WithStyles,
    WithColumnWidths,
    WithCustomStartCell,
    WithEvents
{
    protected $projectId;
    protected $projectName;
    protected $data;
    protected $totalRevenue = 0;
    protected $totalBudget = 0;
    protected $currentRow = 7; // Start after headers

    public function __construct($projectId = null)
    {
        $this->projectId = $projectId;
        
        if ($projectId) {
            $project = \App\Models\Project::find($projectId);
            $this->projectName = $project ? $project->name : 'All Projects';
        } else {
            $this->projectName = 'All Projects';
        }
        
        $this->prepareData();
    }

    protected function prepareData()
    {
        $query = BoqItem::with([
            'costCategory',
            'laborResources',
            'materialResources',
            'equipmentResources',
            'project'
        ])->where('is_parent', false);
        
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }
        
        $items = $query->orderBy('cost_category_id')
                      ->orderBy('item_number')
                      ->get();
        
        // Group by cost category
        $this->data = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $this->totalRevenue = $items->sum('revenue_amount');
        $this->totalBudget = $items->sum(function($item) {
            return $item->total_budget_cost;
        });
    }

    public function collection()
    {
        $rows = collect();
        
        foreach ($this->data as $categoryName => $categoryItems) {
            $catRevenue = $categoryItems->sum('revenue_amount');
            $catBudget = $categoryItems->sum(function($i) { return $i->total_budget_cost; });
            
            // Category header row
            $rows->push([
                '', '', $categoryName, '', '', '', '', '', '', '',
                '', '', '', '', '',
                '', '', '', '',
                '', '', '', '', '', '',
                '', '', '', ''
            ]);
            
            foreach ($categoryItems as $item) {
                $laborResources = $item->laborResources;
                $materialResources = $item->materialResources;
                $equipmentResources = $item->equipmentResources;
                $maxRows = max($laborResources->count(), $materialResources->count(), $equipmentResources->count(), 1);
                
                for ($i = 0; $i < $maxRows; $i++) {
                    $row = [];
                    
                    if ($i == 0) {
                        // Main item data
                        $row[] = $item->costCategory->code ?? ''; // Cost Category
                        $row[] = $item->item_number; // Item No
                        $row[] = $item->description; // Description
                        $row[] = $item->unit; // Unit
                        $row[] = $item->unit_rate; // BOQ Rate
                        $row[] = $item->quantity; // Quantity
                        $row[] = $item->revenue_amount; // Revenue
                        $row[] = $item->duration_days; // Duration
                        $row[] = $item->planned_start_date ? $item->planned_start_date->format('m/d/Y') : ''; // Start
                        $row[] = $item->planned_end_date ? $item->planned_end_date->format('m/d/Y') : ''; // End
                    } else {
                        $row = array_fill(0, 10, '');
                    }
                    
                    // Labor (5 columns)
                    if (isset($laborResources[$i])) {
                        $row[] = $laborResources[$i]->trade_name;
                        $row[] = $laborResources[$i]->number_of_workers;
                        $row[] = $laborResources[$i]->total_hours;
                        $row[] = $laborResources[$i]->wage_per_day;
                        $row[] = $laborResources[$i]->amount;
                    } else {
                        $row = array_merge($row, ['', '', '', '', '']);
                    }
                    
                    // Material (4 columns)
                    if (isset($materialResources[$i])) {
                        $row[] = $materialResources[$i]->description;
                        $row[] = $materialResources[$i]->unit;
                        $row[] = $materialResources[$i]->quantity;
                        $row[] = $materialResources[$i]->unit_rate;
                    } else {
                        $row = array_merge($row, ['', '', '', '']);
                    }
                    
                    // Equipment (6 columns)
                    if (isset($equipmentResources[$i])) {
                        $row[] = $equipmentResources[$i]->description;
                        $row[] = $equipmentResources[$i]->duration_days;
                        $row[] = $equipmentResources[$i]->number_of_units;
                        $row[] = $equipmentResources[$i]->total_hours;
                        $row[] = $equipmentResources[$i]->rate_per_hour;
                        $row[] = $equipmentResources[$i]->amount;
                    } else {
                        $row = array_merge($row, ['', '', '', '', '', '']);
                    }
                    
                    if ($i == 0) {
                        $itemBudget = $item->total_budget_cost;
                        $itemProfitLoss = $item->profit_loss;
                        $itemMargin = $item->profit_margin_percentage;
                        
                        $row[] = $itemBudget; // Total Budget
                        $row[] = $itemProfitLoss; // Profit/Loss
                        $row[] = $itemMargin / 100; // Profit % (as decimal for Excel formatting)
                        $row[] = $item->profit_loss_status; // Status
                    } else {
                        $row = array_merge($row, ['', '', '', '']);
                    }
                    
                    $rows->push($row);
                }
            }
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            ['30-COLUMN BUDGET & COST BREAKDOWN REPORT'],
            ['Project: ' . $this->projectName],
            ['Generated: ' . Carbon::now()->format('F d, Y')],
            [''], // Empty row for spacing
            [
                'Cost Category', 'Item No.', 'ITEM DESCRIPTION', 'UNIT', 'BOQ Rate', 
                'Quantity', 'REVENUE AMOUNT', 'Duration', 'Start Date', 'End Date',
                'TRADE', 'NUMBER', 'TOTAL HOUR', 'WAGE/DAY', 'LABOUR AMOUNT',
                'Material Description', 'Material UNIT', 'Material QUANTITY', 'UNIT RATE',
                'Equipment DESCRIPTION', 'Equipment DURATION', 'NUMBER', 'TOTAL HOUR', 'RATE', 'EQUIPMENT AMOUNT',
                'TOTAL BUDGET AMOUNT', 'PROFIT/LOSS', 'Profit %', 'STATUS'
            ]
        ];
    }

    public function title(): string
    {
        return '30-Column Budget Report';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Cost Category
            'B' => 10,  // Item No
            'C' => 45,  // Description
            'D' => 8,   // Unit
            'E' => 12,  // BOQ Rate
            'F' => 12,  // Quantity
            'G' => 15,  // Revenue
            'H' => 10,  // Duration
            'I' => 12,  // Start Date
            'J' => 12,  // End Date
            'K' => 20,  // Trade
            'L' => 10,  // Number
            'M' => 12,  // Total Hours
            'N' => 12,  // Wage/Day
            'O' => 15,  // Labour Amount
            'P' => 25,  // Material Description
            'Q' => 10,  // Material Unit
            'R' => 12,  // Material Quantity
            'S' => 12,  // Unit Rate
            'T' => 25,  // Equipment Description
            'U' => 12,  // Equipment Duration
            'V' => 10,  // Equipment Number
            'W' => 12,  // Total Hours
            'X' => 12,  // Rate
            'Y' => 15,  // Equipment Amount
            'Z' => 15,  // Total Budget
            'AA' => 15, // Profit/Loss
            'AB' => 10, // Profit %
            'AC' => 12, // Status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '002060']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Project name row
            2 => [
                'font' => ['size' => 11, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Date row
            3 => [
                'font' => ['size' => 9, 'color' => ['rgb' => '999999']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Header row (row 5)
            5 => [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                // Merge title cells
                $sheet->mergeCells('A1:AC1');
                $sheet->mergeCells('A2:AC2');
                $sheet->mergeCells('A3:AC3');
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(5)->setRowHeight(40);
                
                // Apply borders to all data cells
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '333333'],
                        ],
                    ],
                ];
                
                $sheet->getStyle('A5:AC' . $lastRow)->applyFromArray($styleArray);
                
                // Format currency columns
                $currencyColumns = ['E', 'F', 'G', 'N', 'O', 'R', 'S', 'X', 'Y', 'Z', 'AA'];
                foreach ($currencyColumns as $col) {
                    $sheet->getStyle($col . '6:' . $col . $lastRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
                
                // Format percentage column
                $sheet->getStyle('AB6:AB' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('0.0%');
                
                // Apply category header styling
                for ($row = 6; $row <= $lastRow; $row++) {
                    $cellValue = $sheet->getCell('C' . $row)->getValue();
                    // Check if this is a category header (only column C has value, others empty)
                    $aEmpty = empty($sheet->getCell('A' . $row)->getValue());
                    $bEmpty = empty($sheet->getCell('B' . $row)->getValue());
                    
                    if ($aEmpty && $bEmpty && !empty($cellValue)) {
                        $sheet->mergeCells('A' . $row . ':AC' . $row);
                        $sheet->getStyle('A' . $row . ':AC' . $row)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
                        ]);
                    }
                    
                    // Apply PROFIT/LOSS coloring
                    $statusCell = $sheet->getCell('AC' . $row)->getValue();
                    $plCell = $sheet->getCell('AA' . $row)->getValue();
                    
                    if ($statusCell == 'PROFIT') {
                        $sheet->getStyle('AA' . $row . ':AC' . $row)
                            ->getFont()->getColor()->setRGB('006100');
                        $sheet->getStyle('AC' . $row)
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('C6EFCE');
                    } elseif ($statusCell == 'LOSS') {
                        $sheet->getStyle('AA' . $row . ':AC' . $row)
                            ->getFont()->getColor()->setRGB('9C0006');
                        $sheet->getStyle('AC' . $row)
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFC7CE');
                    }
                }
                
                // Add grand total row
                $totalRow = $lastRow + 2;
                $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'GRAND TOTAL:');
                $sheet->setCellValue('G' . $totalRow, $this->totalRevenue);
                $sheet->setCellValue('Z' . $totalRow, $this->totalBudget);
                
                $totalPL = $this->totalRevenue - $this->totalBudget;
                $sheet->setCellValue('AA' . $totalRow, $totalPL);
                
                $totalMargin = $this->totalRevenue > 0 ? ($totalPL / $this->totalRevenue) : 0;
                $sheet->setCellValue('AB' . $totalRow, $totalMargin);
                $sheet->setCellValue('AC' . $totalRow, $totalPL >= 0 ? 'PROFIT' : 'LOSS');
                
                // Style grand total row
                $sheet->getStyle('A' . $totalRow . ':AC' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
                ]);
                
                $sheet->getStyle('G' . $totalRow . ':AC' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                    
                $sheet->getStyle('AB' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('0.0%');
                
                // Freeze panes
                $sheet->freezePane('D6');
                
                // Auto filter
                $sheet->setAutoFilter('A5:AC' . $lastRow);
                
                // Print settings
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.3)
                    ->setRight(0.3);
            },
        ];
    }
}
