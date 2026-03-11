<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Reports</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Reports</span>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilter" class="filter-form">
                    <div class="filter-group">
                        <label>Start Date</label>
                        <input type="date" v-model="filterForm.start_date" class="form-control">
                    </div>
                    <div class="filter-group">
                        <label>End Date</label>
                        <input type="date" v-model="filterForm.end_date" class="form-control">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Apply Filter
                        </button>
                        <button type="button" @click="resetFilter" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card income">
                <div class="summary-icon">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>
                <div class="summary-content">
                    <h3>Total Income</h3>
                    <p class="amount">{{ formatCurrency(summary.total_income) }}</p>
                </div>
            </div>
            <div class="summary-card expense">
                <div class="summary-icon">
                    <i class="bi bi-arrow-up-circle"></i>
                </div>
                <div class="summary-content">
                    <h3>Total Expense</h3>
                    <p class="amount">{{ formatCurrency(summary.total_expense) }}</p>
                </div>
            </div>
            <div class="summary-card net" :class="{ negative: summary.net_amount < 0 }">
                <div class="summary-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="summary-content">
                    <h3>Net Amount</h3>
                    <p class="amount">{{ formatCurrency(summary.net_amount) }}</p>
                </div>
            </div>
            <div class="summary-card bookings">
                <div class="summary-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="summary-content">
                    <h3>Total Bookings</h3>
                    <p class="amount">{{ summary.total_bookings }}</p>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="row mt-4">
            <!-- Pooja-wise Income -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-flower1"></i> Pooja-wise Income
                        </h3>
                    </div>
                    <div class="card-body">
                        <div v-if="poojaDetails.length === 0" class="text-center text-muted py-4">
                            No pooja bookings in this period
                        </div>
                        <div v-else class="report-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pooja</th>
                                        <th class="text-center">Bookings</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pooja in poojaDetails" :key="pooja.pooja_id">
                                        <td>{{ pooja.pooja_name }}</td>
                                        <td class="text-center">{{ pooja.total_bookings }}</td>
                                        <td class="text-right">{{ formatCurrency(pooja.total_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item-wise Expense -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-box-seam"></i> Item-wise Expense
                        </h3>
                    </div>
                    <div class="card-body">
                        <div v-if="expenseDetails.length === 0" class="text-center text-muted py-4">
                            No expenses in this period
                        </div>
                        <div v-else class="report-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="expense in expenseDetails" :key="expense.item_id">
                                        <td>{{ expense.item_name }}</td>
                                        <td class="text-center">{{ expense.total_quantity }}</td>
                                        <td class="text-right">{{ formatCurrency(expense.total_expense) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    filters: Object,
    summary: Object,
    poojaDetails: Array,
    expenseDetails: Array,
});

const filterForm = reactive({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const applyFilter = () => {
    router.get('/reports', filterForm, { preserveState: true });
};

const resetFilter = () => {
    const today = new Date().toISOString().split('T')[0];
    filterForm.start_date = today;
    filterForm.end_date = today;
    router.get('/reports', {}, { preserveState: true });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
    }).format(amount || 0);
};
</script>

<style scoped>
.filter-form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #64748b;
}

.filter-group input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 1200px) {
    .summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.summary-card.income .summary-icon {
    background: #dcfce7;
    color: #16a34a;
}

.summary-card.expense .summary-icon {
    background: #fee2e2;
    color: #dc2626;
}

.summary-card.net .summary-icon {
    background: #dbeafe;
    color: #2563eb;
}

.summary-card.net.negative .summary-icon {
    background: #fef3c7;
    color: #d97706;
}

.summary-card.bookings .summary-icon {
    background: #f3e8ff;
    color: #7c3aed;
}

.summary-content h3 {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
    font-weight: 500;
}

.summary-content .amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0.25rem 0 0;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.75rem;
}

.col-lg-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0.75rem;
}

@media (max-width: 992px) {
    .col-lg-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.mt-4 {
    margin-top: 1.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.card-header .card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.report-table {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
    background: #f8fafc;
}

.table td {
    color: #1e293b;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.text-muted {
    color: #94a3b8;
}

.py-4 {
    padding: 1.5rem 0;
}
</style>
