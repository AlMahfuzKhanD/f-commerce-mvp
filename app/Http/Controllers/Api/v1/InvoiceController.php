<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Get invoice for an order.
     */
    public function show(string $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $invoice = $order->invoice()->with('items')->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found (Order might not be confirmed yet).'], 404);
        }

        return response()->json(['data' => $invoice]);
    }
}
