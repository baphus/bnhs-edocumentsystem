<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { DocumentType } from '@/types';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    documentType: DocumentType;
    email: string;
    trackStrands?: Record<string, Record<string, string>>;
    gradeLevels?: Record<string, string>;
    schoolYears?: Record<string, string>;
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
    document_type_id: props.documentType.id,
    email: props.email,
    signature: '',
});
const lrnError = ref('');
const signatureCanvas = ref<HTMLCanvasElement | null>(null);
const isDrawing = ref(false);
const signatureError = ref('');
let ctx: CanvasRenderingContext2D | null = null;

// Extract trackStrands from props to a local reactive constant
const trackStrands = computed(() => props.trackStrands || {});

const setupCanvas = () => {
    const canvas = signatureCanvas.value;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;

    ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.scale(dpr, dpr);
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
};

onMounted(() => {
    setupCanvas();
    window.addEventListener('resize', setupCanvas);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', setupCanvas);
});

const startDrawing = (e: MouseEvent | TouchEvent) => {
    isDrawing.value = true;
    signatureError.value = '';
    const canvas = signatureCanvas.value;
    if (!canvas || !ctx) return;
    
    const rect = canvas.getBoundingClientRect();
    const x = 'touches' in e ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = 'touches' in e ? e.touches[0].clientY - rect.top : e.clientY - rect.top;
    
    ctx.beginPath();
    ctx.moveTo(x, y);
};

const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value || !ctx || !signatureCanvas.value) return;
    
    e.preventDefault();
    const canvas = signatureCanvas.value;
    const rect = canvas.getBoundingClientRect();
    const x = 'touches' in e ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = 'touches' in e ? e.touches[0].clientY - rect.top : e.clientY - rect.top;
    
    ctx.lineTo(x, y);
    ctx.stroke();
};

const stopDrawing = () => {
    if (isDrawing.value && signatureCanvas.value) {
        isDrawing.value = false;
        form.signature = signatureCanvas.value.toDataURL();
    }
};

const clearSignature = () => {
    if (signatureCanvas.value && ctx) {
        ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height);
        form.signature = '';
        signatureError.value = '';
    }
};

const gradeLevels = [
    { value: 'Grade 7', label: 'Grade 7' },
    { value: 'Grade 8', label: 'Grade 8' },
    { value: 'Grade 9', label: 'Grade 9' },
    { value: 'Grade 10', label: 'Grade 10' },
    { value: 'Grade 11', label: 'Grade 11' },
    { value: 'Grade 12', label: 'Grade 12' },
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

const submitRequest = () => {
    // Validate signature
    if (!form.signature) {
        signatureError.value = 'Please provide your signature';
        return;
    }
    
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
                        <span class="font-semibold text-gray-900">eDocument Request</span>
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
                                    <optgroup v-for="(strands, category) in trackStrands" :key="category" :label="category">
                                        <option v-for="(label, value) in strands" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </optgroup>
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

                    <!-- Signature -->
                    <div>
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Signature *</h2>
                        <div class="space-y-4">
                            <div class="rounded-lg border-2 border-dashed border-gray-300 p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Please sign below using your mouse or finger
                                </label>
                                <div class="relative">
                                    <canvas
                                        ref="signatureCanvas"
                                        class="w-full border border-gray-300 rounded-lg bg-white cursor-crosshair touch-none"
                                        @mousedown="startDrawing"
                                        @mousemove="draw"
                                        @mouseup="stopDrawing"
                                        @mouseleave="stopDrawing"
                                        @touchstart="startDrawing"
                                        @touchmove="draw"
                                        @touchend="stopDrawing"
                                    ></canvas>
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none text-gray-300" v-if="!form.signature">
                                        <p class="text-sm">Sign here</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-between items-center">
                                    <p class="text-xs text-gray-500">
                                        Your signature will be used to verify your identity
                                    </p>
                                    <button
                                        type="button"
                                        @click="clearSignature"
                                        class="text-sm text-red-600 hover:text-red-700 font-medium"
                                    >
                                        Clear
                                    </button>
                                </div>
                                <p v-if="signatureError" class="mt-2 text-sm text-red-600">{{ signatureError }}</p>
                                <p v-if="form.errors.signature" class="mt-2 text-sm text-red-600">{{ form.errors.signature }}</p>
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


