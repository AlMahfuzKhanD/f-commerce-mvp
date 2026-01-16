<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
             'data' => \App\Models\Role::withCount('users')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id' // ID based or Slug based? Let's use ID for stable relationships
        ]);

        $role = \App\Models\Role::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name']
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role->load('permissions')
        ], 201);
    }

    public function show(string $id)
    {
        $role = \App\Models\Role::with('permissions')->findOrFail($id);
        return response()->json(['data' => $role]);
    }

    public function update(Request $request, string $id)
    {
        $role = \App\Models\Role::findOrFail($id);
        
        // Protect Owner role? Maybe just warn, but for now allow editing permissions.
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role->load('permissions')
        ]);
    }

    public function destroy(string $id)
    {
        $role = \App\Models\Role::withCount('users')->findOrFail($id);

        if ($role->name === 'Owner') {
            return response()->json(['message' => 'Cannot delete Owner role'], 403);
        }

        if ($role->users_count > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 409);
        }

        $role->delete();
        return response()->noContent();
    }
}
