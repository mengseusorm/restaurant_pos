<template>
    <section class="detail-page">
        <div class="detail-grid">
            <main class="db-card db-tab-div active">
                <div class="db-card-header border-none">
                    <div>
                        <h3 class="db-card-title">Service Sessions</h3>
                        <p class="detail-subtitle">{{ roomName }}</p>
                    </div>
                    <div class="db-card-filter">
                        <button type="button" class="db-btn py-2 text-white bg-gray-600" aria-label="Back to room tasks" title="Back to room tasks" @click="backToTasks">
                            <i class="lab lab-arrow-left"></i>
                            <span>{{ $t('button.back') }}</span>
                        </button>
                        <button type="button" class="db-btn py-2 text-white bg-primary" @click="openAddService">
                            <i class="lab lab-add-line"></i>
                            <span>{{ $t('button.add_service') }}</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div v-if="error" class="detail-alert">{{ error }}</div>

                    <div class="detail-stats">  
                        <div class="detail-stat-box">
                            <span class="detail-stat-label">{{ $t('label.services') }}</span>
                            <strong>{{ serviceItems.length }}</strong>
                        </div>
                        <div class="detail-stat-box">
                            <span class="detail-stat-label">{{ $t('label.products') }}</span>
                            <strong>{{ productOrders.length }}</strong>
                        </div>
                        <div class="detail-stat-box">
                            <span class="detail-stat-label">{{ $t('label.guest') }}</span>
                            <strong>{{ guestSummary }}</strong>
                        </div>
                    </div>

                    <div v-if="hasFilterOptions" class="detail-filters">
                        <div v-if="roomFilterOptions.length > 0" class="detail-filter-group">
                            <span class="detail-filter-label">{{ $t('label.room') }}</span>
                            <button
                                type="button"
                                class="detail-filter-chip"
                                :class="{ active: selectedRoomKey === 'all' }"
                                @click="selectedRoomKey = 'all'"
                            >
                                {{ $t('label.all') }}
                            </button>
                            <button
                                v-for="roomOption in roomFilterOptions"
                                :key="roomOption.key"
                                type="button"
                                class="detail-filter-chip"
                                :class="{ active: selectedRoomKey === roomOption.key }"
                                @click="selectedRoomKey = roomOption.key"
                            >
                                {{ roomOption.name }}
                            </button>
                        </div>

                        <div v-if="therapistFilterOptions.length > 0" class="detail-filter-group">
                            <span class="detail-filter-label">{{ $t('label.therapist') }}</span>
                            <button
                                type="button"
                                class="detail-filter-chip"
                                :class="{ active: selectedTherapistKey === 'all' }"
                                @click="selectedTherapistKey = 'all'"
                            >
                                {{ $t('label.all') }}
                            </button>
                            <button
                                v-for="therapist in therapistFilterOptions"
                                :key="therapist.key"
                                type="button"
                                class="detail-filter-chip"
                                :class="{ active: selectedTherapistKey === therapist.key }"
                                @click="selectedTherapistKey = therapist.key"
                            >
                                {{ therapist.name }}
                            </button>
                        </div>
                    </div>

                    <div class="service-list">
                        <div v-if="loading" class="detail-empty">Loading session...</div>
                        <div v-else-if="serviceItems.length === 0" class="detail-empty">No service items found for this filter.</div>

                        <template v-else>
                            <article
                                v-for="task in serviceItems"
                                :key="'service-' + task.id"
                                class="service-card"
                                :class="'is-' + (task.status || 'pending')"
                            >
                                <div class="service-card-top">
                                    <div class="service-title-wrap">
                                        <div class="service-avatar">{{ initials(task.therapist?.name || task.item?.name) }}</div>
                                        <div class="min-w-0">
                                            <h4 class="service-title">{{ task.item?.name || 'Service' }}</h4>
                                            <p class="service-meta">
                                                <span>{{ $t('label.room') }}: {{ task.room?.name || roomName }}</span>
                                                <span>{{ $t('label.therapist') }}: {{ task.therapist?.name || 'N/A' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="service-status" :class="'status-' + (task.status || 'pending')">
                                        {{ statusLabel(task.status) }}
                                    </span>
                                </div>

                                <div class="service-time-grid">
                                    <div class="service-time-box">
                                        <span>{{ $t('label.pick_time') }}</span>
                                        <strong>{{ task.start_time || 'N/A' }}</strong>
                                    </div>
                                    <div class="service-time-box">
                                        <span>{{ $t('label.started') }}</span>
                                        <strong>{{ displayStartedTime(task) }}</strong>
                                    </div>
                                    <div class="service-time-box">
                                        <span>{{ $t('label.ended') }}</span>
                                        <strong>{{ displayEndedTime(task) }}</strong>
                                    </div>
                                </div>

                                <div class="service-card-bottom">
                                    <div class="service-duration">
                                        <span>{{ $t('label.duration') }}</span>
                                        <strong>{{ itemDuration(task) }} {{ $t('label.min') }}</strong>
                                    </div>
                                    <div v-if="isInProgress(task)" class="service-countdown">
                                        <span>{{ $t('label.remaining') }}</span>
                                        <strong>{{ remainingLabel(task) }}</strong>
                                    </div>
                                    <div class="service-actions">
                                        <button
                                            v-if="isPending(task)"
                                            type="button"
                                            class="service-action-btn start"
                                            :disabled="isActionLoading(task.id)"
                                            @click="startItem(task)"
                                        >
                                            <i class="lab lab-play-line"></i>
                                            <span>{{ $t('button.start') }} {{ itemDuration(task) }} {{ $t('label.min') }}</span>
                                        </button>
                                        <button
                                            v-else-if="isInProgress(task)"
                                            type="button"
                                            class="service-action-btn stop"
                                            :disabled="isActionLoading(task.id)"
                                            @click="completeItem(task)"
                                        >
                                            <i class="lab lab-check"></i>
                                            <span>Stop</span>
                                        </button>
                                        <span v-else class="service-complete-chip">
                                            <i class="lab lab-check"></i>
                                            {{ $t('label.completed') }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </main>

            <aside class="db-card db-tab-div active">
                <div class="db-card-header border-none">
                    <div>
                        <h3 class="db-card-title">{{ $t('label.product_order') }}</h3>
                        <p class="detail-subtitle">{{ $t('label.order_summary') }}</p>
                    </div>
                    <div class="db-card-filter">
                        <button type="button" class="db-btn py-2 text-white bg-primary" @click="openAddProduct">
                            <i class="lab lab-add-line"></i>
                            <span>{{ $t('button.add_product') }}</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="product-list">
                        <div v-if="loading" class="detail-empty">Loading orders...</div>
                        <div v-else-if="productOrders.length === 0" class="detail-empty">{{ $t('label.no_product_orders') }}</div>
                        <template v-else>
                            <div v-for="task in productOrders" :key="'product-' + task.id" class="product-order-card">
                                <div class="min-w-0">
                                    <h4>{{ task.item?.name || 'Product' }}</h4>
                                    <p>{{ $t('label.qty') }}: {{ task.quantity || 1 }}</p>
                                </div>
                                <strong>{{ priceLabel(task) }}</strong>
                            </div>
                        </template>
                    </div>

                    <div v-if="productOrders.length > 0" class="detail-total">
                        <span>{{ $t('label.total') }}</span>
                        <strong>{{ productTotal }}</strong>
                    </div>
                </div>
            </aside>
        </div>

        <div v-if="addDialogOpen" class="modal active" @click.self="closeAddDialog">
            <div class="modal-dialog max-w-5xl" role="dialog" aria-modal="true" :aria-label="dialogTitle">
                <div class="modal-header">
                    <h3 class="modal-title">{{ dialogTitle }}</h3>
                    <button
                        type="button"
                        class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                        aria-label="Close dialog"
                        @click="closeAddDialog"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] gap-4">
                        <section class="min-w-0">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                <h4 class="text-sm font-semibold text-heading">{{ dialogListTitle }}</h4>
                                <button
                                    type="button"
                                    class="db-btn-outline sm primary"
                                    :disabled="dialogLoading"
                                    aria-label="Refresh items"
                                    @click="loadDialogLookups"
                                >
                                    <i class="lab lab-refresh-line" :class="{ 'animate-spin': dialogLoading }"></i>
                                    <span>{{ $t('button.refresh') || 'Refresh' }}</span>
                                </button>
                            </div>

                            <div v-if="dialogError" class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                {{ dialogError }}
                            </div>

                            <div class="mb-4 relative">
                                <i class="lab lab-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                                <input 
                                    v-model="dialogSearch" 
                                    type="text" 
                                    class="db-field-control w-full pl-10" 
                                    :placeholder="$t('label.search') || 'Search name, price, duration...'"
                                >
                            </div>

                            <div class="flex flex-col gap-3 max-h-[55vh] overflow-y-auto p-1">
                                <div v-if="dialogLoading" class="text-center py-8 text-gray-400">{{ $t('button.loading') }}</div>
                                <div v-else-if="dialogItems.length === 0" class="text-center py-8 text-gray-400">{{ dialogEmptyText }}</div>
                                
                                <div
                                    v-for="item in dialogItems"
                                    v-else
                                    :key="item.id"
                                    class="relative flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all duration-200 hover:shadow-md bg-white"
                                    :class="[
                                        (Number(addForm.item_id) === Number(item.id) || productQty(item.id) > 0)
                                            ? (isServiceDialog ? 'border-primary ring-1 ring-primary/20 shadow-sm' : 'border-gray-200 shadow-sm bg-gray-50')
                                            : 'border-gray-200',
                                        isServiceDialog ? 'hover:border-primary/50' : ''
                                    ]"
                                    @click="selectDialogItem(item)"
                                >
                                    <img v-if="item.thumb" :src="item.thumb" :alt="item.name" class="w-16 h-16 rounded-lg object-cover bg-gray-50 flex-shrink-0 border border-gray-100" />
                                    <div v-else class="w-16 h-16 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-300 flex-shrink-0">
                                        <i class="lab lab-image-line text-2xl"></i>
                                    </div>

                                    <div class="flex-grow min-w-0 py-1">
                                        <h5 class="font-semibold text-heading truncate mb-1" :title="item.name">{{ item.name }}</h5>
                                        <div class="flex items-center gap-3 text-sm">
                                            <span class="font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-md text-xs">
                                                {{ Number(item.price || 0).toFixed(2) }}
                                            </span>
                                            <span v-if="isServiceDialog" class="text-gray-500 text-xs flex items-center gap-1">
                                                <i class="lab lab-time-line"></i> {{ item.duration || 0 }} {{ $t('label.min') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0 ml-auto" @click.stop>
                                        <div v-if="!isServiceDialog" class="inline-flex items-center gap-1 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                            <button type="button" class="flex h-7 w-7 items-center justify-center rounded bg-white text-gray-600 hover:bg-gray-100 shadow-sm transition-colors font-bold" @click="changeProductQty(item, -1)">-</button>
                                            <span class="w-8 text-center font-bold text-heading text-sm">{{ productQty(item.id) }}</span>
                                            <button type="button" class="flex h-7 w-7 items-center justify-center rounded bg-primary text-white hover:bg-primary-600 shadow-sm transition-colors font-bold" @click="changeProductQty(item, 1)">+</button>
                                        </div>
                                        <div v-else>
                                            <button 
                                                type="button" 
                                                class="db-btn-outline sm primary px-4 py-1.5 rounded-lg flex items-center gap-1.5" 
                                                @click.stop="selectDialogItem(item)"
                                            >
                                                <i class="lab lab-check-line text-base"></i>
                                                <span class="hidden sm:inline-block">{{ $t('button.select') || 'Select' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div 
                                        v-if="isServiceDialog && Number(addForm.item_id) === Number(item.id)" 
                                        class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-white shadow-sm ring-2 ring-white"
                                    >
                                        <i class="lab lab-check-line text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <aside class="rounded-md border border-gray-200 bg-[#f9fafb] p-4">
                            <h4 class="text-sm font-semibold text-heading mb-3">
                                {{ isServiceDialog ? $t('label.session_setup') : 'Order Information' }}
                            </h4>

                            <ul class="db-list single mb-4">
                                <li class="db-list-item">
                                    <span class="db-list-item-title">{{ $t('label.guest') }}</span>
                                    <span class="db-list-item-text">{{ sessionGuest }}</span>
                                </li>
                                <li class="db-list-item">
                                    <span class="db-list-item-title">{{ $t('label.room') }}</span>
                                    <span class="db-list-item-text">{{ roomName }}</span>
                                </li>
                                <li class="db-list-item">
                                    <span class="db-list-item-title">{{ isServiceDialog ? $t('label.service') : $t('label.product_order') }}</span>
                                    <span class="db-list-item-text">{{ selectedDialogItem?.name || '-' }}</span>
                                </li>
                            </ul>

                            <div class="space-y-4">
                                <div class="form-col-12">
                                    <label class="db-field-title after:hidden">{{ $t('label.select_bed') }}</label>
                                    <div class="p-3 rounded-lg border border-[#D9DBE9] bg-white">
                                        <div v-if="bedsForRoom.length > 0" class="db-field-radio-group flex-wrap gap-2 pt-0 active-group">
                                            <label
                                                v-for="bed in bedsForRoom"
                                                :key="bed.id"
                                                :for="'dialog_bed_option_' + bed.id"
                                                class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]"
                                                :class="{ active: Number(addForm.bed_id) === Number(bed.id) }"
                                            >
                                                <div class="custom-radio sm">
                                                    <input
                                                        :id="'dialog_bed_option_' + bed.id"
                                                        v-model="addForm.bed_id"
                                                        type="radio"
                                                        name="dialog_bed_id"
                                                        :value="bed.id"
                                                        class="custom-radio-field"
                                                    />
                                                    <span class="custom-radio-span"></span>
                                                </div>
                                                <span class="db-field-label text-sm text-heading">{{ bed.name }}</span>
                                            </label>
                                        </div>
                                        <p v-else class="text-xs text-gray-500">{{ $t('label.no_beds_found') }}</p>
                                    </div>
                                </div>

                                <div v-if="isServiceDialog" class="form-col-12">
                                    <label class="db-field-title after:hidden">{{ $t('label.therapist') }}</label>
                                    <div class="p-3 rounded-lg border border-[#D9DBE9] bg-white">
                                        <div v-if="therapists.length > 0" class="db-field-radio-group flex-wrap gap-2 pt-0 active-group">
                                            <label
                                                v-for="therapist in therapists"
                                                :key="therapist.id"
                                                :for="'dialog_therapist_option_' + therapist.id"
                                                class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]"
                                                :class="{ active: Number(addForm.therapist_id) === Number(therapist.user_id) }"
                                            >
                                                <div class="custom-radio sm">
                                                    <input
                                                        :id="'dialog_therapist_option_' + therapist.id"
                                                        v-model="addForm.therapist_id"
                                                        type="radio"
                                                        name="dialog_therapist_id"
                                                        :value="therapist.user_id"
                                                        class="custom-radio-field"
                                                    />
                                                    <span class="custom-radio-span"></span>
                                                </div>
                                                <span class="db-field-label text-sm text-heading">
                                                    {{ therapist.user?.name || therapist.name || ('#' + therapist.user_id) }}
                                                </span>
                                            </label>
                                        </div>
                                        <p v-else class="text-xs text-gray-500">{{ $t('label.no_therapists_found') }}</p>
                                    </div>
                                </div>

                                <div v-if="isServiceDialog" class="form-col-12">
                                    <label class="db-field-title after:hidden">{{ $t('label.start_time') }}</label>
                                    <input v-model="addForm.start_time" type="datetime-local" class="db-field-control">
                                </div>

                                <div v-if="isServiceDialog" class="form-col-12">
                                    <label class="db-field-title after:hidden">{{ $t('label.quantity') }}</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="db-btn-fill gray sm" @click="changeDialogQty(-1)">-</button>
                                        <span class="min-w-10 text-center text-sm font-semibold text-heading">{{ addForm.quantity }}</span>
                                        <button type="button" class="db-btn-fill primary sm" @click="changeDialogQty(1)">+</button>
                                    </div>
                                </div>

                                <div v-if="!isServiceDialog" class="form-col-12">
                                    <label class="db-field-title after:hidden">{{ $t('label.qty') }}</label>
                                    <div class="rounded-md border border-gray-200 bg-white p-3">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="font-medium text-heading">{{ $t('label.selected') || 'Selected' }}</span>
                                            <strong class="text-heading">{{ selectedProductCount }}</strong>
                                        </div>
                                        <div v-if="selectedProductItems.length > 0" class="mt-3 flex flex-wrap gap-2">
                                            <span
                                                v-for="entry in selectedProductItems"
                                                :key="entry.item.id"
                                                class="rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary"
                                            >
                                                {{ entry.item.name }} x {{ entry.quantity }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline" @click="closeAddDialog">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button>
                                <button
                                    type="button"
                                    class="db-btn py-2 text-white bg-primary"
                                    :disabled="dialogSaving || !canSubmitDialog"
                                    @click="submitDialogAdd"
                                >
                                    <i class="lab lab-save"></i>
                                    <span>{{ dialogSaving ? $t('button.adding') : dialogSubmitLabel }}</span>
                                </button>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Verification Dialog -->
        <div v-if="showPasswordVerification" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 sm:p-0 backdrop-blur-sm">
            <div class="relative w-full max-w-md overflow-hidden bg-white rounded-xl shadow-2xl sm:my-8 sm:w-full border border-gray-100">
                <div class="px-5 py-6 bg-white sm:p-6">
                    <h3 class="text-xl font-bold leading-6 text-heading mb-2">{{ $t('label.verify_therapist') || 'Verify Therapist' }}</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Please enter the password for <strong>{{ verificationTherapistName }}</strong> to start this service.
                    </p>
                    
                    <div v-if="passwordVerificationError" class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ passwordVerificationError }}
                    </div>
                    
                    <div class="mb-4">
                        <label class="db-field-title">{{ $t('label.password') || 'Password' }}</label>
                        <input 
                            v-model="passwordInput" 
                            type="password" 
                            class="db-field-control w-full" 
                            :placeholder="$t('label.enter_password') || 'Enter password'"
                            @keyup.enter="verifyPassword"
                        >
                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <button 
                        type="button" 
                        class="db-btn py-2 text-white bg-primary" 
                        :disabled="passwordVerifying || !passwordInput"
                        @click="verifyPassword"
                    >
                        <i class="lab lab-check-line"></i>
                        <span>{{ passwordVerifying ? $t('button.verifying') : $t('button.verify') || 'Verify' }}</span>
                    </button>
                    <button 
                        type="button" 
                        class="db-btn py-2 text-white bg-red-500 hover:bg-red-600" 
                        :disabled="passwordVerifying"
                        @click="closePasswordVerification"
                    >
                        {{ $t('button.cancel') || 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";
import alertService from "../../services/alertService";

export default {
    name: "TherapistSessionDetailComponent",
    props: {
        roomId: {
            type: [String, Number],
            required: true,
        },
        subSessionId: {
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
            selectedRoomKey: "all",
            selectedTherapistKey: "all",
            now: Date.now(),
            timerInterval: null,
            actionLoading: {},
            addDialogOpen: false,
            addMode: "service",
            dialogLoading: false,
            dialogSaving: false,
            dialogError: "",
            dialogSearch: "",
            items: [],
            beds: [],
            therapists: [],
            productQuantities: {},
            addForm: {
                item_id: "",
                bed_id: "",
                therapist_id: "",
                start_time: "",
                quantity: 1,
                price: 0,
                duration: 0,
            },
            showPasswordVerification: false,
            passwordVerificationTask: null,
            passwordInput: "",
            passwordVerifying: false,
            passwordVerificationError: "",
        };
    },
    computed: {
        roomName() {
            return this.room?.name || this.$route.query.roomName || `Room ${this.roomId}`;
        },
        currentSessionTasks() {
            return this.tasks.filter((task) => Number(task.sub_session_id) === Number(this.subSessionId));
        },
        filteredTasks() {
            return this.currentSessionTasks.filter((task) => {
                const matchesRoom = this.selectedRoomKey === "all" || this.roomKey(task) === this.selectedRoomKey;
                const matchesTherapist = this.selectedTherapistKey === "all" || this.therapistKey(task) === this.selectedTherapistKey;
                return matchesRoom && matchesTherapist;
            });
        },
        serviceItems() {
            return this.filteredTasks.filter((task) => Number(task.item?.item_kind || 1) === 2);
        },
        productOrders() {
            return this.filteredTasks.filter((task) => Number(task.item?.item_kind || 1) !== 2);
        },
        sessionGuest() {
            return this.currentSessionTasks[0]?.guest_name || 'N/A';
        },
        guestSummary() {
            const names = new Set(this.filteredTasks.map((task) => task.guest_name).filter(Boolean));
            if (names.size === 0) return "N/A";
            if (names.size === 1) return Array.from(names)[0];
            return `${names.size} guests`;
        },
        productTotal() {
            const total = this.productOrders.reduce((sum, task) => {
                return sum + Number(task.final_price || task.price || 0);
            }, 0);
            return total.toFixed(2);
        },
        roomFilterOptions() {
            const map = {};
            this.currentSessionTasks.forEach((task) => {
                const key = this.roomKey(task);
                map[key] = { key, name: task.room?.name || this.roomName || "No room" };
            });
            return Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
        },
        therapistFilterOptions() {
            const map = {};
            this.currentSessionTasks.forEach((task) => {
                const key = this.therapistKey(task);
                map[key] = { key, name: task.therapist?.name || "Unassigned" };
            });
            return Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
        },
        hasFilterOptions() {
            return this.roomFilterOptions.length > 0 || this.therapistFilterOptions.length > 0;
        },
        isServiceDialog() {
            return this.addMode === "service";
        },
        dialogTitle() {
            return this.isServiceDialog ? "Add New Service" : "Order Product";
        },
        dialogListTitle() {
            return this.isServiceDialog ? "Please Select Product/Service:" : "Please Select Products:";
        },
        dialogSubmitLabel() {
            return this.isServiceDialog ? this.$t('button.add_service') : this.$t('button.add_product');
        },
        dialogEmptyText() {
            return this.isServiceDialog ? this.$t('label.no_services_found') : this.$t('label.no_product_orders');
        },
        dialogItems() {
            let filtered = this.items.filter((item) => this.isServiceDialog
                ? Number(item.item_kind || 1) === 2
                : Number(item.item_kind || 1) !== 2
            );
            if (this.dialogSearch) {
                const term = this.dialogSearch.toLowerCase();
                filtered = filtered.filter(item => {
                    return (item.name && item.name.toLowerCase().includes(term)) ||
                           (item.price && String(item.price).includes(term)) ||
                           (item.duration && String(item.duration).includes(term));
                });
            }
            return filtered;
        },
        selectedDialogItem() {
            return this.items.find((item) => Number(item.id) === Number(this.addForm.item_id)) || null;
        },
        bedsForRoom() {
            return this.beds.filter((bed) => Number(bed.room_id) === Number(this.roomId));
        },
        selectedProductItems() {
            return this.dialogItems
                .map((item) => ({ item, quantity: this.productQty(item.id) }))
                .filter((entry) => entry.quantity > 0);
        },
        selectedProductCount() {
            return this.selectedProductItems.reduce((sum, entry) => sum + entry.quantity, 0);
        },
        canSubmitDialog() {
            return this.isServiceDialog ? !!this.addForm.item_id : this.selectedProductItems.length > 0;
        },
        verificationTherapistName() {
            if (!this.passwordVerificationTask || !this.passwordVerificationTask.therapist) {
                return "Therapist";
            }
            return this.passwordVerificationTask.therapist.name || this.passwordVerificationTask.therapist.user?.name || "Therapist";
        },
    },
    mounted() {
        this.loadSession();
        this.timerInterval = setInterval(() => {
            this.now = Date.now();
        }, 1000);
    },
    beforeUnmount() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
    },
    methods: {
        loadSession() {
            this.loading = true;
            this.error = "";

            axios.get(`therapist/rooms/${this.roomId}/tasks`).then((res) => {
                this.room = res.data.data || null;
                this.tasks = res.data.data?.session_items || [];
            }).catch((err) => {
                this.error = err.response?.data?.message || "Unable to load session.";
            }).finally(() => {
                this.loading = false;
            });
        },
        backToTasks() {
            this.$router.push({
                name: "therapist.room.tasks",
                params: { roomId: this.roomId },
                query: { roomName: this.roomName },
            });
        },
        openAddService() {
            this.openAddDialog("service");
        },
        openAddProduct() {
            this.openAddDialog("product");
        },
        openAddDialog(mode) {
            this.addMode = mode;
            this.resetAddForm();
            this.addDialogOpen = true;
            if (this.items.length === 0 || this.beds.length === 0 || this.therapists.length === 0) {
                this.loadDialogLookups();
                return;
            }
            this.applyDefaultDialogOptions();
        },
        closeAddDialog() {
            if (this.dialogSaving) return;
            this.addDialogOpen = false;
            this.dialogError = "";
        },
        loadDialogLookups() {
            this.dialogLoading = true;
            this.dialogError = "";

            return Promise.all([
                axios.get("therapist/items", { params: { paginate: 0, order_column: "name", order_type: "asc" } }),
                axios.get("therapist/beds", { params: { paginate: 0 } }),
                axios.get("therapist/therapists", { params: { paginate: 0 } }),
            ]).then(([itemsRes, bedsRes, therapistsRes]) => {
                this.items = itemsRes.data.data || [];
                this.beds = bedsRes.data.data || [];
                this.therapists = therapistsRes.data.data || [];
                this.applyDefaultDialogOptions();
            }).catch((err) => {
                this.dialogError = err.response?.data?.message || "Unable to load item options.";
            }).finally(() => {
                this.dialogLoading = false;
            });
        },
        resetAddForm() {
            this.productQuantities = {};
            this.dialogSearch = "";
            this.addForm = {
                item_id: "",
                bed_id: "",
                therapist_id: this.$store.getters.authInfo?.id || "",
                start_time: this.toDatetimeLocal(new Date()),
                quantity: 1,
                price: 0,
                duration: 0,
            };
        },
        applyDefaultDialogOptions() {
            if (!this.addForm.bed_id && this.bedsForRoom[0]) {
                this.addForm.bed_id = this.bedsForRoom[0].id;
            }
            if (!this.addForm.therapist_id) {
                this.addForm.therapist_id = this.$store.getters.authInfo?.id || "";
            }
        },
        selectDialogItem(item, autoAddProduct = true) {
            this.addForm.item_id = item.id;
            this.addForm.price = item.price || 0;
            this.addForm.duration = item.duration || 0;

            if (autoAddProduct && !this.isServiceDialog && this.productQty(item.id) === 0) {
                this.changeProductQty(item, 1);
            }
        },
        productQty(itemId) {
            return Number(this.productQuantities[itemId] || 0);
        },
        changeProductQty(item, amount) {
            const nextQty = Math.max(0, this.productQty(item.id) + amount);
            this.productQuantities = { ...this.productQuantities, [item.id]: nextQty };
            this.selectDialogItem(item, false);
        },
        changeDialogQty(amount) {
            this.addForm.quantity = Math.max(1, Number(this.addForm.quantity || 1) + amount);
        },
        submitDialogAdd() {
            if (!this.canSubmitDialog) return;

            this.dialogSaving = true;
            this.dialogError = "";

            const requests = this.isServiceDialog
                ? [this.createAddPayload(this.selectedDialogItem, this.addForm.quantity)]
                : this.selectedProductItems.map((entry) => this.createAddPayload(entry.item, entry.quantity));

            Promise.all(requests.map((payload) => axios.post(`therapist/massage-session/${this.subSessionId}/add-item`, payload))).then(() => {
                this.addDialogOpen = false;
                this.loadSession();
                alertService.success(this.isServiceDialog ? "Service added" : "Product added");
            }).catch((err) => {
                this.dialogError = err.response?.data?.message || "Unable to add item.";
            }).finally(() => {
                this.dialogSaving = false;
            });
        },
        createAddPayload(item, quantity) {
            return {
                item_id: item?.id || this.addForm.item_id,
                room_id: this.roomId,
                bed_id: this.addForm.bed_id || null,
                therapist_id: this.isServiceDialog ? (this.addForm.therapist_id || null) : null,
                start_time: this.isServiceDialog ? (this.addForm.start_time || null) : null,
                quantity: quantity || 1,
                price: item?.price || this.addForm.price || 0,
                duration: this.isServiceDialog ? (item?.duration || this.addForm.duration || 0) : 0,
                discount: 0,
            };
        },
        toDatetimeLocal(date) {
            const pad = (value) => String(value).padStart(2, "0");
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },
        priceLabel(task) {
            return Number(task.final_price || task.price || 0).toFixed(2);
        },
        startItem(task) {
            if (task.therapist_id) {
                this.passwordVerificationTask = task;
                this.showPasswordVerification = true;
                this.passwordInput = "";
                this.passwordVerificationError = "";
            } else {
                this.processStartItem(task);
            }
        },
        verifyPassword() {
            if (!this.passwordInput) {
                this.passwordVerificationError = this.$t('message.password_required') || "Password is required";
                return;
            }

            this.passwordVerifying = true;
            this.passwordVerificationError = "";

            const task = this.passwordVerificationTask;
            let url = `therapist/massage-session/${task.sub_session_id}/start-item/${task.id}`;
            const payload = { password: this.passwordInput };

            axios.post(url, payload).then((res) => {
                this.replaceTask(task.id, { ...task, ...(res.data.data || {}) });
                alertService.success(this.$t('message.session_started') || 'Service started');
                this.closePasswordVerification();
            }).catch((err) => {
                this.passwordVerificationError = err.response?.data?.message || err.message || this.$t('message.invalid_password') || 'Password does not match the assigned therapist';
            }).finally(() => {
                this.passwordVerifying = false;
            });
        },
        closePasswordVerification() {
            this.showPasswordVerification = false;
            this.passwordVerificationTask = null;
            this.passwordInput = "";
            this.passwordVerificationError = "";
        },
        processStartItem(task) {
            this.setActionLoading(task.id, true);
            let url = `therapist/massage-session/${task.sub_session_id}/start-item/${task.id}`;
            let payload = {};

            axios.post(url, payload).then((res) => {
                this.replaceTask(task.id, { ...task, ...(res.data.data || {}) });
                alertService.success(this.$t('message.session_started') || 'Service started');
            }).catch((err) => {
                alertService.error(err.response?.data?.message || err.message || 'Failed to start service');
            }).finally(() => {
                this.setActionLoading(task.id, false);
            });
        },
        completeItem(task) {
            this.setActionLoading(task.id, true);
            axios.post(`therapist/massage-session/${task.sub_session_id}/complete-item/${task.id}`).then((res) => {
                this.replaceTask(task.id, { ...task, ...(res.data.data || {}) });
                alertService.success(this.$t('message.session_completed') || 'Service completed');
            }).catch((err) => {
                alertService.error(err.response?.data?.message || err.message || 'Failed to complete service');
            }).finally(() => {
                this.setActionLoading(task.id, false);
            });
        },
        replaceTask(taskId, taskData) {
            this.tasks = this.tasks.map((task) => Number(task.id) === Number(taskId) ? taskData : task);
        },
        setActionLoading(taskId, value) {
            this.actionLoading = { ...this.actionLoading, [taskId]: value };
        },
        isActionLoading(taskId) {
            return !!this.actionLoading[taskId];
        },
        isPending(task) {
            return !task.status || task.status === "pending";
        },
        isInProgress(task) {
            return task.status === "in_progress";
        },
        itemDuration(task) {
            return Number(task.duration || task.duration_minutes || task.item?.duration || 0);
        },
        displayStartedTime(task) {
            return task.started_time || task.started_at || task.start_time || "N/A";
        },
        displayEndedTime(task) {
            if (task.ended_time || task.ended_at) {
                return task.ended_time || task.ended_at;
            }

            if (this.isInProgress(task)) {
                return this.projectedEndTime(task) || task.end_time || "N/A";
            }

            return task.end_time || "N/A";
        },
        projectedEndTime(task) {
            const duration = this.itemDuration(task);
            const startedAt = this.timestampValue(task.started_time_raw || task.started_time || task.started_at_raw || task.started_at);

            if (!duration || !startedAt) {
                return "";
            }

            return this.formatDisplayDateTime(new Date(startedAt + duration * 60 * 1000));
        },
        formatDisplayDateTime(date) {
            if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                return "";
            }

            const pad = (value) => String(value).padStart(2, "0");
            let hours = date.getHours();
            const meridiem = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;

            return `${pad(date.getDate())}-${pad(date.getMonth() + 1)}-${date.getFullYear()}, ${pad(hours)}:${pad(date.getMinutes())} ${meridiem}`;
        },
        remainingLabel(task) {
            return this.formatSeconds(this.remainingSeconds(task));
        },
        remainingSeconds(task) {
            const duration = this.itemDuration(task);
            const startedAt = this.timestampValue(task.started_time_raw || task.started_time || task.started_at_raw || task.started_at);

            if (!duration || !startedAt) {
                return 0;
            }

            const endAt = startedAt + duration * 60 * 1000;
            return Math.max(0, Math.floor((endAt - this.now) / 1000));
        },
        formatSeconds(seconds) {
            const safeSeconds = Math.max(0, Number(seconds) || 0);
            const hours = Math.floor(safeSeconds / 3600);
            const minutes = Math.floor((safeSeconds % 3600) / 60);
            const secs = safeSeconds % 60;
            const pad = (value) => String(value).padStart(2, "0");

            if (hours > 0) {
                return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
            }

            return `${pad(minutes)}:${pad(secs)}`;
        },
        timestampValue(value) {
            if (!value) return null;

            const text = String(value).trim();
            const displayMatch = text.match(/^(\d{2})-(\d{2})-(\d{4}),\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/i);

            if (displayMatch) {
                const [, day, month, year, hourRaw, minute, meridiem] = displayMatch;
                let hour = parseInt(hourRaw, 10);
                if (meridiem.toUpperCase() === "PM" && hour !== 12) hour += 12;
                if (meridiem.toUpperCase() === "AM" && hour === 12) hour = 0;

                return new Date(Number(year), Number(month) - 1, Number(day), hour, Number(minute)).getTime();
            }

            const parsed = new Date(text).getTime();
            return Number.isNaN(parsed) ? null : parsed;
        },
        statusLabel(status) {
            const map = {
                pending: "Pending",
                in_progress: this.$t('label.started') || "Started",
                completed: this.$t('label.completed') || "Completed",
            };
            return map[status] || "Pending";
        },
        roomKey(task) {
            return task.room_id ? String(task.room_id) : `room:${task.room?.name || this.roomName || "none"}`;
        },
        therapistKey(task) {
            return task.therapist_id ? String(task.therapist_id) : "unassigned";
        },
        initials(name) {
            return String(name || "T").split(" ").filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
        },
    },
};
</script>

<style scoped>
.detail-page {
    min-height: calc(100vh - 90px);
    padding: 16px;
}

.detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
    gap: 16px;
}

.detail-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.detail-alert {
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #991b1b;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: 13px;
}

.detail-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.detail-stat-box {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    padding: 10px 12px;
}

.detail-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    color: #6b7280;
}

.detail-stat-box strong {
    display: block;
    margin-top: 4px;
    font-size: 15px;
    color: #1f2937;
}

.detail-filters {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    padding: 12px;
    margin-bottom: 12px;
}

.detail-filter-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.detail-filter-group + .detail-filter-group {
    margin-top: 10px;
}

.detail-filter-label {
    min-width: 72px;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.detail-filter-chip {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    color: #374151;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    padding: 8px 10px;
    transition: 0.2s ease;
}

.detail-filter-chip:hover {
    border-color: rgba(var(--primary), 0.35);
    color: rgb(var(--primary));
}

.detail-filter-chip.active {
    border-color: rgb(var(--primary));
    background: rgba(var(--primary), 0.1);
    color: rgb(var(--primary));
}

.detail-total {
    margin-top: 12px;
    border-top: 1px solid #e5e7eb;
    padding-top: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
}

.detail-total strong {
    font-size: 16px;
    color: #1f2937;
}

.detail-empty {
    border: 1px dashed #d1d5db;
    border-radius: 8px;
    color: #6b7280;
    font-size: 13px;
    padding: 28px 14px;
    text-align: center;
}

.service-list,
.product-list {
    display: grid;
    gap: 12px;
}

.service-card {
    border: 1px solid #e5e7eb;
    border-left: 4px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    padding: 14px;
}

.service-card.is-in_progress {
    border-left-color: #2563eb;
    background: #f8fbff;
}

.service-card.is-completed {
    border-left-color: #16a34a;
}

.service-card-top,
.service-card-bottom,
.product-order-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.service-title-wrap {
    display: flex;
    align-items: center;
    min-width: 0;
    gap: 10px;
}

.service-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(var(--primary), 0.12);
    color: rgb(var(--primary));
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    font-size: 13px;
    font-weight: 700;
}

.service-title {
    color: #111827;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.service-meta {
    color: #6b7280;
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
    font-size: 12px;
    line-height: 1.35;
    margin-top: 3px;
}

.service-status {
    border-radius: 999px;
    flex: 0 0 auto;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    padding: 7px 9px;
    text-transform: uppercase;
}

.status-pending {
    background: #f3f4f6;
    color: #4b5563;
}

.status-in_progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-completed {
    background: #dcfce7;
    color: #15803d;
}

.service-time-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin: 12px 0;
}

