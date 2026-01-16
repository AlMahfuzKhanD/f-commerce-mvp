<template>
    <div v-if="loading" class="text-gray-500">Loading permissions...</div>
    <div v-else class="space-y-6">
        <div v-for="(groupPerms, groupName) in groupedPermissions" :key="groupName" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3 border-b border-gray-200 pb-2">
                <h3 class="text-md font-semibold text-gray-700 capitalize">
                    {{ groupName || 'General' }}
                </h3>
                <button 
                    type="button" 
                    @click="toggleGroup(groupPerms)"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    {{ areAllSelected(groupPerms) ? 'Deselect All' : 'Select All' }}
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <label 
                    v-for="perm in groupPerms" 
                    :key="perm.id" 
                    class="flex items-start space-x-2 cursor-pointer hover:bg-gray-100 p-2 rounded"
                >
                    <input 
                        type="checkbox" 
                        :value="perm.id" 
                        :checked="modelValue.includes(perm.id)"
                        @change="togglePermission(perm.id)"
                        class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                    <div class="text-sm">
                        <span class="block font-medium text-gray-700">{{ formatSlug(perm.slug) }}</span>
                        <span class="block text-gray-500 text-xs">{{ perm.description }}</span>
                    </div>
                </label>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const loading = ref(true);
const permissions = ref([]);
const groupedPermissions = ref({});

const formatSlug = (slug) => {
    // order.create -> Order Create
    const parts = slug.split('.');
    if (parts.length > 1) {
        return parts[1].charAt(0).toUpperCase() + parts[1].slice(1); 
    }
    return slug;
};

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/permissions');
        permissions.value = response.data.data;
        groupedPermissions.value = response.data.grouped;
    } catch (error) {
        console.error('Error fetching permissions', error);
    } finally {
        loading.value = false;
    }
});

const togglePermission = (id) => {
    const newValue = [...props.modelValue];
    if (newValue.includes(id)) {
        const index = newValue.indexOf(id);
        newValue.splice(index, 1);
    } else {
        newValue.push(id);
    }
    emit('update:modelValue', newValue);
};

const areAllSelected = (groupPerms) => {
    return groupPerms.every(p => props.modelValue.includes(p.id));
};

const toggleGroup = (groupPerms) => {
    const allSelected = areAllSelected(groupPerms);
    let newValue = [...props.modelValue];
    
    if (allSelected) {
        // Deselect all
        groupPerms.forEach(p => {
            const index = newValue.indexOf(p.id);
            if (index !== -1) newValue.splice(index, 1);
        });
    } else {
        // Select all
        groupPerms.forEach(p => {
            if (!newValue.includes(p.id)) newValue.push(p.id);
        });
    }
    emit('update:modelValue', newValue);
};
</script>
