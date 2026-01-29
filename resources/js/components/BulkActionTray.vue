<template>
    <div 
        v-if="count > 0"
        class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white rounded-full shadow-2xl border border-gray-200 px-6 py-3 flex items-center space-x-6 z-50 animate-fade-in-up"
    >
        <!-- Info -->
        <div class="flex items-center space-x-2 border-r border-gray-200 pr-4">
            <span class="bg-primary-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ count }}</span>
            <span class="text-sm font-semibold text-gray-700">Selected</span>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-2">
             <button 
                @click="$emit('print-invoice')"
                class="flex items-center space-x-1 px-3 py-1.5 rounded-full hover:bg-gray-100 text-gray-700 text-sm font-medium transition-colors"
            >
                <span>📄</span>
                <span>Invoice</span>
            </button>
            
            <button 
                 @click="$emit('print-stickers')"
                 class="flex items-center space-x-1 px-3 py-1.5 rounded-full hover:bg-gray-100 text-gray-700 text-sm font-medium transition-colors"
            >
                <span>🏷️</span>
                <span>Stickers</span>
            </button>

            <div class="w-px h-4 bg-gray-200 mx-2"></div>

            <button 
                @click="$emit('cancel')"
                class="text-gray-400 hover:text-gray-600 text-sm"
            >
                Clear
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    count: { type: Number, default: 0 }
});
defineEmits(['print-invoice', 'print-stickers', 'cancel']);
</script>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate(-50%, 20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out forwards;
}
</style>
