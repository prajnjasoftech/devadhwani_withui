<template>
    <AdminLayout>
        <div class="page-header">
            <h1 class="page-title">Panchang Calendar</h1>
            <div class="page-breadcrumb">
                <Link href="/dashboard">Home</Link>
                <i class="bi bi-chevron-right"></i>
                <span>Panchang</span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Calendar -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="calendar-nav">
                            <button class="btn btn-icon" @click="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <h3 class="card-title mb-0">{{ monthName }}</h3>
                            <button class="btn btn-icon" @click="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                        <button class="btn btn-sm btn-secondary" @click="goToToday">Today</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="calendar">
                            <div class="calendar-header">
                                <div class="calendar-day-name" v-for="day in dayNames" :key="day">{{ day }}</div>
                            </div>
                            <div class="calendar-body">
                                <!-- Empty cells for alignment -->
                                <div
                                    v-for="n in firstDayOfWeek"
                                    :key="'empty-' + n"
                                    class="calendar-cell empty"
                                ></div>
                                <!-- Calendar days -->
                                <div
                                    v-for="day in calendarDays"
                                    :key="day.date"
                                    class="calendar-cell"
                                    :class="{
                                        'today': day.isToday,
                                        'has-panchang': day.panchang || fetchedPanchang[day.date],
                                        'selected': selectedDate === day.date
                                    }"
                                    @click="selectDate(day)"
                                >
                                    <div class="cell-date">{{ day.day }}</div>
                                    <div class="cell-panchang" v-if="day.panchang || fetchedPanchang[day.date]">
                                        <span class="day-name">{{ (day.panchang || fetchedPanchang[day.date])?.day_name }}</span>
                                        <span class="tithi">{{ truncate((day.panchang || fetchedPanchang[day.date])?.tithi, 10) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Day Details -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ selectedDateFormatted }}</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="text-center py-4">
                            <i class="bi bi-arrow-repeat spin" style="font-size: 2rem;"></i>
                            <p class="mt-2">Fetching panchang...</p>
                        </div>
                        <div v-else-if="selectedPanchang">
                            <div class="panchang-detail" v-if="selectedPanchang.day_name">
                                <label>Day</label>
                                <span>{{ selectedPanchang.day_name }}</span>
                            </div>
                            <div class="panchang-detail">
                                <label>Tithi</label>
                                <span>{{ selectedPanchang.tithi || 'N/A' }}</span>
                            </div>
                            <div class="panchang-detail">
                                <label>Nakshatra</label>
                                <span>{{ selectedPanchang.nakshatra || 'N/A' }}</span>
                            </div>
                            <div class="panchang-detail">
                                <label>Yoga</label>
                                <span>{{ selectedPanchang.yoga || 'N/A' }}</span>
                            </div>
                            <div class="panchang-detail">
                                <label>Karana</label>
                                <span>{{ selectedPanchang.karana || 'N/A' }}</span>
                            </div>
                            <div class="panchang-detail">
                                <label>Sun</label>
                                <span><i class="bi bi-sunrise"></i> {{ formatTime(selectedPanchang.sunrise) }} - <i class="bi bi-sunset"></i> {{ formatTime(selectedPanchang.sunset) }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                            <p class="mt-2">Click to fetch panchang</p>
                        </div>
                    </div>
                </div>

                <!-- Today's Quick View -->
                <div class="card mt-4" v-if="todayPanchang">
                    <div class="card-header">
                        <h3 class="card-title">Today's Highlights</h3>
                    </div>
                    <div class="card-body">
                        <div class="today-highlight">
                            <i class="bi bi-sun"></i>
                            <div>
                                <small>Sunrise / Sunset</small>
                                <strong>{{ formatTime(todayPanchang.sunrise) }} - {{ formatTime(todayPanchang.sunset) }}</strong>
                            </div>
                        </div>
                        <div class="today-highlight">
                            <i class="bi bi-moon"></i>
                            <div>
                                <small>Tithi</small>
                                <strong>{{ todayPanchang.tithi }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    calendarDays: {
        type: Array,
        default: () => []
    },
    currentMonth: Number,
    currentYear: Number,
    monthName: String,
    todayPanchang: Object,
    firstDayOfWeek: {
        type: Number,
        default: 0
    }
});

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const selectedDate = ref(null);
const loading = ref(false);
const fetchedPanchang = reactive({});

// Find today's date and select it by default
const todayDay = props.calendarDays.find(d => d.isToday);
if (todayDay) {
    selectedDate.value = todayDay.date;
}

