<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref } from 'vue';

defineProps<{
    canLogin?: boolean;
}>();

const openFaq = ref<number | null>(null);
const showReportModal = ref(false);
const reportForm = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
});
const isSubmitting = ref(false);
const submitSuccess = ref(false);

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const openReportModal = () => {
    showReportModal.value = true;
    submitSuccess.value = false;
};

const closeReportModal = () => {
    showReportModal.value = false;
    reportForm.value = {
        name: '',
        email: '',
        subject: '',
        message: ''
    };
};

const submitReport = () => {
    isSubmitting.value = true;
    
    // Create mailto link with form data
    const subject = encodeURIComponent(reportForm.value.subject || 'Website Problem Report');
    const body = encodeURIComponent(
        `Name: ${reportForm.value.name}\n` +
        `Email: ${reportForm.value.email}\n\n` +
        `Message:\n${reportForm.value.message}`
    );
    
    window.location.href = `mailto:webdev@batonhs.edu.ph?subject=${subject}&body=${body}`;
    
    setTimeout(() => {
        isSubmitting.value = false;
        submitSuccess.value = true;
        setTimeout(() => {
            closeReportModal();
        }, 2000);
    }, 500);
};

const faqs = [
    {
        question: 'How long does it take to process my request?',
        answer: `Processing time varies by document type:
• Informal Documents (Grade Slips): 1-2 business days
• Official Documents (Good Moral, Enrollment Certificate): 3-5 business days
• Certified Documents (Certified True Copies, Form 137/138, Diploma): 5-7 business days

You will receive email notifications when your document status changes.`
    },
    {
        question: 'What requirements do I need to submit?',
        answer: `Basic requirements include:
• Valid email address for verification and tracking
• Complete personal information (full name, student ID if applicable)
• Reason or purpose for requesting the document
• Valid ID upon pickup (for identity verification)

Additional requirements may vary depending on the document type requested.`
    },
    {
        question: 'What if I lost my Diploma or Report Card?',
        answer: `For lost Diploma or Report Card, you must bring the following documents physically to the Registrar's Office:

• Affidavit of Loss
• Request Letter (addressed to the school/registrar)

These documents are required before we can process your replacement request.`
    },
    {
        question: 'What do I need for Diploma Correction?',
        answer: `For Diploma Correction, you must bring the following documents physically to the Registrar's Office:

• Certificate of Live Birth (issued by PSA)
• Affidavit of Discrepancy
• Affidavit of two (2) Disinterested Persons

Please ensure all documents are complete before visiting the office.`
    },
    {
        question: 'How do I track my request?',
        answer: 'After submitting your request, you\'ll receive a tracking ID via email. Use this ID to check your request status on our tracking page. You\'ll also receive email notifications whenever your request status changes.'
    },
    {
        question: 'Is there a fee for document requests?',
        answer: 'Most documents are free of charge. However, some certified documents may have processing fees. Any applicable fees will be clearly indicated during the request process and can be paid upon pickup.'
    }
];
</script>

