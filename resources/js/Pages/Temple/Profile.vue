<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">About Temple</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>About Temple</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Temple Information</h3>
                <button v-if="!editing" class="btn btn-primary btn-sm" @click="editing = true">
                    <i class="bi bi-pencil"></i> Edit
                </button>
            </div>
            <div class="card-body">
                <!-- View Mode -->
                <div v-if="!editing" class="profile-section">
                    <div class="profile-avatar">
                        <img v-if="temple.temple_logo_base64"
                             :src="'data:image/png;base64,' + temple.temple_logo_base64"
                             alt="Temple Logo">
                        <div v-else class="avatar-placeholder">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-row">
                            <label>Temple Name</label>
                            <span>{{ temple.temple_name }}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phone</label>
                            <span>{{ temple.phone }}</span>
                        </div>
                        <div class="detail-row">
                            <label>Address</label>
                            <span>{{ temple.temple_address || 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <label>Registered On</label>
                            <span>{{ formatDate(temple.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode -->
                <form v-else @submit.prevent="saveProfile">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Temple Name *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.temple_name }"
                                    v-model="form.temple_name"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.temple_name">
                                    {{ errors.temple_name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    v-model="form.phone"
                                    disabled
                                >
                                <small class="text-muted">Phone cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea
                            class="form-control"
                            :class="{ 'is-invalid': errors.temple_address }"
                            v-model="form.temple_address"
                            rows="3"
                            placeholder="Enter temple address"
                        ></textarea>
                        <div class="invalid-feedback" v-if="errors.temple_address">
                            {{ errors.temple_address }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Temple Logo</label>
                        <input
                            type="file"
                            class="form-control"
                            @change="handleLogoChange"
                            accept="image/*"
                        >
                        <small class="text-muted">Upload a new logo (optional)</small>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving">
                                <i class="bi bi-arrow-repeat spin"></i> Saving...
                            </span>
                            <span v-else>
                                <i class="bi bi-check-circle"></i> Save Changes
                            </span>
                        </button>
                        <button type="button" class="btn btn-secondary" @click="cancelEdit">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    temple: {
        type: Object,
        required: true
    }
});

const editing = ref(false);
const saving = ref(false);
const errors = reactive({});
const logoFile = ref(null);

const form = reactive({
    temple_name: props.temple.temple_name,
    phone: props.temple.phone,
    temple_address: props.temple.temple_address || ''
});

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const handleLogoChange = (event) => {
    logoFile.value = event.target.files[0];
};

const saveProfile = () => {
    saving.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    const formData = new FormData();
    formData.append('temple_name', form.temple_name);
    formData.append('temple_address', form.temple_address);
    if (logoFile.value) {
        formData.append('temple_logo', logoFile.value);
    }
    formData.append('_method', 'PUT');

    router.post(`/temple/profile`, formData, {
        onSuccess: () => {
            saving.value = false;
            editing.value = false;
        },
        onError: (errorBag) => {
            saving.value = false;
            Object.assign(errors, errorBag);
        }
    });
};

const cancelEdit = () => {
    editing.value = false;
    form.temple_name = props.temple.temple_name;
    form.temple_address = props.temple.temple_address || '';
    logoFile.value = null;
    Object.keys(errors).forEach(key => delete errors[key]);
};
</script>

<style scoped>
.profile-section {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.profile-avatar {
    flex-shrink: 0;
}

.profile-avatar img {
    width: 120px;
    height: 120px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 12px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e2e8f0;
}

.avatar-placeholder i {
    font-size: 3rem;
    color: #94a3b8;
}

.profile-details {
    flex: 1;
}

.detail-row {
    display: flex;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row label {
    width: 150px;
    font-weight: 600;
    color: #64748b;
}

.detail-row span {
    flex: 1;
    color: #1e293b;
}

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

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .profile-section {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .detail-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-row label {
        width: auto;
    }

    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>
