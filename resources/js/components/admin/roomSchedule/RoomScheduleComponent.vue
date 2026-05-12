<template>
    <div class="db-card">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.room_schedule') }}</h3>
            <div class="flex items-center gap-3">
                <button @click="prevDay" class="db-btn-outline py-1.5 px-3 text-sm">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <input type="date" v-model="selectedDate" @change="load" class="db-field-control w-auto text-sm" />
                <button @click="nextDay" class="db-btn-outline py-1.5 px-3 text-sm">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <button @click="load" class="db-btn py-1.5 px-4 text-sm text-white bg-primary">
                    <i class="fa-solid fa-rotate-right mr-1"></i>{{ $t('button.refresh') }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-20">
            <i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i>
        </div>

        <div v-else-if="rooms.length === 0" class="text-center py-20 text-slate-400">
            {{ $t('label.no_data') }}
        </div>

        <div v-else class="overflow-x-auto p-4">
            <table class="w-full border-collapse text-sm schedule-table">
                <thead>
                    <tr>
                        <th class="schedule-th-time w-20 sticky left-0 bg-white z-10 border border-slate-200 text-slate-500 font-medium py-2 px-3 text-center">
                            {{ $t('label.time') }}
                        </th>
                        <th
                            v-for="room in rooms"
                            :key="room.id"
                            class="schedule-th-room border border-slate-200 bg-slate-50 font-semibold py-2 px-3 text-center min-w-[160px]"
                        >
                            {{ room.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="slot in timeSlots" :key="slot.label">
                        <td class="schedule-td-time sticky left-0 bg-white z-10 border border-slate-200 text-slate-500 text-xs py-1.5 px-2 text-center whitespace-nowrap">
                            {{ slot.label }}
                        </td>
                        <template v-for="room in rooms" :key="room.id">
                            <!-- Cell is covered by a session rowspan from above — skip -->
                            <template v-if="isCovered(room.id, slot.minutes)"></template>
                            <!-- Session starts at this slot -->
                            <td
                                v-else-if="getSessionStart(room.id, slot.minutes)"
                                :rowspan="getSessionRowspan(room.id, slot.minutes)"
                                :class="[statusClass(getSessionStart(room.id, slot.minutes).status), getSessionStart(room.id, slot.minutes).is_pending ? 'border-dashed opacity-75' : '']"
                                class="border border-slate-200 px-2 py-1 align-top cursor-pointer hover:opacity-80 transition-opacity"
                                @click="openSession(getSessionStart(room.id, slot.minutes))"
                            >
                                <div v-if="getSessionStart(room.id, slot.minutes).is_pending" class="text-xs font-bold text-yellow-600 mb-0.5">⏳ {{ $t('label.pending') }}</div>
                                <div class="font-semibold text-xs leading-tight">{{ getSessionStart(room.id, slot.minutes).customer_name || '—' }}</div>
                                <div class="text-xs mt-0.5 opacity-80">{{ getSessionStart(room.id, slot.minutes).service_name || '—' }}</div>
                                <div class="text-xs opacity-70">{{ getSessionStart(room.id, slot.minutes).therapist_name || '—' }}</div>
                                <div class="text-xs opacity-60 mt-0.5">
                                    {{ timeOnly(getSessionStart(room.id, slot.minutes).started_at) }} – {{ timeOnly(getSessionStart(room.id, slot.minutes).ended_at) }}
                                </div>
                            </td>
                            <!-- Empty cell -->
                            <td v-else class="border border-slate-200 bg-white hover:bg-slate-50 transition-colors" style="height: 40px;"></td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-3 px-4 pb-4 pt-2">
            <div class="flex items-center gap-1.5 text-xs">
                <span class="w-4 h-4 rounded bg-yellow-50 border border-dashed border-yellow-300 inline-block"></span>
                <span>{{ $t('label.pending') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="w-4 h-4 rounded bg-yellow-50 border border-yellow-200 inline-block"></span>
                <span>{{ $t('label.waiting') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="w-4 h-4 rounded bg-blue-50 border border-blue-200 inline-block"></span>
                <span>{{ $t('label.in_progress') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="w-4 h-4 rounded bg-green-50 border border-green-200 inline-block"></span>
                <span>{{ $t('label.done') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="w-4 h-4 rounded bg-slate-50 border border-slate-300 inline-block"></span>
                <span>{{ $t('label.checked_out') }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

function parseTimeToMinutes(timeStr) {
    if (!timeStr) return 0;
    const parts = timeStr.split(':');
    return parseInt(parts[0], 10) * 60 + (parseInt(parts[1], 10) || 0);
}

function minutesToLabel(mins) {
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    const hh = String(h).padStart(2, '0');
    const mm = String(m).padStart(2, '0');
    return `${hh}:${mm}`;
}

const SLOT_MINUTES = 30; // each row = 30 minutes

export default {
    name: 'RoomScheduleComponent',
    data() {
        const today = new Date();
        const yyyy  = today.getFullYear();
        const mm    = String(today.getMonth() + 1).padStart(2, '0');
        const dd    = String(today.getDate()).padStart(2, '0');
        return {
            loading: true,
            selectedDate: `${yyyy}-${mm}-${dd}`,
            rooms: [],
            sessions: [],
            openTime: '08:00',
            closeTime: '23:00',
        };
    },
    computed: {
        branchId() { return this.$store.getters.authBranchId || 0; },
        timeSlots() {
            const slots = [];
            const start = parseTimeToMinutes(this.openTime);
            const end   = parseTimeToMinutes(this.closeTime);
            for (let m = start; m < end; m += SLOT_MINUTES) {
                slots.push({ label: minutesToLabel(m), minutes: m });
            }
            return slots;
        },
        // Map: roomId → array of session objects enriched with startMinutes, endMinutes
        sessionsByRoom() {
            const map = {};
            for (const s of this.sessions) {
                if (!map[s.room_id]) map[s.room_id] = [];
                const startMins = s.started_at ? parseTimeToMinutes(s.started_at.split(' ')[1] || '') : null;
                const endMins   = s.ended_at   ? parseTimeToMinutes(s.ended_at.split(' ')[1]   || '') : null;
                map[s.room_id].push({ ...s, startMins, endMins });
            }
            return map;
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            this.loading = true;
            axios.get('admin/room-schedule', { params: { date: this.selectedDate, branch_id: this.branchId } })
                .then((res) => {
                    const d = res.data.data;
                    this.rooms     = d.rooms    || [];
                    this.sessions  = d.sessions || [];
                    this.openTime  = d.open_time  || '08:00';
                    this.closeTime = d.close_time || '23:00';
                })
                .catch(() => {})
                .finally(() => { this.loading = false; });
        },
        prevDay() {
            const d = new Date(this.selectedDate);
            d.setDate(d.getDate() - 1);
            this.selectedDate = d.toISOString().split('T')[0];
            this.load();
        },
        nextDay() {
            const d = new Date(this.selectedDate);
            d.setDate(d.getDate() + 1);
            this.selectedDate = d.toISOString().split('T')[0];
            this.load();
        },
        /** Return the session that *starts within* this slot for a room, or null */
        getSessionStart(roomId, slotMinutes) {
            const sessions = this.sessionsByRoom[roomId] || [];
            return sessions.find(s =>
                s.startMins !== null &&
                s.startMins >= slotMinutes &&
                s.startMins < slotMinutes + SLOT_MINUTES
            ) || null;
        },
        /** How many rows (slots) does this session span? */
        getSessionRowspan(roomId, slotMinutes) {
            const s = this.getSessionStart(roomId, slotMinutes);
            if (!s || s.endMins === null || s.endMins <= slotMinutes) return 1;
            return Math.max(1, Math.ceil((s.endMins - slotMinutes) / SLOT_MINUTES));
        },
        /** Is this cell covered by a session that started in an earlier row? */
        isCovered(roomId, slotMinutes) {
            const sessions = this.sessionsByRoom[roomId] || [];
            return sessions.some(s => {
                if (s.startMins === null || s.endMins === null) return false;
                return s.startMins < slotMinutes && slotMinutes < s.endMins;
            });
        },
        statusClass(status) {
            const classes = {
                waiting:     'bg-yellow-50 border-yellow-200 text-yellow-800',
                in_service:  'bg-blue-50 border-blue-200 text-blue-800',
                done:        'bg-green-50 border-green-200 text-green-800',
                checked_out: 'bg-slate-50 border-slate-300 text-slate-600',
            };
            return classes[status] || 'bg-white text-slate-700';
        },
        openSession(session) {
            this.$router.push({ name: 'admin.sub-session.detail', params: { id: session.id } });
        },
        timeOnly(datetimeStr) {
            if (!datetimeStr) return '';
            const parts = datetimeStr.split(' ');
            return parts[1] ? parts[1].substring(0, 5) : '';
        },
    },
};
</script>

<style scoped>
.schedule-table {
    min-width: 600px;
}
.schedule-th-room {
    min-width: 160px;
}
</style>
