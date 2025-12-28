<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

interface RequestLog {
    id: number;
    action: string;
    user_name: string;
    tracking_id?: string;
    old_value?: string;
    new_value?: string;
    description?: string;
    created_at: string;
}

interface EmailLog {
    id: number;
    recipient_email: string;
    subject: string;
    status: string;
    error_message?: string;
    tracking_id?: string;
    sent_at?: string;
    delivered_at?: string;
    created_at: string;
}

interface Props {
    requestLogs: {
        data: RequestLog[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    emailLogs: {
        data: EmailLog[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    actions: string[];
    users: Array<{ id: number; name: string; email: string }>;
    filters: {
        action?: string;
        user_id?: string;
        from_date?: string;
        to_date?: string;
        email_status?: string;
        email_from_date?: string;
        email_to_date?: string;
    };
}

const props = defineProps<Props>();

const activeTab = ref<'request' | 'email'>('request');

// Request Log Filters
const actionFilter = ref(props.filters.action || '');
const userIdFilter = ref(props.filters.user_id || '');
const fromDateFilter = ref(props.filters.from_date || '');
const toDateFilter = ref(props.filters.to_date || '');

// Email Log Filters
const emailStatusFilter = ref(props.filters.email_status || '');
const emailFromDateFilter = ref(props.filters.email_from_date || '');
const emailToDateFilter = ref(props.filters.email_to_date || '');

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const applyRequestFilters = () => {
    router.get(
        route('admin.superadmin.logs.index'),
        {
            action: actionFilter.value || undefined,
            user_id: userIdFilter.value || undefined,
            from_date: fromDateFilter.value || undefined,
            to_date: toDateFilter.value || undefined,
            // Preserve email filters
            email_status: emailStatusFilter.value || undefined,
            email_from_date: emailFromDateFilter.value || undefined,
            email_to_date: emailToDateFilter.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const applyEmailFilters = () => {
    router.get(
        route('admin.superadmin.logs.index'),
        {
            // Preserve request filters
            action: actionFilter.value || undefined,
            user_id: userIdFilter.value || undefined,
            from_date: fromDateFilter.value || undefined,
            to_date: toDateFilter.value || undefined,
            email_status: emailStatusFilter.value || undefined,
            email_from_date: emailFromDateFilter.value || undefined,
            email_to_date: emailToDateFilter.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const clearRequestFilters = () => {
    actionFilter.value = '';
    userIdFilter.value = '';
    fromDateFilter.value = '';
    toDateFilter.value = '';
    applyRequestFilters();
};

const clearEmailFilters = () => {
    emailStatusFilter.value = '';
    emailFromDateFilter.value = '';
    emailToDateFilter.value = '';
    applyEmailFilters();
};

const getEmailStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        queued: 'bg-yellow-100 text-yellow-800',
        sent: 'bg-blue-100 text-blue-800',
        delivered: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getActionColor = (action: string) => {
    if (action.includes('delete')) return 'text-red-600';
    if (action.includes('status')) return 'text-green-600';
    if (action.includes('create')) return 'text-blue-600';
    return 'text-gray-600';
};
</script>

<template>
    <Head title="System Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">System Logs</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                @click="activeTab = 'request'"
                                :class="[
                                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                    activeTab === 'request'
                                        ? 'border-bnhs-blue text-bnhs-blue'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                ]"
                            >
                                Request Logs
                            </button>
                            <button
                                @click="activeTab = 'email'"
                                :class="[
                                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium',
                                    activeTab === 'email'
                                        ? 'border-bnhs-blue text-bnhs-blue'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                ]"
                            >
                                Email Logs
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Request Logs Tab -->
                <div v-show="activeTab === 'request'">
                    <!-- Filters -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow">
                        <div class="grid gap-4 sm:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Action</label>
                                <select
                                    v-model="actionFilter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">All Actions</option>
                                    <option v-for="action in actions" :key="action" :value="action">
                                        {{ action }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">User</label>
                                <select
                                    v-model="userIdFilter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">All Users</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
                                <input
                                    v-model="fromDateFilter"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
                                <input
                                    v-model="toDateFilter"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </div>
                            <div class="flex items-end gap-2">
                                <PrimaryButton @click="applyRequestFilters" class="flex-1"> Apply </PrimaryButton>
                                <SecondaryButton @click="clearRequestFilters" class="flex-1"> Clear </SecondaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Request Logs Table -->
                    <div class="overflow-hidden rounded-xl bg-white shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Action
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tracking ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Changes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Description
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="log in requestLogs.data" :key="log.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['text-sm font-medium', getActionColor(log.action)]">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ log.user_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span v-if="log.tracking_id" class="font-mono text-bnhs-blue">
                                            {{ log.tracking_id }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div v-if="log.old_value || log.new_value" class="space-y-1">
                                            <div v-if="log.old_value" class="text-red-600">
                                                <span class="font-medium">From:</span> {{ log.old_value }}
                                            </div>
                                            <div v-if="log.new_value" class="text-green-600">
                                                <span class="font-medium">To:</span> {{ log.new_value }}
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ log.description || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="requestLogs.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No request logs found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="requestLogs.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing page {{ requestLogs.current_page }} of {{ requestLogs.last_page }}
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in requestLogs.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md',
                                    link.active
                                        ? 'bg-bnhs-blue text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>

                <!-- Email Logs Tab -->
                <div v-show="activeTab === 'email'">
                    <!-- Filters -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow">
                        <div class="grid gap-4 sm:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                                <select
                                    v-model="emailStatusFilter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="queued">Queued</option>
                                    <option value="sent">Sent</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
                                <input
                                    v-model="emailFromDateFilter"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
                                <input
                                    v-model="emailToDateFilter"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </div>
                            <div class="flex items-end gap-2">
                                <PrimaryButton @click="applyEmailFilters" class="flex-1"> Apply </PrimaryButton>
                                <SecondaryButton @click="clearEmailFilters" class="flex-1"> Clear </SecondaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Email Logs Table -->
                    <div class="overflow-hidden rounded-xl bg-white shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Recipient
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Subject
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Tracking ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Sent At
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Delivered At
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Error
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="log in emailLogs.data" :key="log.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ log.recipient_email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ log.subject }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['rounded-full px-2 py-1 text-xs font-medium', getEmailStatusColor(log.status)]">
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span v-if="log.tracking_id" class="font-mono text-bnhs-blue">
                                            {{ log.tracking_id }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ log.sent_at ? formatDate(log.sent_at) : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ log.delivered_at ? formatDate(log.delivered_at) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-red-600">
                                        {{ log.error_message || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="emailLogs.data.length === 0">
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        No email logs found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="emailLogs.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing page {{ emailLogs.current_page }} of {{ emailLogs.last_page }}
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in emailLogs.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md',
                                    link.active
                                        ? 'bg-bnhs-blue text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
