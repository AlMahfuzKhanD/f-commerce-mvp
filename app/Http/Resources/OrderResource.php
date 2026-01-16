<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'discount' => (float) $this->discount,
            'delivery_charge' => (float) $this->delivery_charge,
            'order_source' => $this->order_source,
            'customer_name' => $this->whenLoaded('customer', fn() => optional($this->customer)->name ?? 'Walk-in Customer'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) ($this->relationLoaded('payments') ? $this->payments->sum('amount') : 0),
            'due_amount' => (float) ($this->total_amount - ($this->relationLoaded('payments') ? $this->payments->sum('amount') : 0)),
            'payment_status' => $this->calculatePaymentStatus(),
            'financials' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount,
                'delivery_charge' => (float) $this->delivery_charge,
                'total' => (float) $this->total_amount,
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_human' => $this->created_at->format('M d, Y'),
        ];
    }

    protected function calculatePaymentStatus(): string
    {
        $total = (float) $this->total_amount;
        $paid = $this->relationLoaded('payments') ? (float) $this->payments->sum('amount') : 0;

        if ($total <= 0) return 'paid'; // Free or zero order
        if ($paid >= $total) return 'paid';
        if ($paid > 0) return 'partial';
        return 'unpaid';
    }
}
