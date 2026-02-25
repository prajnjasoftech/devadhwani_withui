<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Member</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/members">Members</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Edit</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Member Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-group">
                        <label for="name">Name <span class="required">*</span></label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            placeholder="Enter member name"
                        >
                        <span class="error" v-if="errors.name">{{ errors.name }}</span>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone <span class="required">*</span></label>
                        <input
                            type="text"
                            id="phone"
                            v-model="form.phone"
                            class="form-control"
                            :class="{ 'is-invalid': errors.phone }"
                            placeholder="Enter phone number"
                        >
                        <span class="error" v-if="errors.phone">{{ errors.phone }}</span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            class="form-control"
                            :class="{ 'is-invalid': errors.email }"
                            placeholder="Enter email address"
                        >
                        <span class="error" v-if="errors.email">{{ errors.email }}</span>
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <select
                            id="role_id"
                            v-model="form.role_id"
                            class="form-control"
                            :class="{ 'is-invalid': errors.role_id }"
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.role_name }}
                            </option>
                        </select>
                        <span class="error" v-if="errors.role_id">{{ errors.role_id }}</span>
                    </div>

                    <div class="form-actions">
                        <Link href="/members" class="btn btn-secondary">Cancel</Link>
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            {{ processing ? 'Saving...' : 'Save Changes' }}
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
    member: Object,
    roles: Array
});

const form = reactive({
    name: props.member.name || '',
    phone: props.member.phone || '',
    email: props.member.email || '',
    role_id: props.member.role_id || ''
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/members/${props.member.id}`, form, {
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
