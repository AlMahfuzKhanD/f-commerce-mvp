<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Orders</h2>
            <router-link to="/orders/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + New Order
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <ServerDataTable 
                :headers="headers" 
                :items="store.orders" 
                :meta="store.pagination"
                :loading="store.loading"
                :actions="true"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #status="{ item }">
                    <span :class="statusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border">
                        {{ item.status }}
                    </span>
                </template>
                <template #actions="{ item }">
                    <button @click="openPaymentModal(item)" v-if="item.payment_status !== 'paid'" class="text-green-600 hover:text-green-900 mr-2 border border-green-600 rounded px-2 text-xs">Pay</button>
                    <router-link :to="`/orders/${item.id}/invoice`" class="text-gray-600 hover:text-gray-900 mr-2 border border-gray-300 rounded px-2 text-xs">Invoice</router-link>
                    <button @click="openDeliveryModal(item)" class="text-yellow-600 hover:text-yellow-900 mr-2 border border-yellow-600 rounded px-2 text-xs">Delivery</button>
                    <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-900 mr-2 border border-blue-600 rounded px-2 text-xs">Edit</button>
                    <button class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs" @click="confirmDelete(item.id)">Delete</button>
                    <!-- Quick Actions -->
                     <button v-if="item.status === 'new'" @click="updateStatus(item.id, 'confirmed')" class="text-green-600 hover:text-green-900 mr-2 border border-green-600 rounded px-2 text-xs">Confirm</button>
                     <button v-if="item.status === 'confirmed'" @click="updateStatus(item.id, 'shipped')" class="text-blue-600 hover:text-blue-900 mr-2 border border-blue-600 rounded px-2 text-xs">Ship</button>
                </template>
                
                <template #filters>
                     <select v-model="statusFilter" @change="handleSearch" class="border rounded-lg text-gray-700 py-2 px-3 focus:outline-none focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </template>
            </ServerDataTable>
        </div>

        <PaymentModal v-model="showPayment" :order="selectedOrder" @payment-success="refresh" />
        <DeliveryModal v-model="showDelivery" :order="selectedOrder" @saved="refresh" />
        <OrderEditModal v-model="showEditModal" :order="selectedOrder" @saved="refresh" />
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useOrderStore } from '../../stores/order';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import PaymentModal from '../../components/PaymentModal.vue';
import DeliveryModal from '../../components/DeliveryModal.vue';
import OrderEditModal from '../../components/OrderEditModal.vue';
import { debounce } from 'lodash'; // You typically need lodash for debounce, or write a custom one

const store = useOrderStore();
const showPayment = ref(false);
const showDelivery = ref(false);
const showEditModal = ref(false);
const selectedOrder = ref(null);
const search = ref('');
const statusFilter = ref('');

const headers = [
    { key: 'order_number', label: 'Order #' },
    { key: 'customer_name', label: 'Customer' }, 
    { key: 'total_amount', label: 'Total' },
    { key: 'due_amount', label: 'Due' },
    { key: 'payment_status', label: 'Payment' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Date' },
];

const statusClass = (status) => {
    switch (status) {
        case 'new': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'shipped': return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        case 'delivered': return 'bg-green-100 text-green-800 border-green-200';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
        case 'paid': return 'bg-green-100 text-green-800 border-green-200';
        case 'partial': return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'unpaid': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const fetchData = (page = 1) => {
    store.fetchOrders({
        page,
        search: search.value,
        status: statusFilter.value
    });
};

// Debounce search
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

const updateStatus = async (id, status) => {
    if (!confirm(`Change status to ${status}?`)) return;
    await store.updateStatus(id, status);
};

const confirmDelete = async (id) => {
    if (confirm('Are you sure? This will delete the order and revert stock.')) {
        await store.deleteOrder(id);
        fetchData(store.pagination.current_page || 1);
    }
};

const openPaymentModal = (order) => {
    selectedOrder.value = order;
    showPayment.value = true;
};

const openDeliveryModal = (order) => {
    selectedOrder.value = order;
    showDelivery.value = true;
};

const openEditModal = (order) => {
    selectedOrder.value = order;
    showEditModal.value = true;
};

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
    showPayment.value = false;
    showDelivery.value = false;
};
</script>
