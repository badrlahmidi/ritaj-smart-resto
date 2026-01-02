<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'POS' }} - {{ config('app.name') }}</title>
    
    <!-- Fullscreen & App Mode -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { overscroll-behavior: none; } /* Empêche le rebond sur tactile */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased overflow-hidden h-screen w-screen">
    {{ $slot }}
    
    @filamentScripts
    @livewire('notifications')
</body>
</html>
