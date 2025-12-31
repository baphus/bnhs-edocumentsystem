<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { DocumentRequest, PaginatedData } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperadmin = computed(() => user.value?.role === 'superadmin');

const props = defineProps<{
    requests: PaginatedData<DocumentRequest>;
    filters: {
        search?: string;
        status?: string;
        document_type_id?: number;
        from_date?: string;
        to_date?: string;
    };
    documentTypes: Array<{ id: number; name: string }>;
    gradeLevels?: Record<string, string>;
    trackStrands?: Record<string, Record<string, string>>;
    schoolYears?: Record<string, string>;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const documentTypeFilter = ref(props.filters.document_type_id || '');
const fromDateFilter = ref(props.filters.from_date || '');
const toDateFilter = ref(props.filters.to_date || '');
const showCreateModal = ref(false);
const selectedRequests = ref<number[]>([]);
const bulkStatus = ref('');
const bulkNotes = ref('');
const editingRequest = ref<number | null>(null);
const editForm = ref<Record<number, {
    first_name: string;
    last_name: string;
    email: string;
    lrn: string;
    status: string;
}>>({});

const statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

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
    form.post(route('admin.requests.store'), {
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
    router.get(route('admin.requests.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        document_type_id: documentTypeFilter.value || undefined,
        from_date: fromDateFilter.value || undefined,
        to_date: toDateFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([statusFilter, documentTypeFilter, fromDateFilter, toDateFilter], applyFilters);

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

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    documentTypeFilter.value = '';
    fromDateFilter.value = '';
    toDateFilter.value = '';
    router.get(route('admin.requests.index'));
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
        selectedRequests.value = props.requests.data.map((r: DocumentRequest) => r.id);
    }
};

const isSelected = (requestId: number) => {
    return selectedRequests.value.includes(requestId);
};

const isAllSelected = computed(() => {
    return props.requests.data.length > 0 && selectedRequests.value.length === props.requests.data.length;
});

const bulkUpdate = () => {
    if (selectedRequests.value.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    // Check if at least one field is filled
    if (!bulkStatus.value && !bulkNotes.value) {
        alert('Please select a status or enter notes to update.');
        return;
    }

    const updates: string[] = [];
    if (bulkStatus.value) updates.push(`status to "${bulkStatus.value}"`);
    if (bulkNotes.value) updates.push('notes');

    const updateText = updates.join(' and ');
    if (!confirm(`Update ${updateText} for ${selectedRequests.value.length} request(s)?`)) {
        return;
    }

    router.post(route('admin.requests.bulk-update'), {
        request_ids: selectedRequests.value,
        status: bulkStatus.value || undefined,
        admin_notes: bulkNotes.value || undefined,
    }, {
        onSuccess: () => {
            selectedRequests.value = [];
            bulkStatus.value = '';
            bulkNotes.value = '';
        },
    });
};

const startEditing = (request: DocumentRequest) => {
    editingRequest.value = request.id;
    if (!editForm.value[request.id]) {
        editForm.value[request.id] = {
            first_name: request.first_name || '',
            last_name: request.last_name || '',
            email: request.student_email || '',
            lrn: request.lrn || '',
            status: request.status || 'Pending',
        };
    }
};

const cancelEditing = (requestId: number) => {
    editingRequest.value = null;
    delete editForm.value[requestId];
};

const saveEdit = (requestId: number) => {
    const form = editForm.value[requestId];
    if (!form) return;

    router.patch(route('admin.requests.update', requestId), form, {
        preserveScroll: true,
        onSuccess: () => {
            editingRequest.value = null;
            delete editForm.value[requestId];
        },
    });
};
</script>

<template>
    <Head title="Manage Requests" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manage Requests
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Create Request
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8 pb-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <div class="grid gap-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input
                                id="search"
                                type="text"
                                v-model="search"
                                placeholder="Search by tracking ID, name, or LRN..."
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                id="status"
                                v-model="statusFilter"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">All Statuses</option>
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="docType" class="block text-sm font-medium text-gray-700">Document Type</label>
                            <select
                                id="docType"
                                v-model="documentTypeFilter"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">All Types</option>
                                <option v-for="docType in documentTypes" :key="docType.id" :value="docType.id">
                                    {{ docType.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="fromDate" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input
                                id="fromDate"
                                type="date"
                                v-model="fromDateFilter"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                        </div>
                        <div>
                            <label for="toDate" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input
                                id="toDate"
                                type="date"
                                v-model="toDateFilter"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Showing {{ requests.data.length }} of {{ requests.total }} requests
                        </p>
                        <button
                            v-if="filters.search || filters.status || filters.document_type_id || filters.from_date || filters.to_date"
                            @click="clearFilters"
                            class="text-sm text-bnhs-blue hover:underline"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedRequests.length > 0" class="mt-6 rounded-lg bg-bnhs-blue-50 p-4">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-bnhs-blue">
                                {{ selectedRequests.length }} request(s) selected
                            </span>
                            <button
                                @click="selectedRequests = []; bulkStatus = ''; bulkNotes = ''"
                                class="text-sm text-gray-600 hover:text-gray-800"
                            >
                                Clear Selection
                            </button>
                        </div>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Bulk Status Update -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                                <select
                                    v-model="bulkStatus"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">Select Status</option>
                                    <option v-for="status in statuses" :key="status" :value="status">
                                        {{ status }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Bulk Notes Update -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Update Notes</label>
                                <textarea
                                    v-model="bulkNotes"
                                    placeholder="Add notes for selected requests..."
                                    rows="2"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue text-sm"
                                ></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <PrimaryButton 
                                @click="bulkUpdate" 
                                :disabled="!bulkStatus && !bulkNotes"
                            >
                                Apply Changes
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-12 px-6 py-3">
                                    <input
                                        type="checkbox"
                                        :checked="isAllSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Tracking ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Requester
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    LRN
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Document
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Date
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
                                        :checked="isSelected(request.id)"
                                        @change="toggleSelect(request.id)"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link
                                        :href="route('admin.requests.show', request.id)"
                                        class="font-mono font-medium text-bnhs-blue hover:underline"
                                    >
                                        {{ request.tracking_id }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div v-if="!isSuperadmin || editingRequest !== request.id">
                                        <p class="font-medium text-gray-900">
                                            {{ request.first_name }} {{ request.middle_name }} {{ request.last_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ request.student_email }}</p>
                                    </div>
                                    <div v-else class="space-y-1">
                                        <input
                                            v-model="editForm[request.id].first_name"
                                            type="text"
                                            class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                            placeholder="First Name"
                                        />
                                        <input
                                            v-model="editForm[request.id].last_name"
                                            type="text"
                                            class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                            placeholder="Last Name"
                                        />
                                        <input
                                            v-model="editForm[request.id].email"
                                            type="email"
                                            class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                            placeholder="Email"
                                        />
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-500">
                                    <span v-if="!isSuperadmin || editingRequest !== request.id">{{ request.lrn }}</span>
                                    <input
                                        v-else
                                        v-model="editForm[request.id].lrn"
                                        type="text"
                                        maxlength="12"
                                        class="w-24 rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                        placeholder="LRN"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ request.document_type?.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span v-if="!isSuperadmin || editingRequest !== request.id" :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(request.status)]">
                                        {{ request.status }}
                                    </span>
                                    <select
                                        v-else
                                        v-model="editForm[request.id].status"
                                        class="rounded border-gray-300 px-2 py-1 text-xs focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    >
                                        <option v-for="status in statuses" :key="status" :value="status">
                                            {{ status }}
                                        </option>
                                    </select>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div v-if="!isSuperadmin" class="flex justify-end">
                                        <Link
                                            :href="route('admin.requests.show', request.id)"
                                            class="text-bnhs-blue hover:underline"
                                        >
                                            View
                                        </Link>
                                    </div>
                                    <div v-else-if="editingRequest !== request.id" class="flex justify-end gap-2">
                                        <button
                                            @click="startEditing(request)"
                                            class="text-bnhs-blue hover:underline text-sm"
                                        >
                                            Edit
                                        </button>
                                        <Link
                                            :href="route('admin.requests.show', request.id)"
                                            class="text-bnhs-blue hover:underline text-sm"
                                        >
                                            View
                                        </Link>
                                    </div>
                                    <div v-else class="flex justify-end gap-2">
                                        <button
                                            @click="saveEdit(request.id)"
                                            class="text-green-600 hover:text-green-800 text-sm font-medium"
                                        >
                                            Save
                                        </button>
                                        <button
                                            @click="cancelEditing(request.id)"
                                            class="text-gray-600 hover:text-gray-800 text-sm"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="mt-4 font-medium">No requests found</p>
                                    <p class="mt-1 text-sm">Try adjusting your search or filters</p>
                                </td>
                            </tr>
                        </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="requests.last_page > 1" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-500">
                                    Page {{ requests.current_page }} of {{ requests.last_page }}
                                </p>
                                <div class="flex gap-2">
                                    <Link
                                        v-if="requests.prev_page_url"
                                        :href="requests.prev_page_url"
                                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                        preserve-scroll
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="requests.next_page_url"
                                        :href="requests.next_page_url"
                                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                        preserve-scroll
                                    >
                                        Next
                                    </Link>
                                </div>
                            </div>
                        </div>
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

