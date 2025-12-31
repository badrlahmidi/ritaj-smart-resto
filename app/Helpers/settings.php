<?php

if (!function_exists('settings')) {
    /**
     * Get a setting value by key (format: 'group.field')
     * 
     * @param string $key Example: 'general.site_name'
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function settings(string $key, $default = null)
    {
        try {
            if (!str_contains($key, '.')) {
                return $default;
            }

            [$group, $field] = explode('.', $key, 2);
            
            $class = match($group) {
                'general' => \App\Settings\GeneralSettings::class,
                'pos' => \App\Settings\PosSettings::class,
                'printer' => \App\Settings\PrinterSettings::class,
                'feature' => \App\Settings\FeatureSettings::class,
                default => null,
            };
            
            if (!$class || !class_exists($class)) {
                return $default;
            }
            
            $settings = app($class);
            return $settings->{$field} ?? $default;
            
        } catch (\Exception $e) {
            \Log::warning("Settings helper error: " . $e->getMessage());
            return $default;
        }
    }
}
