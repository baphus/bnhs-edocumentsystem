<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { DocumentType } from '@/types';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    documentType: DocumentType;
    email: string;
}>();

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    lrn: '',
    grade_level: '',
    section: '',
    track_strand: '',
    school_year_last_attended: '',
    purpose: '',
    purpose_other: '',
    quantity: 1,
    photo: null as File | null,
    document_type_id: props.documentType.id,
    email: props.email,
});

const photoPreview = ref<string | null>(null);
const lrnError = ref('');

const gradeLevels = [
    { value: 'Grade 7', label: 'Grade 7' },
    { value: 'Grade 8', label: 'Grade 8' },
    { value: 'Grade 9', label: 'Grade 9' },
    { value: 'Grade 10', label: 'Grade 10' },
    { value: 'Grade 11', label: 'Grade 11' },
    { value: 'Grade 12', label: 'Grade 12' },
];

const trackStrands = [
    { value: 'ABM', label: 'Accountancy, Business & Management (ABM)' },
    { value: 'HUMSS', label: 'Humanities and Social Sciences (HUMSS)' },
    { value: 'STEM', label: 'Science, Technology, Engineering & Mathematics (STEM)' },
    { value: 'GAS', label: 'General Academic Strand (GAS)' },
    { value: 'TVL-ICT', label: 'TVL - Information & Communication Technology' },
    { value: 'TVL-HE', label: 'TVL - Home Economics' },
    { value: 'TVL-IA', label: 'TVL - Industrial Arts' },
    { value: 'TVL-AFA', label: 'TVL - Agri-Fishery Arts' },
];

const purposeOptions = [
    { value: 'College Enrollment', label: 'College Enrollment' },
    { value: 'Job Application', label: 'Job Application' },
    { value: 'Scholarship Application', label: 'Scholarship Application' },
    { value: 'Transfer to Another School', label: 'Transfer to Another School' },
    { value: 'Personal Records', label: 'Personal Records' },
    { value: 'Other', label: 'Other' },
];

const showPurposeOther = computed(() => {
    return form.purpose === 'Other';
});

const showTrackStrand = computed(() => {
    return form.grade_level === 'Grade 11' || form.grade_level === 'Grade 12';
});

const currentSchoolYear = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    // If current month is June or later, current SY is YYYY-YYYY+1
    if (month >= 6) {
        return `${year}-${year + 1}`;
    }
    return `${year - 1}-${year}`;
});

const schoolYears = computed(() => {
    const years = [];
    const now = new Date();
    const currentYear = now.getFullYear();
    for (let i = 0; i < 10; i++) {
        const startYear = currentYear - i;
        years.push(`${startYear}-${startYear + 1}`);
    }
    return years;
});

watch(() => form.grade_level, (newValue) => {
    if (!showTrackStrand.value) {
        form.track_strand = '';
    }
});

watch(() => form.purpose, (newValue) => {
    if (newValue !== 'Other') {
        form.purpose_other = '';
    }
});

const validateLrn = () => {
    if (form.lrn && !/^\d{12}$/.test(form.lrn)) {
        lrnError.value = 'LRN must be exactly 12 digits';
    } else {
        lrnError.value = '';
    }
};

const formatLrn = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/\D/g, '').slice(0, 12);
    form.lrn = input.value;
    validateLrn();
};

const handlePhotoUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    
    if (file) {
        // Validate file type
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            form.errors.photo = 'Please upload a JPG or PNG image';
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            form.errors.photo = 'Image must be less than 2MB';
            return;
        }
        
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
};

