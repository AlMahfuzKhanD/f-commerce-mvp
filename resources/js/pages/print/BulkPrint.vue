<template>
    <div class="bg-gray-100 min-h-screen p-8 print:p-0 print:bg-white">
        <!-- No Print UI -->
        <div class="max-w-4xl mx-auto mb-6 print:hidden flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 capitalize">
                Bulk Print: {{ type }} ({{ ids.length }})
            </h1>
            <div class="space-x-3">
                 <button @click="handlePrint" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-bold">
                    🖨 Print Now
                </button>
                <button @click="window.close()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>

        <!-- content -->
        <div v-if="loading" class="text-center py-20">
            <p class="text-gray-500">Loading order data...</p>
        </div>
        
        <div v-else class="max-w-[210mm] mx-auto bg-white shadow-lg print:shadow-none print:w-full">
            <!-- INVOICE MODE -->
            <div v-if="type === 'invoice'">
                <div v-for="(order, index) in orders" :key="order.id" class="p-10 border-b-4 border-gray-100 print:break-after-page print:border-none page-break">
                    <!-- Invoice Header -->
                    <div class="flex justify-between items-start mb-8">
                        <div>
                             <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-secondary-600 print:text-black">F-Commerce</h1>
                             <p class="text-sm text-gray-500 mt-1">Dhaka, Bangladesh</p>
                             <p class="text-sm text-gray-500">Phone: 01700-000000</p>
                        </div>
                        <div class="text-right">
                             <h2 class="text-xl font-bold text-gray-800">INVOICE</h2>
                             <p class="text-gray-600">#{{ order.order_number }}</p>
                             <p class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Customer & Shipping -->
                     <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
                        <div>
                            <h3 class="font-bold text-gray-700 border-b pb-1 mb-2">Bill To:</h3>
                            <p class="font-bold">{{ order.customer?.name }}</p>
                            <p>{{ order.customer?.phone }}</p>
                            <p class="whitespace-pre-line">{{ order.customer?.address }}</p>
                        </div>
                        <div v-if="order.shipping_address">
                             <h3 class="font-bold text-gray-700 border-b pb-1 mb-2">Ship To:</h3>
                             <p class="whitespace-pre-line">{{ order.shipping_address }}</p>
                             <p v-if="order.shipping_phone">Phone: {{ order.shipping_phone }}</p>
                        </div>
                     </div>

                     <!-- Items -->
                     <table class="w-full mb-8 text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-y border-gray-200">
                                <th class="text-left py-2 px-2">Item</th>
                                <th class="text-center py-2 px-2">Qty</th>
                                <th class="text-right py-2 px-2">Price</th>
                                <th class="text-right py-2 px-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100">
                                <td class="py-2 px-2">
                                    <p class="font-bold text-gray-800">{{ item.product?.name }}</p>
                                    <p class="text-xs text-gray-500" v-if="item.product_variant">
                                        {{ item.product_variant.size?.name }} {{ item.product_variant.color?.name }}
                                    </p>
                                </td>
                                <td class="text-center py-2 px-2">{{ item.quantity }}</td>
                                <td class="text-right py-2 px-2">{{ Number(item.unit_price).toFixed(2) }}</td>
                                <td class="text-right py-2 px-2">{{ (item.quantity * item.unit_price).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                     </table>

                     <!-- Totals -->
                     <div class="flex justify-end text-sm">
                        <div class="w-48 space-y-2">
                             <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span>{{ order.total_amount - order.delivery_charge + order.discount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery:</span>
                                <span>{{ order.delivery_charge }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Discount:</span>
                                <span>-{{ order.discount }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-base border-t pt-1">
                                <span>Total:</span>
                                <span>{{ order.total_amount }}</span>
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            <!-- STICKER MODE -->
            <div v-else-if="type === 'stickers'" class="flex flex-wrap content-start p-4 gap-4 bg-gray-200 print:bg-white print:p-0 print:block">
                 <div v-for="order in orders" :key="order.id" class="bg-white w-[100mm] h-[150mm] p-6 border shadow-sm print:shadow-none relative flex flex-col justify-between print:break-after-page sticker-card mx-auto mb-4 print:mb-0">
                    
                    <!-- Header -->
                    <div class="text-center border-b pb-4">
                         <h2 class="text-2xl font-black uppercase tracking-wide">F-Commerce</h2>
                         <p class="font-bold text-gray-600">Order #{{ order.order_number }}</p>
                         <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
                    </div>

                    <!-- Customer Info (BIG) -->
                    <div class="flex-1 flex flex-col justify-center items-center text-center py-4">
                        <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Deliver To</p>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 leading-tight">
                            {{ order.customer?.name }}
                        </h1>
                        <p class="text-2xl font-bold text-gray-800 mb-4 bg-gray-100 px-4 py-1 rounded">
                             {{ order.shipping_phone || order.customer?.phone }}
                        </p>
                        <p class="text-lg text-gray-700 px-8 leading-snug">
                            {{ order.shipping_address || order.customer?.address }}
                        </p>
                    </div>

                    <!-- Footer Details -->
                    <div class="border-t pt-4">
                         <div class="grid grid-cols-2 gap-4 text-sm">
                             <div>
                                 <span class="block text-gray-500 text-xs uppercase">COD Amount</span>
                                 <span class="block text-xl font-black">{{ order.due_amount }} Tk</span>
                             </div>
                              <div class="text-right">
                                 <span class="block text-gray-500 text-xs uppercase">Weight / Qty</span>
                                 <span class="block font-bold">0.5 kg</span>
                             </div>
                         </div>
                         <div class="mt-4 text-center">
                            <!-- Barcode Placeholder -->
                             <div class="h-10 bg-black w-3/4 mx-auto"></div>
                             <p class="text-[10px] mt-1 text-gray-400">Courier Tracking ID</p>
                         </div>
                    </div>

                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const type = computed(() => route.params.type); // 'invoice' or 'stickers'
const ids = computed(() => (route.query.ids || '').split(',').filter(Boolean));

const orders = ref([]);
const loading = ref(true);

onMounted(async () => {
    if (ids.value.length === 0) {
        loading.value = false;
        return;
    }

    try {
        // Fetch all requested orders
        // Use a loop or a bulk API if available. Since we don't have bulk API yet, parallel reqs.
        // Better: Use the existing index API with filter? 
        // Or just one by one for MVP. Index API filter is cleaner.
        // Assuming we update OrderController to accept ?ids=1,2,3 or handle multiple.
        // Actually, let's just fetch individual for safety now.
        
        const promises = ids.value.map(id => axios.get(`/api/v1/orders/${id}`));
        const responses = await Promise.all(promises);
        orders.value = responses.map(r => r.data.data);
    } catch (e) {
        console.error(e);
        alert("Failed to load some orders");
    } finally {
        loading.value = false;
        // Auto print?
        // setTimeout(() => window.print(), 1000);
    }
});

const handlePrint = () => {
    window.print();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-GB');
};
</script>

<style scoped>
@media print {
    .page-break {
        break-after: page;
    }
    .sticker-card {
        break-inside: avoid;
    }
}
</style>
