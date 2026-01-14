<template>
    <Modal v-model="visible" :title="isEdit ? 'Edit Supplier' : 'Add New Supplier'">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50">
                    {{ loading ? 'Saving...' : 'Save Supplier' }}
                </button>
                <button type="button" @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import Modal from './ui/Modal.vue';
import { useSupplierStore } from '../stores/supplier';

const props = defineProps({
    modelValue: Boolean,
    supplier: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);

const store = useSupplierStore();
const visible = ref(props.modelValue);
const loading = ref(false);
const error = ref(null);

const isEdit = computed(() => !!props.supplier);

const form = reactive({
    name: '',
    phone: '',
    email: '',
    address: ''
});

watch(() => props.modelValue, (val) => {
    visible.value = val;
    if (val) {
        // Reset or Fill
        error.value = null;
        if (props.supplier) {
            form.name = props.supplier.name;
            form.phone = props.supplier.phone;
            form.email = props.supplier.email;
            form.address = props.supplier.address;
        } else {
            form.name = '';
            form.phone = '';
            form.email = '';
            form.address = '';
        }
    }
});

watch(visible, (val) => {
    emit('update:modelValue', val);
});

const close = () => {
    visible.value = false;
};

const save = async () => {
    loading.value = true;
    error.value = null;
    try {
        if (isEdit.value) {
            await store.updateSupplier(props.supplier.id, form);
        } else {
            await store.createSupplier(form);
        }
        emit('saved');
        close();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save supplier.';
    } finally {
        loading.value = false;
    }
};
</script>
