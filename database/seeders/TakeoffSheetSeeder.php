<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TakeoffSheet;
use App\Models\TakeoffItem;
use App\Models\TakeoffDescription;
use App\Models\TakeoffMeasurement;
use App\Models\Project;
use Carbon\Carbon;

class TakeoffSheetSeeder extends Seeder
{
    public function run()
    {
        $project = Project::first();
        if (!$project) { echo "No project found.\n"; return; }

        // Sheet 1: Foundation Waterproofing
        $sheet1 = TakeoffSheet::create([
            'project_id' => $project->id,
            'sheet_number' => 'TO-001',
            'division' => 'Sub-Structure',
            'page_no' => 1,
            'measurement_date' => Carbon::now()->subDays(30),
            'measured_by' => 'Site Engineer',
            'status' => 'approved',
            'verified_by' => 'Senior Engineer',
            'approved_by' => 'Project Manager',
        ]);

        // Item 1: Water Proofing
        $item1 = $sheet1->items()->create([
            'item_number' => '1',
            'description' => 'BITUMINOUS DAMP PROOFING - Foundation Footing',
            'display_order' => 0,
        ]);

        // Left Description: Foundation Pads F1-F3
        $leftDesc1 = $item1->descriptions()->create([
            'side' => 'left',
            'description' => 'Foundation Footing Pad - Group 1',
        ]);

        $leftDesc1->measurements()->createMany([
            ['quantity_count' => 9,  'length' => 4.30, 'width' => 4.30, 'height_depth' => 1],
            ['quantity_count' => 3,  'length' => 3.70, 'width' => 3.70, 'height_depth' => 1],
            ['quantity_count' => 13, 'length' => 3.70, 'width' => 3.70, 'height_depth' => 1],
        ]);

        // Right Description: Foundation Pads F4-F6
        $rightDesc1 = $item1->descriptions()->create([
            'side' => 'right',
            'description' => 'Foundation Footing Pad - Group 2',
        ]);

        $rightDesc1->measurements()->createMany([
            ['quantity_count' => 48, 'length' => 3.40, 'width' => 3.40, 'height_depth' => 1],
            ['quantity_count' => 40, 'length' => 2.80, 'width' => 2.80, 'height_depth' => 1],
            ['quantity_count' => 6,  'length' => 2.40, 'width' => 2.40, 'height_depth' => 1],
        ]);

        // Item 2: Foundation Wall
        $item2 = $sheet1->items()->create([
            'item_number' => '2',
            'description' => 'BITUMINOUS DAMP PROOFING - Foundation Wall',
            'display_order' => 1,
        ]);

        $leftDesc2 = $item2->descriptions()->create([
            'side' => 'left',
            'description' => 'Foundation Footing Wall - External',
        ]);

        $leftDesc2->measurements()->createMany([
            ['quantity_count' => 1, 'length' => 7.00, 'width' => 3.50, 'height_depth' => 1],
            ['quantity_count' => 1, 'length' => 5.50, 'width' => 2.80, 'height_depth' => 1],
        ]);

        $rightDesc2 = $item2->descriptions()->create([
            'side' => 'right',
            'description' => 'Foundation Footing Wall - Internal',
        ]);

        $rightDesc2->measurements()->createMany([
            ['quantity_count' => 2, 'length' => 4.20, 'width' => 3.00, 'height_depth' => 1],
            ['quantity_count' => 1, 'length' => 6.00, 'width' => 2.50, 'height_depth' => 1],
        ]);

        // Sheet 2: Road Marking
        $sheet2 = TakeoffSheet::create([
            'project_id' => $project->id,
            'sheet_number' => 'TO-002',
            'division' => 'Road Works',
            'page_no' => 1,
            'measurement_date' => Carbon::now()->subDays(15),
            'measured_by' => 'Site Engineer',
            'status' => 'draft',
        ]);

        $item3 = $sheet2->items()->create([
            'item_number' => '1',
            'description' => 'Road Marking Works',
            'display_order' => 0,
        ]);

        $leftDesc3 = $item3->descriptions()->create([
            'side' => 'left',
            'description' => 'White Lines',
        ]);

        $leftDesc3->measurements()->createMany([
            ['quantity_count' => 1, 'length' => 861.30, 'width' => 1, 'height_depth' => 1],
            ['quantity_count' => 1, 'length' => 268.20, 'width' => 1, 'height_depth' => 1],
        ]);

        $rightDesc3 = $item3->descriptions()->create([
            'side' => 'right',
            'description' => 'Yellow & Zebra Lines',
        ]);

        $rightDesc3->measurements()->createMany([
            ['quantity_count' => 1, 'length' => 1737.60, 'width' => 1, 'height_depth' => 1],
            ['quantity_count' => 1, 'length' => 396.30, 'width' => 1, 'height_depth' => 1],
        ]);

        echo "✅ Takeoff Sheet sample data created!\n";
        echo "   - Sheet TO-001: Foundation Waterproofing (2 items, 4 descriptions, 13 measurements)\n";
        echo "   - Sheet TO-002: Road Marking (1 item, 2 descriptions, 4 measurements)\n";
    }
}
