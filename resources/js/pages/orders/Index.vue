<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Orders</h2>
            <router-link to="/orders/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + New Order
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <DataTable :headers="headers" :items="store.orders" :actions="true">
                <template #status="{ item }">
                    <span :class="statusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border">
                        {{ item.status }}
                    </span>
                </template>
                <template #actions="{ item }">
                    <!-- Quick Actions -->
                     <button v-if="item.status === 'new'" @click="updateStatus(item.id, 'confirmed')" class="text-green-600 hover:text-green-900 mr-2 border border-green-600 rounded px-2 text-xs">Confirm</button>
                     <button v-if="item.status === 'confirmed'" @click="updateStatus(item.id, 'shipped')" class="text-blue-600 hover:text-blue-900 mr-2 border border-blue-600 rounded px-2 text-xs">Ship</button>
                    <!-- <router-link :to="`/orders/${item.id}`" class="text-indigo-600 hover:text-indigo-900">View</router-link> -->
                </template>
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useOrderStore } from '../../stores/order';
import DataTable from '../../components/ui/DataTable.vue';

const store = useOrderStore();

const headers = [
    { key: 'order_number', label: 'Order #' },
    { key: 'customer_name', label: 'Customer' }, // Assuming API returns this flatten or we need to handle nested
    { key: 'total_amount', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Date' },
];

// Helper for status colors
const statusClass = (status) => {
    switch (status) {
        case 'new': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'shipped': return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        case 'delivered': return 'bg-green-100 text-green-800 border-green-200';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800';
    }
};

onMounted(() => {
    store.fetchOrders();
});

const updateStatus = async (id, status) => {
    if (!confirm(`Change status to ${status}?`)) return;
    await store.updateStatus(id, status);
};
</script>
