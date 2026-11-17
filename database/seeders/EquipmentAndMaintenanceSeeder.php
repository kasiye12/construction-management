<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\PmSchedule;
use App\Models\PmScheduleItem;
use App\Models\EquipmentBreakdownReport;
use App\Models\EquipmentBreakdownItem;
use App\Models\EquipmentMaintenanceLog;
use App\Models\Project;
use App\Models\User;

class EquipmentAndMaintenanceSeeder extends Seeder
{
    public function run()
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;
        $project = Project::first();
        $projectId = $project ? $project->id : null;

        // 1. Seed Core TNT Fleet Assets (from FORMAT 1.xlsx)
        $fleet = [
            // Earth Moving & Plant
            [
                'code' => 'EX-0889',
                'equipment_type' => 'EXCAVATOR',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'KOMATSU',
                'model' => 'PC200-8',
                'plate_serial_no' => 'EX-0889',
                'serial_no' => 'KMTPC200-98231',
                'location_name' => 'Central Workshop',
                'reading_unit' => 'hr',
                'current_reading' => 8450.00,
                'last_service_reading' => 8327.00,
                'last_service_date' => '2026-08-15',
                'last_service_type' => 'B',
                'next_service_reading' => 8500.00,
                'next_service_type' => 'C',
                'status' => 'due_service',
            ],
            [
                'code' => 'EX-0601',
                'equipment_type' => 'EXCAVATOR',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'CATERPILLAR',
                'model' => 'CAT 320D',
                'plate_serial_no' => 'EX-0601',
                'serial_no' => 'CAT0320D-77218',
                'location_name' => 'Ambo Referral Project',
                'reading_unit' => 'hr',
                'current_reading' => 9120.00,
                'last_service_reading' => 8958.00,
                'last_service_date' => '2026-09-01',
                'last_service_type' => 'A',
                'next_service_reading' => 9100.00,
                'next_service_type' => 'B',
                'status' => 'breakdown',
            ],
            [
                'code' => 'CMM-0095',
                'equipment_type' => 'CARMIX',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'CARMIX',
                'model' => '3.5 TT',
                'plate_serial_no' => 'CMM-0095',
                'serial_no' => 'CMX35-44109',
                'location_name' => 'Ambo Referral Project',
                'reading_unit' => 'hr',
                'current_reading' => 4320.00,
                'last_service_reading' => 4000.00,
                'last_service_date' => '2026-07-20',
                'last_service_type' => 'A',
                'next_service_reading' => 4500.00,
                'next_service_type' => 'B',
                'status' => 'under_maintenance',
            ],
            [
                'code' => 'LD-2232',
                'equipment_type' => 'BACKHOE LOADER',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'JCB',
                'model' => '3CX',
                'plate_serial_no' => 'LD-2232',
                'serial_no' => 'JCB3CX-88412',
                'location_name' => 'Arba Minch Project',
                'reading_unit' => 'hr',
                'current_reading' => 6100.00,
                'last_service_reading' => 5800.00,
                'last_service_date' => '2026-08-10',
                'last_service_type' => 'A',
                'next_service_reading' => 6250.00,
                'next_service_type' => 'B',
                'status' => 'active',
            ],
            [
                'code' => 'LD-1633',
                'equipment_type' => 'WHEEL LOADER',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'LIUGONG',
                'model' => 'CLG 856',
                'plate_serial_no' => 'LD-1633',
                'serial_no' => 'LG856-33910',
                'location_name' => 'Mega Project',
                'reading_unit' => 'hr',
                'current_reading' => 7450.00,
                'last_service_reading' => 7200.00,
                'last_service_date' => '2026-07-15',
                'last_service_type' => 'B',
                'next_service_reading' => 7700.00,
                'next_service_type' => 'A',
                'status' => 'active',
            ],
            [
                'code' => 'CM-0562',
                'equipment_type' => 'ROLLER COMPACTOR',
                'category' => 'EARTH MOVING MACHINE',
                'make' => 'DYNAPAC',
                'model' => 'CA250D',
                'plate_serial_no' => 'CM-0562',
                'serial_no' => 'DYN250-1092',
                'location_name' => 'South Campus Project',
                'reading_unit' => 'hr',
                'current_reading' => 5890.00,
                'last_service_reading' => 5650.00,
                'last_service_date' => '2026-06-25',
                'last_service_type' => 'A',
                'next_service_reading' => 5900.00,
                'next_service_type' => 'B',
                'status' => 'due_service',
            ],

            // Heavy Trucks
            [
                'code' => 'TRK-3-50327',
                'equipment_type' => 'DUMP TRUCK',
                'category' => 'DUMP TRUCK',
                'make' => 'SINOTRUK',
                'model' => 'HOWO 371',
                'plate_serial_no' => '3-50327',
                'serial_no' => 'SINO371-992381',
                'location_name' => 'Central Workshop',
                'reading_unit' => 'km',
                'current_reading' => 124500.00,
                'last_service_reading' => 119500.00,
                'last_service_date' => '2026-08-01',
                'last_service_type' => 'B2',
                'next_service_reading' => 125000.00,
                'next_service_type' => 'C2',
                'status' => 'due_service',
            ],
            [
                'code' => 'TRK-3-32675',
                'equipment_type' => 'WATER TRUCK',
                'category' => 'DUMP TRUCK',
                'make' => 'ISUZU',
                'model' => 'FSR 10,000L',
                'plate_serial_no' => '3-32675',
                'serial_no' => 'ISZFSR-33410',
                'location_name' => 'Bishoftu Site',
                'reading_unit' => 'km',
                'current_reading' => 98400.00,
                'last_service_reading' => 93000.00,
                'last_service_date' => '2026-07-12',
                'last_service_type' => 'A2',
                'next_service_reading' => 100000.00,
                'next_service_type' => 'B2',
                'status' => 'active',
            ],
            [
                'code' => 'TRK-3-85963',
                'equipment_type' => 'DUMP TRUCK',
                'category' => 'DUMP TRUCK',
                'make' => 'IVECO',
                'model' => 'TRAKKER 380',
                'plate_serial_no' => '3-85963',
                'serial_no' => 'IVCTRK-99120',
                'location_name' => 'Mega Project',
                'reading_unit' => 'km',
                'current_reading' => 165800.00,
                'last_service_reading' => 160000.00,
                'last_service_date' => '2026-08-18',
                'last_service_type' => 'A2',
                'next_service_reading' => 165000.00,
                'next_service_type' => 'B2',
                'status' => 'breakdown',
            ],

            // Light Vehicles
            [
                'code' => 'VEH-3-A44801',
                'equipment_type' => 'PICK UP',
                'category' => 'LIGHT VEHICLES',
                'make' => 'TOYOTA',
                'model' => 'HILUX D4D 4WD',
                'plate_serial_no' => '3-A44801',
                'serial_no' => 'TOYHLX-551029',
                'location_name' => 'Central Depot',
                'reading_unit' => 'km',
                'current_reading' => 88400.00,
                'last_service_reading' => 83400.00,
                'last_service_date' => '2026-08-20',
                'last_service_type' => 'A2',
                'next_service_reading' => 90000.00,
                'next_service_type' => 'B2',
                'status' => 'active',
            ],
            [
                'code' => 'VEH-3-71050',
                'equipment_type' => 'PICK UP',
                'category' => 'LIGHT VEHICLES',
                'make' => 'TOYOTA',
                'model' => 'HILUX DOUBLE CABIN',
                'plate_serial_no' => '3-71050',
                'serial_no' => 'TOYHLX-901182',
                'location_name' => 'Ambo Referral Project',
                'reading_unit' => 'km',
                'current_reading' => 112300.00,
                'last_service_reading' => 107000.00,
                'last_service_date' => '2026-07-28',
                'last_service_type' => 'A2',
                'next_service_reading' => 112000.00,
                'next_service_type' => 'B2',
                'status' => 'active',
            ],
            [
                'code' => 'VEH-2-A47404',
                'equipment_type' => 'PICK UP',
                'category' => 'LIGHT VEHICLES',
                'make' => 'MITSUBISHI',
                'model' => 'L200 SPORTERO',
                'plate_serial_no' => '2-A47404',
                'serial_no' => 'MITL200-44910',
                'location_name' => 'Central Workshop',
                'reading_unit' => 'km',
                'current_reading' => 142000.00,
                'last_service_reading' => 135000.00,
                'last_service_date' => '2026-06-15',
                'last_service_type' => 'C2',
                'next_service_reading' => 140000.00,
                'next_service_type' => 'A2',
                'status' => 'under_maintenance',
            ],

            // Auxiliary & Plant
            [
                'code' => 'GEN-AKSA-132',
                'equipment_type' => 'GENERATOR',
                'category' => 'AUXILIARY',
                'make' => 'AKSA',
                'model' => 'APD 132',
                'plate_serial_no' => '132 KVA',
                'serial_no' => 'AKS132-77291',
                'location_name' => 'Central Depot',
                'reading_unit' => 'hr',
                'current_reading' => 3120.00,
                'last_service_reading' => 2950.00,
                'last_service_date' => '2026-08-05',
                'last_service_type' => 'A',
                'next_service_reading' => 3200.00,
                'next_service_type' => 'B',
                'status' => 'active',
            ],
            [
                'code' => 'GEN-IVECO-60',
                'equipment_type' => 'GENERATOR',
                'category' => 'AUXILIARY',
                'make' => 'IVECO',
                'model' => 'GE 60',
                'plate_serial_no' => '60 KVA',
                'serial_no' => 'IVC60-22104',
                'location_name' => 'Central Workshop',
                'reading_unit' => 'hr',
                'current_reading' => 4560.00,
                'last_service_reading' => 4300.00,
                'last_service_date' => '2026-07-10',
                'last_service_type' => 'A',
                'next_service_reading' => 4550.00,
                'next_service_type' => 'B',
                'status' => 'breakdown',
            ],
        ];

