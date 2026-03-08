<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Deity</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/deities">Deities</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Edit</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Deity Details</h3>
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
                                    :class="{ 'is-invalid': errors.name }"
                                    v-model="form.name"
                                    placeholder="Enter deity name"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea
                            class="form-control"
                            :class="{ 'is-invalid': errors.description }"
                            v-model="form.description"
                            rows="3"
                            placeholder="Enter deity description (optional)"
                        ></textarea>
                        <div class="invalid-feedback" v-if="errors.description">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                v-model="form.is_active"
                            >
                            <span class="form-check-label">Active</span>
                        </label>
                        <small class="text-muted d-block">Active deities will appear in dropdown lists</small>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            <span v-if="processing">
                                <i class="bi bi-arrow-repeat spin"></i> Saving...
                            </span>
                            <span v-else>
                                <i class="bi bi-check-circle"></i> Update Deity
                            </span>
                        </button>
                        <Link href="/deities" class="btn btn-secondary">
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

const props = defineProps({
    deity: Object
});

const form = reactive({
    name: props.deity.name || '',
    description: props.deity.description || '',
    is_active: props.deity.is_active ?? true
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/deities/${props.deity.id}`, form, {
        onSuccess: () => {
            processing.value = false;
        },
        onError: (errorBag) => {
            processing.value = false;
            Object.assign(errors, errorBag);
        }
    });
};
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

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.form-check-input {
    width: 1rem;
    height: 1rem;
}

.form-check-label {
    font-weight: 500;
}

.d-flex {
    display: flex;
}

.d-block {
    display: block;
}

.gap-3 {
    gap: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.text-muted {
    color: #64748b;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
