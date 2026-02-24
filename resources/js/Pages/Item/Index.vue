<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Items</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Items</span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            placeholder="Search items..."
                            v-model="search"
                            @input="debouncedSearch"
                        >
                    </div>
                    <select class="form-select" v-model="categoryId" @change="applyFilters" style="width: auto;">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <select class="form-select" v-model="status" @change="applyFilters" style="width: auto;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <Link href="/items/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Item
                </Link>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Min Qty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items.data" :key="item.id">
                            <td class="fw-bold">{{ item.item_name }}</td>
                            <td>{{ item.category?.name || '-' }}</td>
                            <td>{{ item.unit }}</td>
                            <td>{{ item.min_quantity }}</td>
                            <td>
                                <span class="badge" :class="item.status === 'active' ? 'badge-success' : 'badge-secondary'">
                                    {{ item.status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <Link :href="`/items/${item.id}/edit`" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button class="btn btn-sm btn-danger" @click="confirmDelete(item)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.data.length === 0">
                            <td colspan="6" class="text-center text-muted py-4">
                                No items found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer" v-if="items.last_page > 1">
                <div class="pagination">
                    <button
                        class="btn btn-sm btn-secondary"
                        :disabled="items.current_page === 1"
                        @click="goToPage(items.current_page - 1)"
                    >
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="pagination-info">
                        Page {{ items.current_page }} of {{ items.last_page }}
                    </span>
                    <button
                        class="btn btn-sm btn-secondary"
                        :disabled="items.current_page === items.last_page"
                        @click="goToPage(items.current_page + 1)"
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
                    <h3>Delete Item</h3>
                    <button class="btn-close" @click="showDeleteModal = false">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ itemToDelete?.item_name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button class="btn btn-danger" @click="deleteItem">Delete</button>
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
    items: Object,
    categories: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const categoryId = ref(props.filters?.category_id || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const itemToDelete = ref(null);

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get('/items', {
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const goToPage = (page) => {
    router.get('/items', {
        page,
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const confirmDelete = (item) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    router.delete(`/items/${itemToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
        }
    });
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
    width: 200px;
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

.d-flex { display: flex; }
.align-items-center { align-items: center; }
.gap-3 { gap: 1rem; }
.py-4 { padding: 1.5rem 0; }

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
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 { margin: 0; font-size: 1.125rem; }

.btn-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #64748b;
}

.modal-body { padding: 1.5rem; }

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
}
</style>
