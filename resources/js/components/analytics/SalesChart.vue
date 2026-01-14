<template>
    <div class="relative h-64 w-full">
        <Bar v-if="chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
        <div v-else class="flex items-center justify-center h-full text-gray-400">
            No sales data available.
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    data: {
        type: Array,
        default: () => []
    }
});

const chartData = computed(() => {
    // Expecting data in format: [{ date: '2023-01-01', total: 500 }, ...]
    const labels = props.data.map(item => item.date);
    const totals = props.data.map(item => item.total);

    return {
        labels: labels,
        datasets: [
            {
                label: 'Sales (BDT)',
                backgroundColor: '#4f46e5', // indigo-600
                data: totals
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
};
</script>
