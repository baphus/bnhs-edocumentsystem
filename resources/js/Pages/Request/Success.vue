<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    trackingId: string;
    documentType: string;
    email: string;
}>();

const copied = ref(false);

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.trackingId);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = props.trackingId;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};
</script>

<template>
    <Head title="Request Submitted" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50 p-4">
        <div class="w-full max-w-lg">
            <!-- Success Card -->
            <div class="rounded-2xl bg-white p-8 text-center shadow-xl">
                <!-- Success Icon -->
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="mt-6 text-2xl font-bold text-gray-900">Request Submitted Successfully!</h1>
                <p class="mt-2 text-gray-600">
                    Your document request has been received and is now being processed.
                </p>

                <!-- Tracking ID -->
                <div class="mt-8 rounded-xl bg-bnhs-blue-50 p-6">
                    <p class="text-sm font-medium text-bnhs-blue">Your Tracking ID</p>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <span class="text-3xl font-bold tracking-wider text-bnhs-blue">{{ trackingId }}</span>
                        <button
                            @click="copyToClipboard"
                            class="rounded-lg p-2 text-bnhs-blue hover:bg-bnhs-blue-100"
                            :title="copied ? 'Copied!' : 'Copy to clipboard'"
                        >
                            <svg v-if="!copied" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <svg v-else class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-4 text-sm text-gray-600">
                        <strong>Save this ID!</strong> You'll need it to track your request status.
                    </p>
                </div>

                <!-- Document Info -->
                <div class="mt-6 rounded-xl border border-gray-200 p-4">
                    <div class="grid grid-cols-2 gap-4 text-left text-sm">
                        <div>
                            <p class="text-gray-500">Document Type</p>
                            <p class="font-medium text-gray-900">{{ documentType }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Email</p>
                            <p class="font-medium text-gray-900">{{ email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Email Notification -->
                <div class="mt-6 flex items-center justify-center gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Confirmation email sent to <strong>{{ email }}</strong></span>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <Link
                        :href="route('track.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-bnhs-blue px-6 py-3 font-semibold text-bnhs-blue transition hover:bg-bnhs-blue-50"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Track Request
                    </Link>
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-bnhs-blue px-6 py-3 font-semibold text-white transition hover:bg-bnhs-blue-600"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Back to Home
                    </Link>
                </div>
            </div>

            <!-- Processing Info -->
            <div class="mt-6 rounded-xl bg-white/80 p-6 text-center">
                <h3 class="font-semibold text-gray-900">What happens next?</h3>
                <div class="mt-4 grid gap-4 text-left text-sm sm:grid-cols-3">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-bnhs-blue-100 text-sm font-bold text-bnhs-blue">
                            1
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Verification</p>
                            <p class="text-gray-600">We'll verify your information</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-bnhs-blue-100 text-sm font-bold text-bnhs-blue">
                            2
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Processing</p>
                            <p class="text-gray-600">Document will be prepared</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-bnhs-blue-100 text-sm font-bold text-bnhs-blue">
                            3
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Ready for Pickup</p>
                            <p class="text-gray-600">We'll notify you via email</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


