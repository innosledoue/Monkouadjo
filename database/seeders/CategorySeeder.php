<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Dépenses — contexte ivoirien
            ['name' => 'Alimentation',     'icon' => 'utensils',     'color' => '#ef4444', 'type' => 'expense'],
            ['name' => 'Maquis & Sorties', 'icon' => 'beer',         'color' => '#f97316', 'type' => 'expense'],
            ['name' => 'Transport',        'icon' => 'car',          'color' => '#f59e0b', 'type' => 'expense'],
            ['name' => 'Téléphonie',       'icon' => 'phone',        'color' => '#10b981', 'type' => 'expense'],
            ['name' => 'Loyer',            'icon' => 'home',         'color' => '#3b82f6', 'type' => 'expense'],
            ['name' => 'CIE (Électricité)','icon' => 'bolt',         'color' => '#eab308', 'type' => 'expense'],
            ['name' => 'SODECI (Eau)',     'icon' => 'droplet',      'color' => '#0ea5e9', 'type' => 'expense'],
            ['name' => 'Santé',            'icon' => 'heart',        'color' => '#ec4899', 'type' => 'expense'],
            ['name' => 'Éducation',        'icon' => 'graduation-cap','color' => '#8b5cf6', 'type' => 'expense'],
            ['name' => 'Famille',          'icon' => 'users',        'color' => '#a855f7', 'type' => 'expense'],
            ['name' => 'Tontine',          'icon' => 'piggy-bank',   'color' => '#14b8a6', 'type' => 'expense'],
            ['name' => 'Habillement',      'icon' => 'shirt',        'color' => '#d946ef', 'type' => 'expense'],
            ['name' => 'Loisirs',          'icon' => 'gamepad-2',    'color' => '#06b6d4', 'type' => 'expense'],
            ['name' => 'Frais bancaires',  'icon' => 'credit-card',  'color' => '#64748b', 'type' => 'expense'],
            ['name' => 'Autre dépense',    'icon' => 'circle-dot',   'color' => '#94a3b8', 'type' => 'expense'],

            // Revenus
            ['name' => 'Salaire',          'icon' => 'wallet',       'color' => '#22c55e', 'type' => 'income'],
            ['name' => 'Commerce',         'icon' => 'store',        'color' => '#16a34a', 'type' => 'income'],
            ['name' => 'Mobile Money reçu','icon' => 'smartphone',   'color' => '#84cc16', 'type' => 'income'],
            ['name' => 'Tontine perçue',   'icon' => 'gift',         'color' => '#06b6d4', 'type' => 'income'],
            ['name' => 'Autre revenu',     'icon' => 'circle-plus',  'color' => '#94a3b8', 'type' => 'income'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['user_id' => null, 'name' => $cat['name']],
                array_merge($cat, ['is_default' => true])
            );
        }
    }
}
