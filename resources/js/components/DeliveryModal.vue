<template>
    <Modal v-model="visible" title="Update Delivery Details">
        <form @submit.prevent="saveDelivery" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Courier Name</label>
                <select v-model="form.courier_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="">Select Courier</option>
                    <option value="Pathao">Pathao</option>
                    <option value="Steadfast">Steadfast</option>
                    <option value="RedX">RedX</option>
                    <option value="Paperfly">Paperfly</option>
                    <option value="eCourier">eCourier</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tracking ID / Consignment ID</label>
                <input v-model="form.tracking_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2" placeholder="e.g. PID12345678">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">COD Amount</label>
                 <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">৳</span>
                    </div>
                <input v-model.number="form.cod_amount" type="number" min="0" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md border p-2">
                 </div>
            </div>

             <div>
                <label class="block text-sm font-medium text-gray-700">Delivery Status</label>
                <select v-model="form.delivery_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="pending">Pending</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="returned">Returned</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50">
                    {{ loading ? 'Saving...' : 'Save Details' }}
                </button>
                <button type="button" @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import Modal from './ui/Modal.vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    order: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);

const visible = ref(props.modelValue);
const loading = ref(false);
const error = ref(null);

const form = reactive({
    courier_name: '',
    tracking_number: '',
    cod_amount: 0,
    delivery_status: 'pending'
});

// Load existing delivery info if available
watch(() => props.modelValue, async (val) => {
    visible.value = val;
    if (val && props.order) {
        // Reset form
        form.courier_name = '';
        form.tracking_number = '';
        form.cod_amount = props.order.due_amount || 0; // Default COD to due amount
        form.delivery_status = 'pending';
        error.value = null;

        // Fetch existing info
        try {
            loading.value = true;
            const response = await axios.get(`/api/v1/orders/${props.order.id}/delivery`);
            if (response.data.data) {
                const data = response.data.data;
                form.courier_name = data.courier_name || '';
                form.tracking_number = data.tracking_number || '';
                form.cod_amount = data.cod_amount || 0;
                form.delivery_status = data.delivery_status || 'pending';
            }
        } catch (err) {
            // Ignore 404 if no delivery info yet
        } finally {
            loading.value = false;
        }
    }
});

watch(visible, (val) => {
    emit('update:modelValue', val);
});

const close = () => {
    visible.value = false;
};

const saveDelivery = async () => {
    loading.value = true;
    error.value = null;
    try {
        await axios.post(`/api/v1/orders/${props.order.id}/delivery`, form);
        emit('saved');
        close();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save delivery details.';
    } finally {
        loading.value = false;
    }
};
</script>
