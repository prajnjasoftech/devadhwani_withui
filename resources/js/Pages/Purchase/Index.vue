<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Purchases & Donations</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Purchases</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="header-left">
                    <h3 class="card-title">All Purchases</h3>
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
                    <select v-model="modeFilter" @change="applyFilters" class="filter-select">
                        <option value="">All Types</option>
                        <option value="purchase">Purchase</option>
                        <option value="donation">Donation</option>
                    </select>
                    <select v-model="supplierFilter" @change="applyFilters" class="filter-select">
                        <option value="">All Suppliers</option>
                        <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                    </select>
                    <Link href="/purchases/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Purchase
                    </Link>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="purchases.data.length === 0">
                                <td colspan="9" class="text-center text-muted">No purchases found</td>
                            </tr>
                            <tr v-for="(purchase, index) in purchases.data" :key="purchase.id">
                                <td>{{ (purchases.current_page - 1) * purchases.per_page + index + 1 }}</td>
                                <td>{{ formatDate(purchase.received_date) }}</td>
                                <td>{{ purchase.item?.item_with_category || purchase.item?.item_name || '-' }}</td>
                                <td>{{ purchase.supplier?.name || '-' }}</td>
                                <td>{{ purchase.quantity }}</td>
                                <td>{{ formatCurrency(purchase.unit_price) }}</td>
                                <td>{{ formatCurrency(purchase.total_price) }}</td>
                                <td>
                                    <span class="badge" :class="purchase.mode === 'purchase' ? 'badge-primary' : 'badge-success'">
                                        {{ purchase.mode }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <Link :href="`/purchases/${purchase.id}/edit`" class="btn btn-sm btn-outline">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="confirmDelete(purchase)" class="btn btn-sm btn-outline text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination" v-if="purchases.last_page > 1">
                    <Link
                        v-for="link in purchases.links"
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
                <h3>Delete Purchase</h3>
                <p>Are you sure you want to delete this purchase record?</p>
                <div class="modal-actions">
                    <button @click="showDeleteModal = false" class="btn btn-secondary">Cancel</button>
                    <button @click="deletePurchase" class="btn btn-danger" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    purchases: Object,
    suppliers: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const modeFilter = ref(props.filters?.mode || '');
const supplierFilter = ref(props.filters?.supplier_id || '');
const showDeleteModal = ref(false);
const purchaseToDelete = ref(null);
const deleting = ref(false);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0 }).format(amount || 0);
};

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get('/purchases', {
        search: search.value,
        mode: modeFilter.value,
        supplier_id: supplierFilter.value
    }, { preserveState: true, replace: true });
};

const confirmDelete = (purchase) => {
    purchaseToDelete.value = purchase;
    showDeleteModal.value = true;
};

const deletePurchase = () => {
    deleting.value = true;
    router.delete(`/purchases/${purchaseToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleting.value = false;
        },
        onError: () => {
            deleting.value = false;
        }
    });
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
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
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
    width: 150px;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    min-width: 120px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
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

.table td {
    font-size: 0.875rem;
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

.badge-primary {
    background: #e0e7ff;
    color: #4f46e5;
}

.badge-success {
    background: #d1fae5;
    color: #059669;
}

.action-btns {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.8125rem;
}

.btn-outline {
    background: transparent;
    border: 1px solid #e2e8f0;
}

.btn-outline:hover {
    background: #f3f4f6;
}

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

.page-link:hover:not(.disabled) {
    background: #f3f4f6;
}

.page-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
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

.modal-content h3 {
    margin: 0 0 1rem;
    font-size: 1.125rem;
}

.modal-content p {
    margin: 0 0 1rem;
    color: #6b7280;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }

    .header-right {
        flex-direction: column;
    }

    .search-box input,
    .filter-select {
        width: 100%;
    }
}
</style>
