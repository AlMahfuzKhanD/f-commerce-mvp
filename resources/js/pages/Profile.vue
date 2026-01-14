<template>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">User Profile</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Profile Details -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h2>
                <form @submit.prevent="updateProfile" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input v-model="form.email" type="email" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm border p-2 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Email cannot be changed directly.</p>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                            {{ authStore.user?.role || 'User' }}
                        </span>
                    </div>

                    <div v-if="profileMessage" :class="profileError ? 'text-red-600' : 'text-green-600'" class="text-sm">
                        {{ profileMessage }}
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="loading" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 disabled:opacity-50">
                            {{ loading ? 'Saving...' : 'Update Profile' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Change Password</h2>
                <form @submit.prevent="changePassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input v-model="passwordForm.password" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input v-model="passwordForm.password_confirmation" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>

                    <div v-if="passwordMessage" :class="passwordError ? 'text-red-600' : 'text-green-600'" class="text-sm">
                        {{ passwordMessage }}
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="passwordLoading" class="w-full bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-900 disabled:opacity-50">
                            {{ passwordLoading ? 'Updating...' : 'Change Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const loading = ref(false);
const profileMessage = ref('');
const profileError = ref(false);

const passwordLoading = ref(false);
const passwordMessage = ref('');
const passwordError = ref(false);

const form = reactive({
    name: '',
    email: ''
});

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: ''
});

onMounted(async () => {
    if (!authStore.user) {
        await authStore.fetchUser();
    }
    form.name = authStore.user?.name || '';
    form.email = authStore.user?.email || '';
});

const updateProfile = async () => {
    loading.value = true;
    profileMessage.value = '';
    profileError.value = false;

    try {
        const response = await axios.put('/api/v1/profile', { name: form.name });
        profileMessage.value = 'Profile updated successfully.';
        
        // Refresh user in store
        await authStore.fetchUser();
    } catch (err) {
        profileError.value = true;
        profileMessage.value = err.response?.data?.message || 'Failed to update profile.';
    } finally {
        loading.value = false;
    }
};

const changePassword = async () => {
    passwordLoading.value = true;
    passwordMessage.value = '';
    passwordError.value = false;

    try {
        await axios.put('/api/v1/profile/password', passwordForm);
        passwordMessage.value = 'Password changed successfully.';
        
        // Clear form
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
    } catch (err) {
        passwordError.value = true;
        passwordMessage.value = err.response?.data?.message || 'Failed to change password.';
    } finally {
        passwordLoading.value = false;
    }
};
</script>
