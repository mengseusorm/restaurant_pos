<template>
    <section class="task-page">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <div>
                    <h3 class="db-card-title">Room Tasks</h3>
                    <p class="task-subtitle">{{ roomName }} · {{ tasks.length }} record(s)</p>
                </div>
                <div class="db-card-filter">
                    <button type="button" class="db-btn py-2 text-white bg-primary" aria-label="Back to room list" title="Back to room list" @click="$router.push({ name: 'therapist.room.list' })">
                        <i class="lab lab-arrow-left"></i>
                        <span> {{ $t('button.rooms') }}</span>
                    </button> 
                    <button type="button" class="db-btn py-2 text-white bg-primary" :disabled="loading" aria-label="Refresh tasks" title="Refresh tasks" @click="loadTasks">
                        <i class="lab lab-refresh-line"></i>
                        <span>{{ loading ? $t('button.loading') : 'refresh' }}</span>
                    </button>
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t('label.from_date') }}</label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label class="db-field-title after:hidden">{{ $t('label.to_date') }}</label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t('button.search') }}</span>
                                </button>
                                <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t('button.clear') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-4 sm:p-5">
                <div v-if="error" class="task-alert">{{ error }}</div>

                <div class="db-table-responsive">
                    <table class="db-table stripe">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th"> {{ $t('label.name') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.guest') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.bed') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.duration') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.start_time') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.end_time') }}</th>
                                <th class="db-table-head-th"> {{ $t('label.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body">
                            <tr v-if="loading" class="db-table-body-tr">
                                <td colspan="7" class="db-table-body-td text-center py-8 text-gray-400">Loading room tasks</td>
                            </tr>
                            <tr v-else-if="tasks.length === 0" class="db-table-body-tr">
                                <td colspan="7" class="db-table-body-td text-center py-8 text-gray-400"> {{ $t('label.no_tasks_found') }}</td>
                            </tr>
                            <tr
                                v-for="task in tasks"
                                v-else
                                :key="task.id"
                                class="db-table-body-tr task-row"
                                @click="openSession(task)"
                            >
                                <td class="db-table-body-td">{{ therapistLabel(task) }}</td>
                                <td class="db-table-body-td">{{ task.guest_name || 'N/A' }}</td>
                                <td class="db-table-body-td">{{ task.bed?.name || 'N/A' }}</td>
                                <td class="db-table-body-td">{{ task.duration || task.duration_minutes || 0 }}</td>
                                <td class="db-table-body-td">{{ task.start_time || 'N/A' }}</td>
                                <td class="db-table-body-td">{{ task.end_time || 'N/A' }}</td>
                                <td class="db-table-body-td">
                                    <span class="db-btn-outline sm primary">
                                        Open <i class="lab lab-arrow-right"></i>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import appService from "../../services/appService";

export default {
    name: "TherapistRoomTaskListComponent",
    components: {
        Datepicker,
    },
    props: {
        roomId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            error: "",
            room: null,
            tasks: [],
            first_date: null,
            last_date: null,
            searchParams: {
                from_date: "",
                to_date: "",
            },
        };
    },
    computed: {
        roomName() {
            return this.room?.name || this.$route.query.roomName || `Room ${this.roomId}`;
        },
    },
    mounted() {
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);
        startDate.setHours(0, 0, 0, 0);
        endDate.setHours(23, 59, 59, 999);

        this.first_date = startDate;
        this.last_date = endDate;
        this.searchParams.from_date = appService.formatDateTime(this.first_date);
        this.searchParams.to_date = appService.formatDateTime(this.last_date);

        this.loadTasks();
    },
    watch: {
        roomId() {
            this.loadTasks();
        },
    },
    methods: {
        search() {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.searchParams.from_date = appService.formatDateTime(this.first_date);
                this.searchParams.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.searchParams.from_date = "";
                this.searchParams.to_date = "";
            }
            this.loadTasks();
        },
        clear() {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);

            this.first_date = startDate;
            this.last_date = endDate;
            this.searchParams.from_date = appService.formatDateTime(this.first_date);
            this.searchParams.to_date = appService.formatDateTime(this.last_date);
            this.loadTasks();
        },
        loadTasks() {
            this.loading = true;
            this.error = "";

            axios.get(`therapist/rooms/${this.roomId}/tasks`, { params: this.searchParams }).then((res) => {
                this.room = res.data.data || null;
                this.tasks = res.data.data?.session_items || [];
            }).catch((err) => {
                this.error = err.response?.data?.message || "Unable to load room tasks.";
            }).finally(() => {
                this.loading = false;
            });
        },
        openSession(task) {
            if (!task.sub_session_id) return;

            this.$router.push({
                name: "therapist.session.detail",
                params: {
                    roomId: this.roomId,
                    subSessionId: task.sub_session_id,
                },
                query: {
                    roomName: this.roomName,
                },
            });
        },
        therapistLabel(task) {
            return task.therapist?.name || 'N/A';
        },
        logout() {
            this.$store.dispatch("logout").finally(() => {
                this.$router.push({ name: "auth.login" });
            });
        },
    },
};
</script>

<style scoped>
.task-page {
    min-height: calc(100vh - 90px);
    padding: 16px;
}

.task-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.task-alert {
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #991b1b;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: 13px;
}

.task-row {
    cursor: pointer;
    transition: 0.2s ease;
}

.task-row:hover {
    background: rgba(var(--primary), 0.03);
}

@media (max-width: 768px) {
    .task-page {
        padding: 12px;
    }
}
</style>
