<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Products</h2>
            <router-link to="/products/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + Add Product
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <DataTable :headers="headers" :items="store.products || []" :actions="true">
                <template #actions="{ item }">
                    <button @click="openModal(item)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button @click="store.deleteProduct(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
                </template>
            </DataTable>
        </div>

        <!-- Product Modal -->
        <Modal v-model="showModal" :title="isEditing ? 'Edit Product' : 'Add New Product'">
            <form @submit.prevent="saveProduct" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input v-model="form.sku" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input v-model.number="form.stock_quantity" type="number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost Price</label>
                        <input v-model.number="form.cost_price" type="number" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Selling Price</label>
                        <input v-model.number="form.base_price" type="number" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="errors" class="text-red-600 text-sm">
                    {{ errors }}
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Save
                    </button>
                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useProductStore } from '../../stores/product';
import DataTable from '../../components/ui/DataTable.vue';
import Modal from '../../components/ui/Modal.vue';

const store = useProductStore();
const showModal = ref(false);
const isEditing = ref(false);
const errors = ref(null);

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'sku', label: 'SKU' },
    { key: 'stock_quantity', label: 'Stock' },
    { key: 'base_price', label: 'Price' },
];

const form = reactive({
    id: null,
    name: '',
    sku: '',
    stock_quantity: 0,
    cost_price: 0,
    base_price: 0
});

onMounted(() => {
    store.fetchProducts();
});

const openModal = (item = null) => {
    errors.value = null;
    if (item) {
        isEditing.value = true;
        Object.assign(form, item);
    } else {
        isEditing.value = false;
        Object.assign(form, { id: null, name: '', sku: '', stock_quantity: 0, cost_price: 0, base_price: 0 });
    }
    showModal.value = true;
};

const saveProduct = async () => {
    try {
        if (isEditing.value) {
            await store.updateProduct(form.id, form);
        } else {
            await store.createProduct(form);
        }
        showModal.value = false;
    } catch (error) {
        if (error.response && error.response.status === 422) {
             errors.value = Object.values(error.response.data.errors).flat().join(', ');
        } else {
             errors.value = 'An error occurred.';
        }
    }
};
</script>
