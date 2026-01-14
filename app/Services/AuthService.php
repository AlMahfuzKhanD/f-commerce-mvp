<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Handle user login.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Handle user registration.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // 1. Create Tenant
            $tenant = \App\Models\Tenant::create([
                'name' => $data['company_name'],
                'slug' => \Illuminate\Support\Str::slug($data['company_name']),
                // Add defaults for currency/timezone if needed
            ]);

            // 2. Create Owner Role for this Tenant
            $ownerRole = \App\Models\Role::create([
                'tenant_id' => $tenant->id,
                'name'      => 'Owner',
            ]);

            // 3. Create User linked to Tenant
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'tenant_id' => $tenant->id,
            ]);

            // 4. Assign Owner Role
            $user->assignRole($ownerRole);

            // 5. Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token,
                'tenant' => $tenant,
            ];
        });
    }

    /**
     * Handle user logout.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
