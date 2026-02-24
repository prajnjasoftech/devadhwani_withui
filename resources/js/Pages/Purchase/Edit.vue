<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Purchase</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/purchases">Purchases</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Edit</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Purchase Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Item *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.item_id }"
                                    v-model="form.item_id"
                                    @change="onItemChange"
                                    required
                                >
                                    <option value="">Select item</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">
                                        {{ item.item_name }} ({{ item.unit }})
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.item_id">{{ errors.item_id }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Supplier *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.supplier_id }"
                                    v-model="form.supplier_id"
                                    required
                                >
                                    <option value="">Select supplier</option>
                                    <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
                                        {{ sup.name }} ({{ sup.type }})
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.supplier_id">{{ errors.supplier_id }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Quantity *</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.quantity }"
                                        v-model="form.quantity"
                                        placeholder="0"
                                        min="0.01"
                                        step="0.01"
                                        required
                                    >
                                    <span class="input-suffix" v-if="selectedUnit">{{ selectedUnit }}</span>
                                </div>
                                <div class="invalid-feedback" v-if="errors.quantity">{{ errors.quantity }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Unit Price</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.unit_price }"
                                    v-model="form.unit_price"
                                    placeholder="0.00"
                                    min="0"
                                    step="0.01"
                                >
                                <div class="invalid-feedback" v-if="errors.unit_price">{{ errors.unit_price }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Total Price</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :value="formatCurrency(totalPrice)"
                                    disabled
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Received Date *</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.received_date }"
                                    v-model="form.received_date"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.received_date">{{ errors.received_date }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.mode }"
                                    v-model="form.mode"
                                    required
                                >
                                    <option value="purchase">Purchase</option>
                                    <option value="donation">Donation</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.mode">{{ errors.mode }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Payment Mode</label>
                                <select
                                    class="form-control"
                                    v-model="form.payment_mode"
                                    :disabled="form.mode === 'donation'"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea
                            class="form-control"
                            v-model="form.remarks"
                            rows="2"
                            placeholder="Enter remarks (optional)"
                        ></textarea>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            <span v-if="processing"><i class="bi bi-arrow-repeat spin"></i> Saving...</span>
                            <span v-else><i class="bi bi-check-circle"></i> Update Purchase</span>
                        </button>
                        <Link href="/purchases" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchase: Object,
    items: Array,
    suppliers: Array
});

const form = reactive({
    item_id: props.purchase.item_id || '',
    supplier_id: props.purchase.supplier_id || '',
    quantity: props.purchase.quantity || '',
    unit_price: props.purchase.unit_price || '',
    received_date: props.purchase.received_date?.split('T')[0] || '',
    mode: props.purchase.mode || 'purchase',
    payment_mode: 'cash',
    remarks: props.purchase.remarks || ''
});

const errors = reactive({});
const processing = ref(false);
const selectedUnit = ref('');

const totalPrice = computed(() => {
    return (parseFloat(form.quantity) || 0) * (parseFloat(form.unit_price) || 0);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 2 }).format(amount);
};

const onItemChange = () => {
    const item = props.items.find(i => i.id === form.item_id);
    selectedUnit.value = item?.unit || '';
};

onMounted(() => {
    onItemChange();
});

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/purchases/${props.purchase.id}`, form, {
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

.form-control:disabled {
    background: #f3f4f6;
    color: #6b7280;
}

.form-control.is-invalid { border-color: #ef4444; }
.invalid-feedback { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }

.input-group {
    position: relative;
}

.input-suffix {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.875rem;
}

.input-group .form-control {
    padding-right: 3rem;
}

.d-flex { display: flex; }
.gap-3 { gap: 1rem; }
.mt-4 { margin-top: 1.5rem; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
