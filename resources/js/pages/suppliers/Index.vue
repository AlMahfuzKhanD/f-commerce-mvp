<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Suppliers</h2>
            <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + New Supplier
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <ServerDataTable 
                :headers="headers" 
                :items="store.suppliers" 
                :meta="store.pagination"
                :loading="store.loading"
                :actions="true"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #actions="{ item }">
                    <button @click="openModal(item)" class="text-indigo-600 hover:text-indigo-900 mr-2 border border-indigo-600 rounded px-2 text-xs">Edit</button>
                    <button @click="deleteSupplier(item.id)" class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs">Delete</button>
                </template>
            </ServerDataTable>
        </div>

        <SupplierModal v-model="showModal" :supplier="selectedSupplier" @saved="refresh" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useSupplierStore } from '../../stores/supplier';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import SupplierModal from '../../components/SupplierModal.vue';

const store = useSupplierStore();
const showModal = ref(false);
const selectedSupplier = ref(null);
const search = ref('');

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'phone', label: 'Phone' },
    { key: 'email', label: 'Email' },
    { key: 'address', label: 'Address' },
    { key: 'created_at', label: 'Added On' },
];

const fetchData = (page = 1) => {
    store.fetchSuppliers({
        page,
        search: search.value
    });
};

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

onMounted(() => {
    fetchData();
});

const openModal = (supplier = null) => {
    selectedSupplier.value = supplier;
    showModal.value = true;
};

const deleteSupplier = async (id) => {
    if (confirm('Are you sure you want to delete this supplier?')) {
        await store.deleteSupplier(id);
        fetchData(store.pagination.current_page || 1);
    }
};

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
};
</script>
