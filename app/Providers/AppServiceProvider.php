<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
=======
use Illuminate\Support\Facades\Schema; // <--- AJOUTER CETTE LIGNE

class AppServiceProvider extends ServiceProvider
{
>>>>>>> 80726fd (Fix local migration issues manually)
    public function register(): void
    {
        //
    }

<<<<<<< HEAD
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour l'erreur "Key too long" sur les anciennes versions de MySQL/MariaDB
        Schema::defaultStringLength(191);
=======
    public function boot(): void
    {
        Schema::defaultStringLength(191); // <--- AJOUTER CETTE LIGNE
>>>>>>> 80726fd (Fix local migration issues manually)
    }
}
