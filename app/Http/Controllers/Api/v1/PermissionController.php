<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = \App\Models\Permission::all();
        $grouped = $permissions->groupBy('group');
        
        return response()->json([
            'data' => $permissions,
            'grouped' => $grouped
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:permissions,slug',
            'group' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $permission = \App\Models\Permission::create($validated);

        return response()->json([
            'message' => 'Permission created',
            'data' => $permission
        ], 201);
    }

    public function destroy(string $id)
    {
        $permission = \App\Models\Permission::findOrFail($id);
        $permission->delete();
        return response()->noContent();
    }
}
