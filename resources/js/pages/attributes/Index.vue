<template>
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Attributes</h2>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button 
                    @click="activeTab = 'sizes'"
                    :class="[activeTab === 'sizes' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                >
                    Sizes
                </button>
                <button 
                    @click="activeTab = 'colors'"
                    :class="[activeTab === 'colors' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                >
                    Colors
                </button>
            </nav>
        </div>

        <!-- content -->
        <div v-if="activeTab === 'sizes'">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Manage Sizes</h3>
                
                 <!-- Add Form -->
                <form @submit.prevent="addSize" class="flex gap-4 items-end mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Name</label>
                        <input v-model="sizeForm.name" type="text" required placeholder="e.g. M, L, Unstitched" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                     <div>
                        <label class="block text-xs font-medium text-gray-700">Code (Optional)</label>
                        <input v-model="sizeForm.code" type="text" placeholder="Short Code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Add Size</button>
                </form>

                <!-- List -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="size in store.sizes" :key="size.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ size.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ size.code || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="deleteSize(size.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="activeTab === 'colors'">
             <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Manage Colors</h3>
                
                 <!-- Add Form -->
                <form @submit.prevent="addColor" class="flex gap-4 items-end mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Name</label>
                        <input v-model="colorForm.name" type="text" required placeholder="e.g. Red, Blue" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                     <div>
                        <label class="block text-xs font-medium text-gray-700">Hex Code (Optional)</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input v-model="colorForm.code" type="color" class="h-9 w-9 p-0 border rounded overflow-hidden">
                            <input v-model="colorForm.code" type="text" placeholder="#RRGGBB" class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">Add Color</button>
                </form>

                <!-- List -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                             <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                            <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="color in store.colors" :key="color.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ color.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ color.code || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div v-if="color.code" class="w-6 h-6 rounded-full border border-gray-200" :style="{ backgroundColor: color.code }"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="deleteColor(color.id)" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAttributeStore } from '../../stores/attribute';

const store = useAttributeStore();
const activeTab = ref('sizes');

const sizeForm = reactive({ name: '', code: '' });
const colorForm = reactive({ name: '', code: '' });

onMounted(() => {
    store.fetchSizes();
    store.fetchColors();
});

const addSize = async () => {
    await store.createSize(sizeForm);
    sizeForm.name = '';
    sizeForm.code = '';
};

const deleteSize = async (id) => {
    if (confirm('Delete this size?')) {
        await store.deleteSize(id);
    }
};

const addColor = async () => {
    await store.createColor(colorForm);
    colorForm.name = '';
    colorForm.code = '';
};

const deleteColor = async (id) => {
    if (confirm('Delete this color?')) {
        await store.deleteColor(id);
    }
};
</script>
