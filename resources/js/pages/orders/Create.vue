<template>
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-800">Create New Order</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Product Selection -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Selection -->
                <div class="bg-white p-4 rounded shadow">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Customer</label>
                    <div class="flex space-x-2">
                         <select v-model="form.customer_id" class="block w-full border border-gray-300 rounded-md p-2">
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                        <!-- Simplify: Just load all customers for now, scalable search later -->
                    </div>
                </div>

                <!-- Products -->
                <div class="bg-white p-4 rounded shadow">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cart Items</label>
                    
                    <div v-for="(item, index) in cart" :key="index" class="flex gap-4 mb-4 items-end bg-gray-50 p-2 rounded">
                        <div class="flex-1">
                            <label class="text-xs text-gray-500">Product</label>
                            <select v-model="item.product_id" class="block w-full border border-gray-300 rounded p-1 text-sm" @change="onProductSelect(item)">
                                <option :value="null">Select Product</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">
                                    {{ p.name }} (Stock: {{ p.stock_quantity }})
                                </option>
                            </select>
                            
                            <!-- Variant Selector -->
                            <div v-if="getProductVariants(item.product_id).length > 0" class="mt-1">
                                <select v-model="item.product_variant_id" class="block w-full border border-gray-300 rounded p-1 text-xs" @change="onVariantSelect(item)">
                                    <option :value="null">Select Variant</option>
                                    <option v-for="v in getProductVariants(item.product_id)" :key="v.id" :value="v.id">
                                        {{ v.size }} / {{ v.color }} (Stock: {{ v.stock_quantity }}) <span v-if="v.extra_price > 0">(+{{ v.extra_price }})</span>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="w-20">
                            <label class="text-xs text-gray-500">Qty</label>
                            <input v-model.number="item.quantity" type="number" min="1" class="block w-full border border-gray-300 rounded p-1 text-sm">
                        </div>
                        <div class="w-24">
                            <label class="text-xs text-gray-500">Price</label>
                            <input v-model.number="item.unit_price" type="number" class="block w-full border border-gray-300 rounded p-1 text-sm">
                        </div>
                        <div class="w-24 text-right font-medium">
                            BDT {{ item.quantity * item.unit_price }}
                        </div>
                        <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>

                    <button @click="addItem" class="mt-2 text-indigo-600 text-sm font-medium hover:text-indigo-800">+ Add Item</button>
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
    discount: 0,
    delivery_charge: 0,
    order_source: 'manual'
});
const error = ref(null);

onMounted(() => {
    customerStore.fetchCustomers();
    productStore.fetchProducts();
});

const getProductVariants = (productId) => {
    const product = products.value.find(p => p.id === productId);
    return product ? (product.variants || []) : [];
};

const onProductSelect = (item) => {
    item.product_variant_id = null; // Reset variant
    const product = products.value.find(p => p.id === item.product_id);
    if (product) {
        item.unit_price = product.base_price; 
    }
};

const onVariantSelect = (item) => {
     const product = products.value.find(p => p.id === item.product_id);
     if (product && item.product_variant_id) {
         const variant = product.variants.find(v => v.id === item.product_variant_id);
         if (variant) {
             item.unit_price = Number(product.base_price) + Number(variant.extra_price || 0);
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
