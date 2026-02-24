<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">{{ isToday ? "Today's Poojas" : 'Pooja Bookings' }}</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Bookings</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="header-left">
                    <h3 class="card-title">
                        {{ isToday ? formatDate(dateFilter) : 'All Bookings' }}
                        <span v-if="pendingCount > 0" class="pending-count">({{ pendingCount }} pending)</span>
                    </h3>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search..."
                            @input="debouncedSearch"
                        >
                    </div>
                    <select v-model="statusFilter" @change="applyFilters" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="poojaFilter" @change="applyFilters" class="filter-select">
                        <option value="">All Poojas</option>
                        <option v-for="pooja in poojas" :key="pooja.id" :value="pooja.id">{{ pooja.pooja_name }}</option>
                    </select>
                    <input type="date" v-model="dateFilter" @change="applyFilters" class="filter-date">
                    <button v-if="dateFilter" @click="clearDateFilter" class="btn btn-sm btn-secondary" title="Show all dates">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <Link href="/bookings/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> New Booking
                    </Link>
                </div>
            </div>
            <div class="card-body">
                <!-- Bulk actions for today's poojas -->
                <div v-if="isToday && pendingCount > 0" class="bulk-actions">
                    <button @click="markAllCompleted" class="btn btn-success" :disabled="markingAll">
                        <i class="bi bi-check-all"></i>
                        {{ markingAll ? 'Marking...' : `Mark All ${pendingCount} as Completed` }}
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Devotee</th>
                                <th>Pooja</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="bookings.data.length === 0">
                                <td colspan="8" class="text-center text-muted">No bookings found</td>
                            </tr>
                            <tr v-for="booking in bookings.data" :key="booking.id" :class="{ 'completed-row': booking.booking_status === 'completed' }">
                                <td>
                                    <span class="receipt-number">{{ booking.receipt_number || '-' }}</span>
                                    <span class="booking-number-sub">{{ booking.booking_number }}</span>
                                </td>
                                <td>
                                    <div class="devotee-info">
                                        <span class="devotee-name">{{ booking.devotee?.devotee_name || 'Guest' }}</span>
                                        <span class="devotee-phone" v-if="booking.devotee?.devotee_phone">{{ booking.devotee.devotee_phone }}</span>
                                    </div>
                                </td>
                                <td>{{ booking.pooja?.pooja_name || '-' }}</td>
                                <td>{{ formatDate(booking.booking_date) }}</td>
                                <td>{{ formatCurrency(booking.pooja_amount) }}</td>
                                <td>
                                    <span class="badge" :class="getPaymentBadge(booking.payment_status)">
                                        {{ booking.payment_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(booking.booking_status)">
                                        {{ booking.booking_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <!-- Mark as Completed button for pending/confirmed bookings -->
                                        <button
                                            v-if="booking.booking_status !== 'completed' && booking.booking_status !== 'cancelled'"
                                            @click="markAsCompleted(booking)"
                                            class="btn btn-sm btn-success"
                                            :disabled="completingId === booking.id"
                                            title="Mark as Completed"
                                        >
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <Link :href="`/bookings/${booking.id}`" class="btn btn-sm btn-outline">
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link :href="`/bookings/${booking.id}/edit`" class="btn btn-sm btn-outline">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="confirmDelete(booking)" class="btn btn-sm btn-outline text-danger" v-if="booking.booking_status !== 'completed'">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination" v-if="bookings.last_page > 1">
                    <Link
                        v-for="link in bookings.links"
                        :key="link.label"
                        :href="link.url"
                        class="page-link"
                        :class="{ active: link.active, disabled: !link.url }"
                        v-html="link.label"
                    ></Link>
                </div>
            </div>
        </div>

        <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
            <div class="modal-content" @click.stop>
                <h3>Cancel Booking</h3>
                <p>Are you sure you want to cancel booking <strong>{{ bookingToDelete?.booking_number }}</strong>?</p>
                <div class="modal-actions">
                    <button @click="showDeleteModal = false" class="btn btn-secondary">No, Keep it</button>
                    <button @click="deleteBooking" class="btn btn-danger" :disabled="deleting">
                        {{ deleting ? 'Cancelling...' : 'Yes, Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    bookings: Object,
    poojas: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const poojaFilter = ref(props.filters?.pooja_id || '');
const dateFilter = ref(props.filters?.date || '');
const showDeleteModal = ref(false);
const bookingToDelete = ref(null);
const deleting = ref(false);
const completingId = ref(null);
const markingAll = ref(false);

// Check if current filter is today
const isToday = computed(() => {
    if (!dateFilter.value) return false;
    const today = new Date().toISOString().split('T')[0];
    return dateFilter.value === today;
});

// Count pending bookings
const pendingCount = computed(() => {
    return props.bookings.data.filter(b => b.booking_status !== 'completed' && b.booking_status !== 'cancelled').length;
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'badge-warning',
        confirmed: 'badge-info',
        completed: 'badge-success',
        cancelled: 'badge-danger'
    };
    return badges[status] || 'badge-secondary';
};

const getPaymentBadge = (status) => {
    const badges = {
        pending: 'badge-warning',
        partial: 'badge-info',
        success: 'badge-success',
        done: 'badge-success',
        failed: 'badge-danger',
        refunded: 'badge-secondary'
    };
    return badges[status] || 'badge-secondary';
};

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    const params = {
        search: search.value,
        status: statusFilter.value,
        pooja_id: poojaFilter.value,
    };
    if (dateFilter.value) {
        params.date = dateFilter.value;
    } else {
        params.show_all = 1;
    }
    router.get('/bookings', params, { preserveState: true, replace: true });
};

const clearDateFilter = () => {
    dateFilter.value = '';
    applyFilters();
};

const confirmDelete = (booking) => {
    bookingToDelete.value = booking;
    showDeleteModal.value = true;
};

const deleteBooking = () => {
    deleting.value = true;
    router.delete(`/bookings/${bookingToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleting.value = false;
        },
        onError: () => {
            deleting.value = false;
        }
    });
};

const markAsCompleted = (booking) => {
    completingId.value = booking.id;
    router.patch(`/bookings/${booking.id}/status`, {
        booking_status: 'completed'
    }, {
        preserveScroll: true,
        onSuccess: () => {
            completingId.value = null;
        },
        onError: () => {
            completingId.value = null;
        }
    });
};

const markAllCompleted = () => {
    markingAll.value = true;
    const pendingBookings = props.bookings.data.filter(b => b.booking_status !== 'completed' && b.booking_status !== 'cancelled');

    // Mark each booking as completed sequentially
    const markNext = (index) => {
        if (index >= pendingBookings.length) {
            markingAll.value = false;
            return;
        }

        router.patch(`/bookings/${pendingBookings[index].id}/status`, {
            booking_status: 'completed'
        }, {
            preserveScroll: true,
            onSuccess: () => markNext(index + 1),
            onError: () => {
                markingAll.value = false;
            }
        });
    };

    markNext(0);
};
</script>

<style scoped>
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-right {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.pending-count {
    font-size: 0.875rem;
    color: #d97706;
    font-weight: 500;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-box input {
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    width: 140px;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
}

.filter-select, .filter-date {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
}

.filter-select:focus, .filter-date:focus {
    outline: none;
    border-color: var(--primary);
}

.bulk-actions {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.table-responsive { overflow-x: auto; }

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 0.75rem 0.5rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.table th {
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    font-size: 0.8125rem;
}

.completed-row {
    background: #f0fdf4;
}

.completed-row td {
    color: #6b7280;
}

.receipt-number {
    font-family: monospace;
    font-size: 0.8125rem;
    color: #059669;
    font-weight: 600;
    display: block;
}

.booking-number-sub {
    font-family: monospace;
    font-size: 0.6875rem;
    color: #9ca3af;
    display: block;
}

.devotee-info {
    display: flex;
    flex-direction: column;
}

.devotee-name {
    font-weight: 500;
}

.devotee-phone {
    font-size: 0.75rem;
    color: #9ca3af;
}

.text-center { text-align: center; }
.text-muted { color: #9ca3af; }
.text-danger { color: #ef4444 !important; }

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
.badge-secondary { background: #e5e7eb; color: #6b7280; }

.action-btns {
    display: flex;
    gap: 0.25rem;
}

.btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.8125rem;
}

.btn-outline {
    background: transparent;
    border: 1px solid #e2e8f0;
}

.btn-outline:hover { background: #f3f4f6; }

.btn-success {
    background: #059669;
    color: white;
    border: none;
}

.btn-success:hover { background: #047857; }
.btn-success:disabled { background: #a7f3d0; cursor: not-allowed; }

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin-top: 1rem;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #374151;
    text-decoration: none;
}

.page-link:hover:not(.disabled) { background: #f3f4f6; }
.page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
.page-link.disabled { opacity: 0.5; cursor: not-allowed; }

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

@media (max-width: 768px) {
    .card-header { flex-direction: column; align-items: stretch; }
    .header-right { flex-direction: column; }
    .search-box input, .filter-select, .filter-date { width: 100%; }
    .bulk-actions { flex-direction: column; }
}
</style>
