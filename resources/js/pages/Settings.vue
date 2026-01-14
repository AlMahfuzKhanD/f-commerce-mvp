<template>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Business Settings</h1>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Tenant Details</h2>
            
            <div v-if="loadingData" class="text-center py-4 text-gray-500">Loading settings...</div>
            
            <form v-else @submit.prevent="updateSettings" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Business Name</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Slug (URL)</label>
                        <input v-model="form.slug" type="text" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm border p-2 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Slug cannot be changed.</p>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                        <input v-model="form.currency" type="text" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm border p-2 cursor-not-allowed">
                    </div>
                </div>

                <div v-if="message" :class="error ? 'text-red-600' : 'text-green-600'" class="text-sm">
                    {{ message }}
                </div>

                <div class="pt-4 border-t mt-4">
                    <button type="submit" :disabled="saving" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Update Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';

const loadingData = ref(true);
const saving = ref(false);
const message = ref('');
const error = ref(false);

const form = reactive({
    name: '',
    slug: '',
    currency: ''
});

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/settings');
        const settings = response.data.data;
        form.name = settings.name;
        form.slug = settings.slug;
        form.currency = settings.currency;
    } catch (err) {
        error.value = true;
        message.value = 'Failed to load settings.';
    } finally {
        loadingData.value = false;
    }
});

const updateSettings = async () => {
    saving.value = true;
    message.value = '';
    error.value = false;

    try {
        await axios.put('/api/v1/settings', { name: form.name });
        message.value = 'Settings updated successfully.';
    } catch (err) {
        error.value = true;
        message.value = err.response?.data?.message || 'Failed to update settings.';
    } finally {
        saving.value = false;
    }
};
</script>
