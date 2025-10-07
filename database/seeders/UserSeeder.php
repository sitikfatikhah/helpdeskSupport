<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role jika belum ada
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // Buat user
        $admin = User::create([
            'nik' => '1111',
            'name' => 'Super Admin',
            'company_name' => 'Tesing',
            'email' => 'admin@example.com',
            'status' => 'active',
            'password' => Hash::make('admin123'),
        ]);

        $admin->assignRole($role);

        $user = User::create([
            'nik' => '1112',
            'name' => 'User',
            'company_name' => 'testing',
            'email' => 'user@example.com',
            'status' => 'active',
            'password' => Hash::make('123456789'),
        ]);

        $user->assignRole('super_admin');
    }
}
