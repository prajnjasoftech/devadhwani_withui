<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Devotee Profile</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <Link href="/devotees">Devotees</Link>
                <i class="bi bi-chevron-right"></i>
                <span>{{ devotee.devotee_name }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-large">
                            {{ getInitials(devotee.devotee_name) }}
                        </div>
                        <h3 class="devotee-name">{{ devotee.devotee_name }}</h3>
                        <p class="devotee-phone" v-if="devotee.devotee_phone">
                            <i class="bi bi-telephone"></i> {{ devotee.devotee_phone }}
                        </p>
                        <div class="nakshatra-badge" v-if="devotee.nakshatra">
                            <i class="bi bi-star-fill"></i> {{ toMalayalamNakshatra(devotee.nakshatra) }}
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="detail-item">
                            <label>Address</label>
                            <span>{{ devotee.address || 'Not provided' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Registered</label>
                            <span>{{ formatDate(devotee.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <div class="action-buttons">
                            <Link :href="`/devotees/${devotee.id}/edit`" class="btn btn-primary btn-block">
                                <i class="bi bi-pencil"></i> Edit Devotee
                            </Link>
                            <Link href="/bookings/create" class="btn btn-success btn-block">
                                <i class="bi bi-plus-circle"></i> New Booking
                            </Link>
                            <Link href="/devotees" class="btn btn-secondary btn-block">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Bookings</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" v-if="bookings.length > 0">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Pooja</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="booking in bookings" :key="booking.id">
                                        <td>{{ formatDate(booking.booking_date) }}</td>
                                        <td>{{ booking.pooja?.pooja_name || '-' }}</td>
                                        <td>{{ formatCurrency(booking.pooja_amount) }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusBadge(booking.booking_status)">
                                                {{ booking.booking_status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <p>No bookings yet</p>
                            <Link href="/bookings/create" class="btn btn-primary btn-sm">
                                Create First Booking
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { toMalayalamNakshatra } from '@/utils/malayalam';

const props = defineProps({
    devotee: Object,
    bookings: Array
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

const getStatusBadge = (status) => {
    const badges = { pending: 'badge-warning', confirmed: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger' };
    return badges[status] || 'badge-secondary';
};
</script>

<style scoped>
.row { display: flex; flex-wrap: wrap; margin: -0.75rem; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0.75rem; }
.col-md-8 { flex: 0 0 66.666%; max-width: 66.666%; padding: 0.75rem; }

@media (max-width: 992px) {
    .col-md-4, .col-md-8 { flex: 0 0 100%; max-width: 100%; }
}

.text-center { text-align: center; }

.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0 auto 1rem;
}

.devotee-name {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    color: #374151;
}

.devotee-phone {
    margin: 0 0 0.75rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.devotee-phone i {
    margin-right: 0.5rem;
}

.nakshatra-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: #fef3c7;
    color: #d97706;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.detail-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item label {
    display: block;
    font-size: 0.75rem;
    color: #9ca3af;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.detail-item span {
    color: #374151;
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

.btn-success { background: #059669; color: white; }
.btn-success:hover { background: #047857; }

.table-responsive { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
.table th { font-weight: 600; color: #374151; background: #f9fafb; font-size: 0.8125rem; }

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
}

.badge-warning { background: #fef3c7; color: #d97706; }
.badge-info { background: #dbeafe; color: #2563eb; }
.badge-success { background: #d1fae5; color: #059669; }
.badge-danger { background: #fee2e2; color: #dc2626; }

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    margin: 0 0 1rem;
}

.mt-4 { margin-top: 1.5rem; }
</style>
