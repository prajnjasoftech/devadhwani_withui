<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Add Devotee</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/devotees">Devotees</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Add New</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Devotee Information</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.devotee_name }"
                                    v-model="form.devotee_name"
                                    placeholder="Enter full name"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.devotee_name">
                                    {{ errors.devotee_name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.devotee_phone }"
                                    v-model="form.devotee_phone"
                                    placeholder="+91 9876543210"
                                >
                                <div class="invalid-feedback" v-if="errors.devotee_phone">
                                    {{ errors.devotee_phone }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Nakshatra</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.nakshatra }"
                                    v-model="form.nakshatra"
                                >
                                    <option value="">Select Nakshatra</option>
                                    <option v-for="n in nakshatras" :key="n" :value="n">{{ n }}</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.nakshatra">
                                    {{ errors.nakshatra }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Temple *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.temple_id }"
                                    v-model="form.temple_id"
                                    required
                                >
                                    <option value="">Select Temple</option>
                                    <option v-for="temple in temples" :key="temple.id" :value="temple.id">
                                        {{ temple.temple_name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.temple_id">
                                    {{ errors.temple_id }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea
                            class="form-control"
                            :class="{ 'is-invalid': errors.address }"
                            v-model="form.address"
                            placeholder="Enter full address"
                            rows="3"
                        ></textarea>
                        <div class="invalid-feedback" v-if="errors.address">
                            {{ errors.address }}
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span v-if="submitting">
                                <i class="bi bi-arrow-repeat spin"></i> Saving...
                            </span>
                            <span v-else>
                                <i class="bi bi-check-circle"></i> Save Devotee
                            </span>
                        </button>
                        <Link href="/devotees" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    temples: {
        type: Array,
        default: () => []
    }
});

const form = reactive({
    temple_id: '',
    devotee_name: '',
    devotee_phone: '',
    nakshatra: '',
    address: ''
});

const errors = reactive({});
const submitting = ref(false);
const temples = ref([]);

const nakshatras = [
    'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira',
    'Ardra', 'Punarvasu', 'Pushya', 'Ashlesha', 'Magha',
    'Purva Phalguni', 'Uttara Phalguni', 'Hasta', 'Chitra',
    'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha', 'Mula',
    'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta',
    'Shatabhisha', 'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati'
];

const fetchTemples = async () => {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('/api/temples?fields=dropdown', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.status) {
            temples.value = data.data || [];
            // Auto-select first temple if only one
            if (temples.value.length === 1) {
                form.temple_id = temples.value[0].id;
            }
        }
    } catch (error) {
        console.error('Failed to fetch temples:', error);
    }
};

const submitForm = async () => {
    submitting.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('/api/devotees', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(form)
        });

        const data = await response.json();

        if (response.ok && data.status) {
            window.location.href = '/devotees';
        } else {
            if (data.errors) {
                Object.assign(errors, Object.fromEntries(
                    Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                ));
            } else {
                errors.general = data.error || 'Failed to create devotee';
            }
        }
    } catch (error) {
        errors.general = 'Network error. Please try again.';
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchTemples();
});
</script>

<style scoped>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
