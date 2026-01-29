<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 h-full flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <span class="mr-2">👤</span> Customer Details
            </h3>
        </div>

        <div class="p-4 space-y-4 flex-1 overflow-y-auto">
            <!-- Customer Search / Select -->
            <div class="relative">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Find Customer</label>
                <div class="relative">
                     <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <select 
                        :value="modelValue" 
                        @change="$emit('update:modelValue', $event.target.value)" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 transition-shadow shadow-sm bg-white appearance-none"
                    >
                        <option value="" disabled>Select a customer...</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }} ({{ c.phone }})
                        </option>
                    </select>
                     <!-- Custom Arrow for Select -->
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                <div class="mt-2 text-right">
                    <button class="text-xs text-primary-600 hover:text-primary-700 font-medium hover:underline">
                        + New Customer
                    </button>
                </div>
            </div>

            <!-- Selected Customer Info Card -->
            <div v-if="selectedCustomer" class="bg-primary-50 rounded-lg p-3 border border-primary-100">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ selectedCustomer.name }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ selectedCustomer.phone }}</p>
                        <p class="text-xs text-gray-500 mt-1 truncate max-w-[150px]">{{ selectedCustomer.address || 'No address' }}</p>
                    </div>
                    <button class="text-xs bg-white border border-primary-200 text-primary-600 px-2 py-1 rounded hover:bg-primary-50 font-medium">
                        Edit
                    </button>
                </div>
            </div>

            <hr class="border-gray-100" />

            <!-- Shipping Info (Collapsible) -->
            <div>
                 <button @click="showShipping = !showShipping" class="flex items-center justify-between w-full text-left">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer">
                        Shipping Address
                    </label>
                    <span class="text-gray-400 text-xs">{{ showShipping ? '▼' : '▶' }}</span>
                </button>
                
                <div v-show="showShipping" class="mt-3 space-y-3 transition-all">
                    <div>
                        <input 
                            :value="shippingAddress" 
                            @input="$emit('update:shippingAddress', $event.target.value)"
                            type="text" 
                            class="block w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                            placeholder="Address (if different)"
                        >
                    </div>
                    <div>
                        <input 
                            :value="shippingPhone" 
                            @input="$emit('update:shippingPhone', $event.target.value)"
                            type="text" 
                            class="block w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 shadow-sm"
                            placeholder="Phone (if different)"
                        >
                    </div>
                </div>
            </div>

             <hr class="border-gray-100" />

             <!-- Order Source -->
             <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Order Source</label>
                 <select 
                    :value="orderSource" 
                    @change="$emit('update:orderSource', $event.target.value)"
                    class="block w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 shadow-sm bg-white"
                >
                    <option value="manual">Store / Manual</option>
                    <option value="facebook">Facebook</option>
                    <option value="website">Website</option>
                    <option value="phone">Phone Call</option>
                </select>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number], // customer_id
    customers: { type: Array, default: () => [] },
    shippingAddress: String,
    shippingPhone: String,
    orderSource: String
});

defineEmits(['update:modelValue', 'update:shippingAddress', 'update:shippingPhone', 'update:orderSource']);

const showShipping = ref(true);

const selectedCustomer = computed(() => {
    return props.customers.find(c => c.id == props.modelValue);
});
</script>
