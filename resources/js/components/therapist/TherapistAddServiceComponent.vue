<template>
    <section class="add-page">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <div>
                    <h3 class="db-card-title">{{ pageTitle }}</h3>
                    <p class="add-subtitle">{{ roomName }} · {{ guestName || 'Walk In' }}</p>
                </div>
                <div class="db-card-filter">
                    <button type="button" class="db-btn py-2 text-white bg-gray-600" aria-label="Back to session" title="Back to session" @click="backToSession">
                        <i class="lab lab-arrow-left"></i>
                        <span>{{ $t('button.back') }}</span>
                    </button>
                    <button type="button" class="db-btn py-2 text-white bg-primary" aria-label="Refresh items" title="Refresh items" @click="loadLookups">
                        <i class="lab lab-refresh-line"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div v-if="error" class="add-alert">{{ error }}</div>

                <div class="add-mode-switch" role="tablist" aria-label="Item type">
                    <button
                        type="button"
                        class="add-mode-btn"
                        :class="{ active: form.mode === 'service' }"
                        @click="setMode('service')"
                    >
                        {{ $t('label.service') }}
                    </button>
                    <button
                        type="button"
                        class="add-mode-btn"
                        :class="{ active: form.mode === 'product' }"
                        @click="setMode('product')"
                    >
                        {{ $t('label.product_order') }}
                    </button>
                </div>

                <div class="add-layout">
                    <div>
                        <h4 class="add-section-title">{{ itemListTitle }}</h4>
                        <div class="db-table-responsive">
                            <table class="db-table stripe">
                                <thead class="db-table-head">
                                    <tr class="db-table-head-tr">
                                        <th class="db-table-head-th">{{ $t('label.name') }}</th>
                                        <th class="db-table-head-th">{{ $t('label.code') }}</th>
                                        <th v-if="isServiceMode" class="db-table-head-th text-right">{{ $t('label.duration') }}</th>
                                        <th class="db-table-head-th text-right">{{ $t('label.price') }}</th>
                                        <th class="db-table-head-th text-right">{{ $t('label.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="db-table-body">
                                    <tr v-if="loading" class="db-table-body-tr">
                                        <td :colspan="tableColspan" class="db-table-body-td text-center py-8 text-gray-400">{{ $t('button.loading') }}</td>
                                    </tr>
                                    <tr v-else-if="addableItems.length === 0" class="db-table-body-tr">
                                        <td :colspan="tableColspan" class="db-table-body-td text-center py-8 text-gray-400">{{ emptyListText }}</td>
                                    </tr>
                                    <tr
                                        v-for="item in addableItems"
                                        v-else
                                        :key="item.id"
                                        class="db-table-body-tr"
                                        :class="{ 'add-selected-row': Number(form.item_id) === Number(item.id) }"
                                    >
                                        <td class="db-table-body-td font-medium">{{ item.name }}</td>
                                        <td class="db-table-body-td">{{ item.item_code || item.slug || ('#' + item.id) }}</td>
                                        <td v-if="isServiceMode" class="db-table-body-td text-right">{{ item.duration || 0 }} min</td>
                                        <td class="db-table-body-td text-right">{{ Number(item.price || 0).toFixed(2) }}</td>
                                        <td class="db-table-body-td text-right">
                                            <button
                                                type="button"
                                                class="db-btn-outline sm primary"
                                                @click="selectItem(item)"
                                            >
                                                <i class="lab lab-check-line"></i>
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <aside class="add-form-box">
                        <h4 class="add-section-title">{{ $t('label.session_setup') }}</h4>

                        <div class="add-display">
                            <p class="add-display-label">{{ selectedLabel }}</p>
                            <strong>{{ selectedItem?.name || noSelectionText }}</strong>
                            <span v-if="selectedItem">
                                {{ Number(selectedItem.price || 0).toFixed(2) }}
                                <template v-if="isServiceMode"> · {{ selectedItem.duration || 0 }} min</template>
                            </span>
                        </div>

                        <div class="add-display-row">
                            <span>Guest</span>
                            <strong>{{ guestName || 'N/A' }}</strong>
                        </div>
                        <div class="add-display-row">
                            <span>Room</span>
                            <strong>{{ roomName }}</strong>
                        </div>

                        <div v-if="isServiceMode" class="form-col-12">
                            <label class="db-field-title after:hidden">{{ $t('label.select_bed') }}</label>
                            <div class="p-3 rounded-lg border border-[#D9DBE9]">
                                <div v-if="bedsForRoom.length > 0" class="db-field-radio-group gap-1 active-group add-option-group">
                                    <label
                                        v-for="bed in bedsForRoom"
                                        :key="bed.id"
                                        :for="'bed_option_' + bed.id"
                                        class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]"
                                        :class="{ active: Number(form.bed_id) === Number(bed.id) }"
                                    >
                                        <div class="custom-radio sm">
                                            <input
                                                :id="'bed_option_' + bed.id"
                                                type="radio"
                                                name="bed_id"
                                                :value="bed.id"
                                                v-model="form.bed_id"
                                                class="custom-radio-field"
                                            />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <h3 class="db-field-label text-sm text-heading">{{ bed.name }}</h3>
                                    </label>
                                </div>
                                <p v-else class="text-xs text-gray-500">{{ $t('label.no_beds_found') }}</p>
                            </div>
                        </div>

                        <div v-if="isServiceMode" class="form-col-12">
                            <label class="db-field-title after:hidden">{{ $t('label.therapist') }}</label>
                            <div class="p-3 rounded-lg border border-[#D9DBE9]">
                                <div v-if="therapists.length > 0" class="db-field-radio-group gap-1 active-group add-option-group">
                                    <label
                                        v-for="therapist in therapists"
                                        :key="therapist.id"
                                        :for="'therapist_option_' + therapist.id"
                                        class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]"
                                        :class="{ active: Number(form.therapist_id) === Number(therapist.user_id) }"
                                    >
                                        <div class="custom-radio sm">
                                            <input
                                                :id="'therapist_option_' + therapist.id"
                                                type="radio"
                                                name="therapist_id"
                                                :value="therapist.user_id"
                                                v-model="form.therapist_id"
                                                class="custom-radio-field"
                                            />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <h3 class="db-field-label text-sm text-heading">
                                            {{ therapist.user?.name || therapist.name || ('#' + therapist.user_id) }}
                                        </h3>
                                    </label>
                                </div>
                                <p v-else class="text-xs text-gray-500">{{ $t('label.no_therapists_found') }}</p>
                            </div>
                        </div>

                        <div v-if="isServiceMode" class="form-col-12">
                            <label class="db-field-title after:hidden">{{ $t('label.start_time') }}</label>
                            <input v-model="form.start_time" type="datetime-local" class="db-field-control">
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title after:hidden">{{ $t('label.quantity') }}</label>
                            <div class="add-qty">
                                <button type="button" class="db-btn-fill gray sm" @click="decrementQty">-</button>
                                <span>{{ form.quantity }}</span>
                                <button type="button" class="db-btn-fill primary sm" @click="form.quantity++">+</button>
                            </div>
                        </div>

                        <button type="button" class="db-btn w-full py-2 text-white bg-primary" :disabled="saving || !form.item_id" @click="submitAddService">
                            <i class="lab lab-save"></i>
                            {{ submitButtonLabel }}
                        </button>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";

export default {
    name: "TherapistAddServiceComponent",
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
            saving: false,
            error: "",
            items: [],
            beds: [],
            therapists: [],
            form: {
                mode: "service",
                item_id: "",
                bed_id: "",
                therapist_id: "",
                start_time: "",
                quantity: 1,
                price: 0,
                duration: 0,
            },
        };
    },
    computed: {
        roomName() {
            return this.$route.query.roomName || `Room ${this.roomId}`;
        },
        guestName() {
            return this.$route.query.guestName || "";
        },
        isServiceMode() {
            return this.form.mode === "service";
        },
        pageTitle() {
            return this.isServiceMode ? this.$t('label.add_new_service') : this.$t('label.add_product');
        },
        itemListTitle() {
            return this.isServiceMode ? this.$t('label.service_list') : this.$t('label.product_order');
        },
        selectedLabel() {
            return this.isServiceMode ? this.$t('label.selected_service') : this.$t('label.product_order');
        },
        noSelectionText() {
            return this.isServiceMode ? "No service selected" : "No product selected";
        },
        emptyListText() {
            return this.isServiceMode ? this.$t('label.no_services_found') : this.$t('label.no_product_orders');
        },
        tableColspan() {
            return this.isServiceMode ? 5 : 4;
        },
        submitButtonLabel() {
            if (this.saving) {
                return this.$t('button.adding');
            }
            return this.isServiceMode ? this.$t('button.add_service') : this.$t('button.add_product');
        },
        addableItems() {
            return this.items.filter((item) => this.isServiceMode
                ? Number(item.item_kind || 1) === 2
                : Number(item.item_kind || 1) !== 2
            );
        },
        selectedItem() {
            return this.items.find((item) => Number(item.id) === Number(this.form.item_id)) || null;
        },
        bedsForRoom() {
            return this.beds.filter((bed) => Number(bed.room_id) === Number(this.roomId));
        },
    },
    mounted() {
        this.resetForm();
        this.loadLookups();
    },
    methods: {
        loadLookups() {
            this.loading = true;
            this.error = "";

            return Promise.all([
                axios.get("therapist/items", { params: { paginate: 0, order_column: "name", order_type: "asc" } }),
                axios.get("therapist/beds", { params: { paginate: 0 } }),
                axios.get("therapist/therapists", { params: { paginate: 0 } }),
            ]).then(([itemsRes, bedsRes, therapistsRes]) => {
                this.items = itemsRes.data.data || [];
                this.beds = bedsRes.data.data || [];
                this.therapists = therapistsRes.data.data || [];

                if (!this.form.bed_id && this.bedsForRoom[0]) {
                    this.form.bed_id = this.bedsForRoom[0].id;
                }
                if (!this.form.therapist_id) {
                    this.form.therapist_id = this.$store.getters.authInfo?.id || "";
                }
            }).catch((err) => {
                this.error = err.response?.data?.message || "Unable to load item options.";
            }).finally(() => {
                this.loading = false;
            });
        },
        resetForm() {
            this.form = {
                mode: this.$route.query.mode === "product" ? "product" : "service",
                item_id: "",
                bed_id: "",
                therapist_id: this.$store.getters.authInfo?.id || "",
                start_time: this.toDatetimeLocal(new Date()),
                quantity: 1,
                price: 0,
                duration: 0,
            };
        },
        setMode(mode) {
            if (this.form.mode === mode) return;
            this.form.mode = mode;
            this.form.item_id = "";
            this.form.price = 0;
            this.form.duration = 0;
            this.form.quantity = 1;
        },
        selectItem(item) {
            this.form.item_id = item.id;
            this.form.price = item.price || 0;
            this.form.duration = item.duration || 0;
        },
        decrementQty() {
            this.form.quantity = Math.max(1, this.form.quantity - 1);
        },
        submitAddService() {
            if (!this.form.item_id) return;

            this.saving = true;
            this.error = "";

            axios.post(`therapist/massage-session/${this.subSessionId}/add-item`, {
                item_id: this.form.item_id,
                room_id: this.roomId,
                bed_id: this.isServiceMode ? (this.form.bed_id || null) : null,
                therapist_id: this.isServiceMode ? (this.form.therapist_id || null) : null,
                start_time: this.isServiceMode ? (this.form.start_time || null) : null,
                quantity: this.form.quantity || 1,
                price: this.form.price || 0,
                duration: this.isServiceMode ? (this.form.duration || 0) : 0,
                discount: 0,
            }).then(() => {
                this.backToSession();
            }).catch((err) => {
                this.error = err.response?.data?.message || "Unable to add item.";
            }).finally(() => {
                this.saving = false;
            });
        },
        backToSession() {
            this.$router.push({
                name: "therapist.session.detail",
                params: {
                    roomId: this.roomId,
                    subSessionId: this.subSessionId,
                },
                query: {
                    roomName: this.roomName,
                    guestName: this.guestName,
                },
            });
        },
        toDatetimeLocal(date) {
            const pad = (value) => String(value).padStart(2, "0");
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },
    },
};
</script>

