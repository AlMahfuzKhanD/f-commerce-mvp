<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Purchases</h2>
            <router-link to="/purchases/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + New Purchase Order
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <ServerDataTable 
                :headers="headers" 
                :items="store.purchases" 
                :meta="store.pagination"
                :loading="store.loading"
                :actions="true"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #status="{ item }">
                    <span :class="item.status === 'received' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border">
                        {{ item.status }}
                    </span>
                </template>
                <template #supplier_name="{ item }">
                    {{ item.supplier?.name }}
                </template>
                <template #items_count="{ item }">
                     {{ item.items?.length || 0 }} items
                </template>
                <template #actions="{ item }">
                     <button @click="openEditModal(item)" class="text-indigo-600 hover:text-indigo-900 mr-2 border border-indigo-600 rounded px-2 text-xs">Edit</button>
                     <button @click="store.deletePurchase(item.id)" class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs">Delete</button>
                </template>
            </ServerDataTable>
        </div>
        <PurchaseEditModal v-model="showEditModal" :purchase="selectedPurchase" @saved="refresh" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { usePurchaseStore } from '../../stores/purchase';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import PurchaseEditModal from '../../components/PurchaseEditModal.vue';

const store = usePurchaseStore();
const search = ref('');
const showEditModal = ref(false);
const selectedPurchase = ref(null);

const headers = [
    { key: 'reference_no', label: 'Reference' },
    { key: 'supplier.name', label: 'Supplier' },
    { key: 'purchase_date', label: 'Date' },
    { key: 'total_amount', label: 'Total Cost' },
    { key: 'paid_amount', label: 'Paid' },
    { key: 'status', label: 'Status' },
];

const fetchData = (page = 1) => {
    store.fetchPurchases({
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

const openEditModal = (purchase) => {
    selectedPurchase.value = purchase;
    showEditModal.value = true;
};

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
};

onMounted(() => {
    fetchData();
});
</script>
