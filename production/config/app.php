<?php
/**
 * Application Configuration
 * 
 * 'mode' can be 'demo' or 'production'
 * - 'demo' uses CSV files and in-memory repositories
 * - 'production' uses MySQL with Basic Auth
 * 
 * In production mode, 'auth' credentials are required for Basic Auth protection.
 * In demo mode, 'data_dir' specifies the path to CSV data files.
 */

return [
    'mode' => 'demo', // Set to 'production' to use MySQL and enable authentication

    // Path to data directory (used in demo mode for CSV files)
    // Can be absolute or relative to the application root
    'data_dir' => __DIR__ . '/../../data',

    // Authentication credentials (required in production mode)
    'auth' => [
        'user' => 'admin',
        'pass' => 'placeholder',
    ],
];
