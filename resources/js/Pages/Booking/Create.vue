<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">New Booking</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/bookings">Bookings</Link>
                <i class="bi bi-chevron-right"></i>
                <span>New</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Booking Details</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <!-- Pooja Section (First) -->
                    <div class="section-title">Pooja Details</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Select Pooja *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.pooja_id }"
                                    v-model="form.pooja_id"
                                    @change="onPoojaChange"
                                    required
                                >
                                    <option value="">Select pooja</option>
                                    <option v-for="pooja in poojas" :key="pooja.id" :value="pooja.id">
                                        {{ pooja.pooja_name }} - {{ formatCurrency(pooja.amount) }}
                                        {{ pooja.next_pooja_perform_date ? ' (Next: ' + formatDateShort(pooja.next_pooja_perform_date) + ')' : '' }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.pooja_id">{{ errors.pooja_id }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Period *</label>
                                <select
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.period }"
                                    v-model="form.period"
                                    required
                                >
                                    <option value="once">Once</option>
                                    <option value="daily">Daily</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                <div class="invalid-feedback" v-if="errors.period">{{ errors.period }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Booking Date *</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.booking_date, 'auto-filled': selectedPoojaNextDate }"
                                    v-model="form.booking_date"
                                    required
                                >
                                <div class="invalid-feedback" v-if="errors.booking_date">{{ errors.booking_date }}</div>
                                <div class="form-hint" v-if="selectedPoojaNextDate">
                                    <i class="bi bi-info-circle"></i> Next scheduled date for this pooja
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" v-if="form.period !== 'once'">
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

                    <!-- Devotees Section -->
                    <div class="section-title mt-4">
                        <div class="section-title-row">
                            <span>Devotees ({{ form.devotees.length }})</span>
                            <button type="button" class="btn btn-sm btn-success" @click="addDevotee">
                                <i class="bi bi-plus-lg"></i> Add Devotee
                            </button>
                        </div>
                    </div>

                    <div class="devotee-list">
                        <div
                            v-for="(devotee, index) in form.devotees"
                            :key="index"
                            class="devotee-card"
                        >
                            <div class="devotee-header">
                                <span class="devotee-number">Devotee {{ index + 1 }}</span>
                                <button
                                    type="button"
                                    class="btn-remove"
                                    @click="removeDevotee(index)"
                                    v-if="form.devotees.length > 1"
                                >
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="devotee-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Select Existing Devotee</label>
                                            <select
                                                class="form-control"
                                                v-model="devotee.devotee_id"
                                                @change="onDevoteeSelect(index)"
                                            >
                                                <option value="">-- New Devotee --</option>
                                                <option
                                                    v-for="d in devotees"
                                                    :key="d.id"
                                                    :value="d.id"
                                                >
                                                    {{ d.devotee_name }} ({{ d.devotee_phone || 'No phone' }})
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" v-if="!devotee.devotee_id">
                                        <div class="form-group">
                                            <label class="form-label">Nakshatra</label>
                                            <select class="form-control" v-model="devotee.nakshatra">
                                                <option value="">Select nakshatra</option>
                                                <option v-for="nak in nakshatras" :key="nak" :value="nak">{{ nak }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="!devotee.devotee_id">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Devotee Name *</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="devotee.devotee_name"
                                                placeholder="Enter devotee name"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Phone Number</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="devotee.devotee_phone"
                                                placeholder="Enter phone number"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="section-title mt-4">Payment</div>
                    <div class="calculation-summary" v-if="form.pooja_amount && (form.devotees.length > 1 || periodCount > 1)">
                        <span class="calc-item">{{ formatCurrency(form.pooja_amount) }}</span>
                        <span class="calc-operator">x</span>
                        <span class="calc-item">{{ form.devotees.length }} devotee{{ form.devotees.length > 1 ? 's' : '' }}</span>
                        <span class="calc-operator" v-if="periodCount > 1">x</span>
                        <span class="calc-item" v-if="periodCount > 1">{{ periodCount }} {{ form.period === 'daily' ? 'days' : form.period === 'monthly' ? 'months' : 'years' }}</span>
                        <span class="calc-operator">=</span>
                        <span class="calc-total">{{ formatCurrency(totalAmount) }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Amount Per Occurrence</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form.pooja_amount"
                                    min="0"
                                    step="0.01"
                                >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Total Amount</label>
                                <input
                                    type="text"
                                    class="form-control total-highlight"
                                    :value="formatCurrency(totalAmount)"
                                    disabled
                                >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Amount Received</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form.pooja_amount_receipt"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Balance Due</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :value="formatCurrency(balanceDue)"
                                    disabled
                                >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Payment Mode</label>
                                <select class="form-control" v-model="form.payment_mode">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
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
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" :disabled="processing">
                            <span v-if="processing"><i class="bi bi-arrow-repeat spin"></i> Saving...</span>
                            <span v-else>
                                <i class="bi bi-check-circle"></i>
                                Create {{ form.devotees.length > 1 ? form.devotees.length + ' Bookings' : 'Booking' }}
                            </span>
                        </button>
                        <Link href="/bookings" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    poojas: Array,
    devotees: Array
});

