<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\BoqItem;
use App\Models\QuantityTakeoff;
use Carbon\Carbon;

class WaterproofingTakeoffSeeder extends Seeder
{
    public function run()
    {
        $project = Project::first();
        if (!$project) { echo "No project found.\n"; return; }

        // Find or create BOQ item for waterproofing
        $boqItem = BoqItem::firstOrCreate(
            ['project_id' => $project->id, 'item_number' => '2.01'],
            [
                'description' => 'Supply and Apply 4mm thick APP-Modified water proofing membrane (under Foundation)',
                'unit' => 'm2',
                'quantity' => 5000,
                'unit_rate' => 1150,
                'revenue_amount' => 5750000,
                'status' => 'in_progress',
            ]
        );

        // Foundation footing pads data
        $pads = [
            ['element' => 'F1', 'qty' => 9, 'size' => 4.30, 'area' => 166.41],
            ['element' => 'F2', 'qty' => 3, 'size' => 3.70, 'area' => 41.07],
            ['element' => 'F3', 'qty' => 13, 'size' => 3.70, 'area' => 177.97],
            ['element' => 'F4', 'qty' => 48, 'size' => 3.40, 'area' => 554.88],
            ['element' => 'F5', 'qty' => 40, 'size' => 2.80, 'area' => 313.60],
            ['element' => 'F6', 'qty' => 25, 'size' => 3.20, 'area' => 256.00],
        ];

        foreach ($pads as $pad) {
            QuantityTakeoff::create([
                'project_id' => $project->id,
                'boq_item_id' => $boqItem->id,
                'structure_type' => 'BITUMINOUS DAMP PROOFING - Foundation Footing',
                'element_id' => $pad['element'],
                'location_axis' => 'Foundation Footing Pad ' . $pad['element'],
                'quantity_count' => $pad['qty'],
                'length' => $pad['size'],
                'width' => $pad['size'],
                'height_depth' => 1,
                'total_area_volume' => $pad['area'],
                'measurement_date' => Carbon::now()->subDays(10),
                'measured_by' => 'Site Engineer',
                'status' => 'draft',
                'remarks' => "Pad {$pad['element']}: {$pad['qty']} × {$pad['size']} × {$pad['size']} = {$pad['area']} m²",
            ]);
        }

        echo "✅ Created " . count($pads) . " waterproofing take-off records!\n";
    }
}
