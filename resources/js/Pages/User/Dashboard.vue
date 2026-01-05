<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    email: string;
    userName?: string;
    latestRequest?: {
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
    };
    requestHistory?: Array<{
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
    }>;
    hasRequests: boolean;
}>();

const copied = ref(false);

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
                    <!-- Document Progress Section -->
                    <div v-if="hasRequests && latestRequest" class="mb-6">
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <div class="mb-6">
                                <h2 class="text-xl font-bold text-gray-900">Document Request Progress</h2>
                                <p class="mt-1 text-gray-600">Track the status of your latest document request</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-500">Tracking ID:</span>
                                    <span class="font-mono text-sm font-semibold text-bnhs-blue">{{ latestRequest.tracking_id }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-6">
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Progress</span>
                                    <span class="text-sm font-medium text-gray-700">{{ Math.round(getProgressPercentage(latestRequest.status)) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div 
                                        class="bg-gradient-to-r from-bnhs-blue to-bnhs-blue-600 h-3 rounded-full transition-all duration-500 ease-out"
                                        :style="{ width: getProgressPercentage(latestRequest.status) + '%' }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Progress Steps -->
                            <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:justify-between">
                                <div 
                                    v-for="(step, index) in progressSteps" 
                                    :key="step.key"
                                    class="flex items-center space-x-3 sm:flex-col sm:space-x-0 sm:space-y-2 sm:text-center flex-1"
                                >
                                    <div class="flex items-center">
                                        <div 
                                            :class="[
                                                'flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-300',
                                                isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                    ? 'bg-green-500 border-green-500 text-white'
                                                    : isStepActive(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                    ? 'bg-bnhs-blue border-bnhs-blue text-white'
                                                    : latestRequest.status === 'Rejected'
                                                    ? 'bg-red-500 border-red-500 text-white'
                                                    : 'bg-gray-100 border-gray-300 text-gray-500'
                                            ]"
                                        >
                                            <svg v-if="isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <svg v-else-if="latestRequest.status === 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span v-else class="text-sm font-medium">{{ index + 1 }}</span>
                                        </div>
                                        
                                        <!-- Connector line -->
                                        <div 
                                            v-if="index < progressSteps.length - 1"
                                            :class="[
                                                'h-0.5 w-8 ml-2 sm:hidden transition-all duration-300',
                                                isStepCompleted(index, latestRequest.status) && latestRequest.status !== 'Rejected'
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300'
                                            ]"
                                        ></div>
                                    </div>
                                    
                                    <div class="flex-1 sm:flex-none">
                                        <h3 
                                            :class="[
                                                'text-sm font-semibold transition-colors duration-300',
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
                                        <p class="text-xs text-gray-600 mt-1 sm:max-w-20 sm:text-center">{{ step.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Status Info -->
                            <div class="mt-6 p-4 rounded-lg border border-gray-200 bg-gray-50">
                                <div class="flex items-start gap-3">
                                    <div 
                                        :class="[
                                            'flex h-8 w-8 items-center justify-center rounded-full flex-shrink-0',
                                            latestRequest.status === 'Rejected'
                                                ? 'bg-red-100 text-red-600'
                                                : latestRequest.status === 'Completed'
                                                ? 'bg-green-100 text-green-600'
                                                : 'bg-blue-100 text-bnhs-blue'
                                        ]"
                                    >
                                        <svg v-if="latestRequest.status === 'Rejected'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <svg v-else-if="latestRequest.status === 'Completed'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">Current Status: {{ latestRequest.status }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">{{ latestRequest.status_description }}</p>
                                        <p class="text-xs text-gray-500 mt-2">Document Type: {{ latestRequest.document_type }}</p>
                                        <div v-if="latestRequest.estimated_completion_date" class="text-xs text-gray-500 mt-1">
                                            Estimated Completion: {{ latestRequest.estimated_completion_date }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Message for New Users -->
                    <div v-else class="mb-6">
                        <div class="rounded-xl bg-gradient-to-r from-bnhs-blue to-bnhs-blue-600 p-6 text-white shadow-lg">
                            <div class="text-center">
                                <h1 class="text-2xl font-bold lg:text-3xl">Welcome, {{ displayName }}!</h1>
                                <p class="mt-2 text-blue-100">Get started by submitting your first document request</p>
                                <Link
                                    :href="route('request.select')"
                                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white/20 px-6 py-3 text-sm font-medium text-white transition hover:bg-white/30"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Submit New Request
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 lg:text-2xl">My Requests</h2>
                        <p class="mt-1 text-gray-600">Track and manage your document requests</p>
                    </div>

                    <!-- No Requests State -->
                    <div v-if="!hasRequests" class="rounded-xl bg-white p-8 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">No Requests Yet</h3>
                        <p class="mt-2 text-gray-600">Get started by submitting your first document request.</p>
                        <Link
                            :href="route('request.select')"
                            class="mt-6 inline-flex items-center gap-2 rounded-lg bg-bnhs-blue px-6 py-3 text-sm font-medium text-white transition hover:bg-bnhs-blue-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Submit New Request
                        </Link>
                    </div>

                    <!-- Latest Request -->
                    <div v-else>
                        <div class="mb-6 rounded-xl bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Latest Request</h2>
                                    <p class="mt-1 text-sm text-gray-600">{{ latestRequest?.created_at }}</p>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full border px-3 py-1 text-xs font-medium',
                                        getStatusBadgeClass(latestRequest?.status || '')
                                    ]"
                                >
                                    {{ latestRequest?.status }}
                                </span>
                            </div>

                            <!-- Tracking Code -->
                            <div class="mb-6 rounded-lg bg-gradient-to-r from-bnhs-blue-50 to-bnhs-blue-100 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-bnhs-blue">Tracking Code</p>
                                        <p class="mt-1 text-2xl font-bold tracking-wider text-bnhs-blue">
                                            {{ latestRequest?.tracking_id }}
                                        </p>
                                    </div>
                                    <button
                                        @click="copyToClipboard(latestRequest?.tracking_id || '')"
                                        :class="[
                                            'rounded-lg p-2 transition',
                                            copied ? 'bg-green-100 text-green-600' : 'bg-white text-bnhs-blue hover:bg-bnhs-blue-50'
                                        ]"
                                        :title="copied ? 'Copied!' : 'Copy to clipboard'"
                                    >
                                        <svg v-if="!copied" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Status Description -->
                            <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-sm text-gray-700">{{ latestRequest?.status_description }}</p>
                            </div>

                            <!-- Request Details -->
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Document Type</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ latestRequest?.document_type }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Quantity</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ latestRequest?.quantity }} copy/copies</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Date Requested</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ latestRequest?.created_at }}</p>
                                </div>
                                <div v-if="latestRequest?.estimated_completion_date">
                                    <p class="text-xs font-medium text-gray-500">Estimated Completion</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ latestRequest.estimated_completion_date }}</p>
                                </div>
                                <div v-if="latestRequest?.completed_at">
                                    <p class="text-xs font-medium text-gray-500">Date Completed</p>
                                    <p class="mt-1 font-medium text-green-600">{{ latestRequest.completed_at }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Last Updated</p>
                                    <p class="mt-1 font-medium text-gray-900">{{ latestRequest?.updated_at }}</p>
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-medium text-gray-500">Purpose</p>
                                <p class="mt-1 text-sm text-gray-900">{{ latestRequest?.purpose }}</p>
                            </div>

                            <!-- Admin Notes -->
                            <div v-if="latestRequest?.admin_notes" class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <p class="text-xs font-medium text-yellow-800">Admin Notes</p>
                                <p class="mt-1 text-sm text-yellow-900">{{ latestRequest.admin_notes }}</p>
                            </div>
                        </div>

                        <!-- Request History -->
                        <div v-if="requestHistory && requestHistory.length > 0" class="rounded-xl bg-white shadow-sm">
                            <div class="border-b border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900">Request History</h2>
                                <p class="mt-1 text-sm text-gray-600">Your previous document requests</p>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="request in requestHistory"
                                    :key="request.id"
                                    class="p-6 transition hover:bg-gray-50"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    :class="[
                                                        'rounded-full border px-3 py-1 text-xs font-medium',
                                                        getStatusBadgeClass(request.status)
                                                    ]"
                                                >
                                                    {{ request.status }}
                                                </span>
                                                <span class="text-sm font-mono font-medium text-gray-900">
                                                    {{ request.tracking_id }}
                                                </span>
                                            </div>
                                            <p class="mt-2 font-medium text-gray-900">{{ request.document_type }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ request.status_description }}</p>
                                            <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                                                <span>Requested: {{ request.created_at }}</span>
                                                <span v-if="request.estimated_completion_date">
                                                    Est. Completion: {{ request.estimated_completion_date }}
                                                </span>
                                                <span v-if="request.completed_at">Completed: {{ request.completed_at }}</span>
                                            </div>
                                        </div>
                                        <button
                                            @click="copyToClipboard(request.tracking_id)"
                                            class="ml-4 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
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

