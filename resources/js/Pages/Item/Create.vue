<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Add Item</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/items">Items</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Add</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Item Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Item Name *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.item_name }"
                                    v-model="form.item_name"
                                    placeholder="Enter item name"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.item_name">{{ errors.item_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.category_id }"
                                    v-model="form.category_id"
                                    required
                                >
                                    <option value="">Select category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.category_id">{{ errors.category_id }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Unit *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.unit }"
                                    v-model="form.unit"
                                    placeholder="e.g. kg, pcs, litre"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.unit">{{ errors.unit }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Min Quantity *</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.min_quantity }"
                                    v-model="form.min_quantity"
                                    placeholder="0"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.min_quantity">{{ errors.min_quantity }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.status }"
                                    v-model="form.status"
                                    required
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.status">{{ errors.status }}</div>
                            </div>
                        </div>
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
                            <span v-else><i class="bi bi-check-circle"></i> Save Item</span>
                        </button>
                        <Link href="/items" class="btn btn-secondary">
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
    categories: Array
});

const form = reactive({
    item_name: '',
    category_id: '',
    unit: '',
    min_quantity: '',
    status: 'active',
    description: ''
});

const errors = reactive({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.post('/items', form, {
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
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0.5rem; }

@media (max-width: 768px) {
    .col-md-6, .col-md-4 { flex: 0 0 100%; max-width: 100%; }
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

.d-flex { display: flex; }
.gap-3 { gap: 1rem; }
.mt-4 { margin-top: 1.5rem; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
