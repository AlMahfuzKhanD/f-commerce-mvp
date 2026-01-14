<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $purchases = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($purchases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:received,pending',
            'reference_no' => 'nullable|string',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $tenantId = 1; // Default
            
            // Calculate Total
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            // Create Purchase
            $purchase = Purchase::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $validated['status'],
                'reference_no' => $validated['reference_no'],
                'paid_amount' => $validated['paid_amount'],
                'total_amount' => $totalAmount,
            ]);

            // Create Items and Update Stock
            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'tenant_id' => $tenantId,
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ]);

                // Update Product Stock if Status is Received
                if ($validated['status'] === 'received') {
                    $product = Product::find($item['product_id']);
                    $product->increment('stock_quantity', $item['quantity']);
                    // Optionally update cost_price to moving average or last price
                    $product->update(['cost_price' => $item['unit_cost']]); 
                }
            }

            return response()->json(['message' => 'Purchase created successfully', 'data' => $purchase->load('items')], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['supplier', 'items.product'])->findOrFail($id);
        return response()->json(['data' => $purchase]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'purchase_date' => 'sometimes|date',
            'reference_no' => 'nullable|string',
            'paid_amount' => 'sometimes|numeric|min:0',
            // Items update skipped for MVP complexity involving stock diffs
        ]);

        $purchase = Purchase::findOrFail($id);
        $purchase->update($validated);

        return response()->json(['message' => 'Purchase updated successfully', 'data' => $purchase]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $purchase = Purchase::with('items')->findOrFail($id);

            // Revert Stock if Received
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }
            }

            $purchase->delete(); // Cascade deletes items via DB constraint usually, but let's assume it does or model relationship does

            return response()->json(['message' => 'Purchase deleted successfully']);
        });
    }
}
