<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.facebook_url', null);
        $this->migrator->add('general.instagram_url', null);
    }
};
