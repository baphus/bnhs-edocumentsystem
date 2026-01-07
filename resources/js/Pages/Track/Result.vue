<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    request: {
        tracking_id: string;
        full_name: string;
        email: string;
        lrn: string;
        grade_level: string;
        section: string | null;
        document_type: string;
        document_category: string;
        purpose: string;
        status: string;
        admin_remarks: string | null;
        created_at: string;
        updated_at: string;
        logs: Array<{
            action: string;
            old_value: string | null;
            new_value: string | null;
            description: string | null;
            created_at: string;
            user: string | null;
        }>;
    };
}>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'Pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Verified': 'bg-blue-100 text-blue-800 border-blue-200',
        'Processing': 'bg-purple-100 text-purple-800 border-purple-200',
        'Ready': 'bg-green-100 text-green-800 border-green-200',
        'Completed': 'bg-gray-100 text-gray-800 border-gray-200',
        'Rejected': 'bg-red-100 text-red-800 border-red-200',
    };
    return colors[status] || colors['Pending'];
};

// Document progress helpers (unified with User Dashboard)
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

// Legacy functions for backward compatibility
const getStatusStep = (status: string) => {
    const steps: Record<string, number> = {
        'Pending': 1,
        'Verified': 2,
        'Processing': 3,
        'Ready': 4,
        'Completed': 5,
        'Rejected': -1,
    };
    return steps[status] || 0;
};

const statusSteps = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed'];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const formatDateShort = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatAction = (action: string) => {
    const actionMap: Record<string, string> = {
        'status_change': 'Status Updated',
        'remark_updated': 'Remarks Updated',
        'request_created': 'Request Created',
    };
    // Fallback: convert snake_case to Title Case
    return actionMap[action] || action
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

const getStatusDescription = (status: string) => {
    const descriptions: Record<string, string> = {
        'Pending': 'Your request is currently under review.',
        'Verified': 'Your request has been verified and is ready for processing.',
        'Processing': 'Your document is currently being prepared.',
        'Ready': 'Your document is ready for pickup!',
        'Completed': 'Your request has been completed successfully.',
            'Rejected': 'Your request has been rejected. Please check the admin remarks for details.',
    };
    return descriptions[status] || 'Status updated.';
};
</script>

