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

                $itemsData[] = [
                    // tenant_id will be handled by Model observer or manual if needed? 
                    // Better to rely on relationship or explicit set if inside transaction logic without specific model context
                    // We assume tenant_id is set via request user context or auto-scope, but inside create() we might need to pass it explicitly 
                    // if relying on simple create. But usually BelongsToTenant handles saving.
                    // However, for bulk insert (if optimized) we need tenant_id. 
                    // Using relationship $order->items()->create(...) handles it.
                    
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_name' => $product->name, // SNAPSHOT
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price, // Snapshot cost at moment of sale
                ];
                
                // Inventory Management (Sprint 2)
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Requested: {$item['quantity']}, Available: {$product->stock_quantity}");
                }
                $product->decrement('stock_quantity', $item['quantity']);
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
                'cost_amount' => 0, // Should be sum of item costs
                'profit_amount' => 0, // Total - Cost
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
}
