<template>
    <div class="guest-layout">
        <div class="login-container">
            <div class="login-card">
                <div class="login-brand">
                    <img src="/images/logo.png" alt="Devadhwani" onerror="this.style.display='none'">
                    <h1>Devadhwani</h1>
                    <p>Temple Management System</p>
                </div>
                <slot />
            </div>
            <div class="login-footer">
                <p>&copy; {{ currentYear }} Devadhwani. All rights reserved.</p>
            </div>
        </div>
        <div class="login-banner">
            <div class="banner-content">
                <h2>Welcome to Devadhwani</h2>
                <p>Manage your temple operations efficiently with our comprehensive platform for devotee management, pooja bookings, inventory tracking, and more.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
.guest-layout {
    min-height: 100vh;
    display: flex;
}

.login-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background: #fff;
}

.login-card {
    width: 100%;
    max-width: 400px;
}

.login-brand {
    text-align: center;
    margin-bottom: 2rem;
}

.login-brand img {
    width: 80px;
    height: 80px;
    margin-bottom: 1rem;
}

.login-brand h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.login-brand p {
    color: #64748b;
    font-size: 0.875rem;
}

.login-footer {
    margin-top: 2rem;
    text-align: center;
    color: #94a3b8;
    font-size: 0.8125rem;
}

.login-banner {
    flex: 1;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.banner-content {
    max-width: 400px;
    color: #fff;
    text-align: center;
}

.banner-content h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.banner-content p {
    font-size: 1rem;
    opacity: 0.9;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .login-banner {
        display: none;
    }
}
</style>
