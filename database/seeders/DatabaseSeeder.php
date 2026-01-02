<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use App\Models\UserPin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Nettoyage (Optionnel, désactiver si en prod)
        Schema::disableForeignKeyConstraints();
        Table::truncate();
        Area::truncate();
        Product::truncate();
        Category::truncate();
        OptionGroup::truncate();
        Option::truncate();
        // User::truncate(); // On garde les users existants par sécurité
        Schema::enableForeignKeyConstraints();

        // 2. Utilisateurs & Staff
        $admin = User::firstOrCreate(['email' => 'admin@ritaj.com'], [
            'name' => 'Patron',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $server = User::firstOrCreate(['email' => 'serveur@ritaj.com'], [
            'name' => 'Ahmed Serveur',
            'password' => Hash::make('password'),
            'role' => 'server',
            'is_active' => true,
        ]);
        // PIN pour le serveur (1234)
        UserPin::updateOrCreate(['user_id' => $server->id], ['pin_code' => Hash::make('1234')]);

        $kitchen = User::firstOrCreate(['email' => 'chef@ritaj.com'], [
            'name' => 'Chef Cuisine',
            'password' => Hash::make('password'),
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        // 3. Zones & Tables
        $mainArea = Area::create(['name' => 'Salle Principale']);
        $terraceArea = Area::create(['name' => 'Terrasse']);

        // Tables Salle (1-5)
        for ($i = 1; $i <= 5; $i++) {
            Table::create([
                'name' => "T$i",
                'area_id' => $mainArea->id,
                'capacity' => 4,
                'status' => 'available',
                'shape' => 'square'
            ]);
        }
        // Tables Terrasse (10-12)
        for ($i = 10; $i <= 12; $i++) {
            Table::create([
                'name' => "T$i",
                'area_id' => $terraceArea->id,
                'capacity' => 2,
                'status' => 'available',
                'shape' => 'round'
            ]);
        }

        // 4. Groupes d'Options
        $cookingGroup = OptionGroup::create([
            'name' => 'Cuisson Viande',
            'is_multiselect' => false,
            'is_required' => true,
        ]);
        $cookingGroup->options()->createMany([
            ['name' => 'Saignant', 'price_modifier' => 0],
            ['name' => 'À point', 'price_modifier' => 0],
            ['name' => 'Bien cuit', 'price_modifier' => 0],
        ]);

        $sauceGroup = OptionGroup::create([
            'name' => 'Sauces Supplémentaires',
            'is_multiselect' => true,
            'max_options' => 2,
        ]);
        $sauceGroup->options()->createMany([
            ['name' => 'Mayonnaise Maison', 'price_modifier' => 5],
            ['name' => 'Sauce Champignon', 'price_modifier' => 10],
            ['name' => 'Sauce Poivre', 'price_modifier' => 8],
        ]);

        // 5. Catégories & Produits

        // --- BOISSONS ---
        $catDrinks = Category::create(['name' => 'Boissons', 'is_active' => true]);
        
        Product::create([
            'name' => 'Coca Cola',
            'category_id' => $catDrinks->id,
            'price' => 15,
            'cost' => 5,
            'track_stock' => true,
            'stock_quantity' => 100,
            'kitchen_station' => 'bar',
            'image_url' => null, // Placeholder handled by UI
        ]);
        
        Product::create([
            'name' => 'Eau Minérale 1L',
            'category_id' => $catDrinks->id,
            'price' => 10,
            'cost' => 3,
            'track_stock' => true,
            'stock_quantity' => 50,
            'kitchen_station' => 'bar',
        ]);

        Product::create([
            'name' => 'Espresso',
            'category_id' => $catDrinks->id,
            'price' => 12,
            'cost' => 2,
            'kitchen_station' => 'bar',
        ]);

        // --- PLATS ---
        $catMain = Category::create(['name' => 'Plats Principaux', 'is_active' => true]);

        $burger = Product::create([
            'name' => 'Cheeseburger Royal',
            'category_id' => $catMain->id,
            'price' => 65,
            'cost' => 25,
            'kitchen_station' => 'kitchen',
            'description' => 'Steak haché frais 180g, Cheddar, Salade, Tomate, Oignons confits.',
        ]);
        $burger->optionGroups()->attach([$cookingGroup->id, $sauceGroup->id]);

        $pizza = Product::create([
            'name' => 'Pizza Margherita',
            'category_id' => $catMain->id,
            'price' => 50,
            'cost' => 15,
            'kitchen_station' => 'pizza_oven',
        ]);
        $pizza->optionGroups()->attach([$sauceGroup->id]); // Juste pour l'exemple

        // --- DESSERTS ---
        $catDessert = Category::create(['name' => 'Desserts', 'is_active' => true]);

        Product::create([
            'name' => 'Tiramisu',
            'category_id' => $catDessert->id,
            'price' => 35,
            'cost' => 10,
            'kitchen_station' => 'dessert',
            'stock_quantity' => 10, // Stock tracké (portions prêtes)
            'track_stock' => true,
        ]);
    }
}