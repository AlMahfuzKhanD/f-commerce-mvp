<template>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Purchase Order</h1>

        <div v-if="loadingData" class="text-center py-10">
            Loading...
        </div>

        <div v-else class="bg-white p-6 rounded-lg shadow">
            <form @submit.prevent="savePurchase" class="space-y-6">
                
                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select v-model="form.supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                            <option value="">Select Supplier</option>
                            <option v-for="supplier in supplierStore.suppliers" :key="supplier.id" :value="supplier.id">
                                {{ supplier.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                        <input v-model="form.purchase_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference No</label>
                        <input v-model="form.reference_no" type="text" placeholder="PO-001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                            <option value="received">Received (Updates Stock)</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>


                <!-- Scan Barcode / Search -->
                 <div class="bg-indigo-50 p-4 rounded shadow border border-indigo-100 relative mt-6">
                    <label class="block text-sm font-bold text-indigo-700 mb-2">Scan Barcode / Search Product</label>
                    <div class="flex gap-2 relative">
                        <input 
                            v-model="barcodeInput" 
                            @input="onSearchInput"
                            @keydown.enter.prevent="handleEnter"
                            type="text" 
                            placeholder="Type Name, SKU or Barcode..." 
                            class="block w-full rounded-md border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
                            ref="barcodeRef"
                            autocomplete="off"
                        >
                         <button @click="handleEnter" type="button" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 font-medium whitespace-nowrap">Add</button>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div v-if="searchResults.length > 0" class="absolute z-10 w-full left-0 mt-1 bg-white shadow-lg rounded-md border border-gray-200 max-h-60 overflow-auto">
                        <ul>
                            <li v-for="(result, idx) in searchResults" :key="idx" 
                                @click="selectResult(result)"
                                class="px-4 py-2 hover:bg-indigo-50 cursor-pointer border-b last:border-b-0 flex justify-between items-center text-sm"
                            >
                                <div>
                                    <span class="font-medium text-gray-900">{{ result.name }}</span>
                                    <span class="text-gray-500 ml-1">{{ result.variant_label }}</span>
                                    <div class="text-xs text-gray-400">SKU: {{ result.sku }} | Barcode: {{ result.barcode }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-800">Cost: {{ result.cost_price }}</div>
                                    <div class="text-xs text-gray-500">Current Stock: {{ result.stock }}</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                     <p v-if="scanError" class="text-red-500 text-xs mt-1">{{ scanError }}</p>
                </div>

                <!-- Product Lines -->
                <div class="pt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Items</h3>
                    <div class="space-y-2">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 items-end bg-gray-50 p-3 rounded">
                            <div class="w-48">
                                <label v-if="index === 0" class="block text-xs font-medium text-gray-500 mb-1">Product</label>
                                <select v-model="item.product_id" required class="block w-full rounded-md border-gray-300 sm:text-sm border p-1 h-9">
                                    <option value="">Select Product</option>
                                    <option v-for="product in productStore.products" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.sku }})
                                    </option>
                                </select>
                            </div>
                            <!-- Variant Selector -->
                            <div class="w-48">
                                <label v-if="index === 0" class="block text-xs font-medium text-gray-500 mb-1">Variant</label>
                                <select :disabled="getVariants(item.product_id).length === 0" v-model="item.product_variant_id" class="block w-full rounded-md border-gray-300 sm:text-sm border p-1 h-9 bg-gray-50 disabled:opacity-50">
                                    <option value="">{{ getVariants(item.product_id).length > 0 ? 'Select Variant' : '-' }}</option>
                                    <option v-for="variant in getVariants(item.product_id)" :key="variant.id" :value="variant.id">
                                        {{ variant.size_name || '-' }} / {{ variant.color_name || '-' }} (Stock: {{ variant.stock_quantity }})
                                    </option>
                                </select>
                            </div>
                            <div class="w-24">
                                <label v-if="index === 0" class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                                <input v-model.number="item.quantity" type="number" min="1" required class="block w-full rounded-md border-gray-300 sm:text-sm border p-1 h-9">
                            </div>
                            <div class="w-32">
                                <label v-if="index === 0" class="block text-xs font-medium text-gray-500 mb-1">Unit Cost</label>
                                <input v-model.number="item.unit_cost" type="number" min="0" step="0.01" required class="block w-full rounded-md border-gray-300 sm:text-sm border p-1 h-9">
                            </div>
                            <div class="w-32">
                                <label v-if="index === 0" class="block text-xs font-medium text-gray-500 mb-1">Subtotal</label>
                                <div class="py-2 px-3 text-right text-sm">
                                    {{ (item.quantity * item.unit_cost).toFixed(2) }}
                                </div>
                            </div>
                            <div class="pb-1">
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" @click="addItem" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                        + Add Item
                    </button>
                </div>

                <!-- Totals -->
                <div class="border-t pt-4 flex justify-end">
                    <div class="w-64 space-y-2">
                        <div class="flex justify-between text-base font-medium text-gray-900">
                            <p>Grand Total</p>
                            <p>{{ grandTotal }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Paid Amount</label>
                            <input v-model.number="form.paid_amount" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            Due: {{ (parseFloat(grandTotal) - (form.paid_amount || 0)).toFixed(2) }}
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t flex justify-end space-x-3">
                    <router-link to="/purchases" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </router-link>
                    <button type="submit" :disabled="loading" class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        {{ loading ? 'Updating...' : 'Update Purchase' }}
                    </button>
                </div>

                <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>

            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { useSupplierStore } from '../../stores/supplier';
import { useProductStore } from '../../stores/product';
import { usePurchaseStore } from '../../stores/purchase';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const supplierStore = useSupplierStore();
const productStore = useProductStore();
const purchaseStore = usePurchaseStore();
const router = useRouter();
const route = useRoute();

const loading = ref(false);
const loadingData = ref(true);
const error = ref(null);

const form = reactive({
    supplier_id: '',
    purchase_date: '',
    reference_no: '',
    status: 'received',
    paid_amount: 0,
    items: []
});

const barcodeInput = ref('');
const searchResults = ref([]);
const scanError = ref(null);
const barcodeRef = ref(null);
let searchTimeout = null;

const onSearchInput = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    scanError.value = null;
    
    if (barcodeInput.value.length < 2) {
        searchResults.value = [];
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await productStore.scanProduct(barcodeInput.value); 
            searchResults.value = response.data || [];
        } catch (e) {
            searchResults.value = [];
        }
    }, 300);
};

