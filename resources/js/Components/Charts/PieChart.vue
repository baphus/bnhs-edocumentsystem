<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

try {
    ChartJS.register(ArcElement, Tooltip, Legend);
} catch (e) {
    console.error('Chart.js registration error:', e);
}

interface Props {
    data: {
        category: string;
        count: number;
    }[];
    title?: string;
}

const props = defineProps<Props>();

const colors = [
    'rgb(0, 56, 168)',
    'rgb(252, 209, 22)',
    'rgb(34, 197, 94)',
    'rgb(168, 85, 247)',
    'rgb(239, 68, 68)',
    'rgb(59, 130, 246)',
    'rgb(251, 146, 60)',
    'rgb(139, 92, 246)',
];

const chartData = ref({
    labels: [] as string[],
    datasets: [
        {
            data: [] as number[],
            backgroundColor: [] as string[],
            borderColor: '#ffffff',
            borderWidth: 2,
        },
    ],
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right' as const,
        },
        title: {
            display: !!props.title,
            text: props.title,
        },
    },
};

const updateChart = () => {
    if (!props.data || props.data.length === 0) {
        chartData.value = {
            labels: [],
            datasets: [
                {
                    data: [],
                    backgroundColor: [],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                },
            ],
        };
        return;
    }

    chartData.value = {
        labels: props.data.map((item) => item.category),
        datasets: [
            {
                data: props.data.map((item) => item.count),
                backgroundColor: props.data.map((_, index) => colors[index % colors.length]),
                borderColor: '#ffffff',
                borderWidth: 2,
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
        <Pie :data="chartData" :options="chartOptions" />
    </div>
</template>

