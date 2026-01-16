<template>
    <div class="space-y-6">
        <ServerDataTable
            :headers="headers"
            :items="roles"
            :loading="loading"
            :actions="true"
        >
            <template #filters>
                <div class="flex items-center justify-between w-full">
                     <h2 class="text-xl font-semibold text-gray-800">Roles</h2>
                     <router-link to="/roles/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        + Create Role
                    </router-link>
                </div>
            </template>

            <template #actions="{ item }">
                <button 
                    v-if="$can('settings.update')"
                    @click="$router.push({ name: 'EditRole', params: { id: item.id } })" 
                    class="text-indigo-600 hover:text-indigo-900 mr-3 border border-indigo-600 rounded px-2 text-xs inline-block py-0.5"
                >
                    Edit
                </button>
                <button 
                    v-if="$can('settings.update') && item.name !== 'Owner'"
                    @click="deleteRole(item)" 
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
import { inject, ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { useRouter } from 'vue-router';

const $router = useRouter();

const roles = ref([]);
const loading = ref(true);

const headers = [
    { key: 'name', label: 'Name' },
    { key: 'users_count', label: 'Users Assigned' },
    { key: 'created_at', label: 'Created At' }
];

onMounted(async () => {
    fetchRoles();
});

const fetchRoles = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/roles');
        roles.value = response.data.data.map(role => ({
            ...role,
            created_at: new Date(role.created_at).toLocaleDateString()
        }));
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const deleteRole = async (role) => {
    const result = await Swal.fire({
        title: 'Delete Role?',
        text: "This will fail if users are assigned to this role.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/v1/roles/${role.id}`);
            Swal.fire('Deleted', 'Role removed.', 'success');
            fetchRoles();
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed.', 'error');
        }
    }
};
</script>
