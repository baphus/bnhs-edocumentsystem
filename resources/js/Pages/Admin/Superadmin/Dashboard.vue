<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';

interface Props {
    stats: {
        total_requests: number;
        pending_documents: number;
        fulfillment_rate: number;
        email_success_rate: number;
        total_users: number;
        active_users: number;
    };
    activityFeed: {
        id: number;
        action: string;
        user_name: string;
        tracking_id?: string;
        old_value?: string;
        new_value?: string;
        description?: string;
        created_at: string;
    }[];
    recentRequests: {
        id: number;
        tracking_id: string;
        full_name: string;
        email: string;
        document_type: string;
        status: string;
        created_at: string;
    }[];
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        Pending: 'bg-yellow-100 text-yellow-800',
        Verified: 'bg-blue-100 text-blue-800',
        Processing: 'bg-purple-100 text-purple-800',
        Ready: 'bg-green-100 text-green-800',
        Completed: 'bg-gray-100 text-gray-800',
        Rejected: 'bg-red-100 text-red-800',
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
                <!-- KPI Cards -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <KpiCard
                        title="Total Requests"
                        :value="stats.total_requests"
                        color="blue"
                    />
                    <KpiCard
                        title="Pending Documents"
                        :value="stats.pending_documents"
                        color="yellow"
                    />
                    <KpiCard
                        title="Fulfillment Rate"
                        :value="`${stats.fulfillment_rate}%`"
                        color="green"
                    />
                    <KpiCard
                        title="Email Success Rate"
                        :value="`${stats.email_success_rate}%`"
                        color="purple"
                    />
                </div>

                <!-- Activity Feed and Recent Requests -->
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    <!-- Activity Feed -->
                    <ActivityFeed :activities="activityFeed" />

                    <!-- Recent Requests -->
                    <div class="rounded-xl bg-white shadow">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Requests</h3>
                                <Link
                                    :href="route('admin.requests.index')"
                                    class="text-sm text-bnhs-blue hover:underline"
                                >
                                    View All →
                                </Link>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="request in recentRequests"
                                :key="request.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Link
                                            :href="route('registrar.requests.show', request.id)"
                                            class="font-mono text-sm font-medium text-bnhs-blue hover:underline"
                                        >
                                            {{ request.tracking_id }}
                                        </Link>
                                        <p class="mt-1 text-sm text-gray-900">{{ request.full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ request.document_type }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            :class="[
                                                'rounded-full px-2 py-1 text-xs font-medium',
                                                getStatusColor(request.status),
                                            ]"
                                        >
                                            {{ request.status }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ formatDate(request.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="recentRequests.length === 0" class="px-6 py-8 text-center text-gray-500">
                                No requests yet
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        :href="route('admin.users.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-bnhs-blue-100 p-4">
                            <svg class="h-6 w-6 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Manage Users</h3>
                            <p class="text-sm text-gray-500">User management & impersonation</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('admin.requests.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-bnhs-gold-100 p-4">
                            <svg class="h-6 w-6 text-bnhs-gold-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Request Command</h3>
                            <p class="text-sm text-gray-500">Master request management</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('admin.logs.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-green-100 p-4">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">System Logs</h3>
                            <p class="text-sm text-gray-500">Audit trail & email logs</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('admin.settings.index')"
                        class="flex items-center gap-4 rounded-xl bg-white p-6 shadow transition hover:shadow-md"
                    >
                        <div class="rounded-full bg-purple-100 p-4">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Settings</h3>
                            <p class="text-sm text-gray-500">System configuration</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


