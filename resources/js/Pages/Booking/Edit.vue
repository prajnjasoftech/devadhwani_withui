<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Edit Booking</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/bookings">Bookings</Link>
                <i class="bi bi-chevron-right"></i>
                <span>{{ booking.booking_number }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Booking Details</h3>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="info-box">
                                <div class="info-row">
                                    <span class="label">Receipt #:</span>
                                    <span class="value receipt-number">{{ booking.receipt_number || '-' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Booking #:</span>
                                    <span class="value booking-number">{{ booking.booking_number }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Devotee:</span>
                                    <span class="value">{{ booking.devotee?.devotee_name || 'Guest' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Pooja:</span>
                                    <span class="value">{{ booking.pooja?.pooja_name }}</span>
                                </div>
                                <div class="info-row" v-if="booking.deity">
                                    <span class="label">Deity:</span>
                                    <span class="value">{{ booking.deity?.name }}</span>
                                </div>
                            </div>

                            <div class="section-title mt-4">Update Details</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Booking Date *</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.booking_date }"
                                            v-model="form.booking_date"
                                            required
                                        >
                                        <div class="invalid-feedback" v-if="errors.booking_date">{{ errors.booking_date }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">End Date</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            v-model="form.booking_end_date"
                                            :min="form.booking_date"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Booking Status *</label>
                                        <select
                                            class="form-control"
                                            :class="{ 'is-invalid': errors.booking_status }"
                                            v-model="form.booking_status"
                                            required
                                        >
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <div class="invalid-feedback" v-if="errors.booking_status">{{ errors.booking_status }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <textarea
                                    class="form-control"
                                    v-model="form.remarks"
                                    rows="2"
                                    placeholder="Any additional notes..."
                                ></textarea>
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-primary" :disabled="processing">
                                    <span v-if="processing"><i class="bi bi-arrow-repeat spin"></i> Saving...</span>
                                    <span v-else><i class="bi bi-check-circle"></i> Update Booking</span>
                                </button>
                                <Link href="/bookings" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payment Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="payment-summary">
                            <div class="payment-row">
                                <span>Total Amount</span>
                                <span class="amount">{{ formatCurrency(booking.pooja_amount) }}</span>
                            </div>
                            <div class="payment-row">
                                <span>Received</span>
                                <span class="amount text-success">{{ formatCurrency(booking.pooja_amount_total_received || 0) }}</span>
                            </div>
                            <div class="payment-row pending-row">
                                <span>Pending</span>
                                <span class="amount text-danger">{{ formatCurrency(pendingAmount) }}</span>
                            </div>
                        </div>

                        <div class="payment-status-badge" :class="paymentStatusClass">
                            {{ paymentStatusText }}
                        </div>
                    </div>
                </div>

                <div class="card mt-4" v-if="pendingAmount > 0">
                    <div class="card-header">
                        <h3 class="card-title">Record Payment</h3>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="recordPayment">
                            <div class="form-group">
                                <label class="form-label">Amount *</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="paymentForm.amount"
                                    :max="pendingAmount"
                                    min="1"
                                    step="0.01"
                                    required
                                    placeholder="Enter amount"
                                >
                                <small class="text-muted">Max: {{ formatCurrency(pendingAmount) }}</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Payment Mode</label>
                                <select class="form-control" v-model="paymentForm.payment_mode">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-block" :disabled="paymentProcessing">
                                <span v-if="paymentProcessing"><i class="bi bi-arrow-repeat spin"></i></span>
                                <span v-else><i class="bi bi-cash"></i> Record Payment</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4" v-if="booking.payment_status === 'success'">
                    <div class="card-header">
                        <h3 class="card-title">Refund</h3>
                    </div>
                    <div class="card-body">
                        <button @click="confirmRefund" class="btn btn-outline-danger btn-block">
                            <i class="bi bi-arrow-counterclockwise"></i> Process Refund
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refund Modal -->
        <div class="modal-overlay" v-if="showRefundModal" @click="showRefundModal = false">
            <div class="modal-content" @click.stop>
                <h3>Confirm Refund</h3>
                <p>Are you sure you want to refund {{ formatCurrency(booking.pooja_amount_total_received) }}?</p>
                <div class="form-group">
                    <label class="form-label">Payment Mode</label>
                    <select class="form-control" v-model="refundMode">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button @click="showRefundModal = false" class="btn btn-secondary">Cancel</button>
                    <button @click="processRefund" class="btn btn-danger" :disabled="refundProcessing">
                        {{ refundProcessing ? 'Processing...' : 'Confirm Refund' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    booking: Object,
    poojas: Array,
    devotees: Array,
    deities: Array
});

const form = reactive({
    booking_date: props.booking.booking_date?.split('T')[0] || '',
    booking_end_date: props.booking.booking_end_date?.split('T')[0] || '',
    booking_status: props.booking.booking_status || 'pending',
    remarks: props.booking.remarks || ''
});

const paymentForm = reactive({
    amount: '',
    payment_mode: 'cash'
});

const errors = reactive({});
const processing = ref(false);
const paymentProcessing = ref(false);
const showRefundModal = ref(false);
const refundMode = ref('cash');
const refundProcessing = ref(false);

const pendingAmount = computed(() => {
    return (props.booking.pooja_amount || 0) - (props.booking.pooja_amount_total_received || 0);
});

const paymentStatusClass = computed(() => {
    if (props.booking.payment_status === 'success') return 'status-paid';
    if (pendingAmount.value <= 0) return 'status-paid';
    if (props.booking.pooja_amount_total_received > 0) return 'status-partial';
    return 'status-pending';
});

const paymentStatusText = computed(() => {
    if (props.booking.payment_status === 'success') return 'Fully Paid';
    if (props.booking.payment_status === 'refunded') return 'Refunded';
    if (pendingAmount.value <= 0) return 'Fully Paid';
    if (props.booking.pooja_amount_total_received > 0) return 'Partially Paid';
    return 'Payment Pending';
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.put(`/bookings/${props.booking.id}`, form, {
        onSuccess: () => processing.value = false,
        onError: (errorBag) => {
            processing.value = false;
            Object.assign(errors, errorBag);
        }
    });
};

const recordPayment = () => {
    if (!paymentForm.amount || paymentForm.amount <= 0) return;

    paymentProcessing.value = true;
    router.post(`/bookings/${props.booking.id}/payment`, paymentForm, {
        onSuccess: () => {
            paymentProcessing.value = false;
            paymentForm.amount = '';
        },
        onError: () => {
            paymentProcessing.value = false;
        }
    });
};

const confirmRefund = () => {
    showRefundModal.value = true;
};

const processRefund = () => {
    refundProcessing.value = true;
    router.post(`/bookings/${props.booking.id}/refund`, { payment_mode: refundMode.value }, {
        onSuccess: () => {
            refundProcessing.value = false;
            showRefundModal.value = false;
        },
        onError: () => {
            refundProcessing.value = false;
        }
    });
};
</script>

<style scoped>
.row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
.col-md-8 { flex: 0 0 66.666%; max-width: 66.666%; padding: 0.75rem; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0.75rem; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0.5rem; }

@media (max-width: 992px) {
    .col-md-8, .col-md-4 { flex: 0 0 100%; max-width: 100%; }
}

@media (max-width: 768px) {
    .col-md-6 { flex: 0 0 100%; max-width: 100%; }
}

.info-box {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1rem;
}

.info-row {
    display: flex;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    width: 100px;
    font-weight: 500;
    color: #6b7280;
}

.info-row .value {
    flex: 1;
    color: #374151;
}

.receipt-number {
    font-family: monospace;
    color: #059669 !important;
    font-weight: 600;
}

.booking-number {
    font-family: monospace;
    color: #6366f1 !important;
}

.section-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
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

.payment-summary {
    margin-bottom: 1rem;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.payment-row:last-child {
    border-bottom: none;
}

.payment-row .amount {
    font-weight: 600;
}

.pending-row {
    background: #fef3c7;
    margin: 0 -1rem;
    padding: 0.75rem 1rem;
    border-radius: 0 0 8px 8px;
}

.text-success { color: #059669; }
.text-danger { color: #dc2626; }
.text-muted { color: #9ca3af; font-size: 0.75rem; }

.payment-status-badge {
    text-align: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-paid {
    background: #d1fae5;
    color: #059669;
}

.status-partial {
    background: #dbeafe;
    color: #2563eb;
}

.status-pending {
    background: #fef3c7;
    color: #d97706;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.btn-success {
    background: #059669;
    color: white;
}

.btn-success:hover {
    background: #047857;
}

.btn-outline-danger {
    background: transparent;
    border: 1px solid #ef4444;
    color: #ef4444;
}

.btn-outline-danger:hover {
    background: #fee2e2;
}

.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
}

.modal-content h3 { margin: 0 0 1rem; font-size: 1.125rem; }
.modal-content p { margin: 0 0 1rem; color: #6b7280; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }

.mt-4 { margin-top: 1.5rem; }
.d-flex { display: flex; }
.gap-3 { gap: 1rem; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
