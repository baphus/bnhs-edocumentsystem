<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { DocumentType } from '@/types';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    documentTypes: Record<string, DocumentType[]>;
}>();

const selectedDocument = ref<number | null>(null);

const proceedToVerification = () => {
    if (selectedDocument.value) {
        router.get(route('request.verify'), {
            document_type_id: selectedDocument.value,
        });
    }
};

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        'Official': 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        'Informal': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'Certified': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    };
    return icons[category] || icons['Official'];
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        'Official': 'bg-bnhs-blue-100 text-bnhs-blue border-bnhs-blue-200',
        'Informal': 'bg-bnhs-gold-100 text-bnhs-gold-700 border-bnhs-gold-200',
        'Certified': 'bg-green-100 text-green-700 border-green-200',
    };
    return colors[category] || colors['Official'];
};
</script>

<template>
    <Head title="Select Document - Request" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="font-semibold text-gray-900">eDocument System</span>
                    </Link>
                    <Link :href="route('home')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Progress Steps -->
        <div class="mx-auto max-w-4xl px-4 py-8">
            <div class="mb-8 flex items-center justify-center">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue text-white">
                        1
                    </div>
                    <span class="ml-2 font-medium text-bnhs-blue">Select Document</span>
                </div>
                <div class="mx-4 h-1 w-16 bg-gray-200"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                        2
                    </div>
                    <span class="ml-2 text-gray-500">Verify Email</span>
                </div>
                <div class="mx-4 h-1 w-16 bg-gray-200"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                        3
                    </div>
                    <span class="ml-2 text-gray-500">Fill Form</span>
                </div>
            </div>

            <!-- Document Selection -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <h1 class="text-2xl font-bold text-gray-900">Select Document Type</h1>
                <p class="mt-2 text-gray-600">Choose the document you want to request</p>

                <div class="mt-8 space-y-8">
                    <div v-for="(documents, category) in documentTypes" :key="category">
                        <div class="mb-4 flex items-center gap-2">
                            <div :class="['flex h-8 w-8 items-center justify-center rounded-lg', getCategoryColor(category)]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getCategoryIcon(category)"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ category }} Documents</h2>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label
                                v-for="doc in documents"
                                :key="doc.id"
                                :class="[
                                    'cursor-pointer rounded-xl border-2 p-4 transition-all',
                                    selectedDocument === doc.id
                                        ? 'border-bnhs-blue bg-bnhs-blue-50 ring-2 ring-bnhs-blue/20'
                                        : 'border-gray-200 hover:border-bnhs-blue-200 hover:bg-gray-50'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        :value="doc.id"
                                        v-model="selectedDocument"
                                        class="h-4 w-4 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                    <span class="font-medium text-gray-900">{{ doc.name }}</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button
                        @click="proceedToVerification"
                        :disabled="!selectedDocument"
                        :class="[
                            'flex items-center gap-2 rounded-xl px-8 py-3 font-semibold transition',
                            selectedDocument
                                ? 'bg-bnhs-blue text-white hover:bg-bnhs-blue-600'
                                : 'cursor-not-allowed bg-gray-200 text-gray-500'
                        ]"
                    >
                        Continue
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

