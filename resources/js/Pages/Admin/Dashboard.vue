<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DocumentRequest } from '@/types';

const props = defineProps<{
    stats: {
        total: number;
        pending: number;
        processing: number;
        ready: number;
        completed: number;
        rejected: number;
    };
    recentRequests: DocumentRequest[];
}>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Verified': 'bg-blue-100 text-blue-800',
        'Processing': 'bg-purple-100 text-purple-800',
        'Ready': 'bg-green-100 text-green-800',
        'Completed': 'bg-gray-100 text-gray-800',
        'Rejected': 'bg-red-100 text-red-800',
    };
    return colors[status] || colors['Pending'];
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Date and Calendar
const currentDate = ref(new Date());
const selectedDate = ref(new Date());

const greeting = computed(() => {
    const hour = currentDate.value.getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

const formattedDate = computed(() => {
    return currentDate.value.toLocaleDateString('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const schoolYear = computed(() => {
    const now = currentDate.value;
    const year = now.getFullYear();
    const month = now.getMonth();
    // School year starts in June (month 5) in the Philippines
    if (month >= 5) {
        return `${year}-${year + 1}`;
    }
    return `${year - 1}-${year}`;
});

// Calendar helpers
const currentMonth = computed(() => {
    return selectedDate.value.toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
    const year = selectedDate.value.getFullYear();
    const month = selectedDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const days: { date: number; isCurrentMonth: boolean; isToday: boolean }[] = [];
    
    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        days.push({ date: prevMonthLastDay - i, isCurrentMonth: false, isToday: false });
    }
    
    // Current month days
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
        const isToday = today.getDate() === i && 
                        today.getMonth() === month && 
                        today.getFullYear() === year;
        days.push({ date: i, isCurrentMonth: true, isToday });
    }
    
    // Next month days
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({ date: i, isCurrentMonth: false, isToday: false });
    }
    
    return days;
});

const prevMonth = () => {
    const newDate = new Date(selectedDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    selectedDate.value = newDate;
};

const nextMonth = () => {
    const newDate = new Date(selectedDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    selectedDate.value = newDate;
};

// Update time every minute
onMounted(() => {
    const timer = setInterval(() => {
        currentDate.value = new Date();
    }, 60000);
    
    return () => clearInterval(timer);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Greeting and Date Section -->
                <div class="mb-8 grid gap-6 lg:grid-cols-3">
                    <!-- Greeting Card -->
                    <div class="lg:col-span-2 rounded-xl bg-gradient-to-r from-bnhs-blue to-bnhs-blue-600 p-6 text-white shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-100">{{ formattedDate }}</p>
                                <h1 class="mt-2 text-2xl font-bold lg:text-3xl">{{ greeting }}, {{ $page.props.auth.user.name || 'Admin' }}!</h1>
                                <p class="mt-2 text-blue-100">Welcome back to the admin dashboard. Here's what's happening today.</p>
                                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">School Year {{ schoolYear }}</span>
                                </div>
                            </div>
                            <div class="hidden sm:block">
                                <svg class="h-20 w-20 text-white/20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Calendar -->
                    <div class="rounded-xl bg-white p-4 shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-900">{{ currentMonth }}</h3>
                            <div class="flex gap-1">
                                <button @click="prevMonth" class="rounded p-1 text-gray-500 hover:bg-gray-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <button @click="nextMonth" class="rounded p-1 text-gray-500 hover:bg-gray-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs">
                            <div v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" class="py-1 font-medium text-gray-500">
                                {{ day }}
                            </div>
                            <div
                                v-for="(day, index) in calendarDays"
                                :key="index"
                                :class="[
                                    'py-1 rounded',
                                    day.isCurrentMonth ? 'text-gray-900' : 'text-gray-300',
                                    day.isToday ? 'bg-bnhs-blue text-white font-bold' : ''
                                ]"
                            >
                                {{ day.date }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Requests</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                            </div>
                            <div class="rounded-full bg-bnhs-blue-100 p-3">
                                <svg class="h-6 w-6 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.pending }}</p>
                            </div>
                            <div class="rounded-full bg-yellow-100 p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Processing</p>
                                <p class="mt-2 text-3xl font-bold text-purple-600">{{ stats.processing }}</p>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Ready for Pickup</p>
                                <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.ready }}</p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 grid gap-6 sm:grid-cols-2" :class="$page.props.auth.user.role === 'superadmin' ? 'lg:grid-cols-2' : 'lg:grid-cols-1'">
                    <Link
                        :href="route('admin.requests.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-bnhs-blue-100 p-4">
                            <svg class="h-6 w-6 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Manage Requests</h3>
                            <p class="text-sm text-gray-500">View and update request statuses</p>
                        </div>
                    </Link>

                    <Link
                        v-if="$page.props.auth.user.role === 'superadmin'"
                        :href="route('admin.users.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-bnhs-gold-100 p-4">
                            <svg class="h-6 w-6 text-bnhs-gold-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Manage Users</h3>
                            <p class="text-sm text-gray-500">User management</p>
                        </div>
                    </Link>
                </div>

                <!-- Recent Requests -->
                <div class="mt-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Requests</h3>
                        <Link :href="route('admin.requests.index')" class="text-sm text-bnhs-blue hover:underline">
                            View All →
                        </Link>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tracking ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Requester
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Document
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="request in recentRequests" :key="request.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <Link
                                            :href="route('admin.requests.show', request.id)"
                                            class="font-mono font-medium text-bnhs-blue hover:underline"
                                        >
                                            {{ request.tracking_id }}
                                        </Link>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ request.first_name }} {{ request.last_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ request.document_type?.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(request.status)]">
                                            {{ request.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ formatDate(request.created_at) }}
                                    </td>
                                </tr>
                                <tr v-if="recentRequests.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No requests yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