const handleEnter = async () => {
    if (searchResults.value.length === 0) {
        if (!barcodeInput.value) return;
        try {
             // Immediate Scan if no results yet
             const result = await productStore.scanProduct(barcodeInput.value);
             const hits = result.data || [];
             if (hits.length === 1) {
                 selectResult(hits[0]);
             } else if (hits.length > 1) {
                 searchResults.value = hits; 
             } else {
                 scanError.value = "Product not found";
             }
        } catch (e) {
             scanError.value = "Product not found";
        }
    } else if (searchResults.value.length === 1) {
        selectResult(searchResults.value[0]);
    } else {
        const exactMatch = searchResults.value.find(r => r.barcode === barcodeInput.value);
        if (exactMatch) {
            selectResult(exactMatch);
        }
    }
};

const selectResult = (item) => {
    // Check if exists
    const existing = form.items.find(i => i.product_id === item.product_id && i.product_variant_id === item.product_variant_id);
    if (existing) {
        existing.quantity++;
    } else {
        form.items.push({
            product_id: item.product_id,
            product_variant_id: item.product_variant_id,
            quantity: 1,
            unit_cost: item.cost_price || 0
        });
    }
    
    // Reset
    barcodeInput.value = '';
    searchResults.value = [];
    scanError.value = null;
    if(barcodeRef.value) barcodeRef.value.focus();
};

const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0).toFixed(2);
});

onMounted(async () => {
    try {
        await Promise.all([
            supplierStore.fetchSuppliers({ per_page: 100 }),
            productStore.fetchProducts({ per_page: 100 })
        ]);

        const response = await axios.get(`/api/v1/purchases/${route.params.id}`);
        const purchase = response.data.data;
        
        form.supplier_id = purchase.supplier_id;
        form.purchase_date = purchase.purchase_date;
        form.reference_no = purchase.reference_no;
        form.status = purchase.status;
        form.paid_amount = Number(purchase.paid_amount);
        form.items = purchase.items.map(i => ({
            product_id: i.product_id,
            product_variant_id: i.product_variant_id,
            quantity: i.quantity,
            unit_cost: Number(i.unit_cost)
        }));

        loadingData.value = false;
    } catch (e) {
        error.value = "Failed to load purchase details.";
        loadingData.value = false;
    }
});

const addItem = () => {
    form.items.push({ product_id: '', product_variant_id: '', quantity: 1, unit_cost: 0 });
};

const getVariants = (productId) => {
    if (!productId) return [];
    const product = productStore.products.find(p => p.id === productId);
    return product ? product.variants : [];
};

const removeItem = (index) => {
    if (form.items.length > 0) { // Allow removing all items (unlike create where we forced 1)
        form.items.splice(index, 1);
    }
};

const savePurchase = async () => {
    loading.value = true;
    error.value = null;
    try {
        await purchaseStore.updatePurchase(route.params.id, form);
        router.push('/purchases');
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update purchase.';
    } finally {
        loading.value = false;
    }
};
</script>
