<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Products</h2>
            <router-link to="/products/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + Add Product
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <ServerDataTable
                :headers="headers"
                :items="store.products || []"
                :meta="store.pagination"
                :loading="store.loading"
                :actions="true"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #actions="{ item }">
                    <router-link :to="{ name: 'ProductsEdit', params: { id: item.id } }" class="text-indigo-600 hover:text-indigo-900 mr-3 border border-indigo-600 rounded px-2 text-xs inline-block py-0.5">Edit</router-link>
                    <button type="button" @click.prevent.stop="confirmDelete(item.id)" class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs">Delete</button>
                </template>
            </ServerDataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useProductStore } from '../../stores/product';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import Swal from 'sweetalert2';

const store = useProductStore();
const search = ref('');

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'sku', label: 'SKU' },
    { key: 'stock_quantity', label: 'Stock' },
    { key: 'base_price', label: 'Price' },
];

const fetchData = (page = 1) => {
    store.fetchProducts({
        page,
        search: search.value
    });
};

onMounted(() => {
    fetchData();
});

let timeout = null;
const handleSearch = () => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchData(1);
    }, 300);
};

const handlePageChange = (page) => {
    fetchData(page);
};

const confirmDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! Deleted product will also remove related variants.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
             await store.deleteProduct(id);
             Swal.fire(
                'Deleted!',
                'Product has been deleted.',
                'success'
            );
            // Refresh Data
            fetchData(store.pagination.current_page || 1);
        } catch (e) {
             console.error(e);
             // Handle 422 etc
             const message = e.response?.data?.message || 'Failed to delete product';
             Swal.fire(
                'Error!',
                message,
                'error'
            );
        }
    }
};
</script>
