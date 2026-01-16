<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(\App\Models\User::with('roles')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'tenant_id' => $request->user()->tenant_id // Assume same tenant
        ]);

        $role = \App\Models\Role::find($validated['role_id']);
        $user->assignRole($role);

        if (isset($validated['permissions'])) {
             // We need a way to assign direct permissions. 
             // HasRoles trait usually has permissions() relation methods.
             // We'll verify HasRoles trait content first, effectively we need $user->permissions()->sync(...)
             $user->permissions()->sync($validated['permissions']);
        }

        return new UserResource($user->load(['roles', 'permissions']));
    }

    public function show(string $id)
    {
        return new UserResource(\App\Models\User::with(['roles', 'permissions'])->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
        }

        if (isset($validated['role_id'])) {
            $role = \App\Models\Role::find($validated['role_id']);
            $user->roles()->sync([$role->id]);
        }

        if (isset($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        }

        return new UserResource($user->load(['roles', 'permissions']));
    }

    public function destroy(string $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        $user->delete();
        return response()->noContent();
    }
}
