<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
