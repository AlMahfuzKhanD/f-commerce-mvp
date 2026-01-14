<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * Show delivery info for an order.
     */
    public function show(string $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        return response()->json([
            'data' => $order->delivery // HasOne relationship
        ]);
    }

    /**
     * Assign courier / Update delivery info.
     * POST /orders/{id}/delivery
     */
    public function store(Request $request, string $orderId)
    {
        $request->validate([
            'courier_name' => 'required|string',
            'tracking_number' => 'nullable|string',
            'cod_amount' => 'nullable|numeric|min:0',
            'delivery_status' => 'required|string|in:pending,shipped,delivered,returned,cancelled'
        ]);

        $order = Order::findOrFail($orderId);

        $delivery = DB::transaction(function () use ($order, $request) {
            $delivery = Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'tenant_id' => $order->tenant_id,
                    'courier_name' => $request->courier_name,
                    'tracking_number' => $request->tracking_number,
                    'cod_amount' => $request->cod_amount ?? $order->due_amount, // Default to due amount
                    'delivery_status' => $request->delivery_status,
                ]
            );

            // Sync Order Status if needed (Optional, but good UX)
            // If delivery is 'delivered', we might want to mark order as 'delivered'
            if ($request->delivery_status === 'delivered' && $order->status !== 'delivered') {
                $order->update(['status' => 'delivered']);
                // Generate event
                $order->events()->create([
                    'tenant_id' => $order->tenant_id,
                    'event_type' => 'status_changed',
                    'meta' => ['new_status' => 'delivered', 'reason' => 'courier_update']
                ]);
            }
            
            return $delivery;
        });

        return response()->json([
            'message' => 'Delivery information updated.',
            'data' => $delivery,
            'order_status' => $order->status
        ]);
    }
}
