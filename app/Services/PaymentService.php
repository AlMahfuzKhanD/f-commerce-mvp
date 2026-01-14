<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a new payment for an order.
     */
    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            // 1. Create Payment Record
            $payment = Payment::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'paid_at' => $data['paid_at'] ?? now(),
                'note' => $data['note'] ?? null,
            ]);

            // 2. Update Order Financials
            $order->paid_amount += $data['amount'];
            
            // Recalculate due (Total - Paid)
            // Note: Total might change due to other reasons, but usually fixed here.
            $order->due_amount = max(0, $order->total_amount - $order->paid_amount);

            // 3. Update Payment Status
            if ($order->due_amount <= 0) {
                // Allow overpayment? MVP: Just mark paid. due = 0.
                $order->payment_status = 'paid';
            } elseif ($order->paid_amount > 0) {
                $order->payment_status = 'partially_paid';
            } else {
                $order->payment_status = 'unpaid';
            }

            $order->save();

            return $payment;
        });
    }
}
