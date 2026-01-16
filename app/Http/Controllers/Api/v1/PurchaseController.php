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
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
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
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ]);

                // Update Stock if Status is Received
                if ($validated['status'] === 'received') {
                    // Update Main Product Stock (Aggregate) - SKIPPED as stock is on Variant now
                    // $product = Product::find($item['product_id']);
                    // if ($product) {
                    //    $product->increment('stock_quantity', $item['quantity']);
                    // }

                    // Update Variant Stock
                    if (!empty($item['product_variant_id'])) {
                        $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                        if ($variant) {
                            $variant->increment('stock_quantity', $item['quantity']);
                            $variant->update(['cost_price' => $item['unit_cost']]);
                        }
                    } else {
                        // Fallback: If no variant ID, ideally we error or log.
                        // For MVP, we pass. We don't update product cost if we can't find variant.
                    }
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
            'status' => 'required|in:received,pending', // Start enforcing status control
            'reference_no' => 'nullable|string',
            'paid_amount' => 'sometimes|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $purchase = Purchase::with('items')->findOrFail($id);
            $tenantId = 1;

            // 1. Revert Old Stock (if previously received)
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    if (!empty($item->product_variant_id)) {
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            // VALIDATION: Cannot revert if stock is insufficient (meaning items were sold)
                            // We only check this if we are effectively removing this item amount from stock (temporarily)
                            // Since we delete and recreate, we are effectively removing ALL 'old' items.
                            // However, we must ensure we don't dip below zero in the interim or final calculation?
                            // Actually, strict check: If you want to Edit, you must have the stock available to revert the *original* entry.
                            // If Current Stock < Original Qty, it means we sold some of THIS batch (in a FIFO sense, or just pool sense).
                            
                            // Let's allow flexible editing, but Final Stock must not be negative.
                            // Strategy: Just check decrement.
                            if ($variant->stock_quantity < $item->quantity) {
                                throw new \Exception("Cannot update purchase. Item '{$variant->product->name}' (Variant: {$variant->size_name}/{$variant->color_name}) has been sold/consumed. Current Stock: {$variant->stock_quantity}, Purchase Qty: {$item->quantity}.");
                            }

                            $variant->decrement('stock_quantity', $item->quantity);
                        }
                    }
                }
            }

            // 2. Delete Old Items
            $purchase->items()->delete();

            // 3. Update Purchase Header
            // Calculate New Total
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $validated['status'],
                'reference_no' => $validated['reference_no'],
                'paid_amount' => $validated['paid_amount'],
                'total_amount' => $totalAmount,
            ]);

            // 4. Create New Items & Apply Stock
            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'tenant_id' => $tenantId,
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ]);

                // Update Stock if New Status is Received
                if ($validated['status'] === 'received') {
                     if (!empty($item['product_variant_id'])) {
                        $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                        if ($variant) {
                            $variant->increment('stock_quantity', $item['quantity']);
                            $variant->update(['cost_price' => $item['unit_cost']]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Purchase updated successfully', 'data' => $purchase->load('items')]);
        });
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
                     // Update Variant Stock Revert
                     if (!empty($item->product_variant_id)) {
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                             if ($variant->stock_quantity < $item->quantity) {
                                throw new \Exception("Cannot delete purchase. Item '{$variant->product->name}' has insufficient stock (Sold?). Current: {$variant->stock_quantity}, Required: {$item->quantity}");
                            }
                            $variant->decrement('stock_quantity', $item->quantity);
                        }
                    }
                }
            }

            $purchase->delete(); // Cascade deletes items via DB constraint usually, but let's assume it does or model relationship does

            return response()->json(['message' => 'Purchase deleted successfully']);
        });
    }

    public function addPayment(Request $request, string $id)
    {
        $request->validate([
            'amount' => 'required|numeric', // Removed min:0.01 to allow negative (rollback)
        ]);

        $purchase = Purchase::findOrFail($id);
        
        // Prevent negative final balance
        if ($purchase->paid_amount + $request->amount < 0) {
             return response()->json(['message' => 'Paid amount cannot be less than zero.'], 422);
        }

        $purchase->increment('paid_amount', $request->amount);

        return response()->json(['message' => 'Payment updated successfully', 'data' => $purchase]);
    }
}
