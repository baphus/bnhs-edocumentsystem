<script setup lang="ts">
interface Props {
    title: string;
    value: string | number;
    icon?: string;
    color?: 'blue' | 'yellow' | 'green' | 'purple' | 'red' | 'gray';
    trend?: {
        value: number;
        isPositive: boolean;
    };
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
});

const colorClasses = {
    blue: {
        bg: 'bg-bnhs-blue-100',
        text: 'text-bnhs-blue',
        value: 'text-bnhs-blue-600',
    },
    yellow: {
        bg: 'bg-yellow-100',
        text: 'text-yellow-600',
        value: 'text-yellow-600',
    },
    green: {
        bg: 'bg-green-100',
        text: 'text-green-600',
        value: 'text-green-600',
    },
    purple: {
        bg: 'bg-purple-100',
        text: 'text-purple-600',
        value: 'text-purple-600',
    },
    red: {
        bg: 'bg-red-100',
        text: 'text-red-600',
        value: 'text-red-600',
    },
    gray: {
        bg: 'bg-gray-100',
        text: 'text-gray-600',
        value: 'text-gray-600',
    },
};

const colors = colorClasses[props.color];
</script>

<template>
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ title }}</p>
                <p class="mt-2 text-3xl font-bold" :class="colors.value">{{ value }}</p>
                <div v-if="trend" class="mt-2 flex items-center text-sm">
                    <span :class="trend.isPositive ? 'text-green-600' : 'text-red-600'">
                        {{ trend.isPositive ? '↑' : '↓' }} {{ Math.abs(trend.value) }}%
                    </span>
                    <span class="ml-1 text-gray-500">vs last month</span>
                </div>
            </div>
            <div v-if="icon" class="rounded-full p-3" :class="colors.bg">
                <component :is="icon" class="h-6 w-6" :class="colors.text" />
            </div>
        </div>
    </div>
</template>

