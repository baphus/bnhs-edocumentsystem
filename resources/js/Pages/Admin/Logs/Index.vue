<script setup lang="ts">
import { Head, Link as InertiaLink, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

interface Log {
    id: number;
    source: string;
    created_at: string;
    causer: string;
    action: string;
    description: string;
    details: any;
    subject?: string;
}

interface Props {
    logs: {
        data: Log[];
        links: any[];
        meta?: {
            links: any[];
            from: number;
            to: number;
            total: number;
        }; // Handle both resource and pagination styles
    };
    filters: {
        search?: string;
        from_date?: string;
        to_date?: string;
        source?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    search: props.filters.search || '',
    from_date: props.filters.from_date || '',
    to_date: props.filters.to_date || '',
    source: props.filters.source || '',
});

watch(filters, debounce((newFilters) => {
    router.get(route('admin.logs.index'), newFilters, {
        preserveState: true,
        replace: true,
    });
}, 300), { deep: true });

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getSourceColor = (source: string) => {
    const colors: Record<string, string> = {
        'Audit': 'bg-purple-100 text-purple-700',
        'Request': 'bg-blue-100 text-blue-700',
        'Email': 'bg-amber-100 text-amber-700',
    };
    return colors[source] || 'bg-gray-100 text-gray-700';
};

const getActionColor = (action: string) => {
    const lowerAction = action.toLowerCase();
    if (lowerAction.includes('create') || lowerAction.includes('submitted')) return 'text-green-700 ring-green-600/20 bg-green-50';
    if (lowerAction.includes('update') || lowerAction.includes('process')) return 'text-blue-700 ring-blue-600/20 bg-blue-50';
    if (lowerAction.includes('delete')) return 'text-red-700 ring-red-600/20 bg-red-50';
    if (lowerAction.includes('fail')) return 'text-red-700 ring-red-600/20 bg-red-50';
    if (lowerAction.includes('login')) return 'text-indigo-700 ring-indigo-600/20 bg-indigo-50';
    return 'text-gray-600 ring-gray-500/10 bg-gray-50';
};
</script>


<template>
    <Head title="System Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">System Logs</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="md:flex md:items-center md:justify-between mb-6">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                            Activity Timeline
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Comprehensive history of system activities including requests, emails, and audits.
                        </p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <TextInput
                                id="search"
                                v-model="filters.search"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Search description, subject..."
                            />
                        </div>

                        <!-- Source Filter -->
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                            <select
                                id="source"
                                v-model="filters.source"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue sm:text-sm"
                            >
                                <option value="">All Sources</option>
                                <option value="Audit">Audit Logs</option>
                                <option value="Request">Request Logs</option>
                                <option value="Email">Email Logs</option>
                            </select>
                        </div>

                        <!-- Date Filters -->
                        <div>
                            <label for="from_date" class="block text-sm font-medium text-gray-700">From Date</label>
                            <TextInput
                                id="from_date"
                                v-model="filters.from_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                        </div>
                        <div>
                            <label for="to_date" class="block text-sm font-medium text-gray-700">To Date</label>
                            <TextInput
                                id="to_date"
                                v-model="filters.to_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <SecondaryButton @click="filters = { search: '', source: '', from_date: '', to_date: '' }" class="text-xs">
                            Clear Filters
                        </SecondaryButton>
                    </div>
                </div>

                <!-- Combined Logs Table -->
                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Timestamp
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Source
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Subject
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Causer
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Action
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Description
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="log in logs.data" :key="`${log.source}-${log.id}`" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', getSourceColor(log.source)]">
                                            {{ log.source }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" :title="log.subject">
                                        {{ log.subject || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ log.causer || 'System' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset', getActionColor(log.action)]">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>{{ log.description }}</div>
                                         <div v-if="log.details" class="mt-1 text-xs text-gray-400 truncate max-w-xs" :title="typeof log.details === 'string' ? log.details : JSON.stringify(log.details)">
                                            {{ typeof log.details === 'string' ? log.details : 'Has details...' }}
                                         </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No activity logs found matching your criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <InertiaLink 
                                    v-if="logs.prev_page_url" 
                                    :href="logs.prev_page_url" 
                                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Previous
                                </InertiaLink>
                                <div v-else></div>
                                <InertiaLink 
                                    v-if="logs.next_page_url" 
                                    :href="logs.next_page_url" 
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Next
                                </InertiaLink>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ logs.from || 0 }}</span>
                                        to
                                        <span class="font-medium">{{ logs.to || 0 }}</span>
                                        of
                                        <span class="font-medium">{{ logs.total || 0 }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <template v-for="(link, key) in logs.links" :key="key">
                                            <InertiaLink
                                                v-if="link.url"
                                                :href="link.url"
                                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0"
                                                :class="[
                                                    link.active ? 'z-10 bg-bnhs-blue text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-bnhs-blue' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                                    key === 0 ? 'rounded-l-md' : '',
                                                    key === logs.links.length - 1 ? 'rounded-r-md' : ''
                                                ]"
                                                v-html="link.label"
                                            />
                                            <span 
                                                v-else 
                                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"
                                                v-html="link.label"
                                            ></span>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


