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
                :selectable="true"
                v-model="selectedIds"
                title="Orders"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #created_at="{ item }">
                    <span>{{ formatDate(item.created_at) }}</span>
                </template>
                <template #status="{ item }">
                    <span :class="statusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border capitalize">
                        {{ item.status === 'new' ? 'pending' : item.status }}
                    </span>
                </template>
                <template #actions="{ item, index }">
                    <div class="relative">
                        <button @click="toggleDropdown(item.id)" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded px-2 py-1 text-xs font-semibold inline-flex items-center">
                            Actions
                            <svg class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="openDropdownId === item.id" 
                            :class="[
                                'absolute right-0 w-48 bg-white border rounded shadow-xl z-50 text-left',
                                index >= store.orders.length - 4 ? 'bottom-full mb-2' : 'mt-2'
                            ]"
                        >
                            <div class="py-1">
                                <!-- Standard Actions -->
                                <button v-if="item.payment_status !== 'paid'" @click="openPaymentModal(item)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add Payment</button>
                                <router-link :to="`/orders/${item.id}/invoice`" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Invoice</router-link>
                                <button @click="openDeliveryModal(item)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Delivery</button>
                                
                                <div class="border-t border-gray-100 my-1"></div>

                                <!-- Workflow Logic -->
                                <button v-if="['pending', 'new'].includes(item.status) || ['cancelled', 'returned'].includes(item.status)" 
                                        @click="updateStatusWithClose(item.id, 'confirmed')" 
                                        class="block w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50 font-semibold">
                                    {{ ['cancelled', 'returned'].includes(item.status) ? 'Reactivate (Confirm)' : 'Confirm Order' }}
                                </button>
                                
                                <button v-if="item.status === 'confirmed'" @click="updateStatusWithClose(item.id, 'shipped')" class="block w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 font-semibold">Ship Order</button>
                                <button v-if="item.status === 'shipped'" @click="updateStatusWithClose(item.id, 'delivered')" class="block w-full text-left px-4 py-2 text-sm text-teal-600 hover:bg-teal-50 font-semibold">Mark Delivered</button>
                                
                                <!-- Negative Workflow -->
                                <button v-if="['shipped', 'delivered'].includes(item.status)" @click="updateStatusWithClose(item.id, 'returned')" class="block w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">Return Order</button>
                                <button v-if="['pending', 'new', 'confirmed'].includes(item.status)" @click="updateStatusWithClose(item.id, 'cancelled')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cancel Order</button>

                                <div class="border-t border-gray-100 my-1"></div>

                                <!-- Restricted Edit/Delete -->
                                <router-link v-if="['pending', 'new'].includes(item.status)" :to="{ name: 'EditOrder', params: { id: item.id } }" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">Edit Order</router-link>
                                <button v-if="['pending', 'new'].includes(item.status)" @click="confirmDelete(item.id)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete Order</button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template #filters>
                     <select v-model="statusFilter" @change="handleSearch" class="border rounded-lg text-gray-700 py-2 px-3 focus:outline-none focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending">Received (Pending)</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="returned">Returned</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </template>
            </ServerDataTable>
        </div>

        <PaymentModal v-model="showPayment" :order="selectedOrder" @payment-success="refresh" />
        <DeliveryModal v-model="showDelivery" :order="selectedOrder" @saved="refresh" />
        <BulkActionTray 
            :count="selectedIds.length"
            @print-invoice="printBulk('invoice')"
            @print-stickers="printBulk('stickers')"
            @cancel="selectedIds = []"
        />
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useOrderStore } from '../../stores/order';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import PaymentModal from '../../components/PaymentModal.vue';
import DeliveryModal from '../../components/DeliveryModal.vue';
import BulkActionTray from '../../components/BulkActionTray.vue';
import { debounce } from 'lodash'; 
import Swal from 'sweetalert2';
import toastr from 'toastr';

const store = useOrderStore();
const showPayment = ref(false);
const showDelivery = ref(false);
const selectedOrder = ref(null);
const search = ref('');
const statusFilter = ref('');
const selectedIds = ref([]);

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
        case 'new': 
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'shipped': return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        case 'delivered': return 'bg-green-100 text-green-800 border-green-200';
        case 'returned': return 'bg-orange-100 text-orange-800 border-orange-200';
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
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Change order status to "${status.toUpperCase()}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4F46E5', // Indigo 600
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    });

    if (result.isConfirmed) {
        try {
            await store.updateStatus(id, status);
            Swal.fire({
                title: 'Updated!',
                text: `Order status has been changed to ${status}.`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            // store.updateStatus refreshes list, but we can verify
        } catch (error) {
            Swal.fire('Error!', error.response?.data?.message || 'Failed to update status.', 'error');
        }
    }
};

const confirmDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            await store.deleteOrder(id);
            Swal.fire(
                'Deleted!',
                'Order has been deleted.',
                'success'
            );
            fetchData(store.pagination.current_page || 1);
        } catch (error) {
            Swal.fire('Error!', 'Failed to delete order.', 'error');
        }
    }
};

const openDropdownId = ref(null);

const toggleDropdown = (id) => {
    if (openDropdownId.value === id) {
        openDropdownId.value = null;
    } else {
        openDropdownId.value = id;
    }
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    openDropdownId.value = null;
};

// Global click listener for dropdown closure (optional, but good UX)
// For simplicity in this edit, we'll rely on item-scoped toggles or a click-outside handling later if needed.
// A simple way is to use a transparent overlay or improved VueUse logic, but keeping it vanilla for now.

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date); // Outputs DD/MM/YYYY
};

// ... existing code ...

const openPaymentModal = (order) => {
    selectedOrder.value = order;
    showPayment.value = true;
    openDropdownId.value = null;
};

const openDeliveryModal = (order) => {
    selectedOrder.value = order;
    showDelivery.value = true;
    openDropdownId.value = null;
};

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
    showPayment.value = false;
    showDelivery.value = false;
    openDropdownId.value = null;
};

// Helper for status update with Drowpdown close
const updateStatusWithClose = async (id, status) => {
    openDropdownId.value = null;
    await updateStatus(id, status);
};

const printBulk = (type) => {
    if (selectedIds.value.length === 0) return;
    const ids = selectedIds.value.join(',');
    // Open in new tab (Mock for now)
    const url = `/print/bulk/${type}?ids=${ids}`;
    window.open(url, '_blank');
};
</script>