const nakshatras = [
    'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira', 'Ardra',
    'Punarvasu', 'Pushya', 'Ashlesha', 'Magha', 'Purva Phalguni', 'Uttara Phalguni',
    'Hasta', 'Chitra', 'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha',
    'Moola', 'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta', 'Shatabhisha',
    'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati'
];

const createEmptyDevotee = () => ({
    devotee_id: '',
    devotee_name: '',
    devotee_phone: '',
    nakshatra: ''
});

const form = reactive({
    pooja_id: '',
    period: 'once',
    booking_date: new Date().toISOString().split('T')[0],
    booking_end_date: '',
    pooja_amount: '',
    pooja_amount_receipt: '',
    payment_mode: 'cash',
    remarks: '',
    devotees: [createEmptyDevotee()]
});

const errors = reactive({});
const processing = ref(false);

const periodCount = computed(() => {
    if (form.period === 'once' || !form.booking_date || !form.booking_end_date) {
        return 1;
    }

    const start = new Date(form.booking_date);
    const end = new Date(form.booking_end_date);

    if (end < start) return 1;

    if (form.period === 'daily') {
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return diffDays;
    }

    if (form.period === 'monthly') {
        const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth()) + 1;
        return Math.max(1, months);
    }

    if (form.period === 'yearly') {
        const years = end.getFullYear() - start.getFullYear() + 1;
        return Math.max(1, years);
    }

    return 1;
});

const totalAmount = computed(() => {
    const amount = parseFloat(form.pooja_amount) || 0;
    return amount * form.devotees.length * periodCount.value;
});

const balanceDue = computed(() => {
    const received = parseFloat(form.pooja_amount_receipt) || 0;
    return Math.max(0, totalAmount.value - received);
});

const selectedPoojaNextDate = computed(() => {
    if (!form.pooja_id) return null;
    const pooja = props.poojas.find(p => p.id === form.pooja_id);
    if (!pooja || !pooja.next_pooja_perform_date) return null;
    const nextDate = pooja.next_pooja_perform_date.split('T')[0];
    return form.booking_date === nextDate ? nextDate : null;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

const formatDateShort = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
};

const addDevotee = () => {
    form.devotees.push(createEmptyDevotee());
};

const removeDevotee = (index) => {
    if (form.devotees.length > 1) {
        form.devotees.splice(index, 1);
    }
};

const onDevoteeSelect = (index) => {
    if (form.devotees[index].devotee_id) {
        form.devotees[index].devotee_name = '';
        form.devotees[index].devotee_phone = '';
        form.devotees[index].nakshatra = '';
    }
};

const onPoojaChange = () => {
    const pooja = props.poojas.find(p => p.id === form.pooja_id);
    if (pooja) {
        form.pooja_amount = pooja.amount;
        form.period = pooja.period || 'once';
        // Set booking date to pooja's next perform date if available
        if (pooja.next_pooja_perform_date) {
            form.booking_date = pooja.next_pooja_perform_date.split('T')[0];
        }
    }
};

const submit = () => {
    processing.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    router.post('/bookings', {
        ...form,
        pooja_amount: totalAmount.value
    }, {
        onSuccess: () => processing.value = false,
        onError: (errorBag) => {
            processing.value = false;
            Object.assign(errors, errorBag);
        }
    });
};
</script>

<style scoped>
.section-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.section-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.row { display: flex; flex-wrap: wrap; margin: -0.5rem; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0.5rem; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0.5rem; }
.col-md-3 { flex: 0 0 25%; max-width: 25%; padding: 0.5rem; }

@media (max-width: 768px) {
    .col-md-6, .col-md-4, .col-md-3 { flex: 0 0 100%; max-width: 100%; }
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

.form-control:disabled { background: #f3f4f6; color: #6b7280; }
.form-control.is-invalid { border-color: #ef4444; }
.form-control.auto-filled { border-color: #059669; background: #f0fdf4; }
.invalid-feedback { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }
.form-hint { color: #059669; font-size: 0.75rem; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }

.devotee-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.devotee-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.devotee-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e2e8f0;
}

.devotee-number {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.btn-remove {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-remove:hover {
    color: #dc2626;
}

.devotee-body {
    padding: 1rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}

.btn-success {
    background: #059669;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.btn-success:hover {
    background: #047857;
}

.mt-4 { margin-top: 1.5rem; }
.d-flex { display: flex; }
.gap-3 { gap: 1rem; }

.calculation-summary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.calc-item {
    font-weight: 500;
    color: #374151;
}

.calc-operator {
    color: #9ca3af;
}

.calc-total {
    font-weight: 600;
    color: #059669;
    font-size: 1.1rem;
}

.total-highlight {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #059669 !important;
    font-weight: 600;
}

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
