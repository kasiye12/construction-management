<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\CostCategory;
use App\Models\BoqItem;
use App\Models\Ipc;
use App\Models\IpcItem;
use App\Models\LaborResource;
use App\Models\MaterialResource;
use App\Models\EquipmentResource;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Seeding sample data...\n";

        // ==========================================
        // PROJECT 1: Bus Terminal
        // ==========================================
        $project1 = Project::create([
            'name' => 'Addis Ababa Corridor Development – Megenagna Bus & Taxi Terminal',
            'client_name' => 'Addis Ababa City Administration',
            'contractor_name' => 'TNT Construction and Trading',
            'start_date' => Carbon::parse('2023-01-15'),
            'end_date' => Carbon::parse('2024-06-30'),
            'contract_amount' => 50000000,
            'description' => 'Construction of Megenagna Bus & Taxi Terminal including foundation, superstructure, finishing works, and external works.',
            'status' => 'active'
        ]);
        echo "✅ Project 1 created\n";

        // PROJECT 2: Office Building
        $project2 = Project::create([
            'name' => 'Axolon Engineering Head Office Building',
            'client_name' => 'Axolon Engineering PLC',
            'contractor_name' => 'Axolon Construction',
            'start_date' => Carbon::parse('2023-03-01'),
            'end_date' => Carbon::parse('2024-09-30'),
            'contract_amount' => 35000000,
            'description' => 'Design and construction of 5-story office building with basement parking.',
            'status' => 'active'
        ]);
        echo "✅ Project 2 created\n";

        // PROJECT 3: Completed Project
        $project3 = Project::create([
            'name' => 'Bole Residential Apartments',
            'client_name' => 'Bole Homes PLC',
            'contractor_name' => 'TNT Construction and Trading',
            'start_date' => Carbon::parse('2022-06-01'),
            'end_date' => Carbon::parse('2023-12-31'),
            'contract_amount' => 25000000,
            'description' => 'Construction of 3 residential apartment blocks.',
            'status' => 'completed'
        ]);
        echo "✅ Project 3 created\n";

        // ==========================================
        // SUBCONTRACTORS
        // ==========================================
        $sub1 = Subcontractor::create([
            'name' => 'Amare Water Proofing PLC',
            'contact_person' => 'Amare Abebe',
            'email' => 'amare@waterproofing.com',
            'phone' => '+251911234567',
            'address' => 'Bole Sub City, Addis Ababa',
            'tax_id' => 'TIN-001234567',
            'is_active' => true
        ]);

        $sub2 = Subcontractor::create([
            'name' => 'Ethio Steel Works',
            'contact_person' => 'Solomon Haile',
            'email' => 'solomon@ethiosteel.com',
            'phone' => '+251922345678',
            'address' => 'Akaki Kality, Addis Ababa',
            'tax_id' => 'TIN-002345678',
            'is_active' => true
        ]);

        $sub3 = Subcontractor::create([
            'name' => 'Mekonnen Electrical Installation',
            'contact_person' => 'Mekonnen Tesfaye',
            'email' => 'mekonnen@electrical.com',
            'phone' => '+251933456789',
            'address' => 'Megenagna, Addis Ababa',
            'tax_id' => 'TIN-003456789',
            'is_active' => true
        ]);

        $sub4 = Subcontractor::create([
            'name' => 'Addis Plastering & Finishing',
            'contact_person' => 'Dawit Girma',
            'email' => 'dawit@plastering.com',
            'phone' => '+251944567890',
            'address' => 'CMC, Addis Ababa',
            'tax_id' => 'TIN-004567890',
            'is_active' => true
        ]);
        echo "✅ 4 Subcontractors created\n";

        // Attach subcontractors to projects
        $project1->subcontractors()->attach($sub1->id, [
            'contract_amount' => 5750000,
            'contract_start_date' => Carbon::parse('2023-06-01'),
            'contract_end_date' => Carbon::parse('2024-06-30'),
            'scope_of_work' => 'Water proofing works for foundation and basement'
        ]);
        
        $project1->subcontractors()->attach($sub2->id, [
            'contract_amount' => 3200000,
            'contract_start_date' => Carbon::parse('2023-04-01'),
            'contract_end_date' => Carbon::parse('2024-03-31'),
            'scope_of_work' => 'Steel reinforcement works'
        ]);

        $project2->subcontractors()->attach($sub3->id, [
            'contract_amount' => 4500000,
            'contract_start_date' => Carbon::parse('2023-06-01'),
            'contract_end_date' => Carbon::parse('2024-08-31'),
            'scope_of_work' => 'Complete electrical installation'
        ]);

        $project2->subcontractors()->attach($sub4->id, [
            'contract_amount' => 2800000,
            'contract_start_date' => Carbon::parse('2023-08-01'),
            'contract_end_date' => Carbon::parse('2024-07-31'),
            'scope_of_work' => 'Plastering and finishing works'
        ]);
        echo "✅ Subcontractors attached to projects\n";

        // ==========================================
        // COST CATEGORIES
        // ==========================================
        // Project 1 Categories
        $cat1A = CostCategory::create([
            'project_id' => $project1->id,
            'code' => 'A',
            'name' => 'SUB-STRUCTURE',
            'display_order' => 1
        ]);
        $cat1B = CostCategory::create([
            'project_id' => $project1->id,
            'code' => 'B',
            'name' => 'SUPER-STRUCTURE',
            'display_order' => 2
        ]);
        $cat1C = CostCategory::create([
            'project_id' => $project1->id,
            'code' => 'C',
            'name' => 'FINISHING WORKS',
            'display_order' => 3
        ]);

        // Project 2 Categories
        $cat2A = CostCategory::create([
            'project_id' => $project2->id,
            'code' => 'A',
            'name' => 'SUB-STRUCTURE',
            'display_order' => 1
        ]);
        $cat2B = CostCategory::create([
            'project_id' => $project2->id,
            'code' => 'B',
            'name' => 'SUPER-STRUCTURE',
            'display_order' => 2
        ]);
        echo "✅ Cost categories created\n";

        // ==========================================
        // BOQ ITEMS - Project 1
        // ==========================================
        
        // Parent items for Project 1
        $p1Parent1 = BoqItem::create([
            'project_id' => $project1->id,
            'cost_category_id' => $cat1A->id,
            'item_number' => '1',
            'description' => 'Excavation & Earthwork',
            'unit' => 'LS',
            'quantity' => 1,
            'unit_rate' => 0,
            'revenue_amount' => 0,
            'is_parent' => true,
            'display_order' => 1
        ]);

        $p1Parent2 = BoqItem::create([
            'project_id' => $project1->id,
            'cost_category_id' => $cat1A->id,
            'item_number' => '2',
            'description' => 'Water Proofing Works',
            'unit' => 'LS',
            'quantity' => 1,
            'unit_rate' => 0,
            'revenue_amount' => 0,
            'is_parent' => true,
            'display_order' => 2
        ]);

        // Child item: Site Clearance
        $item1 = BoqItem::create([
            'project_id' => $project1->id,
            'cost_category_id' => $cat1A->id,
            'parent_id' => $p1Parent1->id,
            'item_number' => '1.01',
            'description' => 'Site Clearance to remove top soil 30cm thick from NGL',
            'unit' => 'm2',
            'quantity' => 5234,
            'unit_rate' => 75,
            'revenue_amount' => 392550,
            'duration_days' => 5,
            'planned_start_date' => Carbon::parse('2023-01-15'),
            'planned_end_date' => Carbon::parse('2023-01-20'),
            'status' => 'completed'
        ]);

        // Add resources for Site Clearance
        LaborResource::create([
            'boq_item_id' => $item1->id,
            'trade_name' => 'Equipment Operator',
            'number_of_workers' => 1,
            'total_hours' => 40,
            'wage_per_day' => 800,
            'amount' => 4000
        ]);
        LaborResource::create([
            'boq_item_id' => $item1->id,
            'trade_name' => 'Grease Boy',
            'number_of_workers' => 1,
            'total_hours' => 40,
            'wage_per_day' => 400,
            'amount' => 2000
        ]);
        EquipmentResource::create([
            'boq_item_id' => $item1->id,
            'description' => 'Excavator (1.15m3 capacity)',
            'duration_days' => 5,
            'number_of_units' => 1,
            'total_hours' => 40,
            'rate_per_hour' => 1500,
            'amount' => 60000
        ]);

        // Child item: Water Proofing Membrane
        $item2 = BoqItem::create([
            'project_id' => $project1->id,
            'cost_category_id' => $cat1A->id,
            'parent_id' => $p1Parent2->id,
            'item_number' => '2.01',
            'description' => 'Supply and Apply 4mm thick APP-Modified water proofing membrane (under Foundation)',
            'unit' => 'm2',
            'quantity' => 5000,
            'unit_rate' => 1150,
            'revenue_amount' => 5750000,
            'duration_days' => 30,
            'planned_start_date' => Carbon::parse('2023-06-15'),
            'planned_end_date' => Carbon::parse('2023-07-15'),
            'status' => 'in_progress'
        ]);

        // Resources for Water Proofing
        LaborResource::create([
            'boq_item_id' => $item2->id,
            'trade_name' => 'Water Proofing Applicator',
            'number_of_workers' => 10,
            'total_hours' => 2400,
            'wage_per_day' => 800,
            'amount' => 240000
        ]);
        LaborResource::create([
            'boq_item_id' => $item2->id,
            'trade_name' => 'Assistant',
            'number_of_workers' => 5,
            'total_hours' => 1200,
            'wage_per_day' => 500,
            'amount' => 75000
        ]);
        MaterialResource::create([
            'boq_item_id' => $item2->id,
            'description' => 'APP Modified Water Proofing Membrane (4mm)',
            'unit' => 'roll',
            'quantity' => 250,
            'unit_rate' => 15000,
            'amount' => 3750000
        ]);
        MaterialResource::create([
            'boq_item_id' => $item2->id,
            'description' => 'Primer',
            'unit' => 'liter',
            'quantity' => 1000,
            'unit_rate' => 350,
            'amount' => 350000
        ]);
        MaterialResource::create([
            'boq_item_id' => $item2->id,
            'description' => 'Gas for torch',
            'unit' => 'cylinder',
            'quantity' => 50,
            'unit_rate' => 2500,
            'amount' => 125000
        ]);
        EquipmentResource::create([
            'boq_item_id' => $item2->id,
            'description' => 'Torch Set',
            'duration_days' => 30,
            'number_of_units' => 10,
            'total_hours' => 2400,
            'rate_per_hour' => 50,
            'amount' => 120000
        ]);

        // Backfill item
        $item3 = BoqItem::create([
            'project_id' => $project1->id,
            'cost_category_id' => $cat1A->id,
            'parent_id' => $p1Parent1->id,
            'item_number' => '1.02',
            'description' => 'Backfill under mat foundation compacted, 95% MDD per AASHTO T-180',
            'unit' => 'm3',
            'quantity' => 5351,
            'unit_rate' => 125,
            'revenue_amount' => 668875,
            'duration_days' => 2,
            'planned_start_date' => Carbon::parse('2023-02-05'),
            'planned_end_date' => Carbon::parse('2023-02-07'),
            'status' => 'completed'
        ]);

        LaborResource::create([
            'boq_item_id' => $item3->id,
            'trade_name' => 'Assistant Foreman',
            'number_of_workers' => 1,
            'total_hours' => 16,
            'wage_per_day' => 600,
            'amount' => 1200
        ]);
        MaterialResource::create([
            'boq_item_id' => $item3->id,
            'description' => 'Select Material',
            'unit' => 'm3',
            'quantity' => 5725.57,
            'unit_rate' => 460,
            'amount' => 2633762.20
        ]);
        EquipmentResource::create([
            'boq_item_id' => $item3->id,
            'description' => 'Loader',
            'duration_days' => 2,
            'number_of_units' => 1,
            'total_hours' => 16,
            'rate_per_hour' => 2500,
            'amount' => 40000
        ]);

        echo "✅ BOQ Items for Project 1 created\n";

        // ==========================================
        // BOQ ITEMS - Project 2
        // ==========================================
        $p2Parent1 = BoqItem::create([
            'project_id' => $project2->id,
            'cost_category_id' => $cat2A->id,
            'item_number' => '1',
            'description' => 'Foundation Works',
            'unit' => 'LS',
            'quantity' => 1,
            'unit_rate' => 0,
            'revenue_amount' => 0,
            'is_parent' => true,
            'display_order' => 1
        ]);

        $p2Item1 = BoqItem::create([
            'project_id' => $project2->id,
            'cost_category_id' => $cat2A->id,
            'parent_id' => $p2Parent1->id,
            'item_number' => '1.01',
            'description' => 'Reinforced Concrete Foundation Grade Beam',
            'unit' => 'm3',
            'quantity' => 450,
            'unit_rate' => 8500,
            'revenue_amount' => 3825000,
            'duration_days' => 45,
            'planned_start_date' => Carbon::parse('2023-03-15'),
            'planned_end_date' => Carbon::parse('2023-04-30'),
            'status' => 'completed'
        ]);

        LaborResource::create([
            'boq_item_id' => $p2Item1->id,
            'trade_name' => 'Carpenter',
            'number_of_workers' => 8,
            'total_hours' => 2880,
            'wage_per_day' => 700,
            'amount' => 252000
        ]);
        LaborResource::create([
            'boq_item_id' => $p2Item1->id,
            'trade_name' => 'Steel Fixer',
            'number_of_workers' => 6,
            'total_hours' => 2160,
            'wage_per_day' => 750,
            'amount' => 202500
        ]);
        MaterialResource::create([
            'boq_item_id' => $p2Item1->id,
            'description' => 'Ready Mix Concrete C-25',
            'unit' => 'm3',
            'quantity' => 450,
            'unit_rate' => 5500,
            'amount' => 2475000
        ]);
        MaterialResource::create([
            'boq_item_id' => $p2Item1->id,
            'description' => 'Reinforcement Steel',
            'unit' => 'kg',
            'quantity' => 45000,
            'unit_rate' => 85,
            'amount' => 3825000
        ]);
        EquipmentResource::create([
            'boq_item_id' => $p2Item1->id,
            'description' => 'Concrete Pump',
            'duration_days' => 10,
            'number_of_units' => 1,
            'total_hours' => 80,
            'rate_per_hour' => 3000,
            'amount' => 240000
        ]);

        $p2Parent2 = BoqItem::create([
            'project_id' => $project2->id,
            'cost_category_id' => $cat2B->id,
            'item_number' => '2',
            'description' => 'Electrical Works',
            'unit' => 'LS',
            'quantity' => 1,
            'unit_rate' => 0,
            'revenue_amount' => 0,
            'is_parent' => true,
            'display_order' => 2
        ]);

        $p2Item2 = BoqItem::create([
            'project_id' => $project2->id,
            'cost_category_id' => $cat2B->id,
            'parent_id' => $p2Parent2->id,
            'item_number' => '2.01',
            'description' => 'Electrical Conduit and Wiring Installation',
            'unit' => 'm',
            'quantity' => 2500,
            'unit_rate' => 450,
            'revenue_amount' => 1125000,
            'duration_days' => 60,
            'planned_start_date' => Carbon::parse('2023-07-01'),
            'planned_end_date' => Carbon::parse('2023-08-30'),
            'status' => 'in_progress'
        ]);

        LaborResource::create([
            'boq_item_id' => $p2Item2->id,
            'trade_name' => 'Electrician',
            'number_of_workers' => 4,
            'total_hours' => 1920,
            'wage_per_day' => 900,
            'amount' => 216000
        ]);
        MaterialResource::create([
            'boq_item_id' => $p2Item2->id,
            'description' => 'PVC Conduit 20mm',
            'unit' => 'm',
            'quantity' => 2500,
            'unit_rate' => 45,
            'amount' => 112500
        ]);
        MaterialResource::create([
            'boq_item_id' => $p2Item2->id,
            'description' => 'Electrical Wire 2.5mm²',
            'unit' => 'm',
            'quantity' => 5000,
            'unit_rate' => 35,
            'amount' => 175000
        ]);

        echo "✅ BOQ Items for Project 2 created\n";

        // ==========================================
        // IPC CREATION
        // ==========================================
        
        // IPC 1 for Project 1 - Water Proofing
        $ipc1 = Ipc::create([
            'project_id' => $project1->id,
            'subcontractor_id' => $sub1->id,
            'ipc_number' => 'IPC-01',
            'issue_number' => 1,
            'ipc_date' => Carbon::parse('2023-07-31'),
            'period_start_date' => Carbon::parse('2023-07-01'),
            'period_end_date' => Carbon::parse('2023-07-31'),
            'total_previous_amount' => 0,
            'total_current_amount' => 2300000,
            'total_to_date_amount' => 2300000,
            'retention_percentage' => 5,
            'retention_amount' => 115000,
            'net_payment_amount' => 2185000,
            'remarks' => '40% Completed',
            'status' => 'approved'
        ]);

        IpcItem::create([
            'ipc_id' => $ipc1->id,
            'boq_item_id' => $item2->id,
            'contract_quantity' => 5000,
            'contract_amount' => 5750000,
            'previous_quantity' => 0,
            'previous_amount' => 0,
            'current_quantity' => 2000,
            'current_amount' => 2300000,
            'to_date_quantity' => 2000,
            'to_date_amount' => 2300000,
            'percentage_complete' => 40,
            'remark' => '40% Paid'
        ]);

        // IPC 2 for Project 1 - Water Proofing (Second payment)
        $ipc2 = Ipc::create([
            'project_id' => $project1->id,
            'subcontractor_id' => $sub1->id,
            'ipc_number' => 'IPC-02',
            'issue_number' => 2,
            'ipc_date' => Carbon::parse('2023-09-30'),
            'period_start_date' => Carbon::parse('2023-09-01'),
            'period_end_date' => Carbon::parse('2023-09-30'),
            'total_previous_amount' => 2300000,
            'total_current_amount' => 1725000,
            'total_to_date_amount' => 4025000,
            'retention_percentage' => 5,
            'retention_amount' => 201250,
            'net_payment_amount' => 3823750,
            'remarks' => '70% Completed',
            'status' => 'submitted'
        ]);

        IpcItem::create([
            'ipc_id' => $ipc2->id,
            'boq_item_id' => $item2->id,
            'contract_quantity' => 5000,
            'contract_amount' => 5750000,
            'previous_quantity' => 2000,
            'previous_amount' => 2300000,
            'current_quantity' => 1500,
            'current_amount' => 1725000,
            'to_date_quantity' => 3500,
            'to_date_amount' => 4025000,
            'percentage_complete' => 70,
            'remark' => '70% Paid'
        ]);

        // IPC 3 for Project 2 - Electrical
        $ipc3 = Ipc::create([
            'project_id' => $project2->id,
            'subcontractor_id' => $sub3->id,
            'ipc_number' => 'IPC-01',
            'issue_number' => 1,
            'ipc_date' => Carbon::parse('2023-08-31'),
            'period_start_date' => Carbon::parse('2023-08-01'),
            'period_end_date' => Carbon::parse('2023-08-31'),
            'total_previous_amount' => 0,
            'total_current_amount' => 562500,
            'total_to_date_amount' => 562500,
            'retention_percentage' => 5,
            'retention_amount' => 28125,
            'net_payment_amount' => 534375,
            'remarks' => '50% Completed',
            'status' => 'paid'
        ]);

        IpcItem::create([
            'ipc_id' => $ipc3->id,
            'boq_item_id' => $p2Item2->id,
            'contract_quantity' => 2500,
            'contract_amount' => 1125000,
            'previous_quantity' => 0,
            'previous_amount' => 0,
            'current_quantity' => 1250,
            'current_amount' => 562500,
            'to_date_quantity' => 1250,
            'to_date_amount' => 562500,
            'percentage_complete' => 50,
            'remark' => 'First payment'
        ]);

        echo "✅ IPCs created\n";
        echo "========================================\n";
        echo "🎉 SAMPLE DATA SEEDING COMPLETE!\n";
        echo "========================================\n";
        echo "\n";
        echo "Summary:\n";
        echo "  - 3 Projects (2 Active, 1 Completed)\n";
        echo "  - 4 Subcontractors\n";
        echo "  - 6 Cost Categories\n";
        echo "  - 7 BOQ Items with resources\n";
        echo "  - 3 IPCs with payment tracking\n";
        echo "\n";
        echo "Login: admin@cms.com / password\n";
    }
}
