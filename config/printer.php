<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Imprimante Principale (Reçus clients)
    |--------------------------------------------------------------------------
    */
    'ip' => env('PRINTER_IP', '192.168.1.100'),
    'port' => env('PRINTER_PORT', 9100),
    
    /*
    |--------------------------------------------------------------------------
    | Imprimante Cuisine (KDS - Kitchen Display System)
    |--------------------------------------------------------------------------
    */
    'kitchen_ip' => env('PRINTER_KITCHEN_IP', '192.168.1.101'),
    'kitchen_port' => env('PRINTER_KITCHEN_PORT', 9100),
    
    /*
    |--------------------------------------------------------------------------
    | Paramètres d'impression
    |--------------------------------------------------------------------------
    */
    'ticket_width' => 32,  // Largeur en caractères
    'date_format' => 'd/m/Y H:i',
    'logo_path' => storage_path('app/public/logo.png'),
    
    /*
    |--------------------------------------------------------------------------
    | Options avancées
    |--------------------------------------------------------------------------
    */
    'timeout' => 5,  // Timeout de connexion en secondes
    'retry_attempts' => 3,  // Nombre de tentatives en cas d'échec
];
