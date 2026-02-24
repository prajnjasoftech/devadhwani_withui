<template>
    <GuestLayout>
        <form @submit.prevent="submitOtp" v-if="!otpSent">
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': errors.phone }"
                    v-model="form.phone"
                    placeholder="+91 9876543210"
                    required
                >
                <div class="invalid-feedback" v-if="errors.phone">{{ errors.phone }}</div>
            </div>

            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                <span v-if="loading">
                    <i class="bi bi-arrow-repeat spin"></i> Sending OTP...
                </span>
                <span v-else>
                    <i class="bi bi-whatsapp"></i> Send OTP via WhatsApp
                </span>
            </button>
        </form>

        <form @submit.prevent="verifyOtp" v-else>
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle"></i>
                OTP sent to {{ form.phone }}
            </div>

            <div class="form-group">
                <label class="form-label">Enter OTP</label>
                <input
                    type="text"
                    class="form-control text-center"
                    :class="{ 'is-invalid': errors.otp }"
                    v-model="form.otp"
                    placeholder="Enter 6-digit OTP"
                    maxlength="6"
                    required
                >
                <div class="invalid-feedback" v-if="errors.otp">{{ errors.otp }}</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
                <span v-if="loading">
                    <i class="bi bi-arrow-repeat spin"></i> Verifying...
                </span>
                <span v-else>
                    <i class="bi bi-shield-check"></i> Verify & Login
                </span>
            </button>

            <button type="button" class="btn btn-secondary w-100" @click="resetForm">
                <i class="bi bi-arrow-left"></i> Change Phone Number
            </button>

            <p class="text-center mt-3 text-muted">
                <a href="#" @click.prevent="resendOtp">Resend OTP</a>
            </p>
        </form>
    </GuestLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();
const loading = ref(false);
const otpSent = ref(false);
const localErrors = reactive({});

// Merge local errors with Inertia page errors
const errors = computed(() => ({
    ...localErrors,
    ...page.props.errors
}));

const form = reactive({
    phone: '',
    otp: ''
});

// Get CSRF token from meta tag
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
};

const submitOtp = async () => {
    loading.value = true;
    localErrors.phone = null;

    try {
        const response = await axios.post('/api/send-otp', {
            phone: form.phone
        }, {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        if (response.data) {
            otpSent.value = true;
        }
    } catch (error) {
        if (error.response && error.response.data) {
            localErrors.phone = error.response.data.error || 'Failed to send OTP';
        } else {
            localErrors.phone = 'Network error. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};

const verifyOtp = () => {
    loading.value = true;
    localErrors.otp = null;

    router.post('/login', {
        phone: form.phone,
        otp: form.otp
    }, {
        onSuccess: () => {
            loading.value = false;
        },
        onError: (errorBag) => {
            loading.value = false;
            if (errorBag.otp) {
                localErrors.otp = errorBag.otp;
            } else if (errorBag.phone) {
                localErrors.otp = errorBag.phone;
            } else {
                localErrors.otp = 'Invalid OTP';
            }
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const resetForm = () => {
    otpSent.value = false;
    form.otp = '';
};

const resendOtp = () => {
    form.otp = '';
    submitOtp();
};
</script>

<style scoped>
.w-100 {
    width: 100%;
}

.text-center {
    text-align: center;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
