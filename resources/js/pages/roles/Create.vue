<template>
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
             <h1 class="text-2xl font-bold text-gray-800">Create Role</h1>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submitForm">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                    <input v-model="form.name" type="text" required class="block w-full border border-gray-300 rounded-md p-2" placeholder="e.g. Sales Manager">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Permissions</label>
                    <PermissionSelector v-model="form.permissions" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <router-link to="/roles" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</router-link>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import PermissionSelector from '../../components/ui/PermissionSelector.vue';

const router = useRouter();
const form = reactive({
    name: '',
    permissions: []
});

const submitForm = async () => {
    try {
        await axios.post('/api/v1/roles', form);
        await Swal.fire('Success', 'Role created successfully!', 'success');
        router.push({ name: 'Roles' });
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to create role.', 'error');
    }
};
</script>
