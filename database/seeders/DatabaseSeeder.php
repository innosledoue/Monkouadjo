<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        User::firstOrCreate(
            ['email' => 'test@monkouadjo.ci'],
            ['name' => 'Test Kouadjo', 'password' => Hash::make('password')]
        );
    }
}
