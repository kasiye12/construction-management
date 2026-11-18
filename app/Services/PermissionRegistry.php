<?php

namespace App\Services;

class PermissionRegistry
{
    /**
     * Complete list of permissions grouped by module/category
     */
    public static function getPermissionsGrouped(): array
    {
        return [
            'Projects Management' => [
                'projects.view' => 'View Projects & Project Details',
                'projects.create' => 'Create New Projects',
                'projects.edit' => 'Edit Project Details & Budget',
                'projects.delete' => 'Delete Projects',
            ],
            'BOQ & Cost Estimation' => [
                'boq.view' => 'View BOQ Items & Quantities',
                'boq.create' => 'Add BOQ Items & Resource Breakdown',
                'boq.edit' => 'Edit BOQ Items & Unit Rates',
                'boq.delete' => 'Delete BOQ Items',
            ],
            'Quantity Take-Off (QTO)' => [
                'takeoff.view' => 'View QTO Measurement Sheets',
                'takeoff.create' => 'Record QTO Measurements',
                'takeoff.edit' => 'Edit Takeoff Records',
                'takeoff.delete' => 'Delete Takeoff Records',
            ],
            'Takeoff Sheets (TS)' => [
                'takeoff-sheets.view' => 'View Takeoff Sheets',
                'takeoff-sheets.create' => 'Create Takeoff Sheets',
                'takeoff-sheets.edit' => 'Edit Takeoff Sheets',
                'takeoff-sheets.delete' => 'Delete Takeoff Sheets',
            ],
            'Contractual Follow-up (OF/TNT/CAD/01)' => [
                'contractual-reports.view' => 'View Weekly Contractual Reports',
                'contractual-reports.create' => 'Prepare & Create CAD-01 Reports',
                'contractual-reports.edit' => 'Edit CAD-01 Reports & Claim Events',
                'contractual-reports.delete' => 'Delete CAD-01 Reports',
                'contractual-reports.export' => 'Export Official 3-Page Excel & Print',
            ],
            'Payment Certificates (IPC)' => [
                'ipc.view' => 'View Interim Payment Certificates',
                'ipc.create' => 'Prepare New IPCs',
                'ipc.edit' => 'Edit IPC Progress & Quantities',
                'ipc.approve' => 'Approve Payment Certificates',
                'ipc.delete' => 'Delete IPC Records',
            ],
            'Material Deliveries' => [
                'deliveries.view' => 'View Material Delivery Notes',
                'deliveries.create' => 'Record Material Delivery (GRN)',
                'deliveries.edit' => 'Edit Material Delivery Records',
                'deliveries.confirm' => 'Confirm & Verify Deliveries',
                'deliveries.delete' => 'Delete Material Delivery Records',
            ],
            'Actual Costs & Accounting' => [
                'actual-costs.view' => 'View Actual Cost Records & Ledger',
                'actual-costs.create' => 'Record Actual Costs & Expenses',
                'actual-costs.edit' => 'Edit Actual Cost Transactions',
                'actual-costs.delete' => 'Delete Actual Cost Records',
            ],
            'Gantt Timeline Schedule' => [
                'gantt.view' => 'View Project Schedule & Gantt Charts',
            ],
            'Subcontractors Management' => [
                'subcontractors.view' => 'View Subcontractors & Contract Values',
                'subcontractors.create' => 'Register Subcontractors',
                'subcontractors.edit' => 'Edit Subcontractor Details',
                'subcontractors.delete' => 'Delete Subcontractor Records',
            ],
            'Cost Categories' => [
                'cost-categories.view' => 'View Cost Categories',
                'cost-categories.create' => 'Create Cost Categories',
                'cost-categories.edit' => 'Edit Cost Categories',
                'cost-categories.delete' => 'Delete Cost Categories',
            ],
            'Equipment Fleet Registry (EAMD)' => [
                'equipment.view' => 'View Fleet Registry & Meter Readings',
                'equipment.create' => 'Register New Equipment Assets',
                'equipment.edit' => 'Edit Equipment Specifications & Readings',
                'equipment.delete' => 'Remove Equipment Assets',
            ],
            'Preventive Maintenance (OF/EAMD/074)' => [
                'pm-schedules.view' => 'View Preventive Maintenance Schedules',
                'pm-schedules.create' => 'Prepare & Create PM Schedules',
                'pm-schedules.edit' => 'Edit PM Schedules & Target Readings',
                'pm-schedules.delete' => 'Delete PM Schedules',
                'pm-schedules.export' => 'Export OF/074 Excel & Print A4',
            ],
            'Breakdown & Repairs (OF/TNT/EAMD/030)' => [
                'equipment-breakdowns.view' => 'View Breakdown & Repair Reports',
                'equipment-breakdowns.create' => 'Record Breakdown & Repair Reports',
                'equipment-breakdowns.edit' => 'Edit Breakdown Reports & Statuses',
                'equipment-breakdowns.delete' => 'Delete Breakdown Reports',
                'equipment-breakdowns.export' => 'Export OF/030 Excel & Print A4',
            ],
            'Daily Maintenance & Spare Parts Log' => [
                'equipment-logs.view' => 'View Daily Spare Parts & Repair Logs',
                'equipment-logs.create' => 'Record Spare Part & Labor Expenses',
                'equipment-logs.edit' => 'Edit Spare Part Transactions',
                'equipment-logs.delete' => 'Delete Spare Part Records',
                'equipment-logs.export' => 'Export Daily Maintenance Logs Excel',
            ],
            'Reports & Analytics' => [
                'reports.view' => 'View 30-Column and Variance Reports',
                'reports.export' => 'Export Executive Reports to Excel/PDF',
            ],
            'Documents & Attachments' => [
                'documents.view' => 'View & Download Project/Record Documents',
                'documents.upload' => 'Upload Attachments & Files',
                'documents.delete' => 'Delete Attached Documents',
            ],
            'User & Staff Management' => [
                'users.view' => 'View System Users & Profiles',
                'users.create' => 'Create New User Accounts',
                'users.edit' => 'Edit Users, Roles & Custom Permissions',
                'users.delete' => 'Deactivate / Delete Users',
            ],
            'Roles & Access Control' => [
                'roles.view' => 'View System Roles',
                'roles.create' => 'Create New Roles',
                'roles.edit' => 'Edit Roles & Assign Permissions',
                'roles.delete' => 'Delete Roles',
            ],
            'Administration & System Settings' => [
                'workflow.manage' => 'Manage 4-Stage Workflow Permissions Matrix',
                'settings.manage' => 'Configure Company & Tax Settings',
                'database.backup' => 'Manage Database Backups & Downloads',
                'database.sync' => 'Smart Database Import & Synchronization',
                'audit.view' => 'View System Audit Trail & Logs',
            ],
        ];
    }

