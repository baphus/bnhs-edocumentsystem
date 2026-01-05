<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { DocumentRequest } from '@/types';

interface Props {
    requests: any;
    filters: Record<string, any>;
    statuses: string[];
    documentTypes: Array<{ id: number; name: string }>;
    isSuperadmin?: boolean;
    routePrefix: string; // 'admin.requests' or 'superadmin.requests'
}

const props = withDefaults(defineProps<Props>(), {
    isSuperadmin: false,
});

const emit = defineEmits<{
    export: [];
    createRequest: [];
}>();

// Filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const documentTypeFilter = ref(props.filters.document_type_id || props.filters.document_type || '');
const fromDateFilter = ref(props.filters.from_date || '');
const toDateFilter = ref(props.filters.to_date || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref<'asc' | 'desc'>(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || 25);

// Selection
const selectedRequests = ref<number[]>([]);

// View Settings - Column Visibility
const showViewSettings = ref(false);
const showFilters = ref(false);
const columnVisibility = ref({
    checkbox: true,
    tracking_id: true,
    requester: true,
    lrn: true,
    email: false,
    document: true,
    status: true,
    otp_verified: props.isSuperadmin,
    date: true,
    actions: true,
});

// Load saved column visibility from localStorage
const storageKey = computed(() => `requests-table-columns-${props.isSuperadmin ? 'superadmin' : 'admin'}`);
const loadColumnVisibility = () => {
    const saved = localStorage.getItem(storageKey.value);
    if (saved) {
        try {
            columnVisibility.value = { ...columnVisibility.value, ...JSON.parse(saved) };
        } catch (e) {
            console.error('Failed to load column visibility:', e);
        }
    }
};

// Save column visibility to localStorage
watch(columnVisibility, (newVal) => {
    localStorage.setItem(storageKey.value, JSON.stringify(newVal));
}, { deep: true });

// Load on mount
loadColumnVisibility();

const visibleColumnsCount = computed(() => {
    return Object.values(columnVisibility.value).filter(Boolean).length;
});

const applyFilters = () => {
    router.get(route(`${props.routePrefix}.index`), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        [props.isSuperadmin ? 'document_type' : 'document_type_id']: documentTypeFilter.value || undefined,
        from_date: fromDateFilter.value || undefined,
        to_date: toDateFilter.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortDirection.value || undefined,
        per_page: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const sortColumn = (column: string) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([statusFilter, documentTypeFilter, fromDateFilter, toDateFilter, perPage], applyFilters);

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    documentTypeFilter.value = '';
    fromDateFilter.value = '';
    toDateFilter.value = '';
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    perPage.value = 25;
    router.get(route(`${props.routePrefix}.index`));
};

const toggleSelect = (requestId: number) => {
    const index = selectedRequests.value.indexOf(requestId);
    if (index > -1) {
        selectedRequests.value.splice(index, 1);
    } else {
        selectedRequests.value.push(requestId);
    }
};

const toggleSelectAll = () => {
    if (selectedRequests.value.length === props.requests.data.length) {
        selectedRequests.value = [];
    } else {
        selectedRequests.value = props.requests.data.map((r: any) => r.id);
    }
};

const isSelected = (requestId: number) => {
    return selectedRequests.value.includes(requestId);
};

const isAllSelected = computed(() => {
    return props.requests.data.length > 0 && selectedRequests.value.length === props.requests.data.length;
});

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
        hour: 'numeric',
        minute: '2-digit',
    });
};

defineExpose({
    selectedRequests,
    clearSelection: () => { selectedRequests.value = []; },
});
</script>

