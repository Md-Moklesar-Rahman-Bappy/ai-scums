<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Institution;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * RegistrationService.
 *
 * Handles self-onboarding for a new institution: creates the tenant
 * (institution), its first admin user and assigns the "institution_admin"
 * role. Executed inside a database transaction to guarantee consistency.
 */
class RegistrationService
{
    /**
     * Register a new institution with its founding admin.
     */
    public function __construct(private readonly AuditLogService $audit) {}

    /**
     * Register a new institution with its founding admin.
     *
     * @param  array{institution_name: string, institution_type: string, admin_name: string, email: string, phone: string|null, password: string}  $data
     */
    public function registerInstitution(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $institution = Institution::create([
                'name' => $data['institution_name'],
                'type' => $data['institution_type'],
                'slug' => Str::slug($data['institution_name']).'-'.Str::random(6),
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => $data['admin_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'institution_id' => $institution->id,
                'is_active' => true,
            ]);

            $user->assignRole('institution_admin');

            $this->audit->log('auth.register', [
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
            $this->audit->log('rbac.role_assigned', [
                'model_type' => User::class,
                'model_id' => $user->id,
                'new_values' => ['role' => 'institution_admin'],
            ]);
            $this->audit->log('tenant.created', [
                'model_type' => Institution::class,
                'model_id' => $institution->id,
            ]);

            return $user;
        });
    }
}
