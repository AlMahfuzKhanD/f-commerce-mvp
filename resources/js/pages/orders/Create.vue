<template>
    <div class="h-[calc(100vh-100px)] min-h-[600px] flex flex-col md:flex-row gap-4 p-2 md:p-0">
        <!-- LEFT: Customer Panel (25%) -->
        <div class="w-full md:w-3/12 h-full min-w-[280px]">
            <CustomerSelector
                v-model="form.customer_id"
                :customers="filteredCustomers"
                v-model:shippingAddress="form.shipping_address"
                v-model:shippingPhone="form.shipping_phone"
                v-model:orderSource="form.order_source"
            />
        </div>

        <!-- CENTER: Product Search & Grid (50%) -->
        <div class="w-full md:w-6/12 h-full flex flex-col min-w-[320px]">
            <ProductSearch
                v-model="barcodeInput"
                :search-results="searchResults"
                :loading="searchLoading"
                @search="onSearchInput"
                @enter="handleEnter"
                @select="selectResult"
            />
        </div>

        <!-- RIGHT: Cart & Checkout (25%) -->
        <div class="w-full md:w-3/12 h-full min-w-[280px]">
             <CartSummary
                :cart-items="cart"
                :products="products"
                :subtotal="subtotal"
                :total="total"
                v-model:discount="form.discount"
                v-model:delivery="form.delivery_charge"
                :can-submit="!!form.customer_id"
                :error="error"
                @update-qty="handleUpdateQty"
                @remove="removeItem"
                @submit="submitOrder"
                @clear="clearCart"
             />
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerStore } from '../../stores/customer';
import { useProductStore } from '../../stores/product';
import { useOrderStore } from '../../stores/order';
import CustomerSelector from '../../components/pos/CustomerSelector.vue';
import ProductSearch from '../../components/pos/ProductSearch.vue';
import CartSummary from '../../components/pos/CartSummary.vue';

const router = useRouter();
const customerStore = useCustomerStore();
const productStore = useProductStore();
const orderStore = useOrderStore();

// Stores Data
const customers = computed(() => customerStore.customers);
const filteredCustomers = computed(() => customers.value); // Future: Add customer search logic here if list is long
const products = computed(() => productStore.products);

// Form & Cart
const cart = ref([]);
const form = reactive({
    customer_id: '',
    shipping_address: '',
    shipping_phone: '',
    discount: 0,
    delivery_charge: 0,
    order_source: 'manual'
});
const error = ref(null);

// Search State
const barcodeInput = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);
let searchTimeout = null;

// Initial Load
onMounted(() => {
    customerStore.fetchCustomers();
    // productStore.fetchProducts(); // Not loading all products initially to save performance, relying on search
});

// --- Search Logic ---
const onSearchInput = (val) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    
    if (!val || val.length < 2) {
        searchResults.value = [];
        return;
    }

    searchLoading.value = true;
    searchTimeout = setTimeout(async () => {
        try {
            const response = await window.axios.get(`/api/v1/products/scan?barcode=${val}`);
            // Normalize data for grid: name, price, stock, variant_label
            searchResults.value = response.data.data.map(item => ({
                ...item,
                variant_label: formatVariantLabel(item)
            }));
        } catch (e) {
            searchResults.value = [];
        } finally {
            searchLoading.value = false;
        }
    }, 300);
};

const handleEnter = async () => {
   if (searchResults.value.length === 1) {
       selectResult(searchResults.value[0]);
   } else if (searchResults.value.length === 0 && barcodeInput.value) {
       // Try force exact match (Scanner fast input)
        searchLoading.value = true;
        try {
            const response = await window.axios.get(`/api/v1/products/scan?barcode=${barcodeInput.value}`);
            const hits = response.data.data;
            if (hits.length > 0) {
                 // Pick exact match first
                 const exact = hits.find(h => h.barcode === barcodeInput.value);
                 if (exact) selectResult(exact);
                 else selectResult(hits[0]);
            }
        } catch (e) {
             // Not found
        } finally {
            searchLoading.value = false;
        }
   }
};

const selectResult = (item) => {
    // Add to cart
    const existing = cart.value.find(i => i.product_id === item.product_id && i.product_variant_id === item.product_variant_id);
    
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            product_id: item.product_id,
            product_variant_id: item.product_variant_id,
            quantity: 1,
            unit_price: Number(item.price),
            name: item.name,
            variant_label: formatVariantLabel(item)
        });
    }

    // Reset Search
    barcodeInput.value = '';
    searchResults.value = [];
};

const formatVariantLabel = (item) => {
    if (item.size_name || item.color_name) {
        return `${item.size_name || ''} ${item.color_name || ''}`.trim();
    }
    return '';
};

// --- Cart Logic ---
const handleUpdateQty = ({ index, quantity }) => {
    cart.value[index].quantity = quantity;
};

const removeItem = (index) => {
    cart.value.splice(index, 1);
};

const clearCart = () => {
    cart.value = [];
};

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value + form.delivery_charge - form.discount);
});

// --- Submit Logic ---
const submitOrder = async () => {
    error.value = null;
    try {
        const payload = {
            customer_id: form.customer_id,
            items: cart.value.map(i => ({
                product_id: i.product_id,
                product_variant_id: i.product_variant_id,
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
        console.error(err);
        error.value = err.response?.data?.message || 'Failed to create order';
    }
};
</script>
