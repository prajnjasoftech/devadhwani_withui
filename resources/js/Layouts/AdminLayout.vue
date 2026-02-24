<template>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ open: sidebarOpen }">
            <div class="sidebar-brand">
                <img src="/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                <h2>Devadhwani</h2>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">Main Menu</div>
                <ul class="p-0 mb-4">
                    <li class="nav-item">
                        <Link href="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }">
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard</span>
                        </Link>
                    </li>
                </ul>

                <div class="nav-section">Management</div>
                <ul class="p-0 mb-4">
                    <li class="nav-item">
                        <Link href="/temples" class="nav-link" :class="{ active: isActive('/temples') }">
                            <i class="bi bi-building"></i>
                            <span>Temples</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/devotees" class="nav-link" :class="{ active: isActive('/devotees') }">
                            <i class="bi bi-people"></i>
                            <span>Devotees</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/members" class="nav-link" :class="{ active: isActive('/members') }">
                            <i class="bi bi-person-badge"></i>
                            <span>Members</span>
                        </Link>
                    </li>
                </ul>

                <div class="nav-section">Pooja & Bookings</div>
                <ul class="p-0 mb-4">
                    <li class="nav-item">
                        <Link href="/poojas" class="nav-link" :class="{ active: isActive('/poojas') }">
                            <i class="bi bi-flower1"></i>
                            <span>Poojas</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/bookings" class="nav-link" :class="{ active: isActive('/bookings') }">
                            <i class="bi bi-calendar-check"></i>
                            <span>Bookings</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/panchang" class="nav-link" :class="{ active: isActive('/panchang') }">
                            <i class="bi bi-calendar3"></i>
                            <span>Panchang</span>
                        </Link>
                    </li>
                </ul>

                <div class="nav-section">Inventory</div>
                <ul class="p-0 mb-4">
                    <li class="nav-item">
                        <Link href="/categories" class="nav-link" :class="{ active: isActive('/categories') }">
                            <i class="bi bi-tags"></i>
                            <span>Categories</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/items" class="nav-link" :class="{ active: isActive('/items') }">
                            <i class="bi bi-box"></i>
                            <span>Items</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/suppliers" class="nav-link" :class="{ active: isActive('/suppliers') }">
                            <i class="bi bi-truck"></i>
                            <span>Suppliers</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/purchases" class="nav-link" :class="{ active: isActive('/purchases') }">
                            <i class="bi bi-cart3"></i>
                            <span>Purchases</span>
                        </Link>
                    </li>
                </ul>

                <div class="nav-section">Reports</div>
                <ul class="p-0 mb-4">
                    <li class="nav-item">
                        <Link href="/reports" class="nav-link" :class="{ active: isActive('/reports') }">
                            <i class="bi bi-bar-chart"></i>
                            <span>Reports</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/settings" class="nav-link" :class="{ active: isActive('/settings') }">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </Link>
                    </li>
                </ul>

                <div class="nav-section">Account</div>
                <ul class="p-0">
                    <li class="nav-item">
                        <Link href="/temple/profile" class="nav-link" :class="{ active: isActive('/temple/profile') }">
                            <i class="bi bi-building"></i>
                            <span>About Temple</span>
                        </Link>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="/logout" ref="logoutFormSidebar" class="logout-form">
                            <input type="hidden" name="_token" :value="csrfToken">
                            <a href="#" class="nav-link text-danger" @click.prevent="$refs.logoutFormSidebar.submit()">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-icon btn-secondary d-lg-none" @click="toggleSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="header-search">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search..." v-model="searchQuery">
                    </div>
                </div>

                <div class="header-actions">
                    <button class="header-action-btn">
                        <i class="bi bi-bell"></i>
                        <span class="badge" v-if="notifications > 0">{{ notifications }}</span>
                    </button>
                    <button class="header-action-btn">
                        <i class="bi bi-envelope"></i>
                    </button>
                    <div class="user-dropdown" @click="toggleUserMenu">
                        <div class="user-avatar">
                            {{ userInitials }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ userName }}</span>
                            <span class="user-role">{{ userRole }}</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>

                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu" v-if="userMenuOpen" @click.stop>
                            <Link href="/temple/profile" class="dropdown-item">
                                <i class="bi bi-building"></i> View Temple Profile
                            </Link>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="/logout" ref="logoutFormHeader">
                                <input type="hidden" name="_token" :value="csrfToken">
                                <a href="#" class="dropdown-item text-danger" @click.prevent="$refs.logoutFormHeader.submit()">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $page.props.flash.error }}
                </div>

                <slot />
            </div>
        </main>

        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" v-if="sidebarOpen" @click="closeSidebar"></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);
const searchQuery = ref('');
const notifications = ref(3);
const userMenuOpen = ref(false);

const csrfToken = computed(() => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
});

const userName = computed(() => page.props.auth?.user?.temple_name || page.props.auth?.user?.name || 'Admin');
const userRole = computed(() => page.props.auth?.user?.temple_name ? 'Temple Admin' : 'Member');
const userInitials = computed(() => {
    const name = userName.value;
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const isActive = (path) => {
    return window.location.pathname.startsWith(path);
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const userDropdownRef = ref(null);

const toggleUserMenu = (event) => {
    event.stopPropagation();
    userMenuOpen.value = !userMenuOpen.value;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        userMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

@media (min-width: 1025px) {
    .sidebar-overlay {
        display: none;
    }
}

.user-dropdown {
    position: relative;
    cursor: pointer;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    min-width: 150px;
    z-index: 1000;
    margin-top: 8px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    color: #374151;
    text-decoration: none;
    transition: background-color 0.2s;
}

.dropdown-item:hover {
    background-color: #f3f4f6;
}

.dropdown-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 4px 0;
}

.text-danger {
    color: #dc2626 !important;
}

.dropdown-item i {
    font-size: 1rem;
}
</style>
