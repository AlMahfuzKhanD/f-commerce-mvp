<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KPI: Today's Stats
        $todaySales = Order::whereDate('created_at', $today)->sum('total_amount');
        $todayOrders = Order::whereDate('created_at', $today)->count();
        
        // 2. KPI: Inventory Stats
        $totalProducts = Product::count();
        $lowStockThreshold = 10;
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', $lowStockThreshold)->count();

        // 3. List: Recent Orders
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer ? $order->customer->name : 'Guest',
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                ];
            });

        // 4. List: Low Stock Items
        $lowStockItems = ProductVariant::with('product')
            ->where('stock_quantity', '<=', $lowStockThreshold)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->size_name . ' / ' . $variant->color_name,
                    'sku' => $variant->sku,
                    'stock_quantity' => $variant->stock_quantity,
                ];
            });

        return response()->json([
            'today_sales' => $todaySales,
            'today_orders' => $todayOrders,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'recent_orders' => $recentOrders,
            'low_stock_items' => $lowStockItems,
        ]);
    }
}
