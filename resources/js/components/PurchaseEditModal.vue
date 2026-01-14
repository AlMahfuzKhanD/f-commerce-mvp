<template>
    <Modal v-model="internalShow" title="Edit Purchase">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Reference No</label>
                <input v-model="form.reference_no" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                <input v-model="form.purchase_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Paid Amount</label>
                <input v-model.number="form.paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
             <p class="text-xs text-gray-500">Note: Editing items or supplier for processed purchases is restricted to prevent stock inconsistencies.</p>

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
import { usePurchaseStore } from '../stores/purchase';

const props = defineProps({
    modelValue: Boolean,
    purchase: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);
const store = usePurchaseStore();
const loading = ref(false);
const error = ref(null);

const form = reactive({
    id: null,
    reference_no: '',
    purchase_date: '',
    paid_amount: 0
});

const internalShow = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

watch(() => props.purchase, (newVal) => {
    if (newVal) {
        form.id = newVal.id;
        form.reference_no = newVal.reference_no;
        form.purchase_date = newVal.purchase_date;
        form.paid_amount = newVal.paid_amount;
    }
}, { immediate: true });

const save = async () => {
    loading.value = true;
    error.value = null;
    try {
        await store.updatePurchase(form.id, form);
        emit('saved');
        internalShow.value = false;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update purchase';
    } finally {
        loading.value = false;
    }
};
</script>
