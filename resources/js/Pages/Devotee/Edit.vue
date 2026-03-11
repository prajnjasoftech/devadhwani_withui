<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Devotee</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/devotees">Devotees</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Edit</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Devotee Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Name *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.devotee_name }"
                                    v-model="form.devotee_name"
                                    placeholder="Enter devotee name"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.devotee_name">{{ errors.devotee_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.devotee_phone }"
                                    v-model="form.devotee_phone"
                                    placeholder="Enter phone number"
                                >
                                <div class="invalid-feedback" v-if="errors.devotee_phone">{{ errors.devotee_phone }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Nakshatra</label>
                                <select class="form-control" v-model="form.nakshatra">
                                    <option value="">Select nakshatra</option>
                                    <option v-for="nak in nakshatraList" :key="nak.value" :value="nak.value">{{ nak.label }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea
                            class="form-control"
                            v-model="form.address"
                            rows="2"
                            placeholder="Enter address (optional)"
                        ></textarea>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            <span v-if="processing"><i class="bi bi-arrow-repeat spin"></i> Saving...</span>
                            <span v-else><i class="bi bi-check-circle"></i> Update Devotee</span>
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
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { nakshatraList } from '@/utils/malayalam';

const props = defineProps({
    devotee: Object
});

const form = reactive({
    devotee_name: props.devotee.devotee_name || '',
    devotee_phone: props.devotee.devotee_phone || '',
    nakshatra: props.devotee.nakshatra || '',
    address: props.devotee.address || ''
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/devotees/${props.devotee.id}`, form, {
        onSuccess: () => processing.value = false,
        onError: (errorBag) => {
            processing.value = false;
            Object.assign(errors, errorBag);
        }
    });
};
</script>

<style scoped>
.row { display: flex; flex-wrap: wrap; margin: -0.5rem; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0.5rem; }

@media (max-width: 768px) {
    .col-md-6 { flex: 0 0 100%; max-width: 100%; }
}

.form-group { margin-bottom: 1rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-control.is-invalid { border-color: #ef4444; }
.invalid-feedback { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }

.mt-4 { margin-top: 1.5rem; }
.d-flex { display: flex; }
.gap-3 { gap: 1rem; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
