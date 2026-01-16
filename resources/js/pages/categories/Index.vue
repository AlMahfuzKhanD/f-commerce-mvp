<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Categories</h2>
            <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                + Add Category
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                         <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="category in store.categories" :key="category.id">
                        <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ category.slug }}</td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                             {{ category.parent ? category.parent.name : '-' }}
                         </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="openModal(category)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button @click="confirmDelete(category.id)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="store.categories.length === 0">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No categories found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Category Modal -->
        <Modal v-model="showModal" :title="isEditing ? 'Edit Category' : 'Add New Category'">
            <form @submit.prevent="saveCategory" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Parent Category</label>
                    <select v-model="form.parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        <option :value="null">None (Top Level)</option>
                        <option v-for="cat in store.categories" :key="cat.id" :value="cat.id" :disabled="cat.id === form.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <div v-if="errors" class="text-red-600 text-sm">{{ errors }}</div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Save
                    </button>
                    <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useCategoryStore } from '../../stores/category';
import Modal from '../../components/ui/Modal.vue';

const store = useCategoryStore();
const showModal = ref(false);
const isEditing = ref(false);
const errors = ref(null);

const form = reactive({
    id: null,
    name: '',
    parent_id: null
});

onMounted(() => {
    store.fetchCategories();
});

const openModal = (item = null) => {
    errors.value = null;
    if (item) {
        isEditing.value = true;
        Object.assign(form, { id: item.id, name: item.name, parent_id: item.parent_id });
    } else {
        isEditing.value = false;
        Object.assign(form, { id: null, name: '', parent_id: null });
    }
    showModal.value = true;
};

const saveCategory = async () => {
    try {
        if (isEditing.value) {
            await store.updateCategory(form.id, form);
        } else {
            await store.createCategory(form);
        }
        showModal.value = false;
    } catch (error) {
        errors.value = 'Error saving category.'; 
    }
};

const confirmDelete = async (id) => {
    if(confirm('Are you sure?')) {
        await store.deleteCategory(id);
    }
};
</script>
