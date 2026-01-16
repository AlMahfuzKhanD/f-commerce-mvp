<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Permission</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submitForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                    <input v-model="form.slug" type="text" required class="block w-full border border-gray-300 rounded-md p-2" placeholder="e.g. blog.view">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Group</label>
                    <input v-model="form.group" type="text" required class="block w-full border border-gray-300 rounded-md p-2" placeholder="e.g. blog">
                </div>
                
                 <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input v-model="form.description" type="text" class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="flex justify-end gap-3">
                    <router-link to="/permissions" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</router-link>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create Permission</button>
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

const router = useRouter();
const form = reactive({
    slug: '',
    group: '',
    description: ''
});

const submitForm = async () => {
    try {
        await axios.post('/api/v1/permissions', form);
        await Swal.fire('Success', 'Permission created successfully!', 'success');
        router.push({ name: 'Permissions' });
    } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Failed.', 'error');
    }
};
</script>