<style scoped>
.add-page {
    min-height: calc(100vh - 90px);
    padding: 16px;
}

.add-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.add-alert {
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #991b1b;
    padding: 10px 12px;
    margin-bottom: 12px;
    font-size: 13px;
}

.add-mode-switch {
    display: inline-flex;
    gap: 4px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    padding: 4px;
    margin-bottom: 14px;
}

.add-mode-btn {
    min-width: 116px;
    border-radius: 6px;
    padding: 7px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    transition: 0.2s ease;
}

.add-mode-btn.active {
    background: rgb(var(--primary));
    color: #fff;
}

.add-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
    gap: 16px;
}

.add-section-title {
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.add-selected-row {
    background: rgba(var(--primary), 0.05) !important;
}

.add-form-box {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px;
    background: #fafafa;
}

.add-display {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    padding: 10px 12px;
    margin-bottom: 10px;
}

.add-display-label {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
}

.add-display strong {
    display: block;
    margin-top: 4px;
    color: #1f2937;
}

.add-display span {
    margin-top: 2px;
    font-size: 12px;
    color: #6b7280;
}

.add-display-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
    margin-bottom: 8px;
    color: #4b5563;
}

.add-display-row strong {
    color: #1f2937;
}

.add-qty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.add-qty span {
    min-width: 30px;
    text-align: center;
    font-weight: 600;
    color: #1f2937;
}

.add-option-group {
    flex-wrap: wrap;
    align-items: stretch;
    padding-top: 0;
}

@media (max-width: 1000px) {
    .add-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .add-page {
        padding: 12px;
    }
}
</style>
