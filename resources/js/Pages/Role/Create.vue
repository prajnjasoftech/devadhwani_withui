<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Add Role</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/roles">Roles</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Add</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Role Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="role_name">Role Name <span class="required">*</span></label>
                        <input
                            type="text"
                            id="role_name"
                            v-model="form.role_name"
                            class="form-control"
                            :class="{ 'is-invalid': errors.role_name }"
                            placeholder="Enter role name"
                        >
                        <span class="error" v-if="errors.role_name">{{ errors.role_name }}</span>
                    </div>

                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="permissions-grid">
                            <label
                                v-for="(label, key) in availablePermissions"
                                :key="key"
                                class="permission-item"
                            >
                                <input
                                    type="checkbox"
                                    :value="key"
                                    v-model="form.role"
                                >
                                <span class="checkmark"></span>
                                <span class="permission-label">{{ label }}</span>
                            </label>
                        </div>
                        <span class="error" v-if="errors.role">{{ errors.role }}</span>
                    </div>

                    <div class="form-actions">
                        <Link href="/roles" class="btn btn-secondary">Cancel</Link>
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            {{ processing ? 'Creating...' : 'Create Role' }}
                        </button>
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
    availablePermissions: Object
});

const form = reactive({
    role_name: '',
    role: []
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.post('/roles', form, {
        onSuccess: () => {
            processing.value = false;
        },
        onError: (errs) => {
            Object.assign(errors, errs);
            processing.value = false;
        }
    });
};
</script>

<style scoped>
.card-body {
    max-width: 600px;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.error {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.8125rem;
    color: #ef4444;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 6px;
    transition: background 0.2s;
}

.permission-item:hover {
    background: #f3f4f6;
}

.permission-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}

.permission-label {
    font-size: 0.875rem;
    color: #374151;
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e2e8f0;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
}

.btn-primary:hover:not(:disabled) {
    opacity: 0.9;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
