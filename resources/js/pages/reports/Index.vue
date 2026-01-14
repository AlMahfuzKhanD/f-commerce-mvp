<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Financial Reports</h2>
            <div class="space-x-2">
                <button @click="load('this_month')" :class="period === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'" class="px-3 py-1 rounded border text-sm">This Month</button>
                <button @click="load('last_month')" :class="period === 'last_month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700'" class="px-3 py-1 rounded border text-sm">Last Month</button>
            </div>
        </div>

        <div v-if="store.loading" class="text-center py-10">
            <span class="text-gray-500">Loading Report...</span>
        </div>

        <div v-else class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow border-t-4 border-blue-500">
                    <p class="text-gray-500 text-sm">Revenue</p>
                    <p class="text-2xl font-bold">{{ formatCurrency(totals.revenue) }}</p>
                </div>
                 <div class="bg-white p-4 rounded-lg shadow border-t-4 border-yellow-500">
                    <p class="text-gray-500 text-sm">COGS (Product Cost)</p>
                    <p class="text-2xl font-bold">{{ formatCurrency(totals.cost) }}</p>
                </div>
                 <div class="bg-white p-4 rounded-lg shadow border-t-4 border-red-500">
                    <p class="text-gray-500 text-sm">Operating Expenses</p>
                    <p class="text-2xl font-bold">{{ formatCurrency(totals.expenses) }}</p>
                </div>
                 <div class="bg-white p-4 rounded-lg shadow border-t-4" :class="totals.net_profit >= 0 ? 'border-green-500' : 'border-red-600'">
                    <p class="text-gray-500 text-sm">Net Profit</p>
                    <p class="text-2xl font-bold" :class="totals.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ formatCurrency(totals.net_profit) }}
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">COGS</th>
                             <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Profit</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Expenses</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="row in store.profitData" :key="row.date">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ formatCurrency(row.revenue) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ formatCurrency(row.cost) }}</td>
                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">{{ formatCurrency(row.gross_profit) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">{{ formatCurrency(row.expenses) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold" :class="row.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(row.net_profit) }}
                            </td>
                        </tr>
                        <tr v-if="store.profitData.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No data for this period.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useReportStore } from '../../stores/report';

const store = useReportStore();
const period = ref('this_month');

const load = (p) => {
    period.value = p;
    store.fetchProfitAnalysis(p);
};

const totals = computed(() => {
    return store.profitData.reduce((acc, row) => {
        acc.revenue += parseFloat(row.revenue);
        acc.cost += parseFloat(row.cost);
        acc.expenses += parseFloat(row.expenses);
        acc.net_profit += parseFloat(row.net_profit);
        return acc;
    }, { revenue: 0, cost: 0, expenses: 0, net_profit: 0 });
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'BDT' }).format(value);
};

onMounted(() => {
    load('this_month');
});
</script>