.service-time-box {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    min-width: 0;
    padding: 9px 10px;
}

.service-time-box span,
.service-duration span,
.service-countdown span {
    color: #6b7280;
    display: block;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.service-time-box strong {
    color: #1f2937;
    display: block;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.service-duration strong,
.service-countdown strong {
    color: #111827;
    font-size: 18px;
    font-weight: 800;
    line-height: 1;
}

.service-countdown strong {
    color: #1d4ed8;
    font-variant-numeric: tabular-nums;
}

.service-card-bottom {
    align-items: flex-end;
}

.service-actions {
    display: flex;
    justify-content: flex-end;
    min-width: 132px;
}

.service-action-btn,
.service-complete-chip {
    align-items: center;
    border-radius: 8px;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    gap: 6px;
    min-height: 38px;
    padding: 10px 12px;
}

.service-action-btn {
    border: 0;
    color: #fff;
    cursor: pointer;
}

.service-action-btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.service-action-btn.start {
    background: rgb(var(--primary));
}

.service-action-btn.stop {
    background: #dc2626;
}

.service-complete-chip {
    background: #dcfce7;
    color: #15803d;
}

.product-order-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    min-height: 66px;
    padding: 11px 12px;
}

.product-order-card h4 {
    color: #111827;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-order-card p {
    color: #6b7280;
    font-size: 12px;
    margin-top: 4px;
}

.product-order-card strong {
    color: #111827;
    flex: 0 0 auto;
    font-size: 14px;
    font-weight: 800;
}

@media (max-width: 1100px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .detail-page {
        padding: 12px;
    }

    .detail-stats {
        grid-template-columns: 1fr;
    }

    .detail-filter-label {
        width: 100%;
    }

    .service-card-top,
    .service-card-bottom {
        align-items: stretch;
        flex-direction: column;
    }

    .service-time-grid {
        grid-template-columns: 1fr;
    }

    .service-actions {
        justify-content: stretch;
        min-width: 0;
    }

    .service-action-btn,
    .service-complete-chip {
        justify-content: center;
        width: 100%;
    }
}
</style>
