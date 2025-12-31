<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création User Admin (si n'existe pas déjà)
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@ritaj.com'], 
            [
                'name' => 'Admin Ritaj',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Autres seeders (optionnel)
        // $this->call([
        //     ProductSeeder::class,
        // ]);
    }
}
