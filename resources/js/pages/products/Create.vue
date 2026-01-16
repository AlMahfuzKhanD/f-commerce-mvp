<template>
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Add New Product</h2>
            <router-link to="/products" class="text-indigo-600 hover:text-indigo-900">
                &larr; Back to Products
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="saveProduct" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-12">
                    <div class="sm:col-span-6">
                        <label class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                            <option :value="null">Select Category</option>
                            <option v-for="cat in categoryStore.categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center pt-6">
                        <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                        </label>
                    </div>

                     <div class="sm:col-span-12">
                        <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
                    </div>

                    <div class="sm:col-span-12 border-t pt-4 mt-2">
                        <VariantBuilder v-model="form.variants" />
                    </div>
                </div>

                <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded">
                    {{ error }}
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <router-link to="/products" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </router-link>
                    <button type="submit" :disabled="loading" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        {{ loading ? 'Saving...' : 'Save Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';
import VariantBuilder from '../../components/products/VariantBuilder.vue';
import { generateUniqueBarcode } from '../../utils/barcode';

const router = useRouter();
const store = useProductStore();
const categoryStore = useCategoryStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    name: '',
    description: '',
    is_active: true,
    category_id: null,
    variants: []
});

onMounted(async () => {
    store.fetchProducts(); // Pre-load to avoid stale state? Not needed.
    await categoryStore.fetchCategories();
    // Initialize with one default variant row
    const barcode = await generateUniqueBarcode();
    form.variants.push({
        size_id: null,
        color_id: null,
        sku: '',
        barcode: barcode,
        stock_quantity: 0,
        price: 0
    });
});

const saveProduct = async () => {
    loading.value = true;
    error.value = null;
    try {
        await store.createProduct(form);
        router.push({ name: 'Products' });
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
             error.value = Object.values(err.response.data.errors).flat().join(', ');
        } else {
             error.value = err.response?.data?.message || 'Failed to create product.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
