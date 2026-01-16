<template>
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-800">Create New Order</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Product Selection -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Selection -->
                <div class="bg-white p-4 rounded shadow">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Customer</label>
                    <div class="flex space-x-2 mb-4">
                         <select v-model="form.customer_id" class="block w-full border border-gray-300 rounded-md p-2">
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                    </div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Address <span class="text-xs text-gray-400 font-normal">(Optional, defaults to Customer Address)</span></label>
                    <textarea v-model="form.shipping_address" rows="2" class="block w-full border border-gray-300 rounded-md p-2 text-sm mb-2" placeholder="Enter alternate shipping address..."></textarea>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Phone <span class="text-xs text-gray-400 font-normal">(Optional)</span></label>
                    <input v-model="form.shipping_phone" type="text" class="block w-full border border-gray-300 rounded-md p-2 text-sm" placeholder="Enter alternate contact number...">
                </div>

                <!-- Products -->
                <!-- Scan Barcode -->
                <!-- Scan Barcode / Search -->
                <div class="bg-indigo-50 p-4 rounded shadow border border-indigo-100 relative">
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
                         <button @click="handleEnter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 font-medium whitespace-nowrap">Add</button>
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
                                    <div class="font-bold text-gray-800">{{ result.price }}</div>
                                    <div class="text-xs text-gray-500">Stock: {{ result.stock }}</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                     <p v-if="scanError" class="text-red-500 text-xs mt-1">{{ scanError }}</p>
                </div>

                <!-- Products -->
                <div class="bg-white p-4 rounded shadow">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700">Order Items</label>
                        <button @click="addItem" class="text-indigo-600 text-sm font-medium hover:text-indigo-800">+ Add Product</button>
                    </div>
                    
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Product</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Variant</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Price</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Qty</th>
                                    <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Total</th>
                                    <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Act</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(item, index) in cart" :key="index">
                                    <td class="px-3 py-2">
                                         <select v-model="item.product_id" class="block w-full border border-gray-300 rounded p-1 text-sm focus:ring-indigo-500 focus:border-indigo-500" @change="onProductSelect(item)">
                                            <option :value="null">-- Select --</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">
                                                {{ p.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                         <select v-if="getProductVariants(item.product_id).length > 0" v-model="item.product_variant_id" class="block w-full border border-gray-300 rounded p-1 text-xs focus:ring-indigo-500 focus:border-indigo-500" @change="onVariantSelect(item)">
                                            <option :value="null">-- Variant --</option>
                                            <option v-for="v in getProductVariants(item.product_id)" :key="v.id" :value="v.id">
                                                {{ v.size_name || 'N/A' }} / {{ v.color_name || 'N/A' }} ({{ v.stock_quantity }})
                                            </option>
                                        </select>
                                        <span v-else class="text-xs text-gray-400">N/A</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="item.unit_price" type="number" class="block w-full border border-gray-300 rounded p-1 text-sm text-right focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="item.quantity" type="number" min="1" class="block w-full border border-gray-300 rounded p-1 text-sm text-center focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">
                                        {{ (item.quantity * item.unit_price).toFixed(2) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="cart.length === 0">
                                    <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">
                                        No items added. Click "+ Add Product" to start.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Totals & Submit -->
            <div class="lg:col-span-1">
                <div class="bg-white p-4 rounded shadow space-y-4">
                    <h3 class="font-medium text-gray-900 border-b pb-2">Order Summary</h3>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">{{ subtotal }}</span>
                    </div>
                     <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Discount</span>
                         <input v-model.number="form.discount" type="number" class="w-20 border rounded p-1 text-right">
                    </div>
                     <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Delivery</span>
                         <input v-model.number="form.delivery_charge" type="number" class="w-20 border rounded p-1 text-right">
                    </div>
                    
                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Total</span>
                        <span>{{ total }}</span>
                    </div>

                    <div class="pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                        <select v-model="form.order_source" class="block w-full border border-gray-300 rounded p-2 text-sm">
                            <option value="manual">Manual Entry</option>
                            <option value="facebook">Facebook</option>
                        </select>
                    </div>

                    <button @click="submitOrder" :disabled="cart.length === 0 || !form.customer_id" class="w-full bg-indigo-600 text-white py-2 rounded shadow hover:bg-indigo-700 disabled:opacity-50">
                        Create Order
                    </button>
                    
                     <div v-if="error" class="text-red-600 text-xs text-center">
                        {{ error }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerStore } from '../../stores/customer';
import { useProductStore } from '../../stores/product';
import { useOrderStore } from '../../stores/order';

const router = useRouter();
const customerStore = useCustomerStore();
const productStore = useProductStore();
const orderStore = useOrderStore();

const customers = computed(() => customerStore.customers);
const products = computed(() => productStore.products);

const cart = ref([{ product_id: null, product_variant_id: null, quantity: 1, unit_price: 0 }]);
const form = reactive({
    customer_id: '',
    shipping_address: '',
    shipping_phone: '',
    discount: 0,
    delivery_charge: 0,
    order_source: 'manual'
});
const error = ref(null);

onMounted(() => {
    customerStore.fetchCustomers();
    productStore.fetchProducts();
    if (barcodeRef.value) {
        barcodeRef.value.focus();
    }
});

const getProductVariants = (productId) => {
    const product = products.value.find(p => p.id === productId);
    return product ? (product.variants || []) : [];
};

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
            const response = await window.axios.get(`/api/v1/products/scan?barcode=${barcodeInput.value}`);
            searchResults.value = response.data.data;
        } catch (e) {
            searchResults.value = [];
            // Don't show error while typing, just empty list
        }
    }, 300); // 300ms debounce
};

