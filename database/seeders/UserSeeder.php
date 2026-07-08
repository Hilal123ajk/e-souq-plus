<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@e-souq-plus.com'],
            [
                'name' => 'E-Souq Admin',
                'password' => 'E-Souq@Admin2026',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'manager@e-souq-plus.com'],
            [
                'name' => 'Store Manager',
                'password' => 'E-Souq@Manager2026',
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );
    }
}