    /**
     * Get a flat list of all valid permission keys
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (self::getPermissionsGrouped() as $group => $perms) {
            foreach ($perms as $key => $label) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Get default permissions map for standard roles
     */
    public static function getDefaultRolePermissions(): array
    {
        $allKeys = self::getAllPermissionKeys();

        return [
            'admin' => ['*'],
            'manager' => array_values(array_filter($allKeys, function($perm) {
                // Project manager has full operational and reporting access except system role and DB administration
                return !in_array($perm, ['roles.create', 'roles.delete', 'settings.manage', 'database.backup', 'database.sync']);
            })),
            'engineer' => [
                'projects.view',
                'boq.view', 'boq.create', 'boq.edit',
                'takeoff.view', 'takeoff.create', 'takeoff.edit',
                'takeoff-sheets.view', 'takeoff-sheets.create', 'takeoff-sheets.edit',
                'contractual-reports.view', 'contractual-reports.create', 'contractual-reports.edit', 'contractual-reports.export',
                'ipc.view', 'ipc.create', 'ipc.edit',
                'deliveries.view', 'deliveries.create', 'deliveries.confirm',
                'gantt.view',
                'subcontractors.view',
                'cost-categories.view',
                'equipment.view',
                'pm-schedules.view', 'pm-schedules.create', 'pm-schedules.edit', 'pm-schedules.export',
                'equipment-breakdowns.view', 'equipment-breakdowns.create', 'equipment-breakdowns.edit', 'equipment-breakdowns.export',
                'equipment-logs.view', 'equipment-logs.create',
                'reports.view',
                'documents.view', 'documents.upload',
            ],
            'finance' => [
                'projects.view',
                'boq.view',
                'contractual-reports.view', 'contractual-reports.export',
                'ipc.view', 'ipc.approve',
                'deliveries.view', 'deliveries.confirm',
                'actual-costs.view', 'actual-costs.create', 'actual-costs.edit', 'actual-costs.delete',
                'subcontractors.view',
                'cost-categories.view',
                'equipment.view',
                'pm-schedules.view', 'pm-schedules.export',
                'equipment-breakdowns.view', 'equipment-breakdowns.export',
                'equipment-logs.view', 'equipment-logs.create', 'equipment-logs.edit', 'equipment-logs.export',
                'reports.view', 'reports.export',
                'documents.view', 'documents.upload',
            ],
            'viewer' => [
                'projects.view',
                'boq.view',
                'takeoff.view',
                'takeoff-sheets.view',
                'contractual-reports.view',
                'ipc.view',
                'deliveries.view',
                'actual-costs.view',
                'gantt.view',
                'subcontractors.view',
                'cost-categories.view',
                'equipment.view',
                'pm-schedules.view',
                'equipment-breakdowns.view',
                'equipment-logs.view',
                'reports.view',
                'documents.view',
            ],
            'contract' => [
                'projects.view',
                'contractual-reports.view',
                'contractual-reports.create',
                'contractual-reports.edit',
                'contractual-reports.delete',
                'contractual-reports.export',
                'documents.view',
                'documents.upload',
            ],
        ];
    }
}
