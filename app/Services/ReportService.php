<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get Dashboard KPI Cards.
     */
    public function getDashboardKPIs()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        return [
            'today_sales' => Order::whereDate('created_at', $today)->sum('total_amount'),
            'month_sales' => Order::where('created_at', '>=', $startOfMonth)->sum('total_amount'),
            'total_orders' => Order::count(),
            'total_profit' => Order::sum('profit_amount'),
        ];
    }

    /**
     * Get Sales Report.
     */
    public function getSalesSummary($startDate, $endDate)
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total_amount) as revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get Profit Analysis.
     */
    public function getProfitAnalysis($startDate, $endDate)
    {
        $orders = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, SUM(cost_amount) as cost, SUM(profit_amount) as gross_profit')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $expenses = \App\Models\Expense::selectRaw('DATE(expense_date) as date, SUM(amount) as amount')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Merge dates
        $dates = $orders->keys()->merge($expenses->keys())->unique()->sort();

        $report = $dates->map(function ($date) use ($orders, $expenses) {
            $orderData = $orders->get($date);
            $expenseData = $expenses->get($date);

            $revenue = $orderData ? $orderData->revenue : 0;
            $cost = $orderData ? $orderData->cost : 0;
            $grossProfit = $orderData ? $orderData->gross_profit : 0;
            $expense = $expenseData ? $expenseData->amount : 0;
            $netProfit = $grossProfit - $expense;

            return [
                'date' => $date,
                'revenue' => $revenue,
                'cost' => $cost,
                'gross_profit' => $grossProfit,
                'expenses' => $expense,
                'net_profit' => $netProfit
            ];
        });

        return $report->values();
    }

    /**
     * Get Top Selling Products.
     */
    public function getTopProducts($limit = 10)
    {
        return OrderItem::select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(quantity * selling_price) as revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Get Top Customers.
     */
    public function getTopCustomers($limit = 10)
    {
        // Join with customers to get names
        return Order::join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('customers.name', DB::raw('COUNT(orders.id) as total_orders'), DB::raw('SUM(orders.total_amount) as total_spent'))
            ->groupBy('customers.id', 'customers.name') // Group by ID is safer standard
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }
}
