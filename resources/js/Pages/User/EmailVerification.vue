<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string } | undefined);

const otpSent = ref(false);
const countdown = ref(0);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const emailForm = useForm({
    email: '',
});

const otpForm = useForm({
    email: '',
    otp: '',
});

const canResend = computed(() => countdown.value === 0);

const sendOtp = () => {
    emailForm.post(route('user.dashboard.send-otp'), {
        preserveScroll: true,
        onSuccess: () => {
            otpSent.value = true;
            otpForm.email = emailForm.email;
            startCountdown();
        },
    });
};

const verifyOtp = () => {
    otpForm.post(route('user.dashboard.verify-otp'));
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
    <Head title="Verify Email - My Dashboard" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue">
                            <span class="text-sm font-bold text-white">BNHS</span>
                        </div>
                        <span class="font-semibold text-gray-900">eDocument System</span>
                    </Link>
                    <Link :href="route('home')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Email Form -->
            <div class="mx-auto max-w-md">
                <div class="rounded-2xl bg-white p-8 shadow-xl">
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue-100">
                            <svg class="h-8 w-8 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h1 class="mt-4 text-2xl font-bold text-gray-900">Access Your Dashboard</h1>
                        <p class="mt-2 text-gray-600">
                            {{ otpSent ? 'Enter the 6-digit code sent to your email' : 'Verify your email to view your document requests' }}
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
                        <div v-if="flash?.success" class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm text-green-700">
                            {{ flash.success }}
                        </div>

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
                            <span v-else>Verify & Access Dashboard</span>
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

                <!-- Help Text -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>Only emails with existing document requests can access the dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</template>

