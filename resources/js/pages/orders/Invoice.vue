<template>
    <div v-if="loading" class="text-center p-10">Loading Invoice...</div>
    <div v-else-if="error" class="text-red-500 text-center p-10">{{ error }}</div>
    <div v-else class="max-w-4xl mx-auto bg-white shadow-lg my-8 print:shadow-none print:w-full print:max-w-none print:my-0 print:border-none" id="invoice">
        
        <!-- Print Header / Branding -->
        <div class="p-8 border-b border-gray-100 flex justify-between items-start">
            <div class="flex-1">
                <!-- Logo -->
                <img v-if="settings.logo_url" :src="settings.logo_url" alt="Company Logo" class="h-16 object-contain mb-4">
                <h1 v-else class="text-3xl font-bold text-gray-800 tracking-tight">{{ settings.name || 'Company Name' }}</h1>
                
                <div class="text-gray-500 text-sm leading-relaxed mt-2" v-if="settings.address || settings.phone">
                    <p class="whitespace-pre-line">{{ settings.address }}</p>
                    <p v-if="settings.phone">Phone: {{ settings.phone }}</p>
                </div>
            </div>
            
            <div class="text-right">
                <h2 class="text-4xl font-light text-gray-300 uppercase tracking-widest">Invoice</h2>
                <div class="mt-4 space-y-1">
                    <p class="text-gray-600 font-medium"># {{ order.invoice?.invoice_number || 'PENDING' }}</p>
                    <p class="text-gray-500 text-sm">Date: {{ formatDate(order.created_at) }}</p>
                    <div class="mt-2">
                         <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wide border" :class="statusClass(order.payment_status)">
                            {{ order.payment_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="p-8 grid grid-cols-2 gap-8">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</h3>
                <div class="text-gray-700">
                    <p class="font-bold text-lg text-gray-900">{{ order.customer?.name }}</p>
                    <p class="text-sm mt-1" v-if="order.customer?.phone">{{ order.customer?.phone }}</p>
                    <p class="text-sm mt-1 whitespace-pre-line" v-if="order.customer?.address">{{ order.customer?.address }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="px-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="py-3 text-sm font-bold text-gray-600 uppercase tracking-wider w-1/2">Item Description</th>
                        <th class="py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center">Qty</th>
                        <th class="py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-right">Price</th>
                        <th class="py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                        <td class="py-4 pr-4">
                            <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">SKU: {{ item.product_sku }}</p>
                            <p v-if="item.product_variant_id" class="text-xs text-gray-500 mt-0.5">
                                {{ item.variant?.size_name }} {{ item.variant?.color_name ? '/ ' + item.variant?.color_name : '' }}
                            </p>
                        </td>
                        <td class="py-4 text-center font-medium">{{ item.quantity }}</td>
                        <td class="py-4 text-right">{{ formatCurrency(item.unit_price) }}</td>
                        <td class="py-4 text-right font-bold text-gray-900">{{ formatCurrency(item.subtotal) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals & Payment Info -->
        <div class="p-8 bg-gray-50 mt-4 border-t border-gray-100 print:bg-transparent print:p-0 print:border-none print:mt-8">
            <div class="flex justify-end">
                <div class="w-full md:w-1/2 lg:w-1/3 space-y-3">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900">{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600" v-if="Number(order.discount) > 0">
                        <span>Discount</span>
                        <span class="font-medium text-red-600">- {{ formatCurrency(order.discount) }}</span>
                    </div>
                     <div class="flex justify-between text-sm text-gray-600" v-if="Number(order.delivery_charge) > 0">
                        <span>Delivery</span>
                        <span class="font-medium text-gray-900">+ {{ formatCurrency(order.delivery_charge) }}</span>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-indigo-600 print:text-black">{{ formatCurrency(order.total_amount) }}</span>
                    </div>
                    
                     <div class="flex justify-between text-sm pt-2" :class="{'text-green-600 font-bold': Number(order.due_amount) <= 0}">
                        <span>Paid</span>
                        <span>{{ formatCurrency(order.paid_amount) }}</span>
                    </div>
                     <div class="flex justify-between text-sm text-red-600 font-bold" v-if="Number(order.due_amount) > 0">
                        <span>Due Amount</span>
                        <span>{{ formatCurrency(order.due_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-8 text-center text-gray-400 text-xs print:mt-8">
            <p>Thank you for your business.</p>
            <p class="mt-1" v-if="settings.address">{{ settings.name }} &bull; {{ settings.address }}</p>
        </div>

        <!-- Actions (Hidden on Print) -->
        <div class="p-8 border-t border-gray-100 bg-gray-50 flex justify-between items-center print:hidden rounded-b-lg">
             <router-link to="/orders" class="text-sm font-medium text-gray-600 hover:text-gray-900 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Orders
            </router-link>
            <button @click="printInvoice" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-md transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useOrderStore } from '../../stores/order';
import axios from 'axios';

const route = useRoute();
const store = useOrderStore();
const loading = ref(true);
const error = ref(null);
const order = ref({});
const settings = ref({});

onMounted(async () => {
    try {
        // Fetch Settings for Company Info
        const settingsRes = await axios.get('/api/v1/settings');
        settings.value = settingsRes.data;

        await store.fetchOrder(route.params.id);
        order.value = store.currentOrder;
        
    } catch (e) {
        error.value = 'Failed to load invoice';
    } finally {
        loading.value = false;
    }
});

const subtotal = computed(() => {
    if (!order.value.items) return 0;
    return order.value.items.reduce((sum, item) => sum + Number(item.subtotal), 0);
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatCurrency = (amount) => {
    const currency = settings.value.currency || 'BDT'; // Default fallback
    // Simple formatting. For better i18n use Intl.NumberFormat
    return (currency === 'BDT' ? '৳' : currency + ' ') + Number(amount || 0).toFixed(2);
};

const statusClass = (status) => {
    switch(status) {
        case 'paid': return 'bg-green-100 text-green-700 border-green-200';
        case 'partial': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
        case 'unpaid': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

const printInvoice = () => {
    window.print();
};
</script>

<style scoped>
/* Ensure clean print layout */
@media print {
    body * {
        visibility: hidden;
    }
    #invoice, #invoice * {
        visibility: visible;
    }
    #invoice {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none !important;
        border: none !important;
        max-width: none !important;
        background: white;
    }
    
    /* Hide actionable buttons specifically if they aren't hidden by visibility rule above (handling edge cases) */
    .print\:hidden {
        display: none !important;
    }
}
</style>