<template>
    <Head :title="`Tracking - ${request.tracking_id}`" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="font-semibold text-gray-900">eDocument System</span>
                    </Link>
                    <div class="flex gap-4">
                        <Link :href="route('track.index')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                            Track Another
                        </Link>
                        <Link :href="route('home')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                            ← Back to Home
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Document Progress Section -->
            <div class="mb-6">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Document Request Progress</h2>
                        <p class="mt-1 text-gray-600">Track the status of your document request</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-500">Tracking ID:</span>
                            <span class="font-mono text-sm font-semibold text-bnhs-blue">{{ request.tracking_id }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Progress</span>
                            <span class="text-sm font-medium text-gray-700">{{ Math.round(getProgressPercentage(request.status)) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div 
                                class="bg-gradient-to-r from-bnhs-blue to-bnhs-blue-600 h-3 rounded-full transition-all duration-500 ease-out"
                                :style="{ width: getProgressPercentage(request.status) + '%' }"
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
                                        isStepCompleted(index, request.status) && request.status !== 'Rejected'
                                            ? 'bg-green-500 border-green-500 text-white'
                                            : isStepActive(index, request.status) && request.status !== 'Rejected'
                                            ? 'bg-bnhs-blue border-bnhs-blue text-white'
                                            : request.status === 'Rejected'
                                            ? 'bg-red-500 border-red-500 text-white'
                                            : 'bg-gray-100 border-gray-300 text-gray-500'
                                    ]"
                                >
                                    <svg v-if="isStepCompleted(index, request.status) && request.status !== 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg v-else-if="request.status === 'Rejected'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span v-else class="text-sm font-medium">{{ index + 1 }}</span>
                                </div>
                                
                                <!-- Connector line -->
                                <div 
                                    v-if="index < progressSteps.length - 1"
                                    :class="[
                                        'h-0.5 w-8 ml-2 sm:hidden transition-all duration-300',
                                        isStepCompleted(index, request.status) && request.status !== 'Rejected'
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    ]"
                                ></div>
                            </div>
                            
                            <div class="flex-1 sm:flex-none">
                                <h3 
                                    :class="[
                                        'text-sm font-semibold transition-colors duration-300',
                                        isStepCompleted(index, request.status) && request.status !== 'Rejected'
                                            ? 'text-green-600'
                                            : isStepActive(index, request.status) && request.status !== 'Rejected'
                                            ? 'text-bnhs-blue'
                                            : request.status === 'Rejected'
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
                                    request.status === 'Rejected'
                                        ? 'bg-red-100 text-red-600'
                                        : request.status === 'Completed'
                                        ? 'bg-green-100 text-green-600'
                                        : 'bg-blue-100 text-bnhs-blue'
                                ]"
                            >
                                <svg v-if="request.status === 'Rejected'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <svg v-else-if="request.status === 'Completed'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">Current Status: {{ request.status }}</h4>
                                <div v-if="request.status === 'Rejected' && request.admin_remarks" class="mt-1">
                                    <p class="text-sm text-red-600 font-medium">Request Rejected</p>
                                    <p class="text-sm text-red-700">{{ request.admin_remarks }}</p>
                                </div>
                                <div v-else-if="request.status === 'Ready'" class="mt-1">
                                    <p class="text-sm text-green-600 font-medium">Your document is ready for pickup!</p>
                                    <p class="text-sm text-green-700">Please visit the registrar's office during office hours.</p>
                                </div>
                                <div v-else class="mt-1">
                                    <p class="text-sm text-gray-600">{{ getStatusDescription(request.status) }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Document Type: {{ request.document_type }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <!-- Student Information -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Student Information
                    </h3>

                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Full Name</dt>
                            <dd class="font-medium text-gray-900">{{ request.full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">LRN</dt>
                            <dd class="font-mono font-medium text-gray-900">{{ request.lrn }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="font-medium text-gray-900">{{ request.email }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Grade Level</dt>
                                <dd class="font-medium text-gray-900">{{ request.grade_level }}</dd>
                            </div>
                            <div v-if="request.section">
                                <dt class="text-sm text-gray-500">Section</dt>
                                <dd class="font-medium text-gray-900">{{ request.section }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- Document Information -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Document Information
                    </h3>

                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">Document Type</dt>
                            <dd class="font-medium text-gray-900">{{ request.document_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Category</dt>
                            <dd class="font-medium text-gray-900">{{ request.document_category }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Purpose</dt>
                            <dd class="font-medium text-gray-900">{{ request.purpose }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Date Submitted</dt>
                            <dd class="font-medium text-gray-900">{{ formatDateShort(request.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Last Updated</dt>
                            <dd class="font-medium text-gray-900">{{ formatDateShort(request.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Admin Remarks -->
            <div v-if="request.admin_remarks && request.status !== 'Rejected'" class="mt-6 rounded-xl bg-bnhs-blue-50 border border-bnhs-blue-200 p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-lg font-semibold text-bnhs-blue">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Admin Remarks
                </h3>
                <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ request.admin_remarks }}</p>
            </div>

            <!-- Activity Log -->
            <div v-if="request.logs && request.logs.length > 0" class="mt-6 rounded-xl bg-white p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Activity Timeline
                </h3>

                <div class="mt-4 space-y-4">
                    <div v-for="(log, index) in request.logs" :key="index" class="flex gap-4 border-l-2 border-gray-200 pl-4 pb-4">
                        <div class="flex-shrink-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-bnhs-blue-100">
                                <svg class="h-4 w-4 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-900">
                                    <span v-if="log.action === 'status_change' && log.old_value && log.new_value">
                                        Status changed from <span class="text-gray-600">{{ log.old_value }}</span> to <span class="font-semibold">{{ log.new_value }}</span>
                                    </span>
                                    <span v-else-if="log.action === 'status_change'">
                                        Status set to <span class="font-semibold">{{ log.new_value }}</span>
                                    </span>
                                    <span v-else>{{ formatAction(log.action) }}</span>
                                </p>
                                <span class="text-sm text-gray-500">{{ formatDate(log.created_at) }}</span>
                            </div>
                            <p v-if="log.description" class="mt-1 text-sm text-gray-600">{{ log.description }}</p>
                            <p v-if="log.user" class="mt-1 text-xs text-gray-500">by {{ log.user }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


