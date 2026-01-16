<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function show()
    {
        // For MVP, we assume Single Tenant (ID 1)
        $tenant = DB::table('tenants')->find(1);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }
        
        // Add full URL to logo if exists
        if ($tenant->logo) {
            $tenant->logo_url = Storage::url($tenant->logo);
        } else {
            $tenant->logo_url = null;
        }

        return response()->json($tenant);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $tenantId = 1;

        $data = $request->only(['name', 'currency', 'timezone', 'address', 'phone']);
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($tenant->logo && Storage::exists($tenant->logo)) {
                Storage::delete($tenant->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        DB::table('tenants')->where('id', $tenantId)->update($data);

        $updatedTenant = DB::table('tenants')->find($tenantId);
        if ($updatedTenant->logo) {
            $updatedTenant->logo_url = Storage::url($updatedTenant->logo);
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $updatedTenant
        ]);
    }
}
