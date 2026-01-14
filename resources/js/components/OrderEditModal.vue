<template>
    <Modal v-model="internalShow" title="Edit Order Details">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Order Date</label>
                <input v-model="form.order_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
             <p class="text-xs text-gray-500">Note: To update items, cancel this order and create a new one. Stock adjustments are handled on creation/cancellation.</p>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm">
                    {{ loading ? 'Saving...' : 'Save Changes' }}
                </button>
                <button type="button" @click="internalShow = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, watch, reactive, computed } from 'vue';
import Modal from './ui/Modal.vue';
import { useOrderStore } from '../stores/order';

const props = defineProps({
    modelValue: Boolean,
    order: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);
const store = useOrderStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    id: null,
    order_date: ''
});

const internalShow = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

watch(() => props.order, (newVal) => {
    if (newVal) {
        form.id = newVal.id;
        // Format date for input type="date" if needed, assuming API sends full timestamp
        form.order_date = newVal.order_date ? newVal.order_date.substring(0, 10) : '';
    }
}, { immediate: true });

const save = async () => {
    loading.value = true;
    error.value = null;
    try {
        await store.updateOrder(form.id, form);
        emit('saved');
        internalShow.value = false;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update order';
    } finally {
        loading.value = false;
    }
};
</script>
