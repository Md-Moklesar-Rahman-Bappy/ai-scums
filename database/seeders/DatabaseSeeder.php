<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder.
 *
 * Seeds the RBAC foundation, a platform super admin and a demo institution
 * with its admin so the application is usable immediately after install.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Platform super administrator (cross-tenant).
        $super = User::firstOrCreate(
            ['email' => 'superadmin@iems.test'],
            [
                'name' => 'Platform Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );
        $super->assignRole('super_admin');

        // Demo institution + admin.
        $institution = Institution::firstOrCreate(
            ['slug' => 'demo-school'],
            [
                'name' => 'Demo School',
                'type' => 'school',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'institution_id' => $institution->id,
                'is_active' => true,
            ]
        );
        $admin->assignRole('institution_admin');
    }
}
