<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed super admin users who can access and configure everything.
     */
    public function run(): void
    {
        // Generate Shield permissions for all resources
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
        ]);

        // Reset permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Assign all permissions to super_admin role
        $superAdminRole->syncPermissions(Permission::all());

        $daniel = User::query()->firstOrCreate(
            ['email' => 'daniel@thws.de'],
            [
                'name' => 'Daniel',
                'surname' => 'Goncharov',
                'password' => 'password',
            ]
        );
        $daniel->syncRoles([$superAdminRole, $studentRole]);

        $tim = User::query()->firstOrCreate(
            ['email' => 'tim@thws.de'],
            [
                'name' => 'Tim',
                'surname' => 'Admin',
                'password' => 'password',
            ]
        );
        $tim->syncRoles([$superAdminRole, $studentRole]);
    }
}
