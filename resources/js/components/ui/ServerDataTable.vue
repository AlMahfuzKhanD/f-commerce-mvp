<template>
    <div class="flex flex-col">
        <!-- Search and Filter Header -->
        <!-- Search and Filter Header -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0 mr-auto">
                 <h2 v-if="title" class="text-xl font-bold text-gray-800">{{ title }}</h2>
                 <slot name="title"></slot> 
            </div>

            <!-- Right: Search & Actions -->
            <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <input 
                        type="text" 
                        :value="search" 
                        @input="$emit('update:search', $event.target.value)"
                        placeholder="Search..." 
                        class="w-full pl-10 pr-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:border-primary-500 transition-colors"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                <!-- Actions Button Slot -->
                <div class="w-full sm:w-auto flex justify-end">
                    <slot name="filters"></slot>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg relative">
                    
                    <!-- Loading Overlay -->
                    <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 transition-opacity duration-200">
                       <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SL
                                </th>
                                <th v-if="selectable" class="px-6 py-3 border-b text-left bg-gray-50">
                                   <input 
                                        type="checkbox" 
                                        :checked="isAllSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 h-4 w-4"
                                    >
                                </th>
                                <th v-for="header in headers" :key="header.key" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ header.label }}
                                </th>
                                <th v-if="actions" scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in items" :key="item.id || index">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ calculateSerial(index) }}
                                </td>
                                <td v-if="selectable" class="px-6 py-4 whitespace-nowrap border-b border-gray-100">
                                    <input 
                                        type="checkbox" 
                                        :value="item.id"
                                        v-model="selectedItems"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 h-4 w-4"
                                    >
                                </td>
                                <td 
                                    v-for="header in headers" 
                                    :key="header.key"
                                    class="px-6 py-4 whitespace-nowrap border-b border-gray-100 text-sm text-gray-700"
                                >            
                                    <slot :name="header.key" :item="item" :index="index">
                                        {{ item[header.key] }}
                                    </slot>
                                </td>
                                <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <slot name="actions" :item="item" :index="index"></slot>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0 && !loading">
                                <td :colspan="headers.length + (actions ? 2 : 1)" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No data available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="meta" class="py-3 flex items-center justify-between border-t border-gray-200 mt-2">
            <div class="flex-1 flex justify-between sm:hidden">
                <button 
                    @click="$emit('update:page', meta.current_page - 1)" 
                    :disabled="meta.current_page === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                    Previous
                </button>
                <button 
                    @click="$emit('update:page', meta.current_page + 1)" 
                    :disabled="meta.current_page === meta.last_page"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                >
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                     <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ meta.from || 0 }}</span>
                        to
                        <span class="font-medium">{{ meta.to || 0 }}</span>
                        of
                        <span class="font-medium">{{ meta.total || 0 }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button 
                            @click="$emit('update:page', meta.current_page - 1)"
                            :disabled="meta.current_page === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                        >
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <!-- Simple Page Display (can be enhanced to show page numbers) -->
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            Page {{ meta.current_page }} of {{ meta.last_page }}
                        </span>

                        <button 
                            @click="$emit('update:page', meta.current_page + 1)"
                             :disabled="meta.current_page === meta.last_page"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                        >
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    headers: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object, // Pagination meta from Laravel Resource { current_page, last_page, total, etc. }
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    search: {
        type: String,
        default: ''
    },
    actions: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: ''
    },
    selectable: {
        type: Boolean,
        default: false
    },
    modelValue: { // For selection
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:search', 'update:page', 'update:modelValue']);

// Selection Logic
const selectedItems = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const isAllSelected = computed(() => {
    return props.items.length > 0 && selectedItems.value.length === props.items.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedItems.value = props.items.map(item => item.id);
    } else {
        selectedItems.value = [];
    }
};

const currentPage = computed(() => props.meta.current_page || 1);

const calculateSerial = (index) => {
    if (!props.meta) return index + 1;
    const perPage = props.meta.per_page || 15;
    return (currentPage.value - 1) * perPage + index + 1;
};
</script>
