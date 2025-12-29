<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { DocumentType } from '@/types';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{
    documentType: DocumentType;
}>();

const otpSent = ref(false);
const countdown = ref(0);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const emailForm = useForm({
    email: '',
    document_type_id: props.documentType.id,
});

const otpForm = useForm({
    email: '',
    otp: '',
    document_type_id: props.documentType.id,
});

const canResend = computed(() => countdown.value === 0);

const sendOtp = () => {
    emailForm.post(route('request.send-otp'), {
        preserveScroll: true,
        onSuccess: () => {
            otpSent.value = true;
            otpForm.email = emailForm.email;
            startCountdown();
        },
    });
};

const verifyOtp = () => {
    otpForm.post(route('request.verify-otp'));
};

const startCountdown = () => {
    countdown.value = 60;
    if (countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(countdownInterval!);
        }
    }, 1000);
};

const resendOtp = () => {
    if (canResend.value) {
        sendOtp();
    }
};

const formatOtp = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/\D/g, '').slice(0, 6);
    otpForm.otp = input.value;
};
</script>

<template>
    <Head title="Verify Email - Request" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="font-semibold text-gray-900">eDocument System</span>
                    </Link>
                    <Link :href="route('request.select')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                        ← Back to Selection
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
                <div class="mx-4 h-1 w-16 bg-bnhs-blue"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue text-white">
                        2
                    </div>
                    <span class="ml-2 font-medium text-bnhs-blue">Verify Email</span>
                </div>
                <div class="mx-4 h-1 w-16 bg-gray-200"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                        3
                    </div>
                    <span class="ml-2 text-gray-500">Fill Form</span>
                </div>
            </div>

            <!-- Selected Document Badge -->
            <div class="mb-6 flex justify-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-bnhs-blue-100 px-4 py-2 text-sm font-medium text-bnhs-blue">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ documentType.name }}
                </div>
            </div>

            <!-- Email Form -->
            <div class="mx-auto max-w-md">
                <div class="rounded-2xl bg-white p-8 shadow-xl">
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue-100">
                            <svg class="h-8 w-8 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h1 class="mt-4 text-2xl font-bold text-gray-900">Verify Your Email</h1>
                        <p class="mt-2 text-gray-600">
                            {{ otpSent ? 'Enter the 6-digit code sent to your email' : 'We\'ll send you a verification code' }}
                        </p>
                    </div>

                    <!-- Step 1: Enter Email -->
                    <form v-if="!otpSent" @submit.prevent="sendOtp" class="mt-8">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                v-model="emailForm.email"
                                required
                                placeholder="your.email@gmail.com"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="emailForm.errors.email" class="mt-1 text-sm text-red-600">{{ emailForm.errors.email }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="emailForm.processing || !emailForm.email"
                            class="mt-6 w-full rounded-xl bg-bnhs-blue py-3 font-semibold text-white transition hover:bg-bnhs-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="emailForm.processing">Sending...</span>
                            <span v-else>Send Verification Code</span>
                        </button>
                    </form>

                    <!-- Step 2: Enter OTP -->
                    <form v-else @submit.prevent="verifyOtp" class="mt-8">
                        <div class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm text-green-700">
                            Code sent to <strong>{{ otpForm.email }}</strong>
                        </div>

                        <div>
                            <label for="otp" class="block text-sm font-medium text-gray-700">Verification Code</label>
                            <input
                                id="otp"
                                type="text"
                                :value="otpForm.otp"
                                @input="formatOtp"
                                required
                                maxlength="6"
                                placeholder="000000"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-center text-2xl font-mono tracking-[0.5em] shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="otpForm.errors.otp" class="mt-1 text-sm text-red-600">{{ otpForm.errors.otp }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="otpForm.processing || otpForm.otp.length !== 6"
                            class="mt-6 w-full rounded-xl bg-bnhs-blue py-3 font-semibold text-white transition hover:bg-bnhs-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="otpForm.processing">Verifying...</span>
                            <span v-else>Verify & Continue</span>
                        </button>

                        <div class="mt-4 text-center">
                            <button
                                type="button"
                                @click="resendOtp"
                                :disabled="!canResend || emailForm.processing"
                                class="text-sm text-bnhs-blue hover:underline disabled:cursor-not-allowed disabled:text-gray-400"
                            >
                                <span v-if="!canResend">Resend code in {{ countdown }}s</span>
                                <span v-else>Didn't receive code? Resend</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

