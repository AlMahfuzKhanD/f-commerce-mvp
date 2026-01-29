<template>
    <div class="h-full flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Search Header -->
        <div class="p-4 border-b border-gray-100 bg-white z-10">
            <div class="relative">
                 <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    ref="searchInput"
                    :value="modelValue" 
                    @input="handleInput"
                    @keydown.enter.prevent="handleEnter"
                    type="text" 
                    class="block w-full pl-12 pr-4 py-4 border-2 border-gray-100 rounded-xl text-lg focus:ring-4 focus:ring-primary-50 focus:border-primary-500 transition-all shadow-sm"
                    placeholder="Scan Barcode or Search Product..."
                >
                <div v-if="loading" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <!-- Category Tabs (Placeholder for now) -->
            <div class="flex space-x-2 mt-3 overflow-x-auto pb-1 no-scrollbar hidden md:flex">
                <button class="px-3 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-full whitespace-nowrap">All Products</button>
                <button class="px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full hover:bg-gray-200 whitespace-nowrap">T-Shirts</button>
                <button class="px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full hover:bg-gray-200 whitespace-nowrap">Polos</button>
                <button class="px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full hover:bg-gray-200 whitespace-nowrap">Jeans</button>
            </div>
        </div>

        <!-- Product Grid / No Results -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50 relative">
             <!-- Search Results (Prioritized View) -->
             <div v-if="searchResults.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div 
                    v-for="(product, idx) in searchResults" 
                    :key="idx"
                    @click="emitSelect(product)"
                    class="bg-white rounded-lg border border-gray-200 p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all group flex flex-col justify-between"
                >
                    <div class="mb-2">
                         <!-- Placeholder Image -->
                        <div class="w-full h-24 bg-gray-100 rounded-md mb-2 flex items-center justify-center text-gray-300">
                             <span class="text-2xl">👕</span>
                        </div>
                        <h4 class="font-semibold text-gray-800 text-sm line-clamp-2 group-hover:text-primary-600 leading-tight">
                            {{ product.name }}
                        </h4>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                             <span class="bg-gray-100 px-1.5 py-0.5 rounded text-[10px]">{{ product.variant_label || 'Default' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500">Stock: {{ product.stock }}</span>
                        <span class="font-bold text-gray-900 bg-gray-50 px-2 py-1 rounded text-sm">{{ product.price }}</span>
                    </div>
                </div>
             </div>

             <!-- Default State / Empty -->
             <div v-else-if="!modelValue" class="flex flex-col items-center justify-center h-full text-center text-gray-400">
                <svg class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-sm font-medium">Scan a barcode or type to search products.</p>
             </div>

             <div v-else-if="!loading && searchResults.length === 0" class="flex flex-col items-center justify-center h-full text-center text-gray-400">
                 <p class="text-sm font-medium text-red-400">No products found matching "{{ modelValue }}"</p>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: String,
    searchResults: { type: Array, default: () => [] },
    loading: Boolean
});

const emit = defineEmits(['update:modelValue', 'search', 'enter', 'select']);
const searchInput = ref(null);

const handleInput = (e) => {
    emit('update:modelValue', e.target.value);
    emit('search', e.target.value);
};

const handleEnter = () => {
    emit('enter');
};

const emitSelect = (item) => {
    emit('select', item);
};
</script>
