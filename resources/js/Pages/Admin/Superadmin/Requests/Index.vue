<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed } from 'vue';

interface Request {
    id: number;
    tracking_id: string;
    full_name: string;
    email: string;
    lrn: string;
    document_type: string;
    status: string;
    otp_verified: boolean;
    created_at: string;
}

interface Props {
    requests: {
        data: Request[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    documentTypes: { id: number; name: string }[];
    statuses: string[];
    filters: {
        search?: string;
        status?: string;
        document_type?: string;
        lrn?: string;
        from_date?: string;
        to_date?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    gradeLevels?: Record<string, string>;
    trackStrands?: Record<string, Record<string, string>>;
    schoolYears?: Record<string, string>;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const documentTypeFilter = ref(props.filters.document_type || '');
const fromDateFilter = ref(props.filters.from_date || '');
const toDateFilter = ref(props.filters.to_date || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref<'asc' | 'desc'>(props.filters.sort_direction || 'desc');
const selectedRequests = ref<number[]>([]);
const showCreateModal = ref(false);

const form = useForm({
    email: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    lrn: '',
    grade_level: '',
    section: '',
    track_strand: '',
    school_year_last_attended: '',
    purpose: '',
    photo: null as File | null,
    document_type_id: '',
    status: 'Pending',
    otp_verified: false,
});

const photoPreview = ref<string | null>(null);
const showTrackStrand = computed(() => {
    return form.grade_level === 'Grade 11' || form.grade_level === 'Grade 12';
});

const gradeLevels = props.gradeLevels || {};
const trackStrands = props.trackStrands || {};
const schoolYears = props.schoolYears || {};

const handlePhotoUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submitForm = () => {
    form.post(route('admin.superadmin.requests.store'), {
        forceFormData: true,
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
            photoPreview.value = null;
        },
    });
};

const closeModal = () => {
    showCreateModal.value = false;
    form.reset();
    photoPreview.value = null;
    form.clearErrors();
};

const applyFilters = () => {
    router.get(route('admin.superadmin.requests.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        document_type: documentTypeFilter.value || undefined,
        from_date: fromDateFilter.value || undefined,
        to_date: toDateFilter.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortDirection.value || undefined,
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

const toggleSelect = (requestId: number) => {
    const index = selectedRequests.value.indexOf(requestId);
    if (index > -1) {
        selectedRequests.value.splice(index, 1);
    } else {
        selectedRequests.value.push(requestId);
    }
};

const bulkAction = (action: string) => {
    if (selectedRequests.value.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (action === 'delete' && !confirm(`Delete ${selectedRequests.value.length} request(s)?`)) {
        return;
    }

    if (action === 'status_update') {
        const status = prompt('Enter new status (Pending, Verified, Processing, Ready, Completed, Rejected):');
        if (!status) return;
        router.post(route('admin.superadmin.requests.bulk'), {
            action: 'status_update',
            request_ids: selectedRequests.value,
            status,
        });
    } else {
        router.post(route('admin.superadmin.requests.bulk'), {
            action,
            request_ids: selectedRequests.value,
        });
    }
};

const exportCsv = () => {
    window.location.href = route('admin.superadmin.export.requests', props.filters);
};

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
</script>

<template>
    <Head title="Request Command Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Request Command Center
                </h2>
                <div class="flex gap-2">
                    <PrimaryButton @click="showCreateModal = true">
                        Create Request
                    </PrimaryButton>
                    <PrimaryButton @click="exportCsv">
                        Export CSV
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow">
                    <div class="grid gap-4 sm:grid-cols-6">
                        <div>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search..."
                                class="w-full"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                            >
                                <option value="">All Status</option>
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="documentTypeFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                            >
                                <option value="">All Document Types</option>
                                <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                                    {{ dt.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <input
                                type="date"
                                v-model="fromDateFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                                placeholder="From Date"
                            />
                        </div>
                        <div>
                            <input
                                type="date"
                                v-model="toDateFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                                placeholder="To Date"
                            />
                        </div>
                        <div>
                            <PrimaryButton @click="applyFilters" class="w-full">
                                Apply Filters
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedRequests.length > 0" class="mb-4 rounded-lg bg-bnhs-blue-50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-bnhs-blue">
                            {{ selectedRequests.length }} request(s) selected
                        </span>
                        <div class="flex gap-2">
                            <PrimaryButton @click="bulkAction('status_update')" size="sm">
                                Update Status
                            </PrimaryButton>
                            <PrimaryButton @click="bulkAction('resend_otp')" size="sm">
                                Resend OTP
                            </PrimaryButton>
                            <DangerButton @click="bulkAction('delete')" size="sm">
                                Delete
                            </DangerButton>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="selectedRequests.length === requests.data.length && requests.data.length > 0"
                                        @change="() => selectedRequests = selectedRequests.length === requests.data.length ? [] : requests.data.map(r => r.id)"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </th>
                                <th 
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
                                <th 
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
                                <th 
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
                                <th 
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
                                <th 
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
                                <th 
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
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="selectedRequests.includes(request.id)"
                                        @change="toggleSelect(request.id)"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link
                                        :href="route('admin.requests.show', request.id)"
                                        class="font-mono text-sm font-medium text-bnhs-blue hover:underline"
                                    >
                                        {{ request.tracking_id }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ request.full_name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ request.document_type }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(request.status)]"
                                    >
                                        {{ request.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            request.otp_verified
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{ request.otp_verified ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ new Date(request.created_at).toLocaleDateString() }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <Link
                                        :href="route('admin.requests.show', request.id)"
                                        class="text-bnhs-blue hover:text-bnhs-blue-600"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    No requests found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="requests.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing page {{ requests.current_page }} of {{ requests.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in requests.links"
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

        <!-- Create Request Modal -->
        <Modal :show="showCreateModal" @close="closeModal" max-width="2xl">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Create New Request</h2>
                
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Document Type and Status -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="document_type_id" value="Document Type *" />
                            <select
                                id="document_type_id"
                                v-model="form.document_type_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">Select Document Type</option>
                                <option v-for="dt in documentTypes" :key="dt.id" :value="dt.id">
                                    {{ dt.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.document_type_id" />
                        </div>
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email *" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Personal Information -->
                    <div>
                        <h3 class="mb-3 text-md font-medium text-gray-700">Personal Information</h3>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="first_name" value="First Name *" />
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    required
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>
                            <div>
                                <InputLabel for="middle_name" value="Middle Name" />
                                <TextInput
                                    id="middle_name"
                                    v-model="form.middle_name"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.middle_name" />
                            </div>
                            <div>
                                <InputLabel for="last_name" value="Last Name *" />
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    required
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>
                        </div>
                    </div>

                    <!-- LRN and Grade Level -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="lrn" value="LRN (12 digits) *" />
                            <TextInput
                                id="lrn"
                                v-model="form.lrn"
                                type="text"
                                maxlength="12"
                                pattern="[0-9]{12}"
                                required
                                class="mt-1 block w-full"
                                @input="(e: Event) => form.lrn = (e.target as HTMLInputElement).value.replace(/\D/g, '').slice(0, 12)"
                            />
                            <InputError :message="form.errors.lrn" />
                        </div>
                        <div>
                            <InputLabel for="grade_level" value="Grade Level *" />
                            <select
                                id="grade_level"
                                v-model="form.grade_level"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">Select Grade Level</option>
                                <option v-for="(label, value) in gradeLevels" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.grade_level" />
                        </div>
                    </div>

                    <!-- Section and Track/Strand -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="section" value="Section *" />
                            <TextInput
                                id="section"
                                v-model="form.section"
                                required
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.section" />
                        </div>
                        <div v-if="showTrackStrand">
                            <InputLabel for="track_strand" value="Track/Strand *" />
                            <select
                                id="track_strand"
                                v-model="form.track_strand"
                                :required="showTrackStrand"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">Select Track/Strand</option>
                                <template v-for="(tracks, category) in trackStrands" :key="category">
                                    <optgroup :label="category">
                                        <option v-for="(label, value) in tracks" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </optgroup>
                                </template>
                            </select>
                            <InputError :message="form.errors.track_strand" />
                        </div>
                    </div>

                    <!-- School Year -->
                    <div>
                        <InputLabel for="school_year_last_attended" value="School Year Last Attended *" />
                        <select
                            id="school_year_last_attended"
                            v-model="form.school_year_last_attended"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                        >
                            <option value="">Select School Year</option>
                            <option v-for="(label, value) in schoolYears" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.school_year_last_attended" />
                    </div>

                    <!-- Purpose -->
                    <div>
                        <InputLabel for="purpose" value="Purpose *" />
                        <textarea
                            id="purpose"
                            v-model="form.purpose"
                            required
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                        ></textarea>
                        <InputError :message="form.errors.purpose" />
                    </div>

                    <!-- Photo -->
                    <div>
                        <InputLabel for="photo" value="Photo (Optional)" />
                        <input
                            id="photo"
                            type="file"
                            accept="image/*"
                            @change="handlePhotoUpload"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-bnhs-blue file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-bnhs-blue-600"
                        />
                        <InputError :message="form.errors.photo" />
                        <div v-if="photoPreview" class="mt-2">
                            <img :src="photoPreview" alt="Preview" class="h-32 w-32 rounded-md object-cover" />
                        </div>
                    </div>

                    <!-- OTP Verified -->
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.otp_verified"
                                class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <span class="ml-2 text-sm text-gray-600">OTP Verified</span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            Create Request
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

