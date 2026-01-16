<template>
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sales -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-800">{{ formatCurrency(stats.today_sales) }}</p>
                    </div>
                </div>
            </div>

            <!-- Orders -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Today's Orders</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.today_orders }}</p>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.low_stock_count }}</p>
                    </div>
                </div>
            </div>

             <!-- Products -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Products</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.total_products }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                    <router-link to="/orders" class="text-sm text-indigo-600 hover:text-indigo-900">View All</router-link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-3">Order #</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="order in stats.recent_orders" :key="order.id">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ order.order_number }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ order.customer_name }}</td>
                                <td class="px-6 py-3">
                                    <span :class="{
                                        'bg-green-100 text-green-800': order.status === 'delivered',
                                        'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                        'bg-blue-100 text-blue-800': order.status === 'confirmed',
                                        'bg-red-100 text-red-800': order.status === 'cancelled',
                                    }" class="px-2 py-1 rounded-full text-xs font-semibold">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-medium text-gray-900">{{ formatCurrency(order.total_amount) }}</td>
                            </tr>
                            <tr v-if="!stats.recent_orders?.length">
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No recent orders.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 text-red-600">Low Stock Alerts</h3>
                    <router-link to="/products" class="text-sm text-indigo-600 hover:text-indigo-900">View All</router-link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Variant</th>
                                <th class="px-6 py-3 text-right">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                             <tr v-for="item in stats.low_stock_items" :key="item.id">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ item.product_name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ item.variant_name }}</td>
                                <td class="px-6 py-3 text-right font-bold text-red-600">{{ item.stock_quantity }}</td>
                            </tr>
                            <tr v-if="!stats.low_stock_items?.length">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No low stock items.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const stats = ref({
    today_sales: 0,
    today_orders: 0,
    total_products: 0,
    low_stock_count: 0,
    recent_orders: [],
    low_stock_items: []
});

const formatCurrency = (value) => {
    return Number(value).toFixed(2);
};

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/dashboard');
        stats.value = response.data;
    } catch (e) {
        console.error("Failed to load dashboard stats", e);
    }
});
</script>
