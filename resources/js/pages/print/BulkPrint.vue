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
                 <div v-for="order in orders" :key="order.id" class="bg-white w-[100mm] h-[150mm] border-2 border-black relative flex flex-col print:break-after-page sticker-card mx-auto mb-4 print:mb-0 text-black box-border overflow-hidden">
                    
                    <!-- Top Section: Sender & Date -->
                    <div class="flex justify-between items-start border-b-2 border-black p-2 bg-gray-50 print:bg-white">
                        <div class="text-xs leading-tight">
                            <span class="font-bold uppercase block mb-1">Sender:</span>
                            <span class="font-bold block">F-Commerce Store</span>
                            <span class="block">123 Merul Badda, Dhaka</span>
                            <span class="block">Bob's Plaza, Level 4</span>
                            <span class="block">Phone: 01700-000000</span>
                        </div>
                        <div class="text-right">
                            <div class="border border-black px-2 py-1 mb-1 bg-white">
                                <span class="font-bold text-sm block">STANDARD</span>
                            </div>
                            <span class="text-xs font-mono block">{{ formatDate(order.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Middle Section: Recipient -->
                    <div class="p-4 flex-1 flex flex-col justify-center border-b-2 border-black relative">
                        <span class="text-xs font-bold uppercase text-gray-500 absolute top-2 left-2">Deliver To:</span>
                        
                        <div class="pl-2">
                            <h1 class="text-2xl font-bold mb-1 uppercase truncate">{{ order.customer?.name }}</h1>
                            <p class="text-lg leading-snug font-medium mb-3 pr-2 break-words">
                                {{ order.shipping_address || order.customer?.address }}
                            </p>
                            <div class="inline-block bg-black text-white px-3 py-1 font-bold text-xl rounded-sm">
                                📞 {{ order.shipping_phone || order.customer?.phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Order Details & Weight -->
                    <div class="flex border-b-2 border-black h-16">
                        <div class="w-1/2 border-r-2 border-black p-2">
                             <span class="text-[10px] uppercase font-bold text-gray-500 block">Order Reference</span>
                             <span class="text-lg font-bold block">#{{ order.order_number }}</span>
                             <span class="text-xs block text-gray-600 truncate">Items: {{ order.items?.length || 1 }}</span>
                        </div>
                        <div class="w-1/4 border-r-2 border-black p-2 text-center">
                            <span class="text-[10px] uppercase font-bold text-gray-500 block">Weight</span>
                            <span class="text-lg font-bold block">0.5 <span class="text-sm">KG</span></span>
                        </div>
                         <div class="w-1/4 p-2 text-center">
                            <span class="text-[10px] uppercase font-bold text-gray-500 block">Zone</span>
                            <span class="text-xl font-bold block">DHK</span>
                        </div>
                    </div>

                    <!-- COD Section (Critical) -->
                    <div class="border-b-2 border-black p-4 text-center bg-gray-50 print:bg-white">
                        <span class="text-xs font-bold uppercase tracking-widest block mb-1">Cash On Delivery (COD) Amount</span>
                        <div class="text-4xl font-black">{{ Math.round(order.due_amount) }} <span class="text-lg align-top">TK</span></div>
                         <span class="text-[10px] uppercase block mt-1 text-gray-500 text-center">(Condition)</span>
                    </div>

                    <!-- Footer: Instructions & Barcode -->
                    <div class="flex flex-col justify-between p-2 h-24">
                        <div class="text-xs italic text-gray-600 mb-2 px-1">
                             <span class="font-bold not-italic text-black">Note:</span> 
                             Call customer before delivery. If unreachable, try SMS.
                        </div>
                        
                        <!-- Pseudo Barcode -->
                         <div class="flex flex-col items-center justify-center flex-1 w-full overflow-hidden">
                             <div class="h-8 w-64 bg-repeat-x" style="background-image: repeating-linear-gradient(90deg, black 0, black 2px, transparent 2px, transparent 4px);"></div>
                             <span class="text-[10px] font-mono tracking-widest mt-1">{{ order.order_number }}</span>
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
