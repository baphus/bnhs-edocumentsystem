<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import RequestsTable from '@/Components/RequestsTable.vue';
import { DocumentRequest, PaginatedData } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => ['admin', 'superadmin'].includes(user.value?.role));
const isSuperadmin = computed(() => user.value?.role === 'superadmin');

const props = defineProps<{
    requests: PaginatedData<DocumentRequest>;
    filters: {
        search?: string;
        status?: string;
        document_type_id?: number;
        from_date?: string;
        to_date?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
        per_page?: number;
    };
    documentTypes: Array<{ id: number; name: string }>;
    gradeLevels?: Record<string, string>;
    trackStrands?: Record<string, Record<string, string>>;
    schoolYears?: Record<string, string>;
}>();

const showCreateModal = ref(false);
const requestsTable = ref();
const bulkStatus = ref('');
const bulkNotes = ref('');
const editingRequest = ref<number | null>(null);
const editForm = ref<Record<number, {
    first_name: string;    middle_name: string;    last_name: string;
    email: string;
    lrn: string;
    status: string;
}>>({});

const statuses = ['Pending', 'Verified', 'Processing', 'Ready', 'Completed', 'Rejected'];

onMounted(() => {
    editingRequest.value = null;
    editForm.value = {};
});

// Watch requests to ensure editing state is cleared when data changes
watch(() => props.requests.data, () => {
    editingRequest.value = null;
}, { deep: true });

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
    form.post(route('registrar.requests.store'), {
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
    const url = route('registrar.requests.export');
    window.location.href = params.toString() ? `${url}?${params.toString()}` : url;
};

const bulkUpdate = () => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
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
    if (!confirm(`Update ${updateText} for ${selectedRequests.length} request(s)?`)) {
        return;
    }

    router.post(route('registrar.requests.bulk-update'), {
        request_ids: selectedRequests,
        status: bulkStatus.value || undefined,
        admin_notes: bulkNotes.value || undefined,
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

const bulkDelete = () => {
    const selectedRequests = requestsTable.value?.selectedRequests || [];
    if (selectedRequests.length === 0) {
        alert('Please select at least one request.');
        return;
    }

    if (!confirm(`Are you sure you want to delete ${selectedRequests.length} request(s)? This action cannot be undone.`)) {
        return;
    }

    router.post(route('registrar.requests.bulk-delete'), {
        request_ids: selectedRequests,
    }, {
        onSuccess: () => {
            if (requestsTable.value) {
                requestsTable.value.clearSelection();
            }
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
    const url = route('registrar.requests.export');
    window.location.href = `${url}?${params.toString()}`;
};

const startEditing = (request: DocumentRequest) => {
    editingRequest.value = request.id;
    if (!editForm.value[request.id]) {
        editForm.value[request.id] = {
            first_name: request.first_name || '',
            middle_name: request.middle_name || '',
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

    router.patch(route('registrar.requests.update', requestId), form, {
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Manage Requests
            </h2>
        </template>

        <div class="py-8 pb-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Unified Requests Table -->
                <RequestsTable
                    ref="requestsTable"
                    :requests="requests"
                    :filters="filters"
                    :statuses="statuses"
                    :documentTypes="documentTypes"
                    :isSuperadmin="isAdmin"
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
                            <DangerButton v-if="isAdmin" @click="bulkDelete" class="whitespace-nowrap">
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
                    <template #table-rows="{ columnVisibility, isSelected, toggleSelect, getStatusColor, formatDate }">
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
                                    :href="route('registrar.requests.show', { id: request.id, ...filters })"
                                    class="font-mono font-medium text-bnhs-blue hover:underline"
                                >
                                    {{ request.tracking_id }}
                                </Link>
                            </td>
                            <td v-if="columnVisibility.requester" class="px-6 py-4">
                                <div v-if="!isSuperadmin || editingRequest !== request.id">
                                    <p class="font-medium text-gray-900">
                                        {{ request.first_name }} {{ request.middle_name ? request.middle_name.charAt(0) + '.' : '' }} {{ request.last_name }}
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
                                        v-model="editForm[request.id].middle_name"
                                        type="text"
                                        class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                        placeholder="Middle Name"
                                    />
                                    <input
                                        v-model="editForm[request.id].last_name"
                                        type="text"
                                        class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                        placeholder="Last Name"
                                    />
                                </div>
                            </td>
                            <td v-if="columnVisibility.lrn" class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-500">
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
                            <td v-if="columnVisibility.email" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                <span v-if="!isSuperadmin || editingRequest !== request.id">{{ request.student_email }}</span>
                                <input
                                    v-else
                                    v-model="editForm[request.id].email"
                                    type="email"
                                    class="w-full rounded border-gray-300 px-2 py-1 text-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    placeholder="Email"
                                />
                            </td>
                            <td v-if="columnVisibility.document" class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ request.document_type?.name }}
                            </td>
                            <td v-if="columnVisibility.signature" class="whitespace-nowrap px-6 py-4">
                                <img
                                    v-if="request.signature"
                                    :src="request.signature"
                                    :alt="`${request.first_name}'s signature`"
                                    class="h-10 max-w-xs rounded border border-gray-200 object-contain"
                                />
                                <span v-else class="text-gray-400 text-sm">No signature</span>
                            </td>
                            <td v-if="columnVisibility.status" class="whitespace-nowrap px-6 py-4">
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
                            <td v-if="columnVisibility.date" class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(request.created_at) }}
                            </td>
                            <td v-if="columnVisibility.actions" class="whitespace-nowrap px-6 py-4 text-right">
                                <div v-if="isSuperadmin && editingRequest !== request.id" class="flex justify-end gap-2">
                                    <button
                                        @click="startEditing(request)"
                                        class="text-bnhs-blue hover:underline text-sm"
                                    >
                                        Edit
                                    </button>
                                    <Link
                                        :href="route('registrar.requests.show', { id: request.id, ...filters })"
                                        class="text-bnhs-blue hover:underline text-sm"
                                    >
                                        View
                                    </Link>
                                </div>
                                <div v-else-if="!isSuperadmin" class="flex justify-end">
                                    <Link
                                        :href="route('registrar.requests.show', request.id)"
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


