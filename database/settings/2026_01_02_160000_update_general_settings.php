<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // On utilise 'add' pour ne pas écraser l'existant si déjà présent, 
        // mais pour une refonte propre, on peut s'assurer que tout est là.
        
        // Identité
        $this->migrator->add('general.email', null);
        $this->migrator->add('general.facebook_url', null);
        $this->migrator->add('general.instagram_url', null);

        // Finance
        $this->migrator->add('general.currency_symbol', 'DH');
        $this->migrator->add('general.default_tax_rate', 10.0);

        // Modules (Activés par défaut pour la démo)
        $this->migrator->add('general.enable_stock_management', true);
        $this->migrator->add('general.enable_delivery', true);
        $this->migrator->add('general.enable_takeaway', true);
        $this->migrator->add('general.enable_kds', true);
    }
};
