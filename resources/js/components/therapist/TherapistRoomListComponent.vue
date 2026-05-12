<template>
    <section class="therapist-page">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <div>
                    <h3 class="db-card-title">Select Room</h3>
                    <p class="therapist-subtitle">{{ rooms.length }} rooms available for review</p>
                </div>
                <div class="db-card-filter"> 
                    <button type="button" class="db-btn py-2 text-white bg-primary" :disabled="loading" aria-label="Refresh rooms" title="Refresh rooms" @click="loadRooms">
                        <i class="lab lab-refresh-line"></i>
                        <span>refresh</span>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div v-if="error" class="therapist-alert">{{ error }}</div>
                <div v-if="loading" class="therapist-empty">Loading rooms...</div>
                <div v-else-if="rooms.length === 0" class="therapist-empty">No rooms found.</div>

                <div v-else class="therapist-room-grid">
                    <button
                        v-for="room in rooms"
                        :key="room.id"
                        type="button"
                        class="therapist-room-card"
                        @click="openRoom(room)"
                    >
                        <div class="therapist-room-head">
                            <strong>{{ room.name }}</strong>
                            <span class="therapist-room-status" :class="statusClass(room.status)">
                                {{ room.status || 'ready' }}
                            </span>
                        </div>
                        <span class="therapist-room-meta">
                            <i class="lab lab-door-2"></i>
                            View sessions
                            <i class="lab lab-arrow-right"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";

export default {
    name: "TherapistRoomListComponent",
    data() {
        return {
            loading: false,
            error: "",
            rooms: [],
        };
    },
    mounted() {
        this.loadRooms();
    },
    methods: {
        loadRooms() {
            this.loading = true;
            this.error = "";

            axios.get("therapist/rooms", {
                params: {
                    paginate: 0,
                    order_column: "name",
                    order_type: "asc",
                },
            }).then((res) => {
                this.rooms = res.data.data || [];
            }).catch((err) => {
                this.error = err.response?.data?.message || "Unable to load rooms.";
            }).finally(() => {
                this.loading = false;
            });
        },
        openRoom(room) {
            this.$router.push({
                name: "therapist.room.tasks",
                params: { roomId: room.id },
                query: { roomName: room.name },
            });
        },
        logout() {
            this.$store.dispatch("logout").finally(() => {
                this.$router.push({ name: "auth.login" });
            });
        },
        statusClass(status) {
            if (status === "occupied") return "is-occupied";
            if (status === "cleaning") return "is-cleaning";
            return "is-available";
        },
    },
};
</script>

<style scoped>
.therapist-page {
    min-height: calc(100vh - 90px);
    padding: 16px;
}

.therapist-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.therapist-alert,
.therapist-empty {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 18px;
    text-align: center;
    font-size: 14px;
    color: #6b7280;
}

.therapist-alert {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
    margin-bottom: 12px;
}

.therapist-room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}

.therapist-room-card {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    padding: 14px;
    text-align: left;
    transition: 0.2s ease;
    cursor: pointer;
}

.therapist-room-card:hover {
    border-color: rgba(var(--primary), 0.25);
    background: rgba(var(--primary), 0.03);
}

.therapist-room-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.therapist-room-head strong {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
}

.therapist-room-status {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 4px 10px;
}

.therapist-room-status.is-occupied {
    color: #92400e;
    background: #fef3c7;
}

.therapist-room-status.is-cleaning {
    color: #1e40af;
    background: #dbeafe;
}

.therapist-room-status.is-available {
    color: #166534;
    background: #dcfce7;
}

.therapist-room-meta {
    margin-top: 10px;
    font-size: 12px;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

@media (max-width: 768px) {
    .therapist-page {
        padding: 12px;
    }
}
</style>
