<template>
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
            <router-link to="/products" class="text-indigo-600 hover:text-indigo-900">
                &larr; Back to Products
            </router-link>
        </div>

        <div v-if="initialLoading" class="text-center py-10">
            <p>Loading product...</p>
        </div>

        <div v-else class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="saveProduct" class="space-y-6">
                <!-- Same Form Structure as Create.vue -->
                <!-- Same Form Structure as Create.vue -->
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
                        {{ loading ? 'Updating...' : 'Update Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';
import VariantBuilder from '../../components/products/VariantBuilder.vue';
import { generateUniqueBarcode } from '../../utils/barcode';
import axios from 'axios';
import toastr from 'toastr';

const router = useRouter();
const route = useRoute();
const store = useProductStore();
const categoryStore = useCategoryStore();
const loading = ref(false);
const generating = ref(false);
const initialLoading = ref(true);
const error = ref(null);

const form = reactive({
    id: null,
    name: '',
    description: '',
    is_active: true,
    category_id: null,
    variants: []
});

onMounted(async () => {
    await categoryStore.fetchCategories();
    const productId = route.params.id;
    if (productId) {
        try {
            const response = await window.axios.get(`/api/v1/products/${productId}`);
            const product = response.data.data;
            // Populate form
            Object.assign(form, {
                id: product.id,
                name: product.name,
                is_active: product.is_active,
                description: product.description || '', // Default empty
                category_id: product.category_id,
                variants: (product.variants || []).map(v => ({...v}))
            });

            // Ensure at least one variant row (for existing simple products, it should be there)
            if (form.variants.length === 0) {
                 // Should technically not happen if data is correct, but safe fallback
                 // Wait, we can generate a new row if empty?
            }
            // Logic to flatten or check simple is REMOVED. We just show the variants.

        } catch (err) {
            error.value = "Failed to load product.";
            toastr.error("Failed to load product details");
            console.error(err);
        } finally {
            initialLoading.value = false;
            loading.value = false;
        }
    }
});

const saveProduct = async () => {
    loading.value = true;
    error.value = null;
    try {
        await store.updateProduct(form.id, form);
        toastr.success('Product updated successfully');
        router.push({ name: 'Products' });
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
             error.value = Object.values(err.response.data.errors).flat().join(', ');
        } else {
             error.value = err.response?.data?.message || 'Failed to update product.';
        }
        toastr.error(error.value);
    } finally {
        loading.value = false;
    }
};
</script>
