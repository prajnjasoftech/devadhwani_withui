<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Devotees</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Devotees</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="header-left">
                    <h3 class="card-title">All Devotees</h3>
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
                    <select v-model="nakshatraFilter" @change="applyFilters" class="filter-select">
                        <option value="">All Nakshatras</option>
                        <option v-for="nak in nakshatras" :key="nak" :value="nak">{{ nak }}</option>
                    </select>
                    <Link href="/devotees/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Devotee
                    </Link>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Nakshatra</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="devotees.data.length === 0">
                                <td colspan="6" class="text-center text-muted">No devotees found</td>
                            </tr>
                            <tr v-for="(devotee, index) in devotees.data" :key="devotee.id">
                                <td>{{ (devotees.current_page - 1) * devotees.per_page + index + 1 }}</td>
                                <td>
                                    <div class="devotee-name">
                                        <span class="avatar">{{ getInitials(devotee.devotee_name) }}</span>
                                        <span>{{ devotee.devotee_name }}</span>
                                    </div>
                                </td>
                                <td>{{ devotee.devotee_phone || '-' }}</td>
                                <td>
                                    <span class="nakshatra-badge" v-if="devotee.nakshatra">{{ devotee.nakshatra }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td>{{ devotee.trackings_count || 0 }}</td>
                                <td>
                                    <div class="action-btns">
                                        <Link :href="`/devotees/${devotee.id}`" class="btn btn-sm btn-outline">
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link :href="`/devotees/${devotee.id}/edit`" class="btn btn-sm btn-outline">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="confirmDelete(devotee)" class="btn btn-sm btn-outline text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination" v-if="devotees.last_page > 1">
                    <Link
                        v-for="link in devotees.links"
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
                <h3>Delete Devotee</h3>
                <p>Are you sure you want to delete "{{ devoteeToDelete?.devotee_name }}"?</p>
                <div class="modal-actions">
                    <button @click="showDeleteModal = false" class="btn btn-secondary">Cancel</button>
                    <button @click="deleteDevotee" class="btn btn-danger" :disabled="deleting">
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
    devotees: Object,
    nakshatras: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const nakshatraFilter = ref(props.filters?.nakshatra || '');
const showDeleteModal = ref(false);
const devoteeToDelete = ref(null);
const deleting = ref(false);

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get('/devotees', {
        search: search.value,
        nakshatra: nakshatraFilter.value
    }, { preserveState: true, replace: true });
};

const confirmDelete = (devotee) => {
    devoteeToDelete.value = devotee;
    showDeleteModal.value = true;
};

const deleteDevotee = () => {
    deleting.value = true;
    router.delete(`/devotees/${devoteeToDelete.value.id}`, {
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
    width: 180px;
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
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary);
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
}

.table th {
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    font-size: 0.8125rem;
}

.devotee-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.nakshatra-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    background: #fef3c7;
    color: #d97706;
}

.text-center { text-align: center; }
.text-muted { color: #9ca3af; }
.text-danger { color: #ef4444 !important; }

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
    .search-box input, .filter-select { width: 100%; }
}
</style>
