<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Institution;
use App\Models\User;
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

            return $user;
        });
    }
}
