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
}
