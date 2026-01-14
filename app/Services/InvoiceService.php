<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate an invoice for a confirmed order.
     */
    public function generateInvoice(Order $order): Invoice
    {
        // Prevent duplicate invoices
        if ($order->invoice()->exists()) {
            return $order->invoice;
        }

        return DB::transaction(function () use ($order) {
            $invoiceNumber = $this->generateInvoiceNumber($order->tenant_id);

            $invoice = Invoice::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'invoice_number' => $invoiceNumber,
                'date' => now(),
                'total_amount' => $order->total_amount,
                'status' => 'generated',
            ]);

            // Snapshot Order Items
            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'tenant_id' => $order->tenant_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->selling_price,
                    'total' => $item->selling_price * $item->quantity, // Ensure precise calc
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Generate a unique invoice number (INV-YYYY-SEQ).
     */
    private function generateInvoiceNumber(int $tenantId): string
    {
        $year = date('Y');
        
        // Find last invoice for this tenant in this year
        $lastInvoice = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract sequence
            $parts = explode('-', $lastInvoice->invoice_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return sprintf("INV-%s-%05d", $year, $seq);
    }
}
