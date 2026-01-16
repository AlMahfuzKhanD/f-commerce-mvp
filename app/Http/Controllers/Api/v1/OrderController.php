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
        // Filter by Status
        if ($request->filled('status')) {
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
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled,returned'
        ]);

        $order = Order::findOrFail($id);
        
        try {
            $this->orderService->updateStatus($order, $request->status, $request->user()->id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Sprint 3: Auto-generate Invoice
        if ($request->status === 'confirmed') {
            $invoiceService->generateInvoice($order->fresh());
        }

        return new OrderResource($order->fresh());
    }

    /**
     * Update the specified resource in storage.
     * Allows updating header details like customer, notes, etc.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'order_date' => 'sometimes|date',
            'customer_id' => 'sometimes|exists:customers,id',
            'notes' => 'nullable|string',
            'discount' => 'sometimes|numeric|min:0',
            'delivery_charge' => 'sometimes|numeric|min:0',
        ];

        // If items are being updated, validate them strictly
        if ($request->has('items')) {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_id'] = ['required', 'exists:products,id'];
            $rules['items.*.product_variant_id'] = ['required', 'exists:product_variants,id'];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);
        $order = Order::findOrFail($id);

        // Use Service for complex update (stock management etc)
        $this->orderService->updateOrder($order, $validated);

        return new OrderResource($order->fresh());
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
             if ($item->product_variant_id) {
                 $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                 if ($variant) {
                     $variant->increment('stock_quantity', $item->quantity);
                 }
             }
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
