<template>
    <Modal v-model="internalShow" :title="isEdit ? 'Edit Customer' : 'Add New Customer'">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input v-model="form.phone" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea v-model="form.address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                    {{ loading ? 'Saving...' : 'Save Customer' }}
                </button>
                <button type="button" @click="internalShow = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue';
import Modal from './ui/Modal.vue';
import { useCustomerStore } from '../stores/customer';

const props = defineProps({
    modelValue: Boolean,
    customer: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);
const store = useCustomerStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    id: null,
    name: '',
    phone: '',
    address: ''
});

const isEdit = computed(() => !!props.customer);

const internalShow = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

watch(() => props.modelValue, (val) => {
    if (val) {
        error.value = null;
        if (props.customer) {
            Object.assign(form, props.customer);
        } else {
            Object.assign(form, { id: null, name: '', phone: '', address: '' });
        }
    }
});

const save = async () => {
    loading.value = true;
    error.value = null;
    try {
        if (isEdit.value) {
            await store.updateCustomer(form.id, form);
        } else {
            await store.createCustomer(form);
        }
        emit('saved');
        internalShow.value = false;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save customer';
    } finally {
        loading.value = false;
    }
};
</script>