const handleEnter = async () => {
    // If we have results, use the first one OR if only 1 exact match?
    // If results is empty, try immediate fetch (maybe user pasted and pressed enter fast)
    if (searchResults.value.length === 0) {
        if (!barcodeInput.value) return;
        try {
             const response = await window.axios.get(`/api/v1/products/scan?barcode=${barcodeInput.value}`);
             const hits = response.data.data;
             if (hits.length === 1) {
                 selectResult(hits[0]);
             } else if (hits.length > 1) {
                 searchResults.value = hits; // Show dropdown
             } else {
                 scanError.value = "Product not found";
             }
        } catch (e) {
             scanError.value = "Product not found";
        }
    } else if (searchResults.value.length === 1) {
        selectResult(searchResults.value[0]);
    } else {
        // If multiple, maybe select first? Or let user choose? 
        // For scan use case: "Scan" usually means exact match.
        // If one of the results has EXACT barcode match, pick it.
        const exactMatch = searchResults.value.find(r => r.barcode === barcodeInput.value);
        if (exactMatch) {
            selectResult(exactMatch);
        }
        // Else do nothing, let user click.
    }
};

const selectResult = (item) => {
    const newItem = {
        product_id: item.product_id,
        product_variant_id: item.product_variant_id,
        quantity: 1,
        unit_price: item.price
    };

     // Add to cart
     // Check if exists?
    const existing = cart.value.find(i => i.product_id === newItem.product_id && i.product_variant_id === newItem.product_variant_id);
    if (existing) {
        existing.quantity++;
    } else {
        // Remove empty default row if present
        if (cart.value.length === 1 && !cart.value[0].product_id) {
            cart.value.pop();
        }
        cart.value.push(newItem);
    }
    
    // Reset
    barcodeInput.value = '';
    searchResults.value = [];
    scanError.value = null;
    
    // Re-focus?
    // barcodeRef.value.focus(); // Sometimes problematic with blur
};

const onProductSelect = (item) => {
    item.product_variant_id = null; // Reset variant
    const product = products.value.find(p => p.id === item.product_id);
    if (product) {
        // Auto-select variant if it's the only one (Simple Product)
        if (product.variants && product.variants.length === 1) {
            item.product_variant_id = product.variants[0].id;
            // Set price from that variant
            item.unit_price = Number(product.variants[0].price);
        } else {
            // Variable product, wait for selection. 
            // Optional: Set min price as placeholder?
             item.unit_price = product.base_price; 
        }
    }
};

const onVariantSelect = (item) => {
     const product = products.value.find(p => p.id === item.product_id);
     if (product && item.product_variant_id) {
         const variant = product.variants.find(v => v.id === item.product_variant_id);
         if (variant) {
             // Unified Price Architecture: Use variant price directly
             item.unit_price = Number(variant.price);
         }
     } else if (product) {
          item.unit_price = product.base_price;
     }
};

const addItem = () => {
    cart.value.push({ product_id: null, product_variant_id: null, quantity: 1, unit_price: 0 });
};

const removeItem = (index) => {
    cart.value.splice(index, 1);
};

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const total = computed(() => {
    return subtotal.value + form.delivery_charge - form.discount;
});

const submitOrder = async () => {
    error.value = null;
    try {
        // Prepare payload matching API
        const payload = {
            customer_id: form.customer_id,
            items: cart.value.filter(i => i.product_id).map(i => ({
                product_id: i.product_id,
                product_variant_id: i.product_variant_id, // Add variant ID
                quantity: i.quantity,
                unit_price: i.unit_price
            })),
            discount: form.discount,
            delivery_charge: form.delivery_charge,
            shipping_address: form.shipping_address,
            shipping_phone: form.shipping_phone,
            order_source: form.order_source
        };
        
        await orderStore.createOrder(payload);
        router.push({ name: 'Orders' });
    } catch (err) {
        console.log(err);
        error.value = err.response?.data?.message || 'Failed to create order';
    }
};
</script>
