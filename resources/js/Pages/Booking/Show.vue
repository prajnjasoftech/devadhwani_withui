<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Booking Details</h1>
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
                        <h3 class="card-title">Booking Information</h3>
                        <div class="header-right">
                            <span class="badge" :class="getStatusBadge(booking.booking_status)">{{ booking.booking_status }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Booking Number</label>
                                <span class="booking-number">{{ booking.booking_number }}</span>
                            </div>
                            <div class="info-item">
                                <label>Pooja</label>
                                <span>{{ booking.pooja?.pooja_name || '-' }}</span>
                            </div>
                            <div class="info-item">
                                <label>Booking Date</label>
                                <span>{{ formatDate(booking.booking_date) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Period</label>
                                <span class="text-capitalize">{{ booking.period }}</span>
                            </div>
                            <div class="info-item" v-if="booking.booking_end_date">
                                <label>End Date</label>
                                <span>{{ formatDate(booking.booking_end_date) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Remarks</label>
                                <span>{{ booking.remarks || '-' }}</span>
                            </div>
                        </div>

                        <div class="section-title mt-4">Payment Details</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Pooja Amount</label>
                                <span class="amount">{{ formatCurrency(booking.pooja_amount) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Amount Received</label>
                                <span class="amount text-success">{{ formatCurrency(booking.pooja_amount_total_received) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Balance Due</label>
                                <span class="amount text-danger">{{ formatCurrency(booking.pooja_amount_remaining) }}</span>
                            </div>
                            <div class="info-item">
                                <label>Payment Status</label>
                                <span class="badge" :class="getPaymentBadge(booking.payment_status)">{{ booking.payment_status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tracking Records -->
                <div class="card mt-4" v-if="booking.trackings && booking.trackings.length > 0">
                    <div class="card-header">
                        <h3 class="card-title">Pooja Schedule</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="track in booking.trackings" :key="track.id">
                                    <td>{{ formatDate(track.pooja_date) }}</td>
                                    <td>{{ formatCurrency(track.paid_amount) }}</td>
                                    <td>{{ formatCurrency(track.due_amount) }}</td>
                                    <td>
                                        <span class="badge" :class="getPaymentBadge(track.payment_status)">{{ track.payment_status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getStatusBadge(track.booking_status)">{{ track.booking_status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Devotee</h3>
                    </div>
                    <div class="card-body">
                        <div class="devotee-card">
                            <div class="devotee-avatar">
                                {{ getInitials(booking.devotee?.devotee_name || 'G') }}
                            </div>
                            <div class="devotee-info">
                                <h4>{{ booking.devotee?.devotee_name || 'Guest Devotee' }}</h4>
                                <p v-if="booking.devotee?.devotee_phone">
                                    <i class="bi bi-telephone"></i> {{ booking.devotee.devotee_phone }}
                                </p>
                                <p v-if="booking.devotee?.nakshatra">
                                    <i class="bi bi-star"></i> {{ toMalayalamNakshatra(booking.devotee.nakshatra) }}
                                </p>
                                <p v-if="booking.devotee?.address">
                                    <i class="bi bi-geo-alt"></i> {{ booking.devotee.address }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="action-buttons">
                            <Link :href="`/bookings/${booking.id}/edit`" class="btn btn-primary btn-block">
                                <i class="bi bi-pencil"></i> Edit Booking
                            </Link>
                            <button
                                v-if="booking.booking_status === 'pending'"
                                @click="updateStatus('confirmed')"
                                class="btn btn-info btn-block"
                            >
                                <i class="bi bi-check-circle"></i> Confirm Booking
                            </button>
                            <button
                                v-if="booking.booking_status === 'confirmed'"
                                @click="updateStatus('completed')"
                                class="btn btn-success btn-block"
                            >
                                <i class="bi bi-check-all"></i> Mark Completed
                            </button>
                            <Link href="/bookings" class="btn btn-secondary btn-block">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { toMalayalamNakshatra } from '@/utils/malayalam';

const props = defineProps({
    booking: Object
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusBadge = (status) => {
    const badges = { pending: 'badge-warning', confirmed: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger' };
    return badges[status] || 'badge-secondary';
};

const getPaymentBadge = (status) => {
    const badges = { pending: 'badge-warning', partial: 'badge-info', done: 'badge-success' };
    return badges[status] || 'badge-secondary';
};

const updateStatus = (status) => {
    router.patch(`/bookings/${props.booking.id}/status`, { booking_status: status });
};
</script>

<style scoped>
.row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
.col-md-8 { flex: 0 0 66.666%; max-width: 66.666%; padding: 0.75rem; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0.75rem; }

@media (max-width: 992px) {
    .col-md-8, .col-md-4 { flex: 0 0 100%; max-width: 100%; }
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}

.info-item span {
    font-weight: 500;
    color: #374151;
}

.booking-number {
    font-family: monospace;
    color: #6366f1 !important;
}

.amount { font-size: 1.125rem; }
.text-success { color: #059669 !important; }
.text-danger { color: #dc2626 !important; }
.text-capitalize { text-transform: capitalize; }

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
    width: fit-content;
}

.badge-warning { background: #fef3c7; color: #d97706; }
.badge-info { background: #dbeafe; color: #2563eb; }
.badge-success { background: #d1fae5; color: #059669; }
.badge-danger { background: #fee2e2; color: #dc2626; }

.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
.table th { font-weight: 600; color: #374151; background: #f9fafb; font-size: 0.8125rem; }

.devotee-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.devotee-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.devotee-info h4 {
    margin: 0 0 0.5rem;
    font-size: 1.125rem;
}

.devotee-info p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.devotee-info i {
    margin-right: 0.5rem;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.btn-info { background: #3b82f6; color: white; }
.btn-info:hover { background: #2563eb; }

.mt-4 { margin-top: 1.5rem; }
</style>
