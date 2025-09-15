<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole      = Role::firstOrCreate(['name' => 'administrator']);
        $memberRole     = Role::firstOrCreate(['name' => 'member']);

        // Assign semua permission hanya ke superadmin
        $superadminRole->syncPermissions(Permission::all());

        $users = [
            [
                'nik' => '001',
                'name' => 'Superadmin',
                'email' => 'superadmin@csa.tes',
                'role' => $superadminRole,
                'department' => 'IT',
            ],
            [
                'nik' => '002',
                'name' => 'Administrator',
                'email' => 'administrator@csa.tes',
                'role' => $adminRole,
                'department' => 'FAT',
            ],
            [
                'nik' => '003',
                'name' => 'Operator',
                'email' => 'operator@csa.tes',
                'role' => $memberRole,
                'department' => 'HRGA',
            ],
            [
                'nik' => '004',
                'name' => 'Manager IT',
                'email' => 'manager@csa.tes',
                'role' => $memberRole,
                'department' => 'IT',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']], // prevent duplication
                [
                    'company_id'     => $company->id,
                    // 'department_id' => $departments[$userData['department']],
                    'nik'            => $userData['nik'],
                    'name'           => $userData['name'],
                    'status'         => 'active',
                    'password'       => Hash::make('123456789'),
                ]
            );
            $user->assignRole($userData['role']);
        }
    }
}
