<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Enums\AdminRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create one admin for each role
        $admins = [
            // [
            //     'name' => 'Super Admin',
            //     'email' => 'super@admin.com',
            //     'role' => AdminRole::SUPER_ADMIN,
            // ],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'role' => AdminRole::ADMIN,
            ],
            [
                'name' => 'Tutor User',
                'email' => 'tutor@admin.com',
                'role' => AdminRole::TUTOR,
            ],
            [
                'name' => 'Finance User',
                'email' => 'finance@admin.com',
                'role' => AdminRole::FINANCE,
            ],
            [
                'name' => 'Sales User',
                'email' => 'sales@admin.com',
                'role' => AdminRole::SALES,
            ],
            [
                'name' => 'Management User',
                'email' => 'management@admin.com',
                'role' => AdminRole::MANAGEMENT,
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create([
                ...$admin,
                'password' => Hash::make('password'), // Default password for all admins
                'is_active' => true,
                'phone' => '1234567890', // Default phone number
            ]);
        }
    }
} 