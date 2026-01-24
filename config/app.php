<?php
/**
 * Application Configuration
 * 
 * 'mode' can be 'demo' or 'production'
 * - 'demo' uses CSV files and in-memory repositories
 * - 'production' uses MySQL with Basic Auth
 * 
 * In production mode, 'auth' credentials are required for Basic Auth protection.
 */

return [
    'mode' => 'demo', // Set to 'production' to use MySQL and enable authentication

    // Authentication credentials (required in production mode)
    'auth' => [
        'user' => 'admin',
        'pass' => 'placeholder',
    ],
];
