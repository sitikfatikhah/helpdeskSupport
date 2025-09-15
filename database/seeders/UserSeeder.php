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
        Role::firstOrCreate(['name' => 'super_admin']);

        // Buat user
        $user = User::create([
            'nik' => '12345678',
            'name' => 'Admin User',
            'company_id' => 1,
            'email' => 'admin@example.com',
            'status' => 'active',
            'password' => Hash::make('admin123'),
        ]);

        // Assign role
        $user->assignRole('super_admin');
    }
}
