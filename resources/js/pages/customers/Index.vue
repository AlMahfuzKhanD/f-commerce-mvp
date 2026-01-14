<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Customers</h2>
            <router-link to="/customers/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + Add Customer
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow">
            <DataTable :headers="headers" :items="store.customers" :actions="false">
                <template #actions="{ item }">
                    <!-- Edit not implemented for MVP simplicity in list view -->
                </template>
            </DataTable>
        </div>

        <Modal v-model="showModal" title="Add New Customer">
            <form @submit.prevent="saveCustomer" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input v-model="form.phone" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea v-model="form.address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
                </div>

                <div v-if="errors" class="text-red-600 text-sm">
                    {{ errors }}
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Save
                    </button>
                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useCustomerStore } from '../../stores/customer';
import DataTable from '../../components/ui/DataTable.vue';
import Modal from '../../components/ui/Modal.vue';

const store = useCustomerStore();
const showModal = ref(false);
const errors = ref(null);

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'phone', label: 'Phone' },
    { key: 'address', label: 'Address' },
];

const form = reactive({
    name: '',
    phone: '',
    address: ''
});

onMounted(() => {
    store.fetchCustomers();
});

const openModal = () => {
    errors.value = null;
    Object.assign(form, { name: '', phone: '', address: '' });
    showModal.value = true;
};

const saveCustomer = async () => {
    try {
        await store.createCustomer(form);
        showModal.value = false;
    } catch (error) {
        if (error.response && error.response.status === 422) {
             errors.value = Object.values(error.response.data.errors).flat().join(', ');
        } else {
             errors.value = 'An error occurred.';
        }
    }
};
</script>
