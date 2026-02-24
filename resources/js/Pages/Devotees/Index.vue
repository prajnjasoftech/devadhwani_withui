<template>
    <AdminLayout>
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Devotees</h1>
                <div class="page-breadcrumb">
                    <Link href="/dashboard">Home</Link>
                    <i class="bi bi-chevron-right"></i>
                    <span>Devotees</span>
                </div>
            </div>
            <Link href="/devotees/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Devotee
            </Link>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Search by name or phone..."
                            v-model="filters.search"
                            @input="debouncedSearch"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nakshatra</label>
                        <select class="form-control" v-model="filters.nakshatra" @change="fetchDevotees">
                            <option value="">All Nakshatras</option>
                            <option v-for="n in nakshatras" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Per Page</label>
                        <select class="form-control" v-model="filters.perPage" @change="fetchDevotees">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary" @click="resetFilters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Nakshatra</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="devotee in devotees.data" :key="devotee.id">
                            <td>{{ devotee.id }}</td>
                            <td class="fw-bold">{{ devotee.devotee_name }}</td>
                            <td>{{ devotee.devotee_phone || '-' }}</td>
                            <td>
                                <span class="badge badge-primary" v-if="devotee.nakshatra">
                                    {{ devotee.nakshatra }}
                                </span>
                                <span v-else>-</span>
                            </td>
                            <td>{{ truncate(devotee.address, 30) || '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <Link :href="`/devotees/${devotee.id}`" class="btn btn-sm btn-secondary btn-icon">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link :href="`/devotees/${devotee.id}/edit`" class="btn btn-sm btn-secondary btn-icon">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button class="btn btn-sm btn-danger btn-icon" @click="confirmDelete(devotee)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="devotees.data?.length === 0">
                            <td colspan="6" class="text-center text-muted py-4">
                                No devotees found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center" v-if="devotees.data?.length > 0">
                <span class="text-muted">
                    Showing {{ devotees.from }} to {{ devotees.to }} of {{ devotees.total }} entries
                </span>
                <nav>
                    <ul class="pagination">
                        <li class="page-item" :class="{ disabled: !devotees.prev_page_url }">
                            <a class="page-link" href="#" @click.prevent="goToPage(devotees.current_page - 1)">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <li
                            class="page-item"
                            v-for="page in visiblePages"
                            :key="page"
                            :class="{ active: page === devotees.current_page }"
                        >
                            <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: !devotees.next_page_url }">
                            <a class="page-link" href="#" @click.prevent="goToPage(devotees.current_page + 1)">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-backdrop" v-if="showDeleteModal" @click="showDeleteModal = false">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3 class="modal-title">Confirm Delete</h3>
                    <button class="modal-close" @click="showDeleteModal = false">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ selectedDevotee?.devotee_name }}</strong>?</p>
                    <p class="text-muted">This action can be undone from the trash.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button class="btn btn-danger" @click="deleteDevotee" :disabled="deleting">
                        <span v-if="deleting">Deleting...</span>
                        <span v-else>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    devotees: {
        type: Object,
        default: () => ({ data: [], current_page: 1, last_page: 1 })
    }
});

const filters = reactive({
    search: '',
    nakshatra: '',
    perPage: 10
});

const devotees = ref(props.devotees);
const showDeleteModal = ref(false);
const selectedDevotee = ref(null);
const deleting = ref(false);
const loading = ref(false);

const nakshatras = [
    'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira',
    'Ardra', 'Punarvasu', 'Pushya', 'Ashlesha', 'Magha',
    'Purva Phalguni', 'Uttara Phalguni', 'Hasta', 'Chitra',
    'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha', 'Mula',
    'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta',
    'Shatabhisha', 'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati'
];

const visiblePages = computed(() => {
    const pages = [];
    const current = devotees.value.current_page;
    const last = devotees.value.last_page;

    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
    }
    return pages;
});

let searchTimeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchDevotees, 300);
};

const fetchDevotees = async () => {
    loading.value = true;
    const params = new URLSearchParams({
        search: filters.search,
        per_page: filters.perPage,
        page: 1
    });

    if (filters.nakshatra) {
        params.append('nakshatra', filters.nakshatra);
    }

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/devotees?${params}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.status) {
            devotees.value = {
                data: data.data,
                ...data.meta,
                from: (data.meta.current_page - 1) * data.meta.per_page + 1,
                to: Math.min(data.meta.current_page * data.meta.per_page, data.meta.total)
            };
        }
    } catch (error) {
        console.error('Failed to fetch devotees:', error);
    } finally {
        loading.value = false;
    }
};

const goToPage = async (page) => {
    if (page < 1 || page > devotees.value.last_page) return;

    loading.value = true;
    const params = new URLSearchParams({
        search: filters.search,
        per_page: filters.perPage,
        page: page
    });

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/devotees?${params}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.status) {
            devotees.value = {
                data: data.data,
                ...data.meta,
                from: (data.meta.current_page - 1) * data.meta.per_page + 1,
                to: Math.min(data.meta.current_page * data.meta.per_page, data.meta.total)
            };
        }
    } catch (error) {
        console.error('Failed to fetch devotees:', error);
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => {
    filters.search = '';
    filters.nakshatra = '';
    filters.perPage = 10;
    fetchDevotees();
};

const confirmDelete = (devotee) => {
    selectedDevotee.value = devotee;
    showDeleteModal.value = true;
};

const deleteDevotee = async () => {
    deleting.value = true;

    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`/api/devotees/${selectedDevotee.value.id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            showDeleteModal.value = false;
            fetchDevotees();
        }
    } catch (error) {
        console.error('Failed to delete devotee:', error);
    } finally {
        deleting.value = false;
    }
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

onMounted(() => {
    fetchDevotees();
});
</script>

<style scoped>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-2, .col-md-3, .col-md-4 {
    padding: 0.5rem;
}

.col-md-2 { flex: 0 0 16.666%; max-width: 16.666%; }
.col-md-3 { flex: 0 0 25%; max-width: 25%; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; }

@media (max-width: 768px) {
    .col-md-2, .col-md-3, .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.g-3 {
    gap: 0.75rem;
}

.card-footer {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>
