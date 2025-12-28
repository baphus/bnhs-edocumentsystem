<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    result?: {
        tracking_id: string;
        status: string;
        document_type: string;
        student_name: string;
        date_submitted: string;
        last_updated: string;
        admin_notes?: string;
    };
    error?: string;
}>();

const form = useForm({
    tracking_id: '',
});

const formatTrackingId = (e: Event) => {
    const input = e.target as HTMLInputElement;
    let value = input.value.toUpperCase();
    // Allow BNHS-YYYY-XXXX format
    form.tracking_id = value;
};

const searchRequest = () => {
    form.get(route('track.show', { tracking_id: form.tracking_id }), {
        preserveScroll: true,
    });
};

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
</script>

<template>
    <Head title="Track Request" />

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
                    <Link :href="route('home')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </nav>

        <div class="mx-auto max-w-2xl px-4 py-12">
            <!-- Search Form -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue-100">
                        <svg class="h-8 w-8 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Track Your Request</h1>
                    <p class="mt-2 text-gray-600">Enter your tracking ID to check the status of your document request</p>
                </div>

                <form @submit.prevent="searchRequest" class="mt-8">
                    <div>
                        <label for="tracking_id" class="block text-sm font-medium text-gray-700">Tracking ID</label>
                        <div class="relative mt-1">
                            <input
                                id="tracking_id"
                                type="text"
                                :value="form.tracking_id"
                                @input="formatTrackingId"
                                required
                                placeholder="BNHS-2025-0001"
                                class="block w-full rounded-lg border-gray-300 py-3 pl-4 pr-12 text-lg font-mono uppercase shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing || !form.tracking_id"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-bnhs-blue p-2 text-white hover:bg-bnhs-blue-600 disabled:opacity-50"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Error Message -->
                <div v-if="error" class="mt-6 rounded-lg bg-red-50 p-4 text-center text-red-700">
                    <svg class="mx-auto h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 font-medium">{{ error }}</p>
                    <p class="mt-1 text-sm">Please check your tracking ID and try again.</p>
                </div>

                <!-- Result -->
                <div v-if="result" class="mt-8 rounded-xl border border-gray-200 p-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tracking ID</span>
                        <span :class="['rounded-full border px-3 py-1 text-sm font-medium', getStatusColor(result.status)]">
                            {{ result.status }}
                        </span>
                    </div>
                    <p class="mt-1 font-mono text-lg font-bold text-gray-900">{{ result.tracking_id }}</p>

                    <!-- Status Timeline -->
                    <div v-if="getStatusStep(result.status) !== -1" class="mt-6">
                        <div class="flex items-center justify-between">
                            <template v-for="(step, index) in statusSteps" :key="step">
                                <div class="flex flex-col items-center">
                                    <div
                                        :class="[
                                            'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium',
                                            getStatusStep(result.status) > index
                                                ? 'bg-green-500 text-white'
                                                : getStatusStep(result.status) === index + 1
                                                    ? 'bg-bnhs-blue text-white'
                                                    : 'bg-gray-200 text-gray-500'
                                        ]"
                                    >
                                        <svg v-if="getStatusStep(result.status) > index" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>
                                    <span class="mt-2 text-xs text-gray-600">{{ step }}</span>
                                </div>
                                <div
                                    v-if="index < statusSteps.length - 1"
                                    :class="[
                                        'h-1 flex-1 mx-2',
                                        getStatusStep(result.status) > index + 1 ? 'bg-green-500' : 'bg-gray-200'
                                    ]"
                                ></div>
                            </template>
                        </div>
                    </div>

                    <!-- Rejected Status -->
                    <div v-if="result.status === 'Rejected'" class="mt-6 rounded-lg bg-red-50 p-4 text-red-700">
                        <p class="font-medium">Request Rejected</p>
                        <p v-if="result.admin_notes" class="mt-1 text-sm">{{ result.admin_notes }}</p>
                    </div>

                    <!-- Details -->
                    <div class="mt-6 grid gap-4 border-t border-gray-100 pt-6 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500">Document Type</p>
                            <p class="font-medium text-gray-900">{{ result.document_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requester Name</p>
                            <p class="font-medium text-gray-900">{{ result.student_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date Submitted</p>
                            <p class="font-medium text-gray-900">{{ result.date_submitted }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="font-medium text-gray-900">{{ result.last_updated }}</p>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div v-if="result.admin_notes && result.status !== 'Rejected'" class="mt-6 rounded-lg bg-bnhs-blue-50 p-4">
                        <p class="text-sm font-medium text-bnhs-blue">Admin Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ result.admin_notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>Lost your tracking ID? Contact the registrar's office.</p>
                <p class="mt-1">📧 registrar@bnhs.edu.ph</p>
            </div>
        </div>
    </div>
</template>

