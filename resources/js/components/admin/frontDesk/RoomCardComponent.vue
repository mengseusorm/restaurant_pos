<template>
    <div class="overflow-hidden flex flex-col bg-white rounded-lg shadow-sm" :class="cardBorderClass">
        <!-- Room Header -->
        <div class="px-4 py-3 flex items-center justify-between gap-2" :class="cardHeaderClass">
            <span class="font-semibold text-sm">{{ room.name }}</span>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <span class="text-xs rounded-full px-2 py-0.5 capitalize" :class="statusBadgeClass">
                    {{ sessionStatusLabel }}
                </span>
                <button
                    v-if="canEditStatus"
                    type="button"
                    class="w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center"
                    :title="$t('button.edit')"
                    @click="toggleStatusEditor"
                >
                    <i class="lab lab-edit-line text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Room Status Editor -->
        <div v-if="showStatusEditor" class="px-3 py-2 border-b bg-white flex items-center gap-2">
            <select v-model="editRoomStatus" class="db-field-control text-xs py-1 flex-1">
                <option v-for="option in roomStatusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <button
                type="button"
                class="db-btn text-white bg-primary text-xs py-1 px-2"
                :disabled="statusSaving"
                @click="saveRoomStatus"
            >
                <i class="lab lab-save"></i>
            </button>
            <button
                type="button"
                class="db-btn text-white bg-gray-500 text-xs py-1 px-2"
                :disabled="statusSaving"
                @click="closeStatusEditor"
            >
                {{ $t('button.cancel') }}
            </button>
        </div>

        <!-- Empty Room -->
        <div v-if="!hasActiveItems" class="flex-1 flex flex-col items-center justify-center p-4 gap-3" :class="emptyStateClass">
            <i class="lab lab-door-2 text-3xl"></i>
            <span class="text-sm">{{ sessionStatusLabel }}</span>
            <button v-if="canOpenSession && isAvailable" class="db-btn text-white bg-primary text-xs py-1.5 px-3"
                @click="$emit('open-session', { room })">
                <i class="lab lab-add-line"></i>
                <span>{{ $t('button.open_session') }}</span>
            </button>
        </div>

        <!-- Active Items -->
        <div v-else-if="activeItems && activeItems.length > 0" class="flex-1 flex flex-col p-3 gap-2">
            <!-- Guest Info -->
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-bold flex-shrink-0">
                    {{ customerInitial }}
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-sm truncate">{{ primaryItem?.guest_name || $t('label.walk_in') }}</p>
                    <p v-if="primaryItem?.sub_session?.phone" class="text-xs text-gray-400 truncate">{{ primaryItem.sub_session.phone }}</p>
                </div>
            </div>

            <!-- Service & Therapist -->
            <div class="text-xs text-gray-500 space-y-0.5">
                <div v-if="primaryItem?.item_name">
                    <span class="text-gray-400">{{ $t('label.service') }}:</span> {{ primaryItem.item_name }}
                </div>
                <div v-if="primaryItem?.therapist_name">
                    <span class="text-gray-400">{{ $t('label.therapist') }}:</span> {{ primaryItem.therapist_name }}
                </div>
            </div>

            <!-- Timer (in_progress) -->
            <div v-if="timerItem" class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-xs text-gray-400 mb-0.5">{{ $t('label.time_remaining') }}</p>
                <p class="text-lg font-bold font-mono" :class="timerClass">{{ displayTimer }}</p>
                <p class="text-xs text-gray-400">{{ $t('label.total') }}: {{ itemDuration(timerItem) || 0 }}min</p>
            </div>

            <!-- Additional items (when more than one) -->
            <div v-if="activeItems.length > 1" class="text-xs space-y-0.5">
                <div v-for="(item, index) in activeItems" :key="item.session_item_id" class="flex justify-between text-gray-500">
                    <span class="truncate">{{ index + 1 }}. {{ item.item_name || item.item_id }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded-full ml-1 flex-shrink-0"
                        :class="item.status === 'in_progress' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                        {{ item.status === 'in_progress' ? $t('label.in_progress') : $t('label.pending') }}
                    </span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-auto">
                <button class="w-full db-btn text-white bg-purple-600 text-xs py-1.5" @click="$router.push({ name: 'admin.group-session.detail', params: { id: primaryItem?.group_session_id } })">
                    <i class="lab lab-eye-line"></i> {{ $t('label.view_detail') }}
                </button>
            </div> 
        </div>
    </div>
</template>

<script>
import alertService from "../../../services/alertService";

export default {
    name: "RoomCardComponent",
    props: {
        room: { type: Object, required: true },
        activeItems: { type: Array, default: () => [] },
        canOpenSession: { type: Boolean, default: true },
        canEditStatus: { type: Boolean, default: false },
    },
    emits: ['open-session', 'session-updated', 'view-detail'],
    data() {
        return {
            timerInterval: null,
            secondsRemaining: 0,
            autoCompletingItemId: null,
            autoCompletedItems: {},
            showStatusEditor: false,
            editRoomStatus: '',
            statusSaving: false,
        };
    },
    computed: {
        hasActiveItems() {
            return this.activeItems?.length > 0;
        },
        normalizedRoomStatus() {
            return this.room.status || (this.room.is_occupied ? 'occupied' : 'available');
        },
        effectiveStatus() {
            if (this.hasActiveItems) return this.activeItems[0].status || 'occupied';
            return this.normalizedRoomStatus;
        },
        isAvailable() {
            return !this.hasActiveItems && this.normalizedRoomStatus === 'available';
        },
        serviceItems() {
            return this.activeItems ?? [];
        },
        primaryItem() {
            return this.serviceItems.find((item) => item.status === 'in_progress')
                || this.serviceItems[0]
                || null;
        },
        timerItem() {
            return this.serviceItems.find((item) => {
                return item.status === 'in_progress'
                    && this.isServiceTimerItem(item)
                    && Number(this.itemDuration(item) || 0) > 0;
            })
                || null;
        },
        customerInitial() {
            const name = this.primaryItem?.guest_name ?? '?';
            return name.charAt(0).toUpperCase();
        },
        sessionStatusLabel() {
            const map = {
                available:   this.$t('label.available'),
                pending:     this.$t('label.pending'),
                in_progress: this.$t('label.in_progress'),
                completed:   this.$t('label.completed'),
                occupied:    this.$t('label.occupied'),
                cleaning:    this.$t('label.cleaning'),
            };
            return map[this.effectiveStatus] ?? this.effectiveStatus;
        },
        emptyStateClass() {
            const map = {
                available: 'text-green-500',
                cleaning:  'text-blue-500',
                occupied:  'text-yellow-600',
            };
            return map[this.effectiveStatus] ?? 'text-gray-400';
        },
        cardBorderClass() {
            const map = {
                available:   'border border-green-200',
                pending:     'border-2 border-yellow-400',
                in_progress: 'border-2 border-green-500',
                completed:   'border-2 border-blue-500',
                occupied:    'border-2 border-yellow-400',
                cleaning:    'border-2 border-blue-400',
            };
            return map[this.effectiveStatus] ?? 'border';
        },
        cardHeaderClass() {
            const map = {
                available:   'bg-green-50',
                pending:     'bg-yellow-50',
                in_progress: 'bg-green-50',
                completed:   'bg-blue-50',
                occupied:    'bg-yellow-50',
                cleaning:    'bg-blue-50',
            };
            return map[this.effectiveStatus] ?? 'bg-gray-50';
        },
        statusBadgeClass() {
            const map = {
                available:   'bg-green-100 text-green-700',
                pending:     'bg-yellow-100 text-yellow-700',
                in_progress: 'bg-green-100 text-green-700',
                completed:   'bg-blue-100 text-blue-700',
                occupied:    'bg-yellow-100 text-yellow-700',
                cleaning:    'bg-blue-100 text-blue-700',
            };
            return map[this.effectiveStatus] ?? 'bg-gray-100 text-gray-600';
        },
        timerClass() {
            if (this.secondsRemaining <= 0) return 'text-red-600 animate-pulse';
            if (this.secondsRemaining <= 300) return 'text-orange-500';
            return 'text-green-700';
        },
        displayTimer() {
            const abs = Math.abs(this.secondsRemaining);
            const h = Math.floor(abs / 3600);
            const m = Math.floor((abs % 3600) / 60);
            const s = abs % 60;
            const sign = this.secondsRemaining < 0 ? '-' : '';
            if (h > 0) {
                return `${sign}${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            }
            return `${sign}${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        roomStatusOptions() {
            return [
                { value: 'available', label: this.$t('label.available') },
                { value: 'occupied',  label: this.$t('label.occupied') },
                { value: 'cleaning',  label: this.$t('label.cleaning') },
            ];
        },
    },
    watch: {
        activeItems: {
            immediate: true,
            deep: true,
            handler() { this.syncTimer(); },
        },
        'room.status': {
            immediate: true,
            handler(value) {
                this.editRoomStatus = value || 'available';
            },
        },
    },
    unmounted() { this.clearTimer(); },
    methods: {
        syncTimer() {
            this.clearTimer();
            if (this.timerItem) {
                this.startTimer(this.timerItem);
            } else {
                this.secondsRemaining = 0;
            }
        },
        startTimer(item) {
            const totalMinutes = this.itemDuration(item) || 60;
            const startedAt    = this.parseTimestamp(item.started_time_raw || item.started_time);
            if (!startedAt) {
                this.secondsRemaining = 0;
                return;
            }
            const endAt        = startedAt + totalMinutes * 60 * 1000;

            const tick = () => {
                this.secondsRemaining = Math.round((endAt - Date.now()) / 1000);
                if (this.secondsRemaining <= 0) {
                    this.secondsRemaining = 0;
                    this.completeExpiredItem(item);
                }
            };
            tick();
            this.timerInterval = setInterval(tick, 1000);
        },
        completeExpiredItem(item) {
            const itemId = item?.session_item_id || item?.id;
            const sessionId = item?.sub_session_id;

            if (!itemId || !sessionId || this.autoCompletingItemId === itemId || this.autoCompletedItems[itemId]) {
                return;
            }

            this.clearTimer();
            this.autoCompletingItemId = itemId;
            this.autoCompletedItems = { ...this.autoCompletedItems, [itemId]: true };

            this.$store.dispatch('subSession/completeItem', { sessionId, itemId })
                .then(() => {
                    alertService.success(this.$t('message.service_auto_completed') || 'Service completed automatically');
                    this.$emit('session-updated');
                })
                .catch((err) => {
                    delete this.autoCompletedItems[itemId];
                    this.autoCompletedItems = { ...this.autoCompletedItems };
                    alertService.error(err.response?.data?.message || err.message || 'Failed to complete service');
                })
                .finally(() => {
                    if (this.autoCompletingItemId === itemId) {
                        this.autoCompletingItemId = null;
                    }
                });
        },
        parseTimestamp(value) {
            if (!value) return null;

            const dateValue = String(value).trim();

            const displayMatch = dateValue.match(/^(\d{2})-(\d{2})-(\d{4}),\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (displayMatch) {
                const [, day, month, year, hourRaw, minute, ampm] = displayMatch;
                let hour = parseInt(hourRaw, 10);
                if (ampm.toUpperCase() === 'PM' && hour !== 12) hour += 12;
                if (ampm.toUpperCase() === 'AM' && hour === 12) hour = 0;

                return new Date(
                    parseInt(year, 10),
                    parseInt(month, 10) - 1,
                    parseInt(day, 10),
                    hour,
                    parseInt(minute, 10),
                    0
                ).getTime();
            }

            const localMatch = dateValue.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
            if (localMatch) {
                const [, year, month, day, hour, minute, second = '0'] = localMatch;
                return new Date(
                    parseInt(year, 10),
                    parseInt(month, 10) - 1,
                    parseInt(day, 10),
                    parseInt(hour, 10),
                    parseInt(minute, 10),
                    parseInt(second, 10)
                ).getTime();
            }

            const nativeTimestamp = new Date(dateValue).getTime();
            return Number.isNaN(nativeTimestamp) ? null : nativeTimestamp;
        },
        itemDuration(item) {
            const duration = Number(item?.duration ?? 0);
            if (duration > 0) {
                return duration;
            }
            return Number(item?.item_duration ?? 0);
        },
        isServiceTimerItem(item) {
            if (item?.item_kind === undefined || item?.item_kind === null) {
                return true;
            }
            return Number(item.item_kind) === 2;
        },
        toggleStatusEditor() {
            this.editRoomStatus = this.normalizedRoomStatus;
            this.showStatusEditor = !this.showStatusEditor;
        },
        closeStatusEditor() {
            this.editRoomStatus = this.normalizedRoomStatus;
            this.showStatusEditor = false;
        },
        saveRoomStatus() {
            if (!this.editRoomStatus || this.editRoomStatus === this.room.status) {
                this.showStatusEditor = false;
                return;
            }

            this.statusSaving = true;
            this.$store.dispatch('room/changeStatus', {
                id: this.room.id,
                status: this.editRoomStatus,
                search: { paginate: 0, vuex: false },
            }).then(() => {
                alertService.successFlip(1, this.$t('label.status'));
                this.showStatusEditor = false;
                this.$emit('session-updated');
            }).catch((err) => {
                alertService.error(err.response?.data?.message || err.message || 'Failed to update room status');
            }).finally(() => {
                this.statusSaving = false;
            });
        },
        clearTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },
        /*
        viewDetail() {
            console.log('View detail for sub_session_id:', this.primaryItem);

            
            const subSessionId = this.primaryItem?.sub_session_id;
            if (subSessionId) {
                this.$emit('view-detail', subSessionId);
            }
        },
        */
    },
};
</script>
