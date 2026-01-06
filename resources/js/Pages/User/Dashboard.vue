<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

interface ActivityLog {
    id: number;
    action: string;
    description: string | null;
    old_value: string | null;
    new_value: string | null;
    user_name: string;
    created_at: string;
}

interface Request {
    id: number;
    tracking_id: string;
    document_type: string;
    document_category: string;
    purpose: string;
    quantity: number;
    status: string;
    status_description: string;
    estimated_completion_date?: string;
    completed_at?: string;
    created_at: string;
    updated_at: string;
    admin_notes?: string;
    activity_logs?: ActivityLog[];
}

const props = defineProps<{
    email: string;
    userName?: string;
    latestRequest?: Request;
    requestHistory?: Request[];
    hasRequests: boolean;
}>();

const copied = ref(false);
const expandedRequests = ref<Record<number, boolean>>({});

const toggleRequestExpanded = (requestId: number) => {
    expandedRequests.value[requestId] = !expandedRequests.value[requestId];
};

const copyToClipboard = async (trackingId: string) => {
    try {
        await navigator.clipboard.writeText(trackingId);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        const textArea = document.createElement('textarea');
        textArea.value = trackingId;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};

const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        'Pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Verified': 'bg-blue-100 text-blue-800 border-blue-200',
        'Processing': 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'Ready': 'bg-purple-100 text-purple-800 border-purple-200',
        'Completed': 'bg-green-100 text-green-800 border-green-200',
        'Rejected': 'bg-red-100 text-red-800 border-red-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const sidebarOpen = ref(false);

const displayName = computed(() => {
    return props.userName || props.email.split('@')[0];
});

// Document progress helpers
const progressSteps = [
    { key: 'Pending', label: 'Submitted', description: 'Request submitted and under review' },
    { key: 'Verified', label: 'Verified', description: 'Information verified and approved' },
    { key: 'Processing', label: 'Processing', description: 'Document is being prepared' },
    { key: 'Ready', label: 'Ready', description: 'Document is ready for pickup' },
    { key: 'Completed', label: 'Completed', description: 'Request completed successfully' }
];

const getProgressPercentage = (status: string) => {
    const statusIndex = progressSteps.findIndex(step => step.key === status);
    if (statusIndex === -1) return 0;
    return ((statusIndex + 1) / progressSteps.length) * 100;
};

const getCurrentStepIndex = (status: string) => {
    const index = progressSteps.findIndex(step => step.key === status);
    return index === -1 ? 0 : index;
};

const isStepCompleted = (stepIndex: number, currentStatus: string) => {
    const currentIndex = getCurrentStepIndex(currentStatus);
    return stepIndex <= currentIndex;
};

const isStepActive = (stepIndex: number, currentStatus: string) => {
    return stepIndex === getCurrentStepIndex(currentStatus);
};

const formatActionName = (action: string) => {
    const actionMap: Record<string, string> = {
        'status_change': 'Status Updated',
        'note_updated': 'Notes Updated',
        'request_created': 'Request Created',
        'request_submitted': 'Request Submitted',
    };
    return actionMap[action] || action
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

const getActionIcon = (action: string) => {
    if (action === 'status_change') return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    if (action === 'note_updated') return 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
    return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
};

</script>

<template>
    <Head title="My Dashboard" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <!-- Mobile Header -->
        <div class="border-b border-gray-200 bg-white lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <Link :href="route('home')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-auto" />
                    <span class="font-semibold text-gray-900">My Dashboard</span>
                </Link>
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex">
            <!-- Sidebar -->
            <aside
                :class="[
                    'fixed inset-y-0 left-0 z-50 w-64 transform bg-white shadow-lg transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]"
            >
                <div class="flex h-full flex-col">
                    <!-- Sidebar Header -->
                    <div class="border-b border-gray-200 p-4">
                        <Link :href="route('home')" class="flex items-center gap-3">
                            <ApplicationLogo class="h-10 w-auto" />
                            <div>
                                <p class="font-semibold text-gray-900">eDocument System</p>
                                <p class="text-xs text-gray-500">User Dashboard</p>
                            </div>
                        </Link>
                        <button
                            @click="sidebarOpen = false"
                            class="absolute right-4 top-4 rounded-lg p-1 text-gray-600 hover:bg-gray-100 lg:hidden"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 space-y-1 p-4">
                        <Link
                            :href="route('user.dashboard.index')"
                            class="flex items-center gap-3 rounded-lg bg-bnhs-blue-50 px-4 py-3 text-sm font-medium text-bnhs-blue transition hover:bg-bnhs-blue-100"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </Link>
                        <Link
                            :href="route('request.select')"
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Request
                        </Link>
                        <Link
                            :href="route('track.index')"
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Track Request
                        </Link>
                    </nav>

                    <!-- User Info -->
                    <div class="border-t border-gray-200 p-4">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs font-medium text-gray-500">Logged in as</p>
                            <p class="mt-1 truncate text-sm font-medium text-gray-900">{{ email }}</p>
                        </div>
                        <Link
                            :href="route('home')"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Back to Home
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Overlay for mobile -->
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
            ></div>

            <!-- Main Content -->
            <main class="flex-1 lg:ml-0">
                <div class="p-4 lg:p-8">
                    <!-- Latest Request Progress -->
                    <div v-if="hasRequests && latestRequest" class="mb-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Latest Request</span>
                                    </div>
                                    <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ latestRequest.document_type }}</h2>
                                    <div class="flex items-center gap-3 text-sm text-gray-600">
                                        <span class="font-mono font-medium">{{ latestRequest.tracking_id }}</span>
                                        <button
                                            @click="copyToClipboard(latestRequest.tracking_id)"
                                            :class="[
                                                'p-1 rounded hover:bg-gray-100',
                                                copied ? 'text-green-600' : 'text-gray-400'
                                            ]"
                                            title="Copy tracking ID"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path v-if="!copied" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full border',
                                        getStatusBadgeClass(latestRequest.status)
                                    ]"
                                >
                                    {{ latestRequest.status }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-6">
                                <div class="flex justify-between mb-2 text-sm">
                                    <span class="font-medium text-gray-700">Progress</span>
                                    <span class="font-medium text-gray-900">{{ Math.round(getProgressPercentage(latestRequest.status)) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-sm h-2">
                                    <div 
                                        class="bg-bnhs-blue h-2 rounded-sm transition-all duration-300"
                                        :style="{ width: getProgressPercentage(latestRequest.status) + '%' }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Progress Steps -->
                            <div class="grid grid-cols-5 gap-2 mb-6">
                                <div 
                                    v-for="(step, index) in progressSteps" 
                                    :key="step.key"
                                    class="flex flex-col items-center text-center"
                                >
                                    <div 
                                        :class="[
                                            'flex h-10 w-10 items-center justify-center rounded-full border-2 mb-2',
                                            isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                ? 'bg-green-600 border-green-600 text-white'
                                                : isStepActive(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                ? 'bg-bnhs-blue border-bnhs-blue text-white'
                                                : latestRequest.status === 'Rejected'
                                                ? 'bg-red-600 border-red-600 text-white'
                                                : 'bg-white border-gray-300 text-gray-400'
                                        ]"
                                    >
                                        <svg v-if="isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <svg v-else-if="latestRequest.status === 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <span v-else class="text-sm font-semibold">{{ index + 1 }}</span>
                                    </div>
                                    <h3 
                                        :class="[
                                            'text-xs font-semibold mb-1',
                                            isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                ? 'text-green-600'
                                                : isStepActive(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                ? 'text-bnhs-blue'
                                                : latestRequest.status === 'Rejected'
                                                ? 'text-red-600'
                                                : 'text-gray-500'
                                        ]"
                                    >
                                        {{ step.label }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Status Info -->
                            <div class="border-t border-gray-200 pt-4 grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Current Status</p>
                                    <p class="text-sm text-gray-900">{{ latestRequest.status_description }}</p>
                                </div>
                                <div v-if="latestRequest.estimated_completion_date">
                                    <p class="text-xs text-gray-500 mb-1">Estimated Completion</p>
                                    <p class="text-sm font-medium text-gray-900">{{ latestRequest.estimated_completion_date }}</p>
                                </div>
                            </div>

                            <!-- Activity Timeline -->
                            <div v-if="latestRequest.activity_logs && latestRequest.activity_logs.length > 0" class="border-t border-gray-200 mt-4 pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Activity</h4>
                                <div class="space-y-2">
                                    <div 
                                        v-for="(log, index) in latestRequest.activity_logs.slice(0, 3)" 
                                        :key="log.id"
                                        class="flex gap-3 text-sm"
                                    >
                                        <div class="flex-shrink-0">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100">
                                                <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActionIcon(log.action)"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900">{{ formatActionName(log.action) }}</p>
                                            <p v-if="log.new_value" class="text-xs text-gray-600">{{ log.new_value }}</p>
                                            <p class="text-xs text-gray-500">{{ log.created_at }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Message for New Users -->
                    <div v-else class="mb-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h1 class="text-xl font-bold text-gray-900">Welcome, {{ displayName }}!</h1>
                            <p class="mt-2 text-gray-600">Get started by submitting your first document request</p>
                            <Link
                                :href="route('request.select')"
                                class="mt-6 inline-flex items-center gap-2 bg-bnhs-blue px-6 py-2 text-sm font-medium text-white rounded hover:bg-bnhs-blue-600 transition"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Submit New Request
                            </Link>
                        </div>
                    </div>

                    <!-- My Requests Section -->
                    <div v-if="hasRequests">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-900">My Requests</h2>
                            <Link
                                :href="route('request.select')"
                                class="text-sm text-bnhs-blue hover:underline font-medium"
                            >
                                + New Request
                            </Link>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-200">
                            <!-- Latest Request -->
                            <div v-if="latestRequest">
                                <button
                                    @click="toggleRequestExpanded(latestRequest.id)"
                                    class="w-full p-4 text-left hover:bg-gray-50 transition"
                                >
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span
                                                    :class="[
                                                        'px-2 py-1 text-xs font-medium rounded border',
                                                        getStatusBadgeClass(latestRequest.status)
                                                    ]"
                                                >
                                                    {{ latestRequest.status }}
                                                </span>
                                                <span class="text-xs font-mono text-gray-500">{{ latestRequest.tracking_id }}</span>
                                            </div>
                                            <h3 class="font-semibold text-gray-900">{{ latestRequest.document_type }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">Requested {{ latestRequest.created_at }}</p>
                                        </div>
                                        <svg 
                                            :class="[
                                                'h-5 w-5 text-gray-400 transition-transform',
                                                expandedRequests[latestRequest.id] ? 'rotate-180' : ''
                                            ]" 
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Expanded Details -->
                                <div v-if="expandedRequests[latestRequest.id]" class="border-t border-gray-200 bg-gray-50 p-4">
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Quantity</p>
                                            <p class="text-sm font-medium text-gray-900">{{ latestRequest.quantity }} {{ latestRequest.quantity === 1 ? 'copy' : 'copies' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Category</p>
                                            <p class="text-sm font-medium text-gray-900">{{ latestRequest.document_category }}</p>
                                        </div>
                                        <div v-if="latestRequest.estimated_completion_date">
                                            <p class="text-xs text-gray-500 mb-1">Est. Completion</p>
                                            <p class="text-sm font-medium text-gray-900">{{ latestRequest.estimated_completion_date }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                                            <p class="text-sm font-medium text-gray-900">{{ latestRequest.updated_at }}</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200 pt-3 mb-3">
                                        <p class="text-xs text-gray-500 mb-1">Purpose</p>
                                        <p class="text-sm text-gray-900">{{ latestRequest.purpose }}</p>
                                    </div>

                                    <div v-if="latestRequest.admin_notes" class="bg-yellow-50 border border-yellow-200 rounded p-3">
                                        <p class="text-xs font-semibold text-yellow-900 mb-1">Admin Notes</p>
                                        <p class="text-sm text-yellow-900">{{ latestRequest.admin_notes }}</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Previous Requests -->
                            <div
                                v-for="request in requestHistory"
                                :key="request.id"
                            >
                                <button
                                    @click="toggleRequestExpanded(request.id)"
                                    class="w-full p-4 text-left hover:bg-gray-50 transition"
                                >
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span
                                                    :class="[
                                                        'px-2 py-1 text-xs font-medium rounded border',
                                                        getStatusBadgeClass(request.status)
                                                    ]"
                                                >
                                                    {{ request.status }}
                                                </span>
                                                <span class="text-xs font-mono text-gray-500">{{ request.tracking_id }}</span>
                                            </div>
                                            <h3 class="font-semibold text-gray-900">{{ request.document_type }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">Requested {{ request.created_at }}</p>
                                        </div>
                                        <svg 
                                            :class="[
                                                'h-5 w-5 text-gray-400 transition-transform',
                                                expandedRequests[request.id] ? 'rotate-180' : ''
                                            ]" 
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Expanded Details -->
                                <div v-if="expandedRequests[request.id]" class="border-t border-gray-200 bg-gray-50 p-4">
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Quantity</p>
                                            <p class="text-sm font-medium text-gray-900">{{ request.quantity }} {{ request.quantity === 1 ? 'copy' : 'copies' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Category</p>
                                            <p class="text-sm font-medium text-gray-900">{{ request.document_category }}</p>
                                        </div>
                                        <div v-if="request.estimated_completion_date">
                                            <p class="text-xs text-gray-500 mb-1">Est. Completion</p>
                                            <p class="text-sm font-medium text-gray-900">{{ request.estimated_completion_date }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                                            <p class="text-sm font-medium text-gray-900">{{ request.updated_at }}</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200 pt-3 mb-3">
                                        <p class="text-xs text-gray-500 mb-1">Purpose</p>
                                        <p class="text-sm text-gray-900">{{ request.purpose }}</p>
                                    </div>

                                    <div v-if="request.admin_notes" class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-3">
                                        <p class="text-xs font-semibold text-yellow-900 mb-1">Admin Notes</p>
                                        <p class="text-sm text-yellow-900">{{ request.admin_notes }}</p>
                                    </div>

                                    <!-- Activity Timeline -->
                                    <div v-if="request.activity_logs && request.activity_logs.length > 0" class="border-t border-gray-200 pt-3">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Activity</p>
                                        <div class="space-y-2">
                                            <div 
                                                v-for="log in request.activity_logs.slice(0, 5)" 
                                                :key="log.id"
                                                class="text-xs text-gray-600"
                                            >
                                                <span class="font-medium">{{ formatActionName(log.action) }}</span>
                                                <span v-if="log.new_value"> - {{ log.new_value }}</span>
                                                <span class="text-gray-500"> ({{ log.created_at }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>


