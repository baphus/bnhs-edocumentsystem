<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

try {
    ChartJS.register(
        CategoryScale,
        LinearScale,
        PointElement,
        LineElement,
        Title,
        Tooltip,
        Legend
    );
} catch (e) {
    console.error('Chart.js registration error:', e);
}

interface Props {
    data: {
        date: string;
        total: number;
        completed: number;
    }[];
    title?: string;
}

const props = defineProps<Props>();

const chartData = ref({
    labels: [] as string[],
    datasets: [
        {
            label: 'Total Requests',
            data: [] as number[],
            borderColor: 'rgb(0, 56, 168)',
            backgroundColor: 'rgba(0, 56, 168, 0.1)',
            tension: 0.4,
        },
        {
            label: 'Completed',
            data: [] as number[],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
        },
    ],
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
        },
        title: {
            display: !!props.title,
            text: props.title,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const updateChart = () => {
    if (!props.data || props.data.length === 0) {
        chartData.value = {
            labels: [],
            datasets: [
                {
                    ...chartData.value.datasets[0],
                    data: [],
                },
                {
                    ...chartData.value.datasets[1],
                    data: [],
                },
            ],
        };
        return;
    }

    chartData.value = {
        labels: props.data.map((item) => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [
            {
                ...chartData.value.datasets[0],
                data: props.data.map((item) => item.total),
            },
            {
                ...chartData.value.datasets[1],
                data: props.data.map((item) => item.completed),
            },
        ],
    };
};

watch(() => props.data, updateChart, { deep: true });

onMounted(() => {
    updateChart();
});
</script>

<template>
    <div class="h-64 w-full">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

