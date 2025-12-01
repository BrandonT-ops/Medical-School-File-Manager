<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@medicalschool.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create a demo moderator user
        User::create([
            'name' => 'Moderator User',
            'email' => 'moderator@medicalschool.com',
            'password' => Hash::make('password'),
            'role' => 'moderator',
            'moderated_levels' => [1, 2], // Can moderate Level 1 and Level 2
            'email_verified_at' => now(),
        ]);

        // Create a demo standard user
        User::create([
            'name' => 'Student User',
            'email' => 'student@medicalschool.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Demo users created successfully!');
        $this->command->warn('IMPORTANT: Change default passwords in production!');
        $this->command->info('Admin: admin@medicalschool.com / password');
        $this->command->info('Moderator: moderator@medicalschool.com / password');
        $this->command->info('Student: student@medicalschool.com / password');
    }
}
