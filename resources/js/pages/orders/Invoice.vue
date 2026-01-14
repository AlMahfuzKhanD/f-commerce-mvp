<template>
    <div v-if="loading" class="text-center p-10">Loading Invoice...</div>
    <div v-else-if="error" class="text-red-500 text-center p-10">{{ error }}</div>
    <div v-else class="max-w-3xl mx-auto bg-white p-8 shadow-lg my-8 print:shadow-none print:w-full print:max-w-none print:my-0" id="invoice-area">
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
                <p class="text-gray-500 mt-1">#{{ order.invoice?.invoice_number || 'PENDING' }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-semibold text-gray-700">F-Commerce SaaS</h2>
                <p class="text-gray-500 text-sm mt-1">123 Business Road, Dhaka</p>
                <p class="text-gray-500 text-sm">Phone: +880 1700-000000</p>
            </div>
        </div>

        <!-- Details -->
        <div class="flex justify-between mb-8">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Bill To</h3>
                <p class="font-semibold text-gray-800">{{ order.customer?.name }}</p>
                <p class="text-gray-600 text-sm">{{ order.customer?.phone }}</p>
                <p class="text-gray-600 text-sm whitespace-pre-line">{{ order.customer?.address }}</p>
            </div>
            <div class="text-right">
                 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Date</h3>
                 <p class="text-gray-800">{{ formatDate(order.created_at) }}</p>
                 <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 mt-4">Status</h3>
                 <span class="px-2 py-1 text-xs font-bold rounded" :class="statusClass(order.payment_status)">{{ order.payment_status.toUpperCase() }}</span>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full mb-8">
            <thead>
                <tr class="border-b-2 border-gray-200">
                    <th class="text-left py-3 text-sm font-bold text-gray-600 uppercase">Item</th>
                    <th class="text-center py-3 text-sm font-bold text-gray-600 uppercase">Qty</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-600 uppercase">Rate</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-600 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100">
                    <td class="py-3 text-gray-800">{{ item.product_name }} <span class="text-xs text-gray-500 block">{{ item.product_sku }}</span></td>
                    <td class="py-3 text-center text-gray-600">{{ item.quantity }}</td>
                    <td class="py-3 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                    <td class="py-3 text-right text-gray-800 font-medium">{{ formatCurrency(item.subtotal) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-64">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 text-sm">Subtotal</span>
                    <span class="text-gray-800 font-medium">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 text-sm">Discount</span>
                    <span class="text-gray-800 font-medium">- {{ formatCurrency(order.discount) }}</span>
                </div>
                 <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600 text-sm">Delivery</span>
                    <span class="text-gray-800 font-medium">+ {{ formatCurrency(order.delivery_charge) }}</span>
                </div>
                <div class="flex justify-between py-3 border-b-2 border-gray-800">
                    <span class="text-gray-800 font-bold">Total</span>
                    <span class="text-gray-800 font-bold text-lg">{{ formatCurrency(order.total_amount) }}</span>
                </div>
                 <div class="flex justify-between py-2">
                    <span class="text-gray-600 text-sm">Paid</span>
                    <span class="text-green-600 font-medium">{{ formatCurrency(order.paid_amount) }}</span>
                </div>
                 <div class="flex justify-between py-2">
                    <span class="text-gray-600 text-sm">Due</span>
                    <span class="text-red-600 font-medium">{{ formatCurrency(order.due_amount) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-gray-500 text-sm border-t pt-6 print:mt-6">
            <p>Thank you for your business!</p>
        </div>

        <!-- Actions (Hidden on Print) -->
        <div class="mt-8 flex justify-center space-x-4 print:hidden">
            <router-link to="/orders" class="px-6 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Back to Orders</router-link>
            <button @click="printInvoice" class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 shadow flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useOrderStore } from '../../stores/order';

const route = useRoute();
const store = useOrderStore();
const loading = ref(true);
const error = ref(null);
const order = ref({});

onMounted(async () => {
    try {
        await store.fetchOrder(route.params.id);
        order.value = store.currentOrder;
        
        // Also fetch invoice details specifically if needed, but currentOrder usually has it via relationships
        // If not, we might need a specific call: await axios.get(`/api/v1/orders/${route.params.id}/invoice`)
    } catch (e) {
        error.value = 'Failed to load invoice';
    } finally {
        loading.value = false;
    }
});

const subtotal = computed(() => {
    if (!order.value.items) return 0;
    return order.value.items.reduce((sum, item) => sum + item.subtotal, 0);
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatCurrency = (amount) => {
    return '৳' + parseFloat(amount || 0).toFixed(2);
};

const statusClass = (status) => {
    return status === 'paid' ? 'bg-green-100 text-green-800' : 
           status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
};

const printInvoice = () => {
    window.print();
};
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-area, #invoice-area * {
        visibility: visible;
    }
    #invoice-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
}
</style>
