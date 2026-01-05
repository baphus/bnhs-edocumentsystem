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
import RequestsTable from '@/Components/RequestsTable.vue';
import { ref, computed, watch } from 'vue';

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
        from?: number;
        to?: number;
        total?: number;
        prev_page_url?: string;
        next_page_url?: string;
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
        per_page?: number;
    };
    gradeLevels?: Record<string, string>;
    trackStrands?: Record<string, Record<string, string>>;
    schoolYears?: Record<string, string>;
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const requestsTable = ref();
const bulkStatus = ref('');
const bulkNotes = ref('');

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

const exportCsv = () => {
    const params = new URLSearchParams();
    Object.entries(props.filters).forEach(([key, value]) => {
        if (value) params.append(key, String(value));
    });
    const url = route('admin.requests.export');
    window.location.href = params.toString() ? `${url}?${params.toString()}` : url;
};

const bulkUpdate = () => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (!bulkStatus.value && !bulkNotes.value) {
        alert('Please select a status or enter notes to update.');
        return;
    }

    const updates: string[] = [];
    if (bulkStatus.value) updates.push(`status to "${bulkStatus.value}"`);
    if (bulkNotes.value) updates.push('notes');

    const updateText = updates.join(' and ');
    if (!confirm(`Update ${updateText} for ${selectedRequests.length} request(s)?`)) {
        return;
    }

    const action = bulkStatus.value && bulkNotes.value ? 'status_and_notes' : 
                   bulkStatus.value ? 'status_update' : 'add_notes';

    router.post(route('admin.requests.bulk'), {
        action,
        request_ids: selectedRequests,
        status: bulkStatus.value || undefined,
        notes: bulkNotes.value || undefined,
    }, {
        onSuccess: () => {
            if (requestsTable.value) {
                requestsTable.value.clearSelection();
            }
            bulkStatus.value = '';
            bulkNotes.value = '';
        },
    });
};

const bulkAction = (action: string) => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (action === 'delete' && !confirm(`Delete ${selectedRequests.length} request(s)?`)) {
        return;
    }

    router.post(route('admin.requests.bulk'), {
        action,
        request_ids: selectedRequests,
    }, {
        onSuccess: () => {
            if (requestsTable.value) {
                requestsTable.value.clearSelection();
            }
        },
    });
};

const updateBulkStatus = () => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (!bulkStatus.value) {
        alert('Please select a status.');
        return;
    }

    if (!confirm(`Update status to "${bulkStatus.value}" for ${selectedRequests.length} request(s)?`)) {
        return;
    }

    router.post(route('admin.requests.bulk'), {
        action: 'status_update',
        request_ids: selectedRequests,
        status: bulkStatus.value,
    }, {
        onSuccess: () => {
            if (requestsTable.value) {
                requestsTable.value.clearSelection();
            }
            bulkStatus.value = '';
        },
    });
};

const updateBulkNotes = () => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (!bulkNotes.value) {
        alert('Please enter notes.');
        return;
    }

    router.post(route('admin.requests.bulk'), {
        action: 'add_notes',
        request_ids: selectedRequests,
        notes: bulkNotes.value,
    }, {
        onSuccess: () => {
            if (requestsTable.value) {
                requestsTable.value.clearSelection();
            }
            bulkNotes.value = '';
        },
    });
};

const bulkExport = (selectedRequests: number[]) => {
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    const params = new URLSearchParams();
    selectedRequests.forEach(id => params.append('ids[]', id.toString()));
    const url = route('admin.requests.export');
    window.location.href = `${url}?${params.toString()}`;
};
</script>

<template>
    <Head title="Manage Requests" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Manage Requests
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Unified Requests Table -->
                <RequestsTable
                    ref="requestsTable"
                    :requests="requests"
                    :filters="filters"
                    :statuses="statuses"
                    :documentTypes="documentTypes"
                    :isSuperadmin="true"
                    routePrefix="registrar.requests"
                    @export="exportCsv"
                    @createRequest="showCreateModal = true"
                >
                    <!-- Bulk Actions Slot -->
                    <template #bulk-actions="{ selectedRequests }">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">
                                {{ selectedRequests.length }} selected
                            </span>
                            <select
                                v-model="bulkStatus"
                                class="rounded-lg border-gray-300 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option value="">Update Status</option>
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                            <input
                                v-model="bulkNotes"
                                type="text"
                                placeholder="Add notes..."
                                class="rounded-lg border-gray-300 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <PrimaryButton 
                                @click="bulkUpdate" 
                                :disabled="!bulkStatus && !bulkNotes"
                                class="whitespace-nowrap"
                            >
                                Apply
                            </PrimaryButton>
                            <DangerButton @click="bulkAction('delete')" class="whitespace-nowrap">
                                Delete
                            </DangerButton>
                            <SecondaryButton @click="bulkExport(selectedRequests)" class="whitespace-nowrap">
                                Export
                            </SecondaryButton>
                            <button
                                @click="requestsTable.clearSelection(); bulkStatus = ''; bulkNotes = ''"
                                class="ml-2 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Table Rows Slot -->
                    <template #table-rows="{ columnVisibility, isSelected, toggleSelect, getStatusColor }">
                        <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50">
                            <td v-if="columnVisibility.checkbox" class="whitespace-nowrap px-6 py-4">
                                <input
                                    type="checkbox"
                                    :checked="isSelected(request.id)"
                                    @change="toggleSelect(request.id)"
                                    class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                />
                            </td>
                            <td v-if="columnVisibility.tracking_id" class="whitespace-nowrap px-6 py-4">
                                <Link
                                    :href="route('admin.requests.show', { id: request.id, ...filters })"
                                    class="font-mono text-sm font-medium text-bnhs-blue hover:underline"
                                >
                                    {{ request.tracking_id }}
                                </Link>
                            </td>
                            <td v-if="columnVisibility.requester" class="px-6 py-4 text-sm text-gray-900">
                                <p class="font-medium">{{ request.full_name }}</p>
                                <p class="text-sm text-gray-500">{{ request.email }}</p>
                            </td>
                            <td v-if="columnVisibility.lrn" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 font-mono">
                                {{ request.lrn }}
                            </td>
                            <td v-if="columnVisibility.email" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ request.email }}
                            </td>
                            <td v-if="columnVisibility.document" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ request.document_type }}
                            </td>
                            <td v-if="columnVisibility.status" class="whitespace-nowrap px-6 py-4">
                                <span
                                    :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(request.status)]"
                                >
                                    {{ request.status }}
                                </span>
                            </td>
                            <td v-if="columnVisibility.otp_verified" class="whitespace-nowrap px-6 py-4">
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
                            <td v-if="columnVisibility.date" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ new Date(request.created_at).toLocaleDateString() }}
                            </td>
                            <td v-if="columnVisibility.actions" class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <button
                                    @click="router.visit(route('admin.requests.show', { id: request.id, ...filters }))"
                                    class="text-bnhs-blue hover:text-bnhs-blue-600 mr-3"
                                >
                                    Edit
                                </button>
                                <Link
                                    :href="route('admin.requests.show', { id: request.id, ...filters })"
                                    class="text-bnhs-blue hover:text-bnhs-blue-600"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </template>
                </RequestsTable>
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


