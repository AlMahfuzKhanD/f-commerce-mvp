<template>
    <div class="space-y-6">
        <ServerDataTable
            title="Users"
            api-endpoint="/api/v1/users"
            :columns="columns"
            create-route="CreateUser"
            create-label="Invite User"
            :actions="actions"
            @delete="deleteUser"
        />
    </div>
</template>

<script setup>
import ServerDataTable from '../../components/ui/ServerDataTable.vue';
import { useAuthStore } from '../../stores/auth';
import { inject } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const auth = useAuthStore();
const $can = inject('$can');

// Columns definition
const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'roles', label: 'Role', formatter: (val) => val.join(', ') },
    { key: 'created_at', label: 'Joined', formatter: (val) => new Date(val).toLocaleDateString() }
];

// Actions
const actions = [
    {
        label: 'Edit',
        handler: (row, router) => router.push({ name: 'EditUser', params: { id: row.id } }),
        class: 'text-indigo-600 hover:text-indigo-900 mr-2',
        show: () => $can('settings.update')
    },
    {
        label: 'Delete',
        handler: (row) => deleteUser(row),
        class: 'text-red-600 hover:text-red-900',
        show: (row) => $can('settings.update') && row.id !== auth.user.id
    }
];

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
            // Refresh logic handled by ServerDataTable via ref if needed, or window reload for MVP simplicity
            // But ServerDataTable doesn't expose refresh easily without ref.
            // Let's rely on simple reload or implementing event bus later.
            window.location.reload(); 
        } catch (error) {
            Swal.fire('Error!', 'Failed to delete user.', 'error');
        }
    }
};
</script>