        $createdEquipments = [];
        foreach ($fleet as $item) {
            $item['project_id'] = $projectId;
            $item['created_by'] = $adminId;
            $item['current_reading_date'] = now()->toDateString();
            $eq = Equipment::updateOrCreate(
                ['plate_serial_no' => $item['plate_serial_no']],
                $item
            );
            $createdEquipments[$eq->plate_serial_no] = $eq;
        }

        // 2. Create Initial Preventive Maintenance Schedule (OF/EAMD/074)
        $schedule = PmSchedule::updateOrCreate(
            ['schedule_code' => 'PMS-2026-W46'],
            [
                'title' => 'Preventive Maintenance Schedule',
                'document_code' => 'OF/EAMD/074',
                'issue_no' => '1',
                'page_no' => 'Page 1 of 1',
                'project_id' => $projectId,
                'project_location' => 'All Sites / Central Workshop',
                'schedule_date' => now()->toDateString(),
                'period_start' => now()->startOfWeek()->toDateString(),
                'period_end' => now()->endOfWeek()->toDateString(),
                'status' => 'approved',
                'prepared_by' => $adminId,
                'prepared_name' => $admin ? $admin->name : 'Site Engineer',
                'prepared_at' => now()->subDays(2),
                'checked_by' => $adminId,
                'checked_name' => 'Equipment Manager',
                'checked_at' => now()->subDay(),
                'approved_by' => $adminId,
                'approved_name' => 'Head of EAMD',
                'approved_at' => now(),
                'created_by' => $adminId,
            ]
        );

