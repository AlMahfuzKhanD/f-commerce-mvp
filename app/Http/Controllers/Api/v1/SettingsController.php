<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;

class SettingsController extends Controller
{
    /**
     * Get current tenant settings.
     */
    public function show(Request $request)
    {
        // Tenant is resolved via Middleware, accessible via auth user
        $tenant = $request->user()->tenant;
        
        return response()->json([
            'data' => $tenant
        ]);
    }

    /**
     * Update tenant settings (Branding).
     */
    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        // Ensure user is Owner (Gate or Role check)
        // For MVP, assuming Owner role check is done via middleware or here
        if (!$request->user()->hasRole('Owner')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|url', // Assuming simple URL for MVP, or implement upload later
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|string',
        ]);

        $tenant->update($validated);

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $tenant
        ]);
    }
}
