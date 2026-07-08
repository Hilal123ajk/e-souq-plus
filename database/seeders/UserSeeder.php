<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_SEED_PASSWORD');
        $managerPassword = env('MANAGER_SEED_PASSWORD');

        if (! is_string($adminPassword) || $adminPassword === '') {
            $this->command?->error('ADMIN_SEED_PASSWORD is not set in .env');
            return;
        }

        if (! is_string($managerPassword) || $managerPassword === '') {
            $this->command?->error('MANAGER_SEED_PASSWORD is not set in .env');
            return;
        }

        User::query()->updateOrCreate(
            ['email' => env('ADMIN_SEED_EMAIL', 'admin@e-souq-plus.com')],
            [
                'name' => 'E-Souq Admin',
                'password' => $adminPassword,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => env('MANAGER_SEED_EMAIL', 'manager@e-souq-plus.com')],
            [
                'name' => 'Store Manager',
                'password' => $managerPassword,
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );
    }
}
