<template>
    <div class="space-y-6">
        <ServerDataTable
            :headers="headers"
            :items="users"
            :loading="loading"
            :actions="true"
            title="Users"
        >
            <template #filters>
                 <router-link to="/users/create" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 font-medium transition-colors whitespace-nowrap">
                    + Invite User
                </router-link>
            </template>

            <template #actions="{ item }">
                <button 
                    v-if="$can('settings.update')"
                    @click="$router.push({ name: 'EditUser', params: { id: item.id } })" 
                    class="text-primary-600 hover:text-primary-900 mr-3 border border-primary-600 rounded px-2 text-xs inline-block py-0.5"
                >
                    Edit
                </button>
                <button 
                    v-if="$can('settings.update') && item.id !== auth.user.id"
                    @click="deleteUser(item)" 
                    class="text-red-600 hover:text-red-900 border border-red-600 rounded px-2 text-xs"
                >
                    Delete
                </button>
            </template>
        </ServerDataTable>
    </div>
</template>

<script setup>
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import { useAuthStore } from '../../stores/auth';
import { inject, ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const $router = useRouter();

const users = ref([]);
const loading = ref(true);

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'roles', label: 'Role', formatter: (val) => Array.isArray(val) ? val.join(', ') : val },
    { key: 'created_at', label: 'Joined', formatter: (val) => new Date(val).toLocaleDateString() }
];

onMounted(async () => {
    fetchUsers();
});

const fetchUsers = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/users');
        users.value = response.data.data.map(user => ({
            ...user,
            created_at: new Date(user.created_at).toLocaleDateString()
        }));
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const deleteUser = async (user) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete user!'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/v1/users/${user.id}`);
            Swal.fire('Deleted!', 'User has been removed.', 'success');
            fetchUsers();
        } catch (error) {
            Swal.fire('Error!', 'Failed to delete user.', 'error');
        }
    }
};
</script>
