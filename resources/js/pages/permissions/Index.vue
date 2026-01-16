<template>
    <div class="space-y-6">
        <ServerDataTable
            :headers="headers"
            :items="permissions"
            :loading="loading"
            :actions="true"
        >
            <template #filters>
                <div class="flex items-center justify-between w-full">
                     <h2 class="text-xl font-semibold text-gray-800">Permissions</h2>
                     <router-link to="/permissions/create" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        + Create Permission
                    </router-link>
                </div>
            </template>

            <template #actions="{ item }">
                <button 
                    v-if="$can('settings.update')"
                    @click="deletePermission(item)" 
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

const permissions = ref([]);
const loading = ref(true);

const headers = [
    { key: 'slug', label: 'Slug' },
    { key: 'group', label: 'Group' },
    { key: 'description', label: 'Description' }
];

onMounted(async () => {
    fetchData();
});

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/permissions');
        // PermissionController returns { data: [...], grouped: ... }
        permissions.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const deletePermission = async (perm) => {
    const result = await Swal.fire({
        title: 'Delete Permission?',
        text: "This might break functionality if the code relies on this permission.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/v1/permissions/${perm.id}`);
            Swal.fire('Deleted', 'Permission removed.', 'success');
            fetchData();
        } catch (error) {
            Swal.fire('Error', error.response?.data?.message || 'Failed.', 'error');
        }
    }
};
</script>
