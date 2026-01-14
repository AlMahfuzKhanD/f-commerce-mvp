<template>
    <Modal v-model="visible" title="Record Payment">
        <form @submit.prevent="savePayment" class="space-y-4">
            <div class="bg-gray-50 p-3 rounded mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-bold">{{ formatCurrency(order?.total_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Already Paid:</span>
                    <span class="font-bold text-green-600">{{ formatCurrency(order?.paid_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm border-t mt-2 pt-2">
                    <span class="text-gray-600">Due Amount:</span>
                    <span class="font-bold text-red-600">{{ formatCurrency(order?.due_amount) }}</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                         <span class="text-gray-500 sm:text-sm">৳</span>
                    </div>
                    <input v-model.number="form.amount" type="number" required min="1" step="0.01" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md border p-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select v-model="form.payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="cash">Cash</option>
                    <option value="bkash">Bkash</option>
                    <option value="nagad">Nagad</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Transaction ID (Optional)</label>
                <input v-model="form.transaction_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>
            
            <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50">
                    {{ loading ? 'Processing...' : 'Record Payment' }}
                </button>
                <button type="button" @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, reactive, watch, toRefs } from 'vue';
import Modal from './ui/Modal.vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    order: Object
});

const emit = defineEmits(['update:modelValue', 'payment-success']);

const visible = ref(props.modelValue);
const loading = ref(false);
const error = ref(null);

const form = reactive({
    amount: 0,
    payment_method: 'cash',
    transaction_id: ''
});

watch(() => props.modelValue, (val) => {
    visible.value = val;
    if (val && props.order) {
        form.amount = props.order.due_amount; // Default to full due
        form.payment_method = 'cash';
        form.transaction_id = '';
        error.value = null;
    }
});

watch(visible, (val) => {
    emit('update:modelValue', val);
});

const close = () => {
    visible.value = false;
};

const formatCurrency = (val) => '৳' + parseFloat(val || 0).toFixed(2);

const savePayment = async () => {
    loading.value = true;
    error.value = null;
    try {
        await axios.post(`/api/v1/orders/${props.order.id}/payments`, form);
        emit('payment-success');
        close();
    } catch (err) {
        error.value = err.response?.data?.message || 'Payment failed';
    } finally {
        loading.value = false;
    }
};
</script>
