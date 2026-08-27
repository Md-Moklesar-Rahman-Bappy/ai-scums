<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RolesAndPermissionsSeeder.
 *
 * Creates the platform roles (super_admin, institution_admin, teacher, student,
 * parent, accountant) and a baseline set of permissions. Super admin receives
 * every permission; other roles receive a least-privilege subset.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Platform roles.
     *
     * @var array<int, string>
     */
    private array $roles = [
        'super_admin', 'institution_admin', 'teacher', 'student', 'parent', 'accountant',
    ];

    /**
     * Baseline permissions (resource.action).
     *
     * @var array<int, string>
     */
    private array $permissions = [
        'institutions.view', 'institutions.create', 'institutions.edit', 'institutions.delete',
        'students.view', 'students.create', 'students.edit', 'students.delete',
        'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete',
        'attendance.view', 'attendance.manage',
        'exams.view', 'exams.manage', 'marks.manage',
        'fees.view', 'fees.manage',
        'notices.view', 'notices.manage',
        'routines.view', 'routines.manage',
        'assistant.use',
        'reports.view',
    ];

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Refresh cached permissions.
        Artisan::call('permission:cache-reset');

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach ($this->roles as $roleName) {
            $role = Role::findOrCreate($roleName);

            if ($roleName === 'super_admin') {
                $role->syncPermissions(Permission::all());

                continue;
            }

            $granted = $this->permissionsForRole($roleName);
            $role->syncPermissions($granted);
        }
    }

    /**
     * Least-privilege permission map per role.
     *
     * @return array<int, string>
     */
    private function permissionsForRole(string $role): array
    {
        return match ($role) {
            'institution_admin' => [
                'institutions.view', 'students.view', 'students.create', 'students.edit', 'students.delete',
                'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete',
                'attendance.view', 'attendance.manage', 'exams.view', 'exams.manage', 'marks.manage',
                'fees.view', 'fees.manage', 'notices.view', 'notices.manage',
                'routines.view', 'routines.manage', 'assistant.use', 'reports.view',
            ],
            'teacher' => [
                'students.view', 'attendance.view', 'attendance.manage', 'exams.view', 'marks.manage',
                'routines.view', 'assistant.use', 'reports.view',
            ],
            'accountant' => ['fees.view', 'fees.manage', 'students.view', 'assistant.use', 'reports.view'],
            'student' => ['assistant.use', 'routines.view', 'notices.view'],
            'parent' => ['assistant.use', 'students.view', 'notices.view'],
            default => ['assistant.use'],
        };
    }
}
