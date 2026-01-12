<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@genieinfo.tech'],
            [
                'name' => 'Admin',
                'email' => 'admin@genieinfo.tech',
                'password' => Hash::make('ChangeMe123!'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $this->command->info('Admin user created: admin@genieinfo.tech');
        $this->command->warn('Default password: ChangeMe123! - CHANGE THIS IMMEDIATELY!');

        // Create default settings
        foreach (Setting::getDefaults() as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_array($value) => 'array',
                is_int($value) => 'integer',
                default => 'string',
            };

            Setting::set($key, $value, $type);
        }

        $this->command->info('Default settings created.');
    }
}
