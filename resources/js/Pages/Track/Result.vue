<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
        admin_notes: string | null;
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
</script>

<template>
    <Head :title="`Tracking - ${request.tracking_id}`" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue">
                            <span class="text-sm font-bold text-white">BNHS</span>
                        </div>
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
            <!-- Status Header Card -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Tracking ID</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 font-mono">{{ request.tracking_id }}</p>
                    </div>
                    <span :class="['rounded-full border px-4 py-2 text-sm font-medium', getStatusColor(request.status)]">
                        {{ request.status }}
                    </span>
                </div>

                <!-- Status Timeline -->
                <div v-if="getStatusStep(request.status) !== -1" class="mt-8">
                    <div class="flex items-center justify-between">
                        <template v-for="(step, index) in statusSteps" :key="step">
                            <div class="flex flex-col items-center">
                                <div
                                    :class="[
                                        'flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium',
                                        getStatusStep(request.status) > index + 1
                                            ? 'bg-green-500 text-white'
                                            : getStatusStep(request.status) === index + 1
                                                ? 'bg-bnhs-blue text-white'
                                                : 'bg-gray-200 text-gray-500'
                                    ]"
                                >
                                    <svg v-if="getStatusStep(request.status) > index + 1" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span class="mt-2 text-xs font-medium text-gray-700">{{ step }}</span>
                            </div>
                            <div
                                v-if="index < statusSteps.length - 1"
                                :class="[
                                    'h-1 flex-1 mx-2',
                                    getStatusStep(request.status) > index + 1 ? 'bg-green-500' : 'bg-gray-200'
                                ]"
                            ></div>
                        </template>
                    </div>
                </div>

                <!-- Rejected Status Message -->
                <div v-if="request.status === 'Rejected'" class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4">
                    <p class="font-medium text-red-800">Request Rejected</p>
                    <p v-if="request.admin_notes" class="mt-1 text-sm text-red-700">{{ request.admin_notes }}</p>
                </div>

                <!-- Ready Status Message -->
                <div v-else-if="request.status === 'Ready'" class="mt-6 rounded-lg bg-green-50 border border-green-200 p-4">
                    <p class="font-medium text-green-800">Your document is ready for pickup!</p>
                    <p class="mt-1 text-sm text-green-700">Please visit the registrar's office during office hours.</p>
                </div>
            </div>

            <!-- Request Details -->
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <!-- Student Information -->
                <div class="rounded-xl bg-white p-6 shadow">
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
                <div class="rounded-xl bg-white p-6 shadow">
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

            <!-- Admin Notes -->
            <div v-if="request.admin_notes && request.status !== 'Rejected'" class="mt-6 rounded-xl bg-bnhs-blue-50 border border-bnhs-blue-200 p-6 shadow">
                <h3 class="flex items-center gap-2 text-lg font-semibold text-bnhs-blue">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Admin Notes
                </h3>
                <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ request.admin_notes }}</p>
            </div>

            <!-- Activity Log -->
            <div v-if="request.logs && request.logs.length > 0" class="mt-6 rounded-xl bg-white p-6 shadow">
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
                                    <span v-else>{{ log.action }}</span>
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