<template>
    <div>
        <!-- Filters Card -->
        <div class="mb-6 rounded-xl bg-white p-6 shadow">
            <div class="flex flex-col gap-4">
                <!-- Top Row: Search and Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search by tracking ID, name, or LRN..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <SecondaryButton @click="showFilters = !showFilters" class="whitespace-nowrap">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filters
                            <svg class="ml-2 h-4 w-4" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </SecondaryButton>

                        <!-- View Settings Toggle -->
                        <div class="relative">
                            <SecondaryButton @click="showViewSettings = !showViewSettings" class="whitespace-nowrap" title="Toggle Columns">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                            </SecondaryButton>
                            
                            <!-- View Settings Dropdown -->
                            <div v-if="showViewSettings" class="absolute right-0 z-10 mt-2 w-64 rounded-lg bg-white shadow-xl border border-gray-200">
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Toggle Columns</h3>
                                    <div class="space-y-2">
                                        <label v-if="!isSuperadmin" class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.checkbox" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" :disabled="columnVisibility.checkbox" />
                                            <span class="ml-2 text-sm text-gray-700">Checkbox</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.tracking_id" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Tracking ID</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.requester" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Requester</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.lrn" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">LRN</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.email" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Email</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.document" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Document Type</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.status" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Status</span>
                                        </label>
                                        <label v-if="isSuperadmin" class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.otp_verified" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">OTP Verified</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.date" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" />
                                            <span class="ml-2 text-sm text-gray-700">Date</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="columnVisibility.actions" class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue" :disabled="columnVisibility.actions" />
                                            <span class="ml-2 text-sm text-gray-700">Actions</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <PrimaryButton @click="$emit('createRequest')" class="whitespace-nowrap">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Request
                        </PrimaryButton>
                        <SecondaryButton @click="$emit('export')" class="whitespace-nowrap">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                        </SecondaryButton>
                    </div>
                </div>

                <!-- Expandable Filters -->
                <transition name="slide-down">
                    <div v-if="showFilters" class="pt-4 border-t border-gray-200">
                        <div class="grid gap-4 sm:grid-cols-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    id="status"
                                    v-model="statusFilter"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">All Statuses</option>
                                    <option v-for="status in statuses" :key="status" :value="status">
                                        {{ status }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="docType" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                                <select
                                    id="docType"
                                    v-model="documentTypeFilter"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="docType in documentTypes" :key="docType.id" :value="docType.id">
                                        {{ docType.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="fromDate"
                                        type="date"
                                        v-model="fromDateFilter"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                    <span class="text-gray-500 font-medium">to</span>
                                    <input
                                        id="toDate"
                                        type="date"
                                        v-model="toDateFilter"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </div>
                            </div>
                            <div class="flex items-end">
                                <button
                                    v-if="filters.search || filters.status || filters.document_type_id || filters.document_type || filters.from_date || filters.to_date"
                                    @click="clearFilters"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>



        <!-- Table Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th v-if="columnVisibility.checkbox" class="w-12 px-6 py-3">
                                <input
                                    type="checkbox"
                                    :checked="isAllSelected"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </th>
                            <th v-if="columnVisibility.tracking_id"
                                @click="sortColumn('tracking_id')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    Tracking ID
                                    <span v-if="sortBy === 'tracking_id'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.requester"
                                @click="sortColumn('last_name')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    Requester
                                    <span v-if="sortBy === 'last_name'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.lrn"
                                @click="sortColumn('lrn')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    LRN
                                    <span v-if="sortBy === 'lrn'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.email" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Email
                            </th>
                            <th v-if="columnVisibility.document"
                                @click="sortColumn('document_type_id')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    Document
                                    <span v-if="sortBy === 'document_type_id'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.status"
                                @click="sortColumn('status')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    Status
                                    <span v-if="sortBy === 'status'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.otp_verified && isSuperadmin"
                                @click="sortColumn('otp_verified')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    OTP Verified
                                    <span v-if="sortBy === 'otp_verified'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.date"
                                @click="sortColumn('created_at')"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 cursor-pointer hover:bg-gray-100 select-none"
                            >
                                <div class="flex items-center gap-1">
                                    Date
                                    <span v-if="sortBy === 'created_at'" class="text-bnhs-blue">
                                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th v-if="columnVisibility.actions" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <slot name="table-rows" :columnVisibility="columnVisibility" :isSelected="isSelected" :toggleSelect="toggleSelect" :getStatusColor="getStatusColor" :formatDate="formatDate"></slot>
                        
                        <tr v-if="requests.data.length === 0">
                            <td :colspan="visibleColumnsCount" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="mt-4 font-medium">No requests found</p>
                                <p class="mt-1 text-sm">Try adjusting your search or filters</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination with Entries per page -->
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Left: Entries info and selector -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label for="perPage" class="text-sm text-gray-700">Show</label>
                            <select
                                id="perPage"
                                v-model="perPage"
                                class="rounded border-gray-300 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                            <span class="text-sm text-gray-700">entries</span>
                        </div>
                        <p class="text-sm text-gray-700">
                            Showing {{ requests.from || 0 }} to {{ requests.to || 0 }} of {{ requests.total || 0 }} entries
                        </p>
                    </div>

                    <!-- Right: Pagination controls -->
                    <div v-if="requests.last_page > 1" class="flex items-center gap-1">
                        <!-- Previous Button -->
                        <Link
                            v-if="requests.current_page > 1"
                            :href="requests.prev_page_url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <button
                            v-else
                            disabled
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- First Page -->
                        <Link
                            v-if="requests.current_page > 2"
                            :href="requests.links[1]?.url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            1
                        </Link>
                        
                        <!-- Ellipsis before current page -->
                        <span v-if="requests.current_page > 3" class="px-2 text-gray-500">...</span>

                        <!-- Previous page number -->
                        <Link
                            v-if="requests.current_page > 1"
                            :href="requests.links[requests.current_page - 1]?.url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            {{ requests.current_page - 1 }}
                        </Link>

                        <!-- Current Page -->
                        <button
                            class="rounded-lg border-2 border-bnhs-blue bg-bnhs-blue px-3 py-2 text-sm font-medium text-white"
                            disabled
                        >
                            {{ requests.current_page }}
                        </button>

                        <!-- Next page number -->
                        <Link
                            v-if="requests.current_page < requests.last_page"
                            :href="requests.links[requests.current_page + 1]?.url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            {{ requests.current_page + 1 }}
                        </Link>

                        <!-- Ellipsis after current page -->
                        <span v-if="requests.current_page < requests.last_page - 2" class="px-2 text-gray-500">...</span>

                        <!-- Last Page -->
                        <Link
                            v-if="requests.current_page < requests.last_page - 1"
                            :href="requests.links[requests.last_page]?.url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            {{ requests.last_page }}
                        </Link>

                        <!-- Next Button -->
                        <Link
                            v-if="requests.current_page < requests.last_page"
                            :href="requests.next_page_url"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                            preserve-scroll
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                        <button
                            v-else
                            disabled
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Bulk Actions Bar -->
        <transition name="slide-up">
            <div v-if="selectedRequests.length > 0" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50">
                <div class="bg-white rounded-lg shadow-2xl border border-gray-200 px-6 py-4">
                    <slot name="bulk-actions" :selectedRequests="selectedRequests"></slot>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Smooth transitions for filter panel */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease-in-out;
    max-height: 500px;
    overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
    max-height: 0;
    opacity: 0;
    transform: translateY(-10px);
}

/* Smooth rotation for chevron icon */
.rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.3s ease-in-out;
}

svg:not(.rotate-180) {
    transition: transform 0.3s ease-in-out;
}

/* Smooth transitions for floating bulk actions */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease-in-out;
}

.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translate(-50%, 20px);
}

.slide-up-enter-to,
.slide-up-leave-from {
    opacity: 1;
    transform: translate(-50%, 0);
}
</style>

