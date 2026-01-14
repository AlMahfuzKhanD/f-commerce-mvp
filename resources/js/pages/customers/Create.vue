<template>
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Add New Customer</h2>
             <router-link to="/customers" class="text-indigo-600 hover:text-indigo-900">
                &larr; Back to Customers
            </router-link>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="saveCustomer" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input v-model="form.phone" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>

                 <div>
                    <label class="block text-sm font-medium text-gray-700">Email (Optional)</label>
                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
                </div>

                <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded">
                    {{ error }}
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <router-link to="/customers" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </router-link>
                    <button type="submit" :disabled="loading" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        {{ loading ? 'Saving...' : 'Save Customer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCustomerStore } from '../../stores/customer';

const router = useRouter();
const store = useCustomerStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    name: '',
    phone: '',
    email: '',
    address: ''
});

const saveCustomer = async () => {
    loading.value = true;
    error.value = null;
    try {
        await store.createCustomer(form);
        router.push({ name: 'Customers' });
    } catch (err) {
         if (err.response && err.response.data && err.response.data.errors) {
             error.value = Object.values(err.response.data.errors).flat().join(', ');
        } else {
             error.value = err.response?.data?.message || 'Failed to create customer.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
