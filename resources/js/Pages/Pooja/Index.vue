<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Poojas</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Poojas</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            placeholder="Search poojas..."
                            v-model="search"
                            @input="debouncedSearch"
                        >
                    </div>
                    <select class="form-select" v-model="period" @change="applyFilters" style="width: auto;">
                        <option value="">All Periods</option>
                        <option value="once">Once</option>
                        <option value="daily">Daily</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <Link href="/poojas/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Pooja
                </Link>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Devotees Required</th>
                            <th>Next Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="pooja in poojas.data" :key="pooja.id">
                            <td class="fw-bold">{{ pooja.pooja_name }}</td>
                            <td>
                                <span class="badge" :class="getPeriodClass(pooja.period)">
                                    {{ pooja.period }}
                                </span>
                            </td>
                            <td>{{ formatCurrency(pooja.amount) }}</td>
                            <td>
                                <i v-if="pooja.devotees_required" class="bi bi-check-circle text-success"></i>
                                <i v-else class="bi bi-x-circle text-muted"></i>
                            </td>
                            <td>{{ pooja.next_pooja_perform_date ? formatDate(pooja.next_pooja_perform_date) : '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <Link :href="`/poojas/${pooja.id}/edit`" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button class="btn btn-sm btn-danger" @click="confirmDelete(pooja)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="poojas.data.length === 0">
                            <td colspan="6" class="text-center text-muted py-4">
                                No poojas found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer" v-if="poojas.last_page > 1">
                <div class="pagination">
                    <button
                        class="btn btn-sm btn-secondary"
                        :disabled="poojas.current_page === 1"
                        @click="goToPage(poojas.current_page - 1)"
                    >
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="pagination-info">
                        Page {{ poojas.current_page }} of {{ poojas.last_page }}
                    </span>
                    <button
                        class="btn btn-sm btn-secondary"
                        :disabled="poojas.current_page === poojas.last_page"
                        @click="goToPage(poojas.current_page + 1)"
                    >
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>Delete Pooja</h3>
                    <button class="btn-close" @click="showDeleteModal = false">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ poojaToDelete?.pooja_name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button class="btn btn-danger" @click="deletePooja">Delete</button>
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
    poojas: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const period = ref(props.filters?.period || '');
const showDeleteModal = ref(false);
const poojaToDelete = ref(null);

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get('/poojas', {
        search: search.value || undefined,
        period: period.value || undefined,
    }, { preserveState: true });
};

const goToPage = (page) => {
    router.get('/poojas', {
        page,
        search: search.value || undefined,
        period: period.value || undefined,
    }, { preserveState: true });
};

const confirmDelete = (pooja) => {
    poojaToDelete.value = pooja;
    showDeleteModal.value = true;
};

const deletePooja = () => {
    router.delete(`/poojas/${poojaToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            poojaToDelete.value = null;
        }
    });
};

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

const getPeriodClass = (period) => {
    const classes = {
        once: 'badge-secondary',
        daily: 'badge-primary',
        monthly: 'badge-success',
        yearly: 'badge-warning'
    };
    return classes[period] || 'badge-secondary';
};
</script>

<style scoped>
.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.search-box input {
    padding: 0.5rem 0.75rem 0.5rem 2.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    width: 250px;
}

.form-select {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.btn-group {
    display: flex;
    gap: 0.25rem;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.pagination-info {
    color: #64748b;
    font-size: 0.875rem;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.gap-3 {
    gap: 1rem;
}

.py-4 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
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
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #64748b;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
}
</style>
