<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <div class="page-breadcrumb">
                <i class="bi bi-house"></i>
                <span>Home</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-card-value">{{ props.stats.totalDevotees }}</div>
                    <div class="stat-card-label">Total Devotees</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-card-value">{{ props.stats.todayBookings }}</div>
                    <div class="stat-card-label">Today's Bookings</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div class="stat-card-value">{{ formatCurrency(props.stats.monthlyRevenue) }}</div>
                    <div class="stat-card-label">Monthly Revenue</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon info">
                        <i class="bi bi-flower1"></i>
                    </div>
                    <div class="stat-card-value">{{ props.stats.pendingPoojas }}</div>
                    <div class="stat-card-label">Pending Poojas</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Bookings -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Bookings</h3>
                        <Link href="/bookings" class="btn btn-sm btn-secondary">View All</Link>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Booking #</th>
                                    <th>Devotee</th>
                                    <th>Pooja</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in props.recentBookings" :key="booking.id">
                                    <td class="fw-bold">{{ booking.booking_number }}</td>
                                    <td>{{ booking.devotee_name }}</td>
                                    <td>{{ booking.pooja_name }}</td>
                                    <td>{{ formatDate(booking.booking_date) }}</td>
                                    <td>
                                        <span class="badge" :class="getStatusClass(booking.status)">
                                            {{ booking.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="props.recentBookings.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No recent bookings
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Today's Transaction Summary -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="transaction-item income">
                            <div class="transaction-icon">
                                <i class="bi bi-arrow-down-circle"></i>
                            </div>
                            <div class="transaction-details">
                                <span class="transaction-label">Income</span>
                                <span class="transaction-value">{{ formatCurrency(props.transactionSummary.totalBookingAmount) }}</span>
                            </div>
                            <span class="transaction-meta">{{ props.transactionSummary.totalPoojaCount }} poojas</span>
                        </div>
                        <div class="transaction-item expense">
                            <div class="transaction-icon">
                                <i class="bi bi-arrow-up-circle"></i>
                            </div>
                            <div class="transaction-details">
                                <span class="transaction-label">Expense</span>
                                <span class="transaction-value">{{ formatCurrency(props.transactionSummary.totalExpenseAmount) }}</span>
                            </div>
                            <span class="transaction-meta">purchases</span>
                        </div>
                        <div class="transaction-item" :class="netBalance >= 0 ? 'profit' : 'loss'">
                            <div class="transaction-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div class="transaction-details">
                                <span class="transaction-label">Net</span>
                                <span class="transaction-value">{{ formatCurrency(netBalance) }}</span>
                            </div>
                            <span class="transaction-meta">{{ netBalance >= 0 ? 'profit' : 'loss' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Today's Panchang -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Today's Panchang</h3>
                    </div>
                    <div class="card-body">
                        <div class="panchang-item" v-for="item in panchangItems" :key="item.label">
                            <span class="panchang-label">{{ item.label }}</span>
                            <span class="panchang-value">{{ item.value }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <Link href="/bookings/create" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> New Booking
                            </Link>
                            <Link href="/devotees/create" class="btn btn-secondary">
                                <i class="bi bi-person-plus"></i> Add Devotee
                            </Link>
                            <Link href="/reports" class="btn btn-secondary">
                                <i class="bi bi-file-earmark-text"></i> Generate Report
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalDevotees: 0,
            todayBookings: 0,
            monthlyRevenue: 0,
            pendingPoojas: 0
        })
    },
    transactionSummary: {
        type: Object,
        default: () => ({
            totalBookingAmount: '0.00',
            totalExpenseAmount: '0.00',
            totalPoojaCount: 0,
            poojaWiseSummary: [],
            expenseWiseSummary: []
        })
    },
    recentBookings: {
        type: Array,
        default: () => []
    },
    panchang: {
        type: Object,
        default: () => ({})
    }
});

// Computed net balance
const netBalance = computed(() => {
    const income = parseFloat(props.transactionSummary.totalBookingAmount) || 0;
    const expense = parseFloat(props.transactionSummary.totalExpenseAmount) || 0;
    return income - expense;
});

// Panchang items from props
const panchangItems = computed(() => {
    if (!props.panchang) return [];
    return [
        { label: 'Tithi', value: props.panchang.tithi || 'N/A' },
        { label: 'Nakshatra', value: props.panchang.nakshatra || 'N/A' },
        { label: 'Yoga', value: props.panchang.yoga || 'N/A' },
        { label: 'Karana', value: props.panchang.karana || 'N/A' },
        { label: 'Sunrise', value: props.panchang.sunrise || 'N/A' },
        { label: 'Sunset', value: props.panchang.sunset || 'N/A' },
    ];
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const getStatusClass = (status) => {
    const classes = {
        completed: 'badge-success',
        pending: 'badge-warning',
        confirmed: 'badge-primary',
        cancelled: 'badge-danger'
    };
    return classes[status] || 'badge-secondary';
};
</script>

<style scoped>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.75rem;
}

.col-md-6, .col-xl-3, .col-lg-8, .col-lg-4 {
    padding: 0.75rem;
}

.col-md-6 { flex: 0 0 50%; max-width: 50%; }
.col-lg-8 { flex: 0 0 66.666%; max-width: 66.666%; }
.col-lg-4 { flex: 0 0 33.333%; max-width: 33.333%; }

@media (min-width: 1200px) {
    .col-xl-3 { flex: 0 0 25%; max-width: 25%; }
}

@media (max-width: 768px) {
    .col-md-6, .col-lg-8, .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.g-4 {
    gap: 1.5rem;
}

.panchang-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.panchang-item:last-child {
    border-bottom: none;
}

.panchang-label {
    color: #64748b;
    font-size: 0.875rem;
}

.panchang-value {
    font-weight: 600;
    color: var(--dark);
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

.py-4 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

/* Transaction Summary Compact Styles */
.transaction-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background: #f8fafc;
}

.transaction-item:last-child {
    margin-bottom: 0;
}

.transaction-item.income {
    background: #ecfdf5;
}

.transaction-item.expense {
    background: #fef2f2;
}

.transaction-item.profit {
    background: #eff6ff;
}

.transaction-item.loss {
    background: #fefce8;
}

.transaction-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.transaction-item.income .transaction-icon {
    background: #10b981;
    color: white;
}

.transaction-item.expense .transaction-icon {
    background: #ef4444;
    color: white;
}

.transaction-item.profit .transaction-icon {
    background: #3b82f6;
    color: white;
}

.transaction-item.loss .transaction-icon {
    background: #f59e0b;
    color: white;
}

.transaction-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.transaction-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
}

.transaction-value {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}

.transaction-meta {
    font-size: 0.75rem;
    color: #94a3b8;
}

.text-success {
    color: #10b981 !important;
}

.text-danger {
    color: #ef4444 !important;
}

.text-end {
    text-align: right;
}

.text-center {
    text-align: center;
}
</style>
