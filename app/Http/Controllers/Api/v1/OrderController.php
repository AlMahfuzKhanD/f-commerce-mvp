<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items', 'payments']);

        // Filter by Status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by Order Number or Customer Name
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($curr) use ($search) {
                      $curr->where('name', 'like', "%{$search}%")
                           ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate($request->input('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return new OrderResource($order);
    }

    /**
     * Display the specified order.
     */
    public function show(string $id)
    {
        $order = Order::with(['customer', 'items', 'events'])->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Update order status.
     * Sprint 3: Triggers invoice generation on confirmation.
     */
    public function updateStatus(Request $request, string $id, \App\Services\InvoiceService $invoiceService)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['message' => 'Status is already ' . $newStatus]);
        }

        $order->update(['status' => $newStatus]);

        // Log Event
        $order->events()->create([
            'tenant_id' => $order->tenant_id,
            'event_type' => 'status_changed',
            'meta' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $request->user()->id
            ]
        ]);

        // Sprint 3: Auto-generate Invoice
        if ($newStatus === 'confirmed') {
            $invoiceService->generateInvoice($order);
        }

        return new OrderResource($order->fresh());
    }

    /**
     * Update the specified resource in storage.
     * Allows updating header details like customer, notes, etc.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'order_date' => 'sometimes|date',
            // Add other updatable fields here, e.g. customer_id, notes
        ]);

        $order = Order::findOrFail($id);
        $order->update($validated);

        return new OrderResource($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Optional: Revert stock if order was reserved but not shipped/delivered?
        // Sourcing logic handles purchase stock in. Order logic handles stock out.
        // If we delete an order, we should increment stock back if it was decremented.
        // `OrderService` decrements stock on creation.
        
        $order = Order::with('items')->findOrFail($id);
        
        foreach ($order->items as $item) {
             $product = \App\Models\Product::find($item->product_id);
             if ($product) {
                 $product->increment('stock_quantity', $item->quantity);
             }
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
