<template>
    <div class="db-card p-4 h-full flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-base">{{ $t('label.waiting_queue') }}</h4>
            <span class="text-xs bg-yellow-100 text-yellow-800 rounded-full px-2 py-0.5">
                {{ waitingCount }} {{ $t('label.waiting') }}
            </span>
        </div>
        <div v-if="queues.length === 0" class="flex-1 flex items-center justify-center text-gray-400 text-sm">
            {{ $t('label.no_waiting_customers') }}
        </div>

        <div v-else class="flex-1 overflow-y-auto space-y-2">
            <div
                v-for="(q, index) in queues"
                :key="q.id"
                class="border rounded-lg p-3 bg-white"
                :class="queueCardClass(q.status)"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-bold mr-2">
                            {{ index + 1 }}
                        </span>
                        <span class="font-medium text-sm">{{ q.customer_name }}</span>
                        <span v-if="q.customer_phone" class="text-xs text-gray-400 ml-1">{{ q.customer_phone }}</span>
                    </div>
                    <span :class="queueStatusBadgeClass(q.status)" class="text-xs rounded-full px-2 py-0.5 capitalize">
                        {{ q.status }}
                    </span>
                </div>
                <div class="mt-1 text-xs text-gray-500 space-y-0.5">
                    <div v-if="q.service">{{ $t('label.service') }}: {{ q.service.name }}</div>
                    <div v-if="q.room">{{ $t('label.room') }}: {{ q.room.name }}</div>
                    <div v-if="q.therapist">{{ $t('label.therapist') }}: {{ q.therapist.name }}</div>
                </div>
                <div class="flex gap-1.5 mt-2" v-if="q.status === 'waiting'">
                    <button
                        class="flex-1 py-1 text-xs rounded bg-blue-100 text-blue-700 hover:bg-blue-200 transition"
                        @click="callQueue(q)"
                    >
                        {{ $t('button.call') }}
                    </button>
                    <button
                        class="flex-1 py-1 text-xs rounded bg-red-100 text-red-700 hover:bg-red-200 transition"
                        @click="cancelQueue(q)"
                    >
                        {{ $t('button.cancel') }}
                    </button>
                </div>
                <div v-else-if="q.status === 'called'" class="flex gap-1.5 mt-2">
                    <button
                        class="flex-1 py-1 text-xs rounded bg-green-100 text-green-700 hover:bg-green-200 transition"
                        @click="seatQueue(q)"
                    >
                        {{ $t('button.seat') }}
                    </button>
                    <button
                        class="flex-1 py-1 text-xs rounded bg-red-100 text-red-700 hover:bg-red-200 transition"
                        @click="cancelQueue(q)"
                    >
                        {{ $t('button.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import alertService from "../../../services/alertService";

export default {
    name: "WaitingQueueComponent",
    props: { branchId: { type: [Number, String], default: null } },
    emits: ['queue-updated'],
    data() {
        return {
            loading: { isActive: false },
            props: {
                search: {
                    paginate: 0,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                },
            },
        };
    },
    computed: {
        queues() {
            return this.$store.getters['sessionQueue/lists'] ?? [];
        },
        waitingCount() {
            return (this.$store.getters['sessionQueue/lists'] || []).filter(
                (q) => q.status === 'waiting'
            ).length;
        },
    },
    mounted() { this.loadQueue(); },
    methods: {
        loadQueue() {
            this.loading.isActive = true;
            if (this.branchId) { this.props.search.branch_id = this.branchId; }
            this.$store.dispatch('sessionQueue/lists', this.props.search)
                .then(() => { this.loading.isActive = false; })
                .catch(() => { this.loading.isActive = false; });
        },
        callQueue(q) {
            this.$store.dispatch('sessionQueue/call', { id: q.id, search: this.props.search })
                .then(() => {
                    alertService.success(this.$t('message.customer_called'));
                    this.$emit('queue-updated');
                })
                .catch((err) => { alertService.error(err.response?.data?.message); });
        },
        seatQueue(q) {
            this.$store.dispatch('sessionQueue/seat', { id: q.id, search: this.props.search })
                .then(() => {
                    alertService.success(this.$t('message.customer_seated'));
                    this.$emit('queue-updated');
                })
                .catch((err) => { alertService.error(err.response?.data?.message); });
        },
        cancelQueue(q) {
            this.$store.dispatch('sessionQueue/cancel', { id: q.id, search: this.props.search })
                .then(() => {
                    alertService.success(this.$t('message.queue_cancelled'));
                    this.$emit('queue-updated');
                })
                .catch((err) => { alertService.error(err.response?.data?.message); });
        },
        queueCardClass(status) {
            return {
                'border-yellow-300 bg-yellow-50': status === 'waiting',
                'border-blue-300 bg-blue-50':   status === 'called',
            };
        },
        queueStatusBadgeClass(status) {
            const map = {
                waiting:   'bg-yellow-100 text-yellow-700',
                called:    'bg-blue-100 text-blue-700',
                seated:    'bg-green-100 text-green-700',
                cancelled: 'bg-red-100 text-red-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },
    },
};
</script>
