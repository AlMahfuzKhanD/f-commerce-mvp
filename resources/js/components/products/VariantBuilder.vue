<template>
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Product Variants</h3>
        <p class="text-sm text-gray-500">Add variants if this product has different options (e.g., Size, Color).</p>

        <div v-for="(variant, index) in variants" :key="index" class="flex gap-4 items-start bg-gray-50 p-4 rounded-md border text-sm">
            <div class="flex-1 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700">Size</label>
                    <select v-model="variant.size_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                        <option :value="null">Select Size</option>
                        <option v-for="size in attributeStore.sizes" :key="size.id" :value="size.id">{{ size.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Color</label>
                    <select v-model="variant.color_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                        <option :value="null">Select Color</option>
                        <option v-for="color in attributeStore.colors" :key="color.id" :value="color.id">{{ color.name }}</option>
                    </select>
                </div>
                 <div>
                    <label class="block text-xs font-medium text-gray-700">Stock</label>
                    <input v-model.number="variant.stock_quantity" type="number" min="0" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                </div>
                 <div>
                    <label class="block text-xs font-medium text-gray-700">SKU (Optional)</label>
                    <input v-model="variant.sku" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                </div>
                 <div>
                    <label class="block text-xs font-medium text-gray-700">Extra Price (+/-)</label>
                    <input v-model.number="variant.extra_price" type="number" step="0.01" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                </div>
            </div>
             <button @click.prevent="removeVariant(index)" class="text-red-600 hover:text-red-900 mt-6">
                <span class="sr-only">Remove</span>
                &times;
            </button>
        </div>

        <button @click.prevent="addVariant" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
            + Add Another Variant
        </button>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useAttributeStore } from '../../stores/attribute';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);
const attributeStore = useAttributeStore();
const variants = ref([...props.modelValue]);

onMounted(() => {
    attributeStore.fetchSizes();
    attributeStore.fetchColors();
});

const addVariant = () => {
    variants.value.push({
        size_id: null,
        color_id: null,
        stock_quantity: 0,
        sku: '',
        extra_price: 0
    });
    emitUpdate();
};

const removeVariant = (index) => {
    variants.value.splice(index, 1);
    emitUpdate();
};

const emitUpdate = () => {
    emit('update:modelValue', variants.value);
};

// Sync with parent if v-model changes externaly (rare but good practice)
watch(() => props.modelValue, (newVal) => {
    if (JSON.stringify(newVal) !== JSON.stringify(variants.value)) {
        variants.value = [...newVal];
    }
}, { deep: true });

// Emit changes when any input changes
watch(variants, () => {
    emitUpdate();
}, { deep: true });
</script>
