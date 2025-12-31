<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    // Configuration Imprimante (Multi-driver)
    'printer' => [
        'driver' => env('PRINTER_DRIVER', 'network'), // Options: 'network', 'windows', 'dummy'
        
        'network' => [
            'ip' => env('PRINTER_KITCHEN_IP', '192.168.1.200'),
            'port' => env('PRINTER_KITCHEN_PORT', 9100),
        ],
        
        'windows' => [
            // Nom de l'imprimante partagée ou locale (ex: "Microsoft Print to PDF" ou "EPSON_TM_T20")
            'name' => env('PRINTER_NAME', 'Microsoft Print to PDF'), 
        ],
    ],

];
