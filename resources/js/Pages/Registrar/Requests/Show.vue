<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DocumentRequest, RequestLog } from '@/types';

const props = defineProps<{
    request: DocumentRequest & {
        request_logs: RequestLog[];
    };
}>();

const showStatusModal = ref(false);
const showNotesModal = ref(false);

const statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

const statusForm = useForm({
    status: props.request.status,
});

const notesForm = useForm({
    admin_remarks: props.request.admin_remarks || '',
});

const updateStatus = () => {
    statusForm.patch(route('registrar.requests.update-status', props.request.id), {
        onSuccess: () => {
            showStatusModal.value = false;
        },
    });
};

const updateNotes = () => {
    notesForm.patch(route('registrar.requests.update-notes', props.request.id), {
        onSuccess: () => {
            showNotesModal.value = false;
        },
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

// Document progress helpers (unified system)
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
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
</script>

<template>
    <Head :title="`Request ${request.tracking_id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <button @click="router.visit(route('registrar.requests.index'))" type="button" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Request Details
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Tracking ID</p>
                            <p class="text-2xl font-bold text-gray-900">{{ request.tracking_id }}</p>
                        </div>
                        <span :class="['rounded-full border px-4 py-2 text-sm font-medium', getStatusColor(request.status)]">
                            {{ request.status }}
                        </span>
                    </div>

                    <!-- Compact Progress Section -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-900">Progress</h3>
                            <span class="text-sm font-medium text-gray-600">{{ Math.round(getProgressPercentage(request.status)) }}%</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                            <div 
                                class="bg-gradient-to-r from-bnhs-blue to-bnhs-blue-600 h-2 rounded-full transition-all duration-500 ease-out"
                                :style="{ width: getProgressPercentage(request.status) + '%' }"
                            ></div>
                        </div>

                        <!-- Compact Progress Steps -->
                        <div class="flex justify-between items-center">
                            <div 
                                v-for="(step, index) in progressSteps" 
                                :key="step.key"
                                class="flex flex-col items-center flex-1"
                            >
                                <div 
                                    :class="[
                                        'flex h-6 w-6 items-center justify-center rounded-full border transition-all duration-300 mb-1',
                                        isStepCompleted(index, request.status) && request.status !== 'Rejected'
                                            ? 'bg-green-500 border-green-500 text-white'
                                            : isStepActive(index, request.status) && request.status !== 'Rejected'
                                            ? 'bg-bnhs-blue border-bnhs-blue text-white'
                                            : request.status === 'Rejected'
                                            ? 'bg-red-500 border-red-500 text-white'
                                            : 'bg-gray-100 border-gray-300 text-gray-500'
                                    ]"
                                >
                                    <svg v-if="isStepCompleted(index, request.status) && request.status !== 'Rejected'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg v-else-if="request.status === 'Rejected'" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span v-else class="text-xs font-medium">{{ index + 1 }}</span>
                                </div>
                                <span 
                                    :class="[
                                        'text-xs font-medium text-center',
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
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button
                            @click="showStatusModal = true"
                            class="flex items-center gap-2 rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Update Status
                        </button>
                        <button
                            @click="showNotesModal = true"
                            class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ request.admin_remarks ? 'Edit Remarks' : 'Add Remarks' }}
                        </button>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <!-- Student Information -->
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h3 class="flex items-center gap-2 font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Student Information
                        </h3>

                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-sm text-gray-500">Full Name</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ request.first_name }} {{ request.middle_name }} {{ request.last_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">LRN</dt>
                                <dd class="font-mono font-medium text-gray-900">{{ request.lrn }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Email</dt>
                                <dd class="font-medium text-gray-900">{{ request.student_email }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm text-gray-500">Grade Level</dt>
                                    <dd class="font-medium text-gray-900">{{ request.grade_level }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Section</dt>
                                    <dd class="font-medium text-gray-900">{{ request.section || 'N/A' }}</dd>
                                </div>
                            </div>
                            <div v-if="request.track_strand">
                                <dt class="text-sm text-gray-500">Track/Strand</dt>
                                <dd class="font-medium text-gray-900">{{ request.track_strand }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">School Year Last Attended</dt>
                                <dd class="font-medium text-gray-900">{{ request.school_year_last_attended }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Document Information -->
                    <div class="space-y-6">
                        <div class="rounded-xl bg-white p-6 shadow">
                            <h3 class="flex items-center gap-2 font-semibold text-gray-900">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Document Details
                            </h3>

                            <dl class="mt-4 space-y-4">
                                <div>
                                    <dt class="text-sm text-gray-500">Document Type</dt>
                                    <dd class="font-medium text-gray-900">{{ request.document_type?.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Purpose</dt>
                                    <dd class="font-medium text-gray-900">{{ request.purpose || 'Not specified' }}</dd>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm text-gray-500">Date Submitted</dt>
                                        <dd class="font-medium text-gray-900">{{ formatDate(request.created_at) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Last Updated</dt>
                                        <dd class="font-medium text-gray-900">{{ formatDate(request.updated_at) }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <!-- 2x2 Photo -->
                        <div v-if="request.photo_path" class="rounded-xl bg-white p-6 shadow">
                            <h3 class="flex items-center gap-2 font-semibold text-gray-900">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                2x2 Photo
                            </h3>
                            <div class="mt-4">
                                <img
                                    :src="`/storage/${request.photo_path}`"
                                    :alt="`${request.first_name}'s photo`"
                                    class="h-32 w-32 rounded-lg border object-cover shadow"
                                />
                            </div>
                        </div>

                        <!-- Digital Signature -->
                        <div v-if="request.signature" class="rounded-xl bg-white p-6 shadow">
                            <h3 class="flex items-center gap-2 font-semibold text-gray-900">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Digital Signature
                            </h3>
                            <div class="mt-4">
                                <img
                                    :src="request.signature"
                                    alt="Student's signature"
                                    class="max-h-24 max-w-full rounded-lg border border-gray-200 object-contain p-2"
                                />
                            </div>
                        </div>

                        <!-- Admin Remarks -->
                        <div v-if="request.admin_remarks" class="rounded-xl bg-bnhs-blue-50 p-6 shadow">
                            <h3 class="flex items-center gap-2 font-semibold text-bnhs-blue">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                Admin Remarks
                            </h3>
                            <p class="mt-2 text-gray-700">{{ request.admin_remarks }}</p>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="mt-6 rounded-xl bg-white p-6 shadow">
                    <h3 class="flex items-center gap-2 font-semibold text-gray-900">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Activity Log
                    </h3>

                    <div class="mt-4 space-y-4">
                        <div
                            v-for="log in request.request_logs"
                            :key="log.id"
                            class="flex items-start gap-4 border-l-2 border-gray-200 pl-4"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900">{{ formatAction(log.action) }}</p>
                                    <span v-if="log.old_value && log.new_value && log.action === 'status_change'" class="text-xs text-gray-500">
                                        ({{ log.old_value }} → {{ log.new_value }})
                                    </span>
                                </div>
                                <p v-if="log.description" class="mt-1 text-sm text-gray-600">{{ log.description }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ formatDate(log.created_at) }}
                                    <span v-if="log.user"> · by {{ log.user.name }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-if="!request.request_logs?.length" class="text-center text-gray-500">
                            No activity logged yet
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <Teleport to="body">
            <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showStatusModal = false"></div>
                    <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                        <h3 class="text-lg font-semibold text-gray-900">Update Status</h3>
                        <form @submit.prevent="updateStatus" class="mt-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">New Status</label>
                                <select
                                    id="status"
                                    v-model="statusForm.status"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option v-for="status in statuses" :key="status" :value="status">
                                        {{ status }}
                                    </option>
                                </select>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showStatusModal = false"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="statusForm.processing"
                                    class="rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600 disabled:opacity-50"
                                >
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Remarks Modal -->
        <Teleport to="body">
            <div v-if="showNotesModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showNotesModal = false"></div>
                    <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                        <h3 class="text-lg font-semibold text-gray-900">Admin Remarks</h3>
                        <form @submit.prevent="updateNotes" class="mt-4">
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea
                                    id="remarks"
                                    v-model="notesForm.admin_remarks"
                                    rows="4"
                                    placeholder="Add remarks visible to the student..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                ></textarea>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showNotesModal = false"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="notesForm.processing"
                                    class="rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600 disabled:opacity-50"
                                >
                                    Save Remarks
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>


