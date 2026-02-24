<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Category</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/categories">Categories</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Edit</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label class="form-label">Category Name *</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            v-model="form.name"
                            placeholder="Enter category name"
                            required
                        >
                        <div class="invalid-feedback" v-if="errors.name">{{ errors.name }}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea
                            class="form-control"
                            v-model="form.description"
                            rows="3"
                            placeholder="Enter description (optional)"
                        ></textarea>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            <span v-if="processing"><i class="bi bi-arrow-repeat spin"></i> Saving...</span>
                            <span v-else><i class="bi bi-check-circle"></i> Update Category</span>
                        </button>
                        <Link href="/categories" class="btn btn-secondary">
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
    category: Object
});

const form = reactive({
    name: props.category.name || '',
    description: props.category.description || ''
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/categories/${props.category.id}`, form, {
        onSuccess: () => processing.value = false,
        onError: (errorBag) => {
            processing.value = false;
            Object.assign(errors, errorBag);
        }
    });
};
</script>

<style scoped>
.form-group { margin-bottom: 1rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }

.form-control {
    width: 100%;
    max-width: 500px;
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

.d-flex { display: flex; }
.gap-3 { gap: 1rem; }
.mt-4 { margin-top: 1.5rem; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
