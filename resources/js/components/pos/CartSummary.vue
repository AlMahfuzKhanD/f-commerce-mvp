<template>
    <div class="h-full flex flex-col bg-white rounded-xl shadow-sm border border-gray-100">
         <!-- Cart Header -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <span class="mr-2">🛒</span> Cart ({{ cartItems.length }})
            </h3>
            <button @click="$emit('clear')" class="text-xs text-red-500 hover:text-red-700 font-medium">
                Clear All
            </button>
        </div>

        <!-- Cart Items List -->
        <div class="flex-1 overflow-y-auto p-2 space-y-2">
            <div v-if="cartItems.length === 0" class="flex flex-col items-center justify-center h-48 text-gray-400">
                <span class="text-3xl mb-2">🛍️</span>
                <p class="text-sm">Cart is empty</p>
            </div>

            <div 
                v-for="(item, index) in cartItems" 
                :key="index"
                class="bg-white border border-gray-100 rounded-lg p-2.5 hover:border-primary-200 transition-colors flex justify-between items-start group shadow-sm"
            >
                <div class="flex-1 min-w-0 pr-2">
                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ getProductName(item) }}</h4>
                    <p class="text-xs text-gray-500">{{ getVariantName(item) }}</p>
                    <div class="mt-1.5 flex items-center text-xs text-primary-600 font-semibold">
                         {{ item.unit_price }} x 
                    </div>
                </div>

                <div class="flex flex-col items-end space-y-2">
                     <p class="text-sm font-bold text-gray-800 w-16 text-right">
                        {{ (item.quantity * item.unit_price).toFixed(2) }}
                    </p>
                    
                     <!-- Qty Control -->
                    <div class="flex items-center border border-gray-200 rounded-md">
                        <button 
                            @click="updateQty(index, -1)" 
                            class="px-2 py-0.5 text-gray-500 hover:bg-gray-100 hover:text-red-500 text-sm font-bold border-r border-gray-200"
                        >-</button>
                        <span class="px-2 text-xs font-semibold text-gray-700 min-w-[20px] text-center">{{ item.quantity }}</span>
                        <button 
                            @click="updateQty(index, 1)" 
                            class="px-2 py-0.5 text-gray-500 hover:bg-gray-100 hover:text-green-500 text-sm font-bold border-l border-gray-200"
                        >+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="p-4 bg-gray-50 border-t border-gray-100 rounded-b-xl space-y-3">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span class="font-medium">{{ formatPrice(subtotal) }}</span>
            </div>
            
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600 w-24">Discount</span>
                <input 
                    :value="discount" 
                    @input="$emit('update:discount', Number($event.target.value))" 
                    type="number" 
                    class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-xs focus:ring-primary-500 focus:border-primary-500"
                >
            </div>
             <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600 w-24">Delivery</span>
                <input 
                    :value="delivery" 
                    @input="$emit('update:delivery', Number($event.target.value))" 
                    type="number" 
                    class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-xs focus:ring-primary-500 focus:border-primary-500"
                >
            </div>
            
            <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                <span class="text-base font-bold text-gray-800">Total</span>
                <span class="text-xl font-bold text-primary-700">{{ formatPrice(total) }}</span>
            </div>

            <button 
                @click="$emit('submit', 'draft')"
                :disabled="cartItems.length === 0 || !canSubmit"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 rounded-lg shadow-sm transition-all mb-2 flex items-center justify-center"
            >
                <span class="mr-2">💾</span> Save as Draft
            </button>

            <button 
                @click="$emit('submit', 'new')"
                :disabled="cartItems.length === 0 || !canSubmit"
                class="w-full bg-gradient-to-r from-accent-500 to-accent-600 hover:from-accent-600 hover:to-accent-700 text-white font-bold py-3 rounded-lg shadow-md transition-all transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
                <span class="mr-2">✅</span> Complete Order
            </button>
             <div v-if="error" class="text-red-500 text-xs text-center font-medium">{{ error }}</div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    cartItems: { type: Array, required: true },
    products: { type: Array, required: true }, // Needed to lookup names if not cached in item
    subtotal: Number,
    total: Number,
    discount: Number,
    delivery: Number,
    canSubmit: Boolean,
    error: String
});

const emit = defineEmits(['update:discount', 'update:delivery', 'update-qty', 'remove', 'submit', 'clear']);

const getProductName = (item) => {
    // If name is embedded in item, use it. Else lookup.
    // Assuming search/scan returns full objects with name, but cart stores IDs + derived data.
    // Let's assume the parent pushes items with 'name' property for ease.
    if (item.name) return item.name;
    const p = props.products.find(x => x.id === item.product_id);
    return p ? p.name : 'Unknown Product';
};

const getVariantName = (item) => {
    if (item.variant_label) return item.variant_label;
    return 'Standard';
};

const updateQty = (index, change) => {
    const newQty = props.cartItems[index].quantity + change;
    if (newQty <= 0) {
        emit('remove', index);
    } else {
        emit('update-qty', { index, quantity: newQty });
    }
};

const formatPrice = (val) => {
    return Number(val).toFixed(2);
};
</script>
