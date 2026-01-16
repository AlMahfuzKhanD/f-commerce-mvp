<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create a new order with items.
     */
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Calculate Totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                // Snapshot product details
                $product = Product::find($item['product_id']);
                
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                $productName = $product->name;
                $productName = $product->name;
                $variantId = $item['product_variant_id']; // Validated as required

                $variant = \App\Models\ProductVariant::find($variantId);
                // Should exist due to validation, but double check
                if (!$variant) {
                    throw new \Exception("Variant not found for product: {$product->name}");
                }
                
                // Check Variant Stock
                if ($variant->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for: {$product->name} " . ($variant->size_name ? "({$variant->size_name}/{$variant->color_name})" : "(Default)") . ". Requested: {$item['quantity']}, Available: {$variant->stock_quantity}");
                }
                
                $variant->decrement('stock_quantity', $item['quantity']);
                
                // Append variant info to snapshot name if useful
                if ($variant->size_name || $variant->color_name) {
                    $productName .= " ({$variant->size_name} / {$variant->color_name})";
                }

                $itemsData[] = [
                    // tenant_id will be handled by Model observer or manual if needed? 
                    // Better to rely on relationship or explicit set if inside transaction logic without specific model context
                    // We assume tenant_id is set via request user context or auto-scope, but inside create() we might need to pass it explicitly 
                    // if relying on simple create. But usually BelongsToTenant handles saving.
                    // However, for bulk insert (if optimized) we need tenant_id. 
                    // Using relationship $order->items()->create(...) handles it.
                    
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $variantId,
                    'product_name' => $productName, // SNAPSHOT with Variant info
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['unit_price'],
                    'cost_price' => $variant->cost_price, // Snapshot cost at moment of sale
                ];
            }

            $delivery = $data['delivery_charge'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $total = $subtotal + $delivery - $discount;

            // 2. Create Order
            // Auto-generate order number (Simple logic for now: TIME-RAND)
            // In real app, maybe strict sequential per tenant
            $orderNumber = 'ORD-' . strtoupper(Str::random(8)); 

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'order_number' => $orderNumber,
                'order_source' => $data['order_source'],
                'status' => 'pending', // Default
                'notes' => $data['notes'] ?? null,
                
                // Financials
                'subtotal' => $subtotal,
                'delivery_charge' => $delivery,
                'discount' => $discount,
                'total_amount' => $total,
                
                // For MVP metrics
                'cost_amount' => collect($itemsData)->sum(fn($i) => $i['cost_price'] * $i['quantity']),
                'profit_amount' => $total - collect($itemsData)->sum(fn($i) => $i['cost_price'] * $i['quantity']),
            ]);

            // 3. Create Items
            foreach ($itemsData as $iData) {
                $order->items()->create($iData);
            }

            // 4. Log Event
            $order->events()->create([
                'event_type' => 'status_change',
                'meta' => [
                    'old_status' => null,
                    'new_status' => 'pending',
                    'action' => 'created'
                ]
            ]);

            return $order->load('items');
        });
    }

    /**
     * Update an order (replaces items if provided).
     */
    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function () use ($order, $data) {
            // Check if Items are being updated
            if (isset($data['items'])) {
                // 1. Revert Stock for OLD items
                foreach ($order->items as $item) {
                     if ($item->product_variant_id) {
                         $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                         if ($variant) $variant->increment('stock_quantity', $item->quantity);
                     }
                }
                
                // 2. Delete OLD items
                $order->items()->delete();

                // 3. Process New Items (Logic similar to create)
                $subtotal = 0;
                $itemsData = [];

                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    $productName = $product->name;
                    $variantId = $item['product_variant_id'];

                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $lineTotal;

                    $variant = \App\Models\ProductVariant::find($variantId);
                    if (!$variant) {
                        throw new \Exception("Variant not found for product: {$product->name}");
                    }
                    
                    if ($variant->stock_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for: {$product->name}.");
                    }
                    
                    $variant->decrement('stock_quantity', $item['quantity']);
                    
                    if ($variant->size_name || $variant->color_name) {
                        $productName .= " ({$variant->size_name} / {$variant->color_name})";
                    }

                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $variantId,
                        'product_name' => $productName,
                        'quantity' => $item['quantity'],
                        'selling_price' => $item['unit_price'],
                        'cost_price' => $variant->cost_price, 
                    ];
                }

                $order->subtotal = $subtotal;
                 
                 // Create items
                foreach ($itemsData as $iData) {
                    $order->items()->create($iData);
                }
                
                // Recalculate cost amount
                $order->cost_amount = collect($itemsData)->sum(fn($i) => $i['cost_price'] * $i['quantity']);
            }
            
            // Update other fields
            if (isset($data['customer_id'])) $order->customer_id = $data['customer_id'];
            if (isset($data['order_date'])) $order->created_at = $data['order_date']; // Careful if casting
            if (isset($data['notes'])) $order->notes = $data['notes'];
            if (isset($data['discount'])) $order->discount = $data['discount'];
            if (isset($data['delivery_charge'])) $order->delivery_charge = $data['delivery_charge'];

            // Recalculate Totals
            $order->total_amount = $order->subtotal + $order->delivery_charge - $order->discount;
            $order->profit_amount = $order->total_amount - $order->cost_amount;
            
            $order->save();
            
            return $order->load('items');
        });
    }
    /**
     * Update order status with Stock Management.
     */
    public function updateStatus(Order $order, string $newStatus, $userId = null)
    {
        return DB::transaction(function () use ($order, $newStatus, $userId) {
            $oldStatus = $order->status;
            
            if ($oldStatus === $newStatus) return $order;

            // Define inactive statuses where stock should be "returned"
            $inactiveStatuses = ['cancelled', 'returned'];
            
            // 1. Moving TO Inactive (Stock In)
            if (in_array($newStatus, $inactiveStatuses) && !in_array($oldStatus, $inactiveStatuses)) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) $variant->increment('stock_quantity', $item->quantity);
                    }
                }
            }
            
            // 2. Moving FROM Inactive (Stock Out / Reactivating)
            if (!in_array($newStatus, $inactiveStatuses) && in_array($oldStatus, $inactiveStatuses)) {
                foreach ($order->items as $item) {
                     $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                     if ($variant) {
                         if ($variant->stock_quantity < $item->quantity) {
                             throw new \Exception("Cannot reactivate order. Insufficient stock for: {$variant->sku}");
                         }
                         $variant->decrement('stock_quantity', $item->quantity);
                     }
                }
            }

            $order->update(['status' => $newStatus]);

            // Log Event
            $order->events()->create([
                'tenant_id' => $order->tenant_id,
                'event_type' => 'status_changed',
                'meta' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $userId
                ]
            ]);
            
            return $order;
        });
    }
}
