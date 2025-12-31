{{-- Force loading of application styles (Tailwind) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    /* Force Full Screen & Remove Filament Padding */
    .fi-layout, .fi-main, .fi-page {
        height: 100vh !important;
        width: 100vw !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
        overflow: hidden !important;
    }
    
    /* Hide Filament Header if present */
    header.fi-header, .fi-topbar {
        display: none !important;
    }

    /* Reset Body */
    body {
        overflow: hidden;
    }
</style>

<div class="w-full h-full bg-gray-50 dark:bg-gray-900">
    @livewire('pos-interface')
</div>
