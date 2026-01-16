<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Expenses</h2>
            <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + New Expense
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <ServerDataTable 
                :headers="headers" 
                :items="store.expenses" 
                :meta="store.pagination"
                :loading="store.loading"
                :actions="true"
                v-model:search="search"
                @update:search="handleSearch"
                @update:page="handlePageChange"
            >
                <template #amount="{ item }">
                    <span class="font-medium bg-red-50 text-red-700 px-2 py-1 rounded">
                        {{ item.amount }}
                    </span>
                </template>
                <template #actions="{ item }">
                    <button @click="openModal(item)" class="text-indigo-600 hover:text-indigo-900 mr-2 border border-indigo-600 rounded px-2 text-xs">Edit</button>
                    <button @click="deleteExpense(item.id)" class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs">Delete</button>
                </template>
                
                 <template #filters>
                     <select v-model="categoryFilter" @change="handleSearch" class="border rounded-lg text-gray-700 py-2 px-3 focus:outline-none focus:border-indigo-500">
                        <option value="">All Categories</option>
                        <option value="Rent">Rent</option>
                        <option value="Salaries">Salaries</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Supplies">Supplies</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Software">Software</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Other">Other</option>
                    </select>
                </template>
            </ServerDataTable>
        </div>

        <ExpenseModal v-model="showModal" :expense="selectedExpense" @saved="refresh" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useExpenseStore } from '../../stores/expense';
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import ExpenseModal from '../../components/ExpenseModal.vue';
import Swal from 'sweetalert2';

const store = useExpenseStore();
const showModal = ref(false);
const selectedExpense = ref(null);
const search = ref(''); // While controller currently doesn't implement search query, keeping it for ref_no/desc search later
const categoryFilter = ref('');

const headers = [
    { key: 'category', label: 'Category' },
    { key: 'expense_date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'reference_no', label: 'Ref No' },
    { key: 'amount', label: 'Amount' },
];

const fetchData = (page = 1) => {
    store.fetchExpenses({
        page,
        category: categoryFilter.value
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

const openModal = (expense = null) => {
    selectedExpense.value = expense;
    showModal.value = true;
};

const deleteExpense = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await store.deleteExpense(id);
                Swal.fire('Deleted!', 'Expense has been deleted.', 'success');
            } catch (error) {
                Swal.fire('Error!', 'Failed to delete expense.', 'error');
            }
        }
    });
};

const refresh = () => {
    fetchData(store.pagination.current_page || 1);
};
</script>
