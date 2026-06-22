<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'SSO Admin', 'email' => 'sso_admin@dkmapps.com', 'role' => 'sso_admin'],
            ['name' => 'Admin Inventory', 'email' => 'admin@dkmapps.com', 'role' => 'admin'],
            ['name' => 'User Inventory', 'email' => 'user@dkmapps.com', 'role' => 'user'],
            ['name' => 'Approval Inventory', 'email' => 'approval@dkmapps.com', 'role' => 'approval'],
            ['name' => 'Viewer Inventory', 'email' => 'viewer@dkmapps.com', 'role' => 'viewer'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], [
                'name' => $user['name'],
                'role' => $user['role'],
                'is_active' => true,
            ]);
        }
    }
}
