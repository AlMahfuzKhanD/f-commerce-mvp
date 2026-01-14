<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate($request->input('per_page', 15));

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Sprint 2: Include latest 5 orders
        $customer = Customer::with(['orders' => function($query) {
            $query->latest()->limit(5);
        }])->findOrFail($id);
        
        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        
        // Validation: Prevent deletion if has orders
        if ($customer->orders()->exists()) {
             return response()->json([
                'message' => 'Cannot delete customer with existing orders.',
                'error' => 'conflict_with_orders'
             ], 409);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
