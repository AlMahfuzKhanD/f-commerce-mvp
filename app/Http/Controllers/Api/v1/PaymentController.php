<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get payments for an order.
     */
    public function index(string $orderId)
    {
        $order = Order::findOrFail($orderId);
        return response()->json(['data' => $order->payments]);
    }

    /**
     * Record a new payment.
     */
    public function store(Request $request, string $orderId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'note' => 'nullable|string'
        ]);

        $order = Order::findOrFail($orderId);

        $payment = $this->paymentService->recordPayment($order, $request->all());

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'data' => $payment,
            'order_status' => $order->payment_status
        ], 201);
    }
}