// Fetch today's panchang on page load if not available
onMounted(async () => {
    if (todayDay && !todayDay.panchang && !props.todayPanchang) {
        await fetchPanchang(todayDay.date);
    }
});

const selectedPanchang = computed(() => {
    if (!selectedDate.value) return null;

    // Check fetched data first
    if (fetchedPanchang[selectedDate.value]) {
        return fetchedPanchang[selectedDate.value];
    }

    // Check props data
    const day = props.calendarDays.find(d => d.date === selectedDate.value);
    return day?.panchang || null;
});

const selectedDateFormatted = computed(() => {
    if (!selectedDate.value) return 'Select a date';
    return new Date(selectedDate.value).toLocaleDateString('en-IN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const selectDate = async (day) => {
    selectedDate.value = day.date;

    // If no panchang data exists, fetch it
    if (!day.panchang && !fetchedPanchang[day.date]) {
        await fetchPanchang(day.date);
    }
};

const fetchPanchang = async (date) => {
    loading.value = true;
    try {
        const response = await axios.post('/panchang/fetch', { date });
        if (response.data.status && response.data.data) {
            fetchedPanchang[date] = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch panchang:', error);
    } finally {
        loading.value = false;
    }
};

const prevMonth = () => {
    let month = props.currentMonth - 1;
    let year = props.currentYear;
    if (month < 1) {
        month = 12;
        year--;
    }
    router.get('/panchang', { month, year }, { preserveState: true });
};

const nextMonth = () => {
    let month = props.currentMonth + 1;
    let year = props.currentYear;
    if (month > 12) {
        month = 1;
        year++;
    }
    router.get('/panchang', { month, year }, { preserveState: true });
};

const goToToday = () => {
    const now = new Date();
    router.get('/panchang', {
        month: now.getMonth() + 1,
        year: now.getFullYear()
    }, { preserveState: true });
};

const truncate = (str, len) => {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
};

const formatTime = (time) => {
    if (!time) return 'N/A';
    if (typeof time === 'string' && time.includes(':')) {
        try {
            const date = new Date(time);
            if (!isNaN(date)) {
                return date.toLocaleTimeString('en-IN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }
        } catch (e) {
            return time;
        }
    }
    return time;
};
</script>

<style scoped>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.75rem;
}

.col-lg-8, .col-lg-4 {
    padding: 0.75rem;
}

.col-lg-8 { flex: 0 0 66.666%; max-width: 66.666%; }
.col-lg-4 { flex: 0 0 33.333%; max-width: 33.333%; }

@media (max-width: 992px) {
    .col-lg-8, .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
}

.btn-icon:hover {
    background: #f1f5f9;
}

.calendar {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.calendar-day-name {
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-cell {
    min-height: 90px;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-left: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-cell:nth-child(7n) {
    border-right: none;
}

.calendar-cell:hover {
    background: #f8fafc;
}

.calendar-cell.empty {
    background: #f8fafc;
    cursor: default;
}

.calendar-cell.today {
    background: #eff6ff;
}

.calendar-cell.today .cell-date {
    background: var(--primary);
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-cell.selected {
    background: #dbeafe;
    border-color: var(--primary);
}

.cell-date {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.cell-panchang {
    font-size: 0.65rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.cell-panchang .day-name {
    color: #6366f1;
    font-weight: 600;
}

.cell-panchang .tithi {
    color: #059669;
    font-weight: 500;
}

.panchang-detail {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.panchang-detail:last-child {
    border-bottom: none;
}

.panchang-detail label {
    font-weight: 600;
    color: #64748b;
    font-size: 0.875rem;
}

.panchang-detail span {
    color: #1e293b;
    text-align: right;
    max-width: 60%;
}

.today-highlight {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.today-highlight:last-child {
    border-bottom: none;
}

.today-highlight i {
    font-size: 1.5rem;
    color: var(--primary);
    width: 40px;
}

.today-highlight div {
    display: flex;
    flex-direction: column;
}

.today-highlight small {
    font-size: 0.75rem;
    color: #64748b;
}

.today-highlight strong {
    color: #1e293b;
}

.g-4 {
    gap: 1.5rem;
}

.mt-4 {
    margin-top: 1rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.py-4 {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.text-muted {
    color: #64748b;
}

.text-center {
    text-align: center;
}

.mb-0 {
    margin-bottom: 0;
}

.spin {
    animation: spin 1s linear infinite;
    color: var(--primary);
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
