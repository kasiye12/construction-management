<?php
namespace App\Exports;

use App\Models\BoqItem;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ThirtyColumnReportExport implements 
    FromCollection, WithHeadings, WithTitle, WithStyles, 
    WithColumnWidths, WithEvents
{
    protected $projectId;
    protected $filters;
    protected $projectName;
    protected $totalRevenue = 0;
    protected $totalBudget = 0;

    public function __construct($projectId = null, $filters = [])
    {
        $this->projectId = $projectId;
        $this->filters = is_array($filters) ? $filters : [];
        
        if ($projectId) {
            $project = Project::find($projectId);
            $this->projectName = $project ? $project->name : 'All Projects';
        } else {
            $this->projectName = 'All Projects';
        }
    }

    public function collection()
    {
        $query = BoqItem::with([
            'costCategory', 'laborResources', 'materialResources', 'equipmentResources'
        ])->where('is_parent', false);
        
        // Apply ALL filters - same as PDF
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }
        if (!empty($this->filters['category_id'])) {
            $query->where('cost_category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->where('planned_start_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->where('planned_start_date', '<=', $this->filters['date_to']);
        }
        
        $items = $query->orderBy('cost_category_id')->orderBy('item_number')->get();
        
        $this->totalRevenue = $items->sum('revenue_amount');
        $this->totalBudget = $items->sum(fn($i) => $i->total_budget_cost);
        
        $groupedItems = $items->groupBy(function($item) {
            return $item->costCategory ? $item->costCategory->code . '. ' . $item->costCategory->name : 'Uncategorized';
        });
        
        $rows = collect();
        
        foreach ($groupedItems as $categoryName => $categoryItems) {
            // Category header row
            $rows->push(array_merge(['', '', $categoryName], array_fill(0, 26, '')));
            
            foreach ($categoryItems as $item) {
                $labor = $item->laborResources;
                $material = $item->materialResources;
                $equipment = $item->equipmentResources;
                $maxRows = max($labor->count(), $material->count(), $equipment->count(), 1);
                
                for ($i = 0; $i < $maxRows; $i++) {
                    $row = [];
                    
                    if ($i == 0) {
                        $row = [
                            $item->costCategory->code ?? '',
                            $item->item_number,
                            $item->description,
                            $item->unit,
                            $item->unit_rate,
                            $item->quantity,
                            $item->revenue_amount,
                            $item->duration_days ?? '',
                            $item->planned_start_date ? $item->planned_start_date->format('m/d/Y') : '',
                            $item->planned_end_date ? $item->planned_end_date->format('m/d/Y') : '',
                        ];
                    } else {
                        $row = array_fill(0, 10, '');
                    }
                    
                    // Labor (5 cols)
                    if (isset($labor[$i])) {
                        $row[] = $labor[$i]->trade_name;
                        $row[] = $labor[$i]->number_of_workers;
                        $row[] = $labor[$i]->total_hours;
                        $row[] = $labor[$i]->wage_per_day;
                        $row[] = $labor[$i]->amount;
                    } else { $row = array_merge($row, ['','','','','']); }
                    
                    // Material (4 cols)
                    if (isset($material[$i])) {
                        $row[] = $material[$i]->description;
                        $row[] = $material[$i]->unit;
                        $row[] = $material[$i]->quantity;
                        $row[] = $material[$i]->unit_rate;
                    } else { $row = array_merge($row, ['','','','']); }
                    
                    // Equipment (6 cols)
                    if (isset($equipment[$i])) {
                        $row[] = $equipment[$i]->description;
                        $row[] = $equipment[$i]->duration_days ?? '';
                        $row[] = $equipment[$i]->number_of_units;
                        $row[] = $equipment[$i]->total_hours;
                        $row[] = $equipment[$i]->rate_per_hour;
                        $row[] = $equipment[$i]->amount;
                    } else { $row = array_merge($row, ['','','','','','']); }
                    
                    if ($i == 0) {
                        $row[] = $item->total_budget_cost;
                        $row[] = $item->profit_loss;
                        $row[] = $item->profit_margin_percentage / 100;
                        $row[] = $item->profit_loss_status;
                    } else { $row = array_merge($row, ['','','','']); }
                    
                    $rows->push($row);
                }
            }
        }
        
        return $rows;
    }

    public function headings(): array
    {
        $filterInfo = [];
        if (!empty($this->filters['category_id'])) $filterInfo[] = 'Category Filtered';
        if (!empty($this->filters['status'])) $filterInfo[] = 'Status: ' . $this->filters['status'];
        if (!empty($this->filters['date_from']) || !empty($this->filters['date_to'])) {
            $filterInfo[] = 'Date: ' . ($this->filters['date_from'] ?? 'Any') . ' to ' . ($this->filters['date_to'] ?? 'Any');
        }
        $filterText = count($filterInfo) > 0 ? ' | Filters: ' . implode(', ', $filterInfo) : '';
        
        return [
            ['30-COLUMN BUDGET & COST BREAKDOWN REPORT'],
            ['Project: ' . $this->projectName . $filterText],
            ['Generated: ' . Carbon::now()->format('F d, Y')],
            [''],
            [
                'Cost Category', 'Item No.', 'ITEM DESCRIPTION', 'UNIT', 'BOQ Rate',
                'Quantity', 'REVENUE AMOUNT', 'Duration', 'Start Date', 'End Date',
                'TRADE', 'NUMBER', 'TOTAL HOUR', 'WAGE/DAY', 'LABOUR AMOUNT',
                'Material Desc', 'UNIT', 'QUANTITY', 'UNIT RATE',
                'Equipment Desc', 'DURATION', 'NUMBER', 'TOTAL HOUR', 'RATE', 'EQUIP AMOUNT',
                'TOTAL BUDGET', 'PROFIT/LOSS', 'Profit %', 'STATUS'
            ]
        ];
    }

    public function title(): string { return '30-Column Budget Report'; }

    public function columnWidths(): array
    {
        return [
            'A'=>12,'B'=>10,'C'=>40,'D'=>8,'E'=>12,'F'=>12,'G'=>14,
            'H'=>8,'I'=>10,'J'=>10,
            'K'=>18,'L'=>8,'M'=>10,'N'=>10,'O'=>14,
            'P'=>22,'Q'=>8,'R'=>12,'S'=>12,
            'T'=>22,'U'=>10,'V'=>8,'W'=>10,'X'=>10,'Y'=>14,
            'Z'=>14,'AA'=>14,'AB'=>10,'AC'=>12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold'=>true,'size'=>14,'color'=>['rgb'=>'002060']],'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold'=>true,'size'=>8,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'4472C4']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                $sheet->mergeCells('A1:AC1');
                $sheet->mergeCells('A2:AC2');
                $sheet->mergeCells('A3:AC3');
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(5)->setRowHeight(35);
                
                $sheet->getStyle('A5:AC' . $lastRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                
                // Format currency columns
                foreach(['E','F','G','N','O','R','S','X','Y','Z','AA'] as $col) {
                    $sheet->getStyle($col.'6:'.$col.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                $sheet->getStyle('AB6:AB'.$lastRow)->getNumberFormat()->setFormatCode('0.0%');
                
                // Category headers styling
                for ($row = 6; $row <= $lastRow; $row++) {
                    $c = $sheet->getCell('C'.$row)->getValue();
                    $a = $sheet->getCell('A'.$row)->getValue();
                    if (empty($a) && !empty($c)) {
                        $sheet->mergeCells('A'.$row.':AC'.$row);
                        $sheet->getStyle('A'.$row.':AC'.$row)->applyFromArray([
                            'font'=>['bold'=>true,'size'=>10],
                            'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'D6E4F0']],
                        ]);
                    }
                }
                
                // Grand total row
                $tr = $lastRow + 2;
                $sheet->mergeCells('A'.$tr.':F'.$tr);
                $sheet->setCellValue('A'.$tr, 'GRAND TOTAL:');
                $sheet->setCellValue('G'.$tr, $this->totalRevenue);
                $sheet->setCellValue('Z'.$tr, $this->totalBudget);
                $pl = $this->totalRevenue - $this->totalBudget;
                $sheet->setCellValue('AA'.$tr, $pl);
                $sheet->setCellValue('AB'.$tr, $this->totalRevenue>0?$pl/$this->totalRevenue:0);
                $sheet->setCellValue('AC'.$tr, $pl>=0?'PROFIT':'LOSS');
                
                $sheet->getStyle('A'.$tr.':AC'.$tr)->applyFromArray([
                    'font'=>['bold'=>true,'size'=>10,'color'=>['rgb'=>'FFFFFF']],
                    'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'002060']],
                ]);
                
                $sheet->freezePane('D6');
                $sheet->setAutoFilter('A5:AC'.$lastRow);
                
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
                    ->setFitToWidth(1)->setFitToHeight(0);
            },
        ];
    }
}
