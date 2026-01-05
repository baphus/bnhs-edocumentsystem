<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Date and Calendar
const currentDate = ref(new Date());
const selectedDate = ref(new Date());
const page = usePage();

const greeting = computed(() => {
    const hour = currentDate.value.getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

const userName = computed(() => {
    const auth = page.props.auth as { user?: { first_name?: string; name?: string; email?: string } };
    return auth.user?.first_name || auth.user?.name || 'User';
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
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
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
                                <h1 class="mt-2 text-2xl font-bold lg:text-3xl">{{ greeting }}, {{ userName }}!</h1>
                                <p class="mt-2 text-blue-100">Welcome to your dashboard.</p>
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

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