const submitRequest = () => {
    // Use purpose_other if "Other" is selected, otherwise use the selected purpose
    const finalPurpose = form.purpose === 'Other' ? form.purpose_other : form.purpose;
    
    form.transform((data) => ({
        ...data,
        purpose: finalPurpose,
    })).post(route('request.submit'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Request Form" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="font-semibold text-gray-900">eDocument System</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Progress Steps -->
        <div class="mx-auto max-w-4xl px-4 py-8">
            <div class="mb-8 flex items-center justify-center">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="ml-2 text-green-600">Select Document</span>
                </div>
                <div class="mx-4 h-1 w-16 bg-green-500"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="ml-2 text-green-600">Verify Email</span>
                </div>
                <div class="mx-4 h-1 w-16 bg-bnhs-blue"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue text-white">
                        3
                    </div>
                    <span class="ml-2 font-medium text-bnhs-blue">Fill Form</span>
                </div>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <!-- Header -->
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Document Request Form</h1>
                            <p class="mt-1 text-gray-600">Fill in your details accurately</p>
                        </div>
                        <div class="rounded-full bg-bnhs-blue-100 px-4 py-2 text-sm font-medium text-bnhs-blue">
                            {{ documentType.name }}
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Email verified: {{ email }}
                    </div>
                </div>

                <form @submit.prevent="submitRequest" class="space-y-8">
                    <!-- Personal Information -->
                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Personal Information</h2>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                <input
                                    id="first_name"
                                    type="text"
                                    v-model="form.first_name"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input
                                    id="middle_name"
                                    type="text"
                                    v-model="form.middle_name"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p v-if="form.errors.middle_name" class="mt-1 text-sm text-red-600">{{ form.errors.middle_name }}</p>
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                <input
                                    id="last_name"
                                    type="text"
                                    v-model="form.last_name"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Student Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="lrn" class="block text-sm font-medium text-gray-700">
                                    LRN (Learner Reference Number) *
                                </label>
                                <input
                                    id="lrn"
                                    type="text"
                                    :value="form.lrn"
                                    @input="formatLrn"
                                    required
                                    maxlength="12"
                                    placeholder="12-digit LRN"
                                    class="mt-1 block w-full rounded-lg border-gray-300 font-mono shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p v-if="lrnError || form.errors.lrn" class="mt-1 text-sm text-red-600">
                                    {{ lrnError || form.errors.lrn }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">{{ form.lrn.length }}/12 digits</p>
                            </div>
                            <div>
                                <label for="grade_level" class="block text-sm font-medium text-gray-700">Grade Level *</label>
                                <select
                                    id="grade_level"
                                    v-model="form.grade_level"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">Select Grade Level</option>
                                    <option v-for="grade in gradeLevels" :key="grade.value" :value="grade.value">
                                        {{ grade.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.grade_level" class="mt-1 text-sm text-red-600">{{ form.errors.grade_level }}</p>
                            </div>
                            <div>
                                <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                                <input
                                    id="section"
                                    type="text"
                                    v-model="form.section"
                                    placeholder="e.g., A, B, Orchid"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p v-if="form.errors.section" class="mt-1 text-sm text-red-600">{{ form.errors.section }}</p>
                            </div>
                            <div v-if="showTrackStrand">
                                <label for="track_strand" class="block text-sm font-medium text-gray-700">
                                    Track/Strand *
                                </label>
                                <select
                                    id="track_strand"
                                    v-model="form.track_strand"
                                    :required="showTrackStrand"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">Select Track/Strand</option>
                                    <option v-for="strand in trackStrands" :key="strand.value" :value="strand.value">
                                        {{ strand.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.track_strand" class="mt-1 text-sm text-red-600">{{ form.errors.track_strand }}</p>
                            </div>
                            <div :class="{ 'sm:col-span-2': !showTrackStrand }">
                                <label for="school_year" class="block text-sm font-medium text-gray-700">
                                    School Year Last Attended *
                                </label>
                                <select
                                    id="school_year"
                                    v-model="form.school_year_last_attended"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">Select School Year</option>
                                    <option v-for="sy in schoolYears" :key="sy" :value="sy">
                                        {{ sy }}
                                    </option>
                                </select>
                                <p v-if="form.errors.school_year_last_attended" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.school_year_last_attended }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 2x2 Photo -->
                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">2x2 Photo <span class="text-sm font-normal text-gray-500">(Optional)</span></h2>
                        <div class="rounded-lg border-2 border-dashed border-gray-300 p-6">
                            <div v-if="!photoPreview" class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <div class="mt-4">
                                    <label for="photo" class="cursor-pointer rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600">
                                        Upload Photo
                                    </label>
                                    <input
                                        id="photo"
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png"
                                        @change="handlePhotoUpload"
                                        class="hidden"
                                    />
                                </div>
                                <p class="mt-2 text-xs text-gray-500">JPG or PNG, max 2MB</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-6">
                                <img :src="photoPreview" alt="Preview" class="h-32 w-32 rounded-lg border object-cover shadow" />
                                <div>
                                    <p class="font-medium text-gray-900">Photo uploaded</p>
                                    <button
                                        type="button"
                                        @click="removePhoto"
                                        class="mt-2 text-sm text-red-600 hover:underline"
                                    >
                                        Remove photo
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.photo" class="mt-2 text-center text-sm text-red-600">{{ form.errors.photo }}</p>
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Additional Information</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input
                                    id="quantity"
                                    type="number"
                                    v-model.number="form.quantity"
                                    min="1"
                                    max="10"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <p class="mt-1 text-xs text-gray-500">Number of copies (1-10)</p>
                                <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</p>
                            </div>
                            <div>
                                <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose of Request *</label>
                                <select
                                    id="purpose"
                                    v-model="form.purpose"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                >
                                    <option value="">Select Purpose</option>
                                    <option v-for="option in purposeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">{{ form.errors.purpose }}</p>
                                <div v-if="showPurposeOther" class="mt-3">
                                    <label for="purpose_other" class="block text-sm font-medium text-gray-700">Please specify *</label>
                                    <input
                                        id="purpose_other"
                                        type="text"
                                        v-model="form.purpose_other"
                                        :required="showPurposeOther"
                                        placeholder="Enter your purpose"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                    <p v-if="form.errors.purpose_other" class="mt-1 text-sm text-red-600">{{ form.errors.purpose_other }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                        <Link :href="route('request.select')" class="text-gray-600 hover:text-gray-900">
                            ← Start Over
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || lrnError !== ''"
                            class="flex items-center gap-2 rounded-xl bg-bnhs-blue px-8 py-3 font-semibold text-white transition hover:bg-bnhs-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="form.processing">Submitting...</span>
                            <span v-else>Submit Request</span>
                            <svg v-if="!form.processing" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

