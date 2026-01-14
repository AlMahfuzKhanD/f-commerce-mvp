<template>
    <Modal v-model="visible" :title="isEdit ? 'Edit Expense' : 'Record Expense'">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select v-model="form.category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    <option value="">Select Category</option>
                    <option value="Rent">Rent</option>
                    <option value="Salaries">Salaries</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Supplies">Supplies</option>
                    <option value="Marketing">Marketing / Ads</option>
                    <option value="Software">Software / Hosting</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input v-model="form.expense_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Amount</label>
                <input v-model="form.amount" type="number" min="0" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Reference No (Optional)</label>
                <input v-model="form.reference_no" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

             <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"></textarea>
            </div>

            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="submit" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50">
                    {{ loading ? 'Saving...' : 'Save Expense' }}
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
import { useExpenseStore } from '../stores/expense';

const props = defineProps({
    modelValue: Boolean,
    expense: Object
});

const emit = defineEmits(['update:modelValue', 'saved']);

const store = useExpenseStore();
const visible = ref(props.modelValue);
const loading = ref(false);
const error = ref(null);

const isEdit = computed(() => !!props.expense);

const form = reactive({
    category: '',
    expense_date: new Date().toISOString().substr(0, 10),
    amount: '',
    reference_no: '',
    description: ''
});

watch(() => props.modelValue, (val) => {
    visible.value = val;
    if (val) {
        error.value = null;
        if (props.expense) {
            form.category = props.expense.category;
            form.expense_date = props.expense.expense_date;
            form.amount = props.expense.amount;
            form.reference_no = props.expense.reference_no;
            form.description = props.expense.description;
        } else {
            form.category = '';
            form.expense_date = new Date().toISOString().substr(0, 10);
            form.amount = '';
            form.reference_no = '';
            form.description = '';
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
            await store.updateExpense(props.expense.id, form);
        } else {
            await store.createExpense(form);
        }
        emit('saved');
        close();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save expense.';
    } finally {
        loading.value = false;
    }
};
</script>
