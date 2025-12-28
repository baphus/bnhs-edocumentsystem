<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { DocumentRequest, PaginatedData } from '@/types';

const props = defineProps<{
    requests: PaginatedData<DocumentRequest>;
    filters: {
        search?: string;
        status?: string;
        document_type_id?: number;
    };
    documentTypes: Array<{ id: number; name: string }>;
    gradeLevels?: Record<string, string>;
    trackStrands?: Record<string, Record<string, string>>;
    schoolYears?: Record<string, string>;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const documentTypeFilter = ref(props.filters.document_type_id || '');
const showCreateModal = ref(false);

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

watch([statusFilter, documentTypeFilter], applyFilters);

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
    router.get(route('admin.requests.index'));
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

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <div class="grid gap-4 sm:grid-cols-4">
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
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Showing {{ requests.data.length }} of {{ requests.total }} requests
                        </p>
                        <button
                            v-if="filters.search || filters.status || filters.document_type_id"
                            @click="clearFilters"
                            class="text-sm text-bnhs-blue hover:underline"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
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
                                    <Link
                                        :href="route('admin.requests.show', request.id)"
                                        class="font-mono font-medium text-bnhs-blue hover:underline"
                                    >
                                        {{ request.tracking_id }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ request.first_name }} {{ request.middle_name }} {{ request.last_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ request.student_email }}</p>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-500">
                                    {{ request.lrn }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ request.document_type?.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', getStatusColor(request.status)]">
                                        {{ request.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Link
                                        :href="route('admin.requests.show', request.id)"
                                        class="text-bnhs-blue hover:underline"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
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

