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
                <template #supplier="{ item }">
                    {{ item.supplier?.name }}
                </template>
                <template #paid_amount="{ item }">
                    <span :class="{'text-red-500 font-bold': item.paid_amount < item.total_amount, 'text-green-600': item.paid_amount >= item.total_amount}">
                        {{ item.paid_amount }} / {{ item.total_amount }}
                    </span>
                </template>
                <template #actions="{ item }">
                     <button @click="handlePayment(item)" class="text-green-600 hover:text-green-900 mr-2 border border-green-600 rounded px-2 text-xs">
                        {{ Number(item.paid_amount) >= Number(item.total_amount) ? 'Adjust' : 'Pay' }}
                     </button>
                     <router-link :to="`/purchases/${item.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-2 border border-indigo-600 rounded px-2 text-xs">Edit</router-link>
                     <button @click="handleDelete(item.id)" class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs">Delete</button>
                </template>
            </ServerDataTable>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { usePurchaseStore } from '../../stores/purchase';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import Swal from 'sweetalert2';

const store = usePurchaseStore();
const search = ref('');

const headers = [
    { key: 'reference_no', label: 'Reference' },
    { key: 'supplier', label: 'Supplier' }, // Changed to 'supplier' object key
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

const handleDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! Stock will be deducted if 'Received'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await store.deletePurchase(id);
                Swal.fire(
                    'Deleted!',
                    'Purchase has been deleted.',
                    'success'
                );
                // fetch triggered by store? check store. store.deletePurchase calls fetchPurchases.
            } catch (error) {
                Swal.fire(
                    'Error!',
                     error.response?.data?.message || 'Failed to delete.',
                    'error'
                );
            }
        }
    });
};

const handlePayment = async (item) => {
    const due = Number(item.total_amount) - Number(item.paid_amount);
    const { value: amount } = await Swal.fire({
        title: due <= 0 ? 'Adjust Payment' : 'Add Payment',
        html: `Current Due: <b>${due.toFixed(2)}</b><br><span class='text-sm text-gray-500'>Enter negative amount (e.g. -100) to reverse payment.</span>`,
        input: 'number',
        inputLabel: 'Amount',
        inputValue: due > 0 ? due : '',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return 'You need to write a valid amount!'
            }
        }
    });

    if (amount) {
        try {
            await store.addPayment(item.id, amount);
            Swal.fire('Success', 'Payment updated!', 'success');
        } catch (e) {
            Swal.fire('Error', e.response?.data?.message || 'Failed to update payment', 'error');
        }
    }
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

// openEditModal removed

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
};



onMounted(() => {
    fetchData();
});
</script>
