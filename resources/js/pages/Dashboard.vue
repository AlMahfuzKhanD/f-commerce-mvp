<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-800">{{ formatCurrency(store.summary.total_sales) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800">{{ store.summary.total_orders }}</p>
                    </div>
                </div>
            </div>

            <div v-if="auth.user?.role === 'owner'" class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Profit</p>
                        <p class="text-2xl font-bold text-gray-800">{{ formatCurrency(store.summary.total_profit) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Trends (Last 7 Days)</h3>
                <SalesChart :data="store.salesChart" />
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                 <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                 <div class="space-y-4">
                     <router-link to="/orders/create" class="block w-full text-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                         Create New Order
                     </router-link>
                     <router-link to="/products/create" class="block w-full text-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                         Add Product
                     </router-link>
                     <router-link to="/customers/create" class="block w-full text-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                         Add Customer
                     </router-link>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useReportStore } from '../stores/report';
import { useAuthStore } from '../stores/auth';
import SalesChart from '../components/analytics/SalesChart.vue';

const store = useReportStore();
const auth = useAuthStore();

const formatCurrency = (val) => '৳' + parseFloat(val || 0).toFixed(2);

onMounted(() => {
    store.fetchDashboardData();
});
</script>
