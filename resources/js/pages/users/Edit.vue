<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit User</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submitForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input v-model="form.name" type="text" required class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input v-model="form.email" type="email" required class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select v-model="form.role_id" required class="block w-full border border-gray-300 rounded-md p-2">
                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-gray-400 text-xs">(Leave blank to keep current)</span></label>
                    <input v-model="form.password" type="password" minlength="8" class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="flex justify-end gap-3">
                    <router-link to="/users" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</router-link>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update User</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();
const route = useRoute();
const roles = ref([]);
const form = reactive({
    name: '',
    email: '',
    role_id: '',
    password: ''
});

onMounted(async () => {
    try {
        const [rolesRes, userRes] = await Promise.all([
            axios.get('/api/v1/roles'),
            axios.get(`/api/v1/users/${route.params.id}`)
        ]);
        
        roles.value = rolesRes.data.data;
        const user = userRes.data.data;
        
        form.name = user.name;
        form.email = user.email;
        form.role_id = user.role_id;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
});

const submitForm = async () => {
    try {
        await axios.put(`/api/v1/users/${route.params.id}`, form);
        await Swal.fire('Success', 'User updated successfully!', 'success');
        router.push({ name: 'Users' });
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to update user.', 'error');
    }
};
</script>
