<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DatabaseSeeder.
 *
 * Seeds the RBAC foundation and (in non-production environments only) a
 * platform super admin and a demo institution with its admin so the
 * application is usable immediately after install.
 *
 * SECURITY: demo accounts with a known weak password are NEVER created in
 * production. In production only the RBAC foundation is seeded; real accounts
 * are created through the registration / invitation flow.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Real demo data is strictly a local / staging convenience.
        if (! app()->environment('local', 'staging')) {
            $this->command->info('Production environment detected: skipping demo accounts and data.');

            return;
        }

        // Platform super administrator (cross-tenant). A random, strong password
        // is generated when not supplied via DEMO_SUPERADMIN_PASSWORD so the
        // seeded credential is never a guessable default.
        $superPassword = config('demo.superadmin_password') ?? Str::random(24);
        $super = User::firstOrCreate(
            ['email' => config('demo.superadmin_email', 'superadmin@iems.test')],
            [
                'name' => 'Platform Super Admin',
                'password' => Hash::make($superPassword),
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

        $adminPassword = config('demo.admin_password') ?? Str::random(24);
        $admin = User::firstOrCreate(
            ['email' => config('demo.admin_email', 'admin@demo.test')],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make($adminPassword),
                'institution_id' => $institution->id,
                'is_active' => true,
            ]
        );
        $admin->assignRole('institution_admin');

        if (app()->environment('local')) {
            $this->command->warn('Demo credentials (random, shown once):');
            $this->command->info("  superadmin@iems.test / {$superPassword}");
            $this->command->info("  admin@demo.test / {$adminPassword}");
        }

        // Rich demo dataset for the demo school so the UI and AI assistant
        // are immediately usable after a fresh install.
        $this->call(DemoDataSeeder::class);
    }
}