<template>
    <Head title="BNHS eDocument Request" />
    
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b border-gray-100 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-gray-900">eDocument Request</p>
                            <p class="text-xs text-gray-600">Bato National High School</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-24 pb-16 sm:pt-32 sm:pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-bnhs-blue/10 px-4 py-1.5 text-sm font-medium text-bnhs-blue">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        No Account Required
                    </div>
                    
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                        <span class="block">Bato National High School</span>
                        <span class="block text-bnhs-blue">eDocument Request</span>
                    </h1>
                    
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">
                        Request and track your school documents online. Simple, fast, and secure—verify your email and submit your request in minutes.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            :href="route('request.select')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-bnhs-blue px-8 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-bnhs-blue-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-bnhs-blue sm:w-auto"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Request a Document
                        </Link>
                        <Link
                            :href="route('track.index')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-8 py-3.5 text-base font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-bnhs-blue sm:w-auto"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Track Request
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Documents Section -->
        <section class="py-16 sm:py-24 bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Available Documents</h2>
                    <p class="mt-4 text-lg text-gray-600">Request any of these documents online</p>
                </div>

                <div class="mx-auto mt-12 grid max-w-lg gap-8 lg:max-w-none lg:grid-cols-3">
                    <div class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="p-8">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-bnhs-blue">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Official Documents</h3>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-blue" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Good Moral Certificate
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-blue" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Enrollment Certificate
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-blue" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Report Card (Form 138)
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-blue" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Permanent Record (Form 137)
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-blue" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Diploma
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="p-8">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-bnhs-gold">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Informal Documents</h3>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-gold" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Grade Slip (Quarter 1)
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-gold" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Grade Slip (Quarter 2)
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-gold" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Grade Slip (Quarter 3)
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-bnhs-gold" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Grade Slip (Quarter 4)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="p-8">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-600">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Certified Documents</h3>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Certified True Copy of Report Card
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    Certified True Copy of Diploma
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-5 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    CAV Documents
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">How It Works</h2>
                    <p class="mt-4 text-lg text-gray-600">Four simple steps to get your documents</p>
                </div>

                <div class="mx-auto mt-16 max-w-5xl">
                    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue text-2xl font-bold text-white shadow-lg">
                                1
                            </div>
                            <h3 class="mt-6 text-base font-semibold text-gray-900">Select Document</h3>
                            <p class="mt-2 text-sm text-gray-600">Choose the document type you need</p>
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue text-2xl font-bold text-white shadow-lg">
                                2
                            </div>
                            <h3 class="mt-6 text-base font-semibold text-gray-900">Verify Email</h3>
                            <p class="mt-2 text-sm text-gray-600">Enter your email and verify with OTP</p>
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue text-2xl font-bold text-white shadow-lg">
                                3
                            </div>
                            <h3 class="mt-6 text-base font-semibold text-gray-900">Submit Request</h3>
                            <p class="mt-2 text-sm text-gray-600">Fill out the form and get tracking ID</p>
                        </div>

                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-gold text-2xl font-bold text-white shadow-lg">
                                4
                            </div>
                            <h3 class="mt-6 text-base font-semibold text-gray-900">Pickup Document</h3>
                            <p class="mt-2 text-sm text-gray-600">Get notified and pick up at school</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Help & Support -->
        <section class="bg-gray-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Help & Support</h2>
                    <p class="mt-4 text-lg text-gray-600">Everything you need to know about document requests</p>
                </div>

                <!-- FAQs -->
                <div class="mx-auto mt-16 max-w-3xl">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">Frequently Asked Questions</h3>
                    <dl class="space-y-4">
                        <div 
                            v-for="(faq, index) in faqs" 
                            :key="index"
                            class="rounded-lg border border-gray-200 bg-white overflow-hidden transition-all"
                        >
                            <dt>
                                <button
                                    @click="toggleFaq(index)"
                                    class="flex w-full items-start justify-between p-6 text-left"
                                    :aria-expanded="openFaq === index"
                                >
                                    <span class="text-base font-semibold leading-7 text-gray-900">
                                        {{ faq.question }}
                                    </span>
                                    <span class="ml-6 flex h-7 items-center">
                                        <svg 
                                            :class="['h-6 w-6 transition-transform', openFaq === index ? 'rotate-180' : '']"
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke-width="1.5" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </span>
                                </button>
                            </dt>
                            <dd 
                                v-show="openFaq === index"
                                class="px-6 pb-6"
                            >
                                <p class="text-sm leading-7 text-gray-600 whitespace-pre-line">{{ faq.answer }}</p>
                                <Link 
                                    v-if="index === 2"
                                    :href="route('track.index')" 
                                    class="mt-2 inline-block text-sm font-medium text-bnhs-blue hover:text-bnhs-blue-600"
                                >
                                    Go to tracking page →
                                </Link>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Contact Information -->
                <div class="mx-auto mt-16 max-w-3xl">
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="p-8 sm:p-10">
                            <h3 class="text-xl font-semibold text-gray-900 text-center mb-8">Contact the Registrar's Office</h3>
                            <dl class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                <div class="flex gap-x-4">
                                    <dt class="flex-none">
                                        <span class="sr-only">Email</span>
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-bnhs-blue">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </dt>
                                    <dd>
                                        <p class="text-sm font-semibold text-gray-900">Email</p>
                                        <a href="mailto:registrar@batonhs.edu.ph" class="mt-1 text-sm leading-6 text-bnhs-blue hover:text-bnhs-blue-600">
                                            registrar@batonhs.edu.ph
                                        </a>
                                    </dd>
                                </div>

                                <div class="flex gap-x-4">
                                    <dt class="flex-none">
                                        <span class="sr-only">Phone</span>
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-bnhs-blue">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                    </dt>
                                    <dd>
                                        <p class="text-sm font-semibold text-gray-900">Phone</p>
                                        <a href="tel:+639123456789" class="mt-1 text-sm leading-6 text-bnhs-blue hover:text-bnhs-blue-600">
                                            +63 912 345 6789
                                        </a>
                                    </dd>
                                </div>

                                <div class="flex gap-x-4">
                                    <dt class="flex-none">
                                        <span class="sr-only">Office Hours</span>
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-bnhs-blue">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </dt>
                                    <dd>
                                        <p class="text-sm font-semibold text-gray-900">Office Hours</p>
                                        <p class="mt-1 text-sm leading-6 text-gray-600">
                                            Monday - Friday<br>
                                            8:00 AM - 5:00 PM
                                        </p>
                                    </dd>
                                </div>

                                <div class="flex gap-x-4">
                                    <dt class="flex-none">
                                        <span class="sr-only">Location</span>
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-bnhs-blue">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                    </dt>
                                    <dd>
                                        <p class="text-sm font-semibold text-gray-900">Location</p>
                                        <p class="mt-1 text-sm leading-6 text-gray-600">
                                            Bato National High School<br>
                                            Toledo City, Cebu
                                        </p>
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-8 rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-600">
                                    <strong class="font-semibold text-gray-900">Need immediate assistance?</strong> 
                                    Visit the Registrar's Office during office hours or send us an email with your tracking ID for faster support.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white py-12 border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6">
                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:justify-between w-full">
                        <div class="flex items-center gap-3">
                            <ApplicationLogo class="h-10 w-auto" />
                            <div class="text-center sm:text-left">
                                <p class="text-sm font-semibold text-gray-900">Bato National High School</p>
                                <p class="text-sm text-gray-600">Toledo City, Cebu</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-center">
                            <button
                                @click="openReportModal"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                Report a Problem
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            &copy; {{ new Date().getFullYear() }} BNHS eDocument Request. All rights reserved.
                        </p>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="mt-1 text-xs text-gray-400 hover:text-gray-500 transition"
                        >
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Report Problem Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showReportModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4"
                    @click.self="closeReportModal"
                >
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            v-if="showReportModal"
                            class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl"
                        >
                            <!-- Modal Header -->
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">Report a Problem</h3>
                                    <button
                                        @click="closeReportModal"
                                        type="button"
                                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-200 hover:text-gray-600"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Help us improve by reporting any issues you encounter</p>
                            </div>

                            <!-- Modal Body -->
                            <form @submit.prevent="submitReport" class="px-6 py-6">
                                <div v-if="submitSuccess" class="mb-4 rounded-lg bg-green-50 p-4">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="text-sm font-medium text-green-800">Your report will be sent via email. Thank you!</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                                        <input
                                            v-model="reportForm.name"
                                            type="text"
                                            id="name"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-bnhs-blue focus:outline-none focus:ring-1 focus:ring-bnhs-blue"
                                            placeholder="John Doe"
                                        />
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                        <input
                                            v-model="reportForm.email"
                                            type="email"
                                            id="email"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-bnhs-blue focus:outline-none focus:ring-1 focus:ring-bnhs-blue"
                                            placeholder="john@example.com"
                                        />
                                    </div>

                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                        <input
                                            v-model="reportForm.subject"
                                            type="text"
                                            id="subject"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-bnhs-blue focus:outline-none focus:ring-1 focus:ring-bnhs-blue"
                                            placeholder="Brief description of the issue"
                                        />
                                    </div>

                                    <div>
                                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                        <textarea
                                            v-model="reportForm.message"
                                            id="message"
                                            rows="5"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-bnhs-blue focus:outline-none focus:ring-1 focus:ring-bnhs-blue"
                                            placeholder="Please describe the problem in detail..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="mt-6 flex gap-3">
                                    <button
                                        type="button"
                                        @click="closeReportModal"
                                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="flex-1 rounded-lg bg-bnhs-blue px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-bnhs-blue-600 disabled:opacity-50"
                                    >
                                        <span v-if="isSubmitting">Sending...</span>
                                        <span v-else>Send Report</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

