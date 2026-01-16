<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Invite User</h1>
        
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input v-model="form.password" type="password" required minlength="8" class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="flex justify-end gap-3">
                    <router-link to="/users" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</router-link>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Invite User</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();
const roles = ref([]);
const form = reactive({
    name: '',
    email: '',
    role_id: '',
    password: ''
});

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/roles');
        roles.value = response.data.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
});

const submitForm = async () => {
    try {
        await axios.post('/api/v1/users', form);
        await Swal.fire('Success', 'User invited successfully!', 'success');
        router.push({ name: 'Users' });
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed to invite user.', 'error');
    }
};
</script>
