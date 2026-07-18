<?php
return [
    'company' => [
        'name' => env('COMPANY_NAME', 'TNT Construction & Trading'),
        'short_name' => 'TNT',
        'domain' => env('COMPANY_DOMAIN', 'www.tnt-constructions.com'),
        'address' => 'Addis Ababa, Ethiopia',
        'phone' => '+251-000-000000',
        'email' => 'info@tnt-constructions.com',
        'tin' => '000000000',
    ],
    
    'document' => [
        'prefix' => env('DOC_PREFIX', 'OF/TNT/ECD'),
        'certificate_format' => '{prefix}/{number}',
    ],
    
    'defaults' => [
        'vat_percentage' => 15,
        'retention_percentage' => 5,
        'withholding_tax' => 2,
        'currency' => 'ETB',
        'date_format' => 'm/d/Y',
    ],
    
    'measurement' => [
        'units' => ['m2', 'm3', 'kg', 'pcs', 'LS', 'm', 'liter', 'roll', 'cylinder', 'bag'],
        'formula' => 'Qty × Length × Width × Height',
    ],
];