        $sort = 0;
        foreach ($createdEquipments as $eq) {
            $remain = $eq->next_service_reading - $eq->current_reading;
            $status = 'Normal';
            if ($remain < 0) $status = 'Overdue';
            elseif ($remain <= 50) $status = 'Due';

            PmScheduleItem::updateOrCreate(
                [
                    'pm_schedule_id' => $schedule->id,
                    'equipment_id' => $eq->id,
                ],
                [
                    'category' => $eq->category,
                    'equipment_type' => $eq->equipment_type,
                    'make' => $eq->make,
                    'model' => $eq->model,
                    'plate_serial_no' => $eq->plate_serial_no,
                    'serial_no' => $eq->serial_no,
                    'location' => $eq->location_name,
                    'last_service_reading' => $eq->last_service_reading,
                    'last_service_date' => $eq->last_service_date,
                    'last_service_type' => $eq->last_service_type,
                    'next_service_reading' => $eq->next_service_reading,
                    'next_service_type' => $eq->next_service_type,
                    'current_reading' => $eq->current_reading,
                    'current_reading_date' => $eq->current_reading_date,
                    'remain_reading' => $remain,
                    'service_status' => $status,
                    'remark' => $status === 'Overdue' ? 'Service critically overdue, schedule filter & oil change immediately' : ($status === 'Due' ? 'Service due in 50 hr/km' : 'Operational'),
                    'sort_order' => $sort++,
                ]
            );
        }

        // 3. Create Initial Breakdown Report (OF/TNT/ EAMD/030)
        $breakdownReport = EquipmentBreakdownReport::updateOrCreate(
            ['report_code' => 'MR-2026-W46'],
            [
                'title' => 'Maintenance Report',
                'document_code' => 'OF/TNT/ EAMD/030',
                'issue_no' => '1',
                'page_no' => 'Page 1 of 1',
                'report_date' => now()->toDateString(),
                'project_id' => $projectId,
                'location_name' => 'Central Workshop & Project Sites',
                'status' => 'checked',
                'prepared_by' => $adminId,
                'prepared_name' => 'Getish Y.',
                'prepared_at' => now()->subDay(),
                'checked_by' => $adminId,
                'checked_name' => 'Tewodros',
                'checked_at' => now(),
                'created_by' => $adminId,
            ]
        );

