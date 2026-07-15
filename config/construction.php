<?php
return [
    'company' => [
        'name' => env('COMPANY_NAME', 'TNT Construction and Trading'),
        'address' => env('COMPANY_ADDRESS', 'Addis Ababa, Ethiopia'),
        'phone' => env('COMPANY_PHONE', '+251-000-000000'),
        'email' => env('COMPANY_EMAIL', 'info@tntconstruction.com'),
        'tax_id' => env('COMPANY_TAX_ID', 'TIN-000000000'),
    ],
    
    'defaults' => [
        'vat_percentage' => 15,
        'retention_percentage' => 5,
        'currency' => 'ETB',
        'date_format' => 'm/d/Y',
        'items_per_page' => 20,
    ],
    
    'ipc' => [
        'prefix' => 'IPC',
        'approval_workflow' => ['draft', 'submitted', 'approved', 'paid'],
    ],
    
    'measurement' => [
        'units' => ['m2', 'm3', 'kg', 'pcs', 'LS', 'm', 'liter', 'roll', 'cylinder', 'bag'],
        'rebar_standard_weight_formula' => 'd²/162',
    ],
];
