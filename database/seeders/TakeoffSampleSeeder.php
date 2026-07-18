<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\BoqItem;
use App\Models\QuantityTakeoff;
use App\Models\MaterialDelivery;
use Carbon\Carbon;

class TakeoffSampleSeeder extends Seeder
{
    public function run()
    {
        // Get existing project
        $project = Project::first();
        if (!$project) {
            echo "No project found. Run SampleDataSeeder first.\n";
            return;
        }

        // Create a subcontractor for road marking
        $subcontractor = Subcontractor::firstOrCreate(
            ['name' => 'UNITRAC STEEL ONE MEMBER PLC'],
            [
                'contact_person' => 'Unitrac Manager',
                'email' => 'unitrac@example.com',
                'phone' => '+251-000-000000',
                'is_active' => true,
            ]
        );

        // Assign subcontractor to project if not already
        if (!$project->subcontractors()->where('subcontractor_id', $subcontractor->id)->exists()) {
            $project->subcontractors()->attach($subcontractor->id, [
                'contract_amount' => 2500000,
                'scope_of_work' => 'Road marking works - White lines, Yellow edge lines, Zebra crossing',
            ]);
        }

        // Create BOQ items for road marking if not exist
        $items = [
            ['item_number' => '3.01', 'description' => 'White Broken & Unbroken Line Marking', 'unit' => 'ml', 'quantity' => 861.30, 'unit_rate' => 450],
            ['item_number' => '3.02', 'description' => 'White Parking Line Marking', 'unit' => 'ml', 'quantity' => 268.20, 'unit_rate' => 400],
            ['item_number' => '3.03', 'description' => 'Yellow Edge Line Marking', 'unit' => 'ml', 'quantity' => 1737.60, 'unit_rate' => 500],
            ['item_number' => '3.04', 'description' => 'Zebra Crossing Line Marking', 'unit' => 'ml', 'quantity' => 396.30, 'unit_rate' => 600],
        ];

        foreach ($items as $itemData) {
            $boqItem = BoqItem::firstOrCreate(
                ['project_id' => $project->id, 'item_number' => $itemData['item_number']],
                [
                    'project_id' => $project->id,
                    'description' => $itemData['description'],
                    'unit' => $itemData['unit'],
                    'quantity' => $itemData['quantity'],
                    'unit_rate' => $itemData['unit_rate'],
                    'revenue_amount' => $itemData['quantity'] * $itemData['unit_rate'],
                    'status' => 'in_progress',
                ]
            );

            // Create Quantity Takeoff for each item
            $takeoffData = [
                '3.01' => ['length' => 861.30, 'quantity_count' => 1, 'prev_qty' => 775.17, 'curr_qty' => 86.13, 'location' => 'Nefas Silk Lafto Site - Main Road'],
                '3.02' => ['length' => 268.20, 'quantity_count' => 1, 'prev_qty' => 241.38, 'curr_qty' => 26.82, 'location' => 'Nefas Silk Lafto Site - Parking Area'],
                '3.03' => ['length' => 1737.60, 'quantity_count' => 1, 'prev_qty' => 1563.84, 'curr_qty' => 173.76, 'location' => 'Nefas Silk Lafto Site - Road Edge'],
                '3.04' => ['length' => 396.30, 'quantity_count' => 1, 'prev_qty' => 356.67, 'curr_qty' => 39.63, 'location' => 'Nefas Silk Lafto Site - Crossing'],
            ];

            if (isset($takeoffData[$itemData['item_number']])) {
                $td = $takeoffData[$itemData['item_number']];
                
                QuantityTakeoff::firstOrCreate(
                    ['project_id' => $project->id, 'boq_item_id' => $boqItem->id],
                    [
                        'structure_type' => 'Road Marking',
                        'element_id' => $itemData['item_number'],
                        'location_axis' => $td['location'],
                        'quantity_count' => $td['quantity_count'],
                        'length' => $td['length'],
                        'width' => 1,
                        'height_depth' => 1,
                        'total_area_volume' => $td['length'] * 1 * 1,
                        'measurement_date' => Carbon::now()->subDays(15),
                        'measured_by' => 'Site Engineer',
                        'status' => 'verified',
                        'remarks' => "Previous: {$td['prev_qty']} ml, Current: {$td['curr_qty']} ml",
                    ]
                );
            }
        }

        // Create Material Deliveries
        $deliveries = [
            [
                'item_description' => 'Thermoplastic Road Marking Paint (White)',
                'unit' => 'kg',
                'quantity' => 500,
                'unit_multiplier' => 1,
                'gate_pass_number' => 'GP-2024-001',
                'delivery_date' => Carbon::now()->subDays(30),
                'source_location' => 'Head Office Warehouse',
            ],
            [
                'item_description' => 'Thermoplastic Road Marking Paint (Yellow)',
                'unit' => 'kg',
                'quantity' => 300,
                'unit_multiplier' => 1,
                'gate_pass_number' => 'GP-2024-002',
                'delivery_date' => Carbon::now()->subDays(25),
                'source_location' => 'Head Office Warehouse',
            ],
            [
                'item_description' => 'Glass Beads for Road Marking',
                'unit' => 'kg',
                'quantity' => 150,
                'unit_multiplier' => 1,
                'gate_pass_number' => 'GP-2024-003',
                'delivery_date' => Carbon::now()->subDays(20),
                'source_location' => 'Supplier - Addis Ababa',
            ],
            [
                'item_description' => 'Reflective Road Studs',
                'unit' => 'pcs',
                'quantity' => 200,
                'unit_multiplier' => 1,
                'gate_pass_number' => 'GP-2024-004',
                'delivery_date' => Carbon::now()->subDays(10),
                'source_location' => 'Supplier - Addis Ababa',
            ],
        ];

        foreach ($deliveries as $del) {
            MaterialDelivery::firstOrCreate(
                ['gate_pass_number' => $del['gate_pass_number']],
                [
                    'project_id' => $project->id,
                    'subcontractor_id' => $subcontractor->id,
                    'item_description' => $del['item_description'],
                    'unit' => $del['unit'],
                    'quantity' => $del['quantity'],
                    'unit_multiplier' => $del['unit_multiplier'],
                    'converted_quantity' => $del['quantity'] * $del['unit_multiplier'],
                    'delivery_date' => $del['delivery_date'],
                    'source_location' => $del['source_location'],
                    'created_by' => 1,
                ]
            );
        }

        echo "✅ Sample Take-Off and Material Delivery data created!\n";
        echo "   - 4 Quantity Take-Off records\n";
        echo "   - 4 Material Delivery records\n";
        echo "   - Subcontractor: UNITRAC STEEL ONE MEMBER PLC\n";
    }
}