        $breakdownData = [
            [
                'plate' => 'EX-0889',
                'type' => 'EXCAVATOR',
                'category' => 'EARTH MOVING MACHINE AND PLANT',
                'reason' => 'ENGINE OVERHAUL',
                'action' => 'DOWN',
                'location' => 'CENTRAL',
                'repair_status' => 'down',
            ],
            [
                'plate' => 'EX-0601',
                'type' => 'EXCAVATOR',
                'category' => 'EARTH MOVING MACHINE AND PLANT',
                'reason' => 'OVERHEATING',
                'action' => 'DOWN',
                'location' => 'AMBO REFERRAL',
                'repair_status' => 'under_maintenance',
            ],
            [
                'plate' => 'CMM-0095',
                'type' => 'CARMIX',
                'category' => 'EARTH MOVING MACHINE AND PLANT',
                'reason' => 'HYDRAULIC PUMP',
                'action' => 'DOWN',
                'location' => 'AMBO REFERRAL',
                'repair_status' => 'proforma',
            ],
            [
                'plate' => '3-85963',
                'type' => 'DUMP TRUCK',
                'category' => 'DUMP TRUCK',
                'reason' => 'BATTERY & STARTER',
                'action' => 'DOWN',
                'location' => 'MEGA',
                'repair_status' => 'purchase',
            ],
            [
                'plate' => '2-A47404',
                'type' => 'MITSUBISHI',
                'category' => 'LIGHT VEHICLES',
                'reason' => 'ENGINE OVERHAUL',
                'action' => 'UNDER MAINTENANCE',
                'location' => 'CENTRAL',
                'repair_status' => 'under_maintenance',
            ],
            [
                'plate' => '60 KVA',
                'type' => 'GENERATOR-IVECO',
                'category' => 'AUXILIARY',
                'reason' => 'ENGINE OVERHAUL',
                'action' => 'DOWN',
                'location' => 'CENTRAL',
                'repair_status' => 'down',
            ],
        ];

        $bSort = 0;
        foreach ($breakdownData as $b) {
            $eq = $createdEquipments[$b['plate']] ?? null;
            EquipmentBreakdownItem::updateOrCreate(
                [
                    'breakdown_report_id' => $breakdownReport->id,
                    'plate_no_sn' => $b['plate'],
                ],
                [
                    'equipment_id' => $eq?->id,
                    'category' => $b['category'],
                    'equipment_type' => $b['type'],
                    'quantity' => 1,
                    'down_date' => now()->subDays(rand(5, 30))->toDateString(),
                    'reason_of_down' => $b['reason'],
                    'action_taken' => $b['action'],
                    'location' => $b['location'],
                    'repair_status' => $b['repair_status'],
                    'sort_order' => $bSort++,
                ]
            );
        }

        // 4. Create Sample Daily Maintenance Logs
        $logs = [
            [
                'plate' => '3-50327',
                'type' => 'SINO TRUCK',
                'part' => 'Return spring and brake lining replacement',
                'po_no' => 'EQ-MPR000035',
                'unit' => 'PCS',
                'qty' => 2,
                'unit_cost' => 1956.52,
                'total_cost_with_vat' => 4500.00,
                'supplier' => 'Abiye Eshetu Spare Parts',
            ],
            [
                'plate' => '3-A44801',
                'type' => 'TOYOTA PICKUP',
                'part' => 'Axle shaft extract and replace, differential housing welding',
                'po_no' => 'EQ-MPR000036',
                'unit' => 'SET',
                'qty' => 1,
                'unit_cost' => 8843.48,
                'total_cost_with_vat' => 10170.00,
                'supplier' => 'Kasma Engineering',
            ],
            [
                'plate' => 'EX-0601',
                'type' => 'EXCAVATOR',
                'part' => 'Hydraulic oil top-up (ISO VG 68)',
                'po_no' => 'PBR-EQ000116',
                'unit' => 'LIT',
                'qty' => 20,
                'unit_cost' => 250.00,
                'total_cost_with_vat' => 5750.00,
                'supplier' => 'TotalEnergies Ethiopia',
            ],
            [
                'plate' => '132 KVA',
                'type' => 'GENERATOR',
                'part' => 'Oil filter JS2021 & fuel filter FS19652 replacement',
                'po_no' => 'POEQ/16/00099',
                'unit' => 'PCS',
                'qty' => 2,
                'unit_cost' => 1400.00,
                'total_cost_with_vat' => 3220.00,
                'supplier' => 'Auto Zone Addis',
            ],
        ];

        foreach ($logs as $log) {
            $eq = $createdEquipments[$log['plate']] ?? null;
            EquipmentMaintenanceLog::updateOrCreate(
                [
                    'po_no' => $log['po_no'],
                    'spare_part_name' => $log['part'],
                ],
                [
                    'equipment_id' => $eq?->id,
                    'equipment_type' => $log['type'],
                    'equipment_plate' => $log['plate'],
                    'unit' => $log['unit'],
                    'quantity' => $log['qty'],
                    'unit_cost' => $log['unit_cost'],
                    'total_cost_with_vat' => $log['total_cost_with_vat'],
                    'supplier' => $log['supplier'],
                    'log_date' => now()->subDays(rand(1, 14))->toDateString(),
                    'project_id' => $projectId,
                    'logged_by' => $adminId,
                ]
            );
        }
    }
}
