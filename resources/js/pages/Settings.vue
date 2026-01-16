<template>
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">System Settings</h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form @submit.prevent="saveSettings" class="p-6 space-y-6">
                
                <!-- Logo Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Logo</label>
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            <img v-if="previewLogo" :src="previewLogo" class="h-24 w-24 object-contain rounded-md border border-gray-200 bg-gray-50" />
                            <div v-else class="h-24 w-24 rounded-md border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50">
                                <span class="text-gray-400 text-xs">No Logo</span>
                            </div>
                        </div>
                        <label class="block">
                            <span class="sr-only">Choose logo</span>
                            <input type="file" @change="handleFileChange" accept="image/*" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                            "/>
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Allowed formats: JPG, PNG, SVG. Max size: 2MB.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Company Name</label>
                        <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>

                    <!-- Currency -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency Code</label>
                        <select v-model="form.currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                            <option value="BDT">BDT (Bangladeshi Taka)</option>
                            <option value="USD">USD (US Dollar)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="GBP">GBP (British Pound)</option>
                        </select>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Timezone</label>
                        <select v-model="form.timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                            <option value="Asia/Dhaka">Asia/Dhaka</option>
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York</option>
                             <option value="Europe/London">Europe/London</option>
                        </select>
                    </div>
                </div>

                <!-- Address -->
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t flex justify-end">
                     <button type="submit" :disabled="loading" class="bg-indigo-600 py-2 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ loading ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const loading = ref(false);
const previewLogo = ref(null);
const logoFile = ref(null);

const form = reactive({
    name: '',
    currency: 'BDT',
    timezone: 'Asia/Dhaka',
    address: '',
    phone: ''
});

const fetchSettings = async () => {
    try {
        const response = await axios.get('/api/v1/settings');
        const data = response.data;
        form.name = data.name;
        form.currency = data.currency;
        form.timezone = data.timezone;
        form.address = data.address;
        form.phone = data.phone;
        previewLogo.value = data.logo_url;
    } catch (e) {
        console.error("Failed to fetch settings", e);
    }
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        logoFile.value = file;
        previewLogo.value = URL.createObjectURL(file);
    }
};

const saveSettings = async () => {
    loading.value = true;
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('currency', form.currency);
    formData.append('timezone', form.timezone);
    formData.append('address', form.address || '');
    formData.append('phone', form.phone || '');
    
    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }
    
    try {
        const response = await axios.post('/api/v1/settings', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        Swal.fire({
            icon: 'success',
            title: 'Saved',
            text: 'System settings updated successfully.'
        });
        
        // Update local state with exact data from server (e.g. logo path might change)
        const d = response.data.data;
        previewLogo.value = d.logo_url;
        logoFile.value = null; // Reset file input logic pending
        
    } catch (e) {
         Swal.fire({
            icon: 'error',
            title: 'Error',
            text: e.response?.data?.message || 'Failed to update settings.'
        });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>
