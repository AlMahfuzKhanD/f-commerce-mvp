<template>
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
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
                                <td v-for="header in headers" :key="header.key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <slot :name="header.key" :item="item">
                                        {{ item[header.key] }}
                                    </slot>
                                </td>
                                <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <slot name="actions" :item="item"></slot>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td :colspan="headers.length + (actions ? 1 : 0)" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No data available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    headers: {
        type: Array, // [{ key: 'name', label: 'Name' }]
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    actions: {
        type: Boolean,
        default: false,
    }
});
</script>
