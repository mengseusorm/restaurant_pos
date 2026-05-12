<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.customer_beverage_storage') }}</h3>
                <div class="db-card-filter">
                    <router-link :to="{ name: 'admin.customerBeverageStorage.list' }" class="db-btn h-[38px] text-white bg-primary">
                        <i class="lab lab-arrow-left"></i>
                        <span>{{ $t('button.back') }}</span>
                    </router-link>
                </div>
            </div>
            <div class="db-card-body">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.storage_code') }}</label>
                            <p class="text-gray-700">{{ item.storage_code || '-' }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.customer_name') }}</label>
                            <p class="text-gray-700">{{ item.customer_name || '-' }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.customer_phone') }}</label>
                            <p class="text-gray-700">{{ item.customer_phone || '-' }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.beverage_name') }}</label>
                            <p class="text-gray-700">{{ item.beverage_name || '-' }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.quantity') }}</label>
                            <p class="text-gray-700">{{ item.quantity }} {{ item.unit }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.original_quantity') }}</label>
                            <p class="text-gray-700">{{ item.original_quantity }} {{ item.unit }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.store_date') }}</label>
                            <p class="text-gray-700">{{ formatDate(item.store_date) }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.expiry_date') }}</label>
                            <p :class="expiryClass(item.expiry_date, item.status)">
                                {{ formatDate(item.expiry_date) }}
                            </p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.status') }}</label>
                            <p>
                                <span :class="statusClass(item.status)">
                                    {{ enums.customerBeverageStorageStatusEnumArray[item.status] }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.storage_location') }}</label>
                            <p class="text-gray-700">{{ item.storage_location || '-' }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4" v-if="item.claimed_date">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.claimed_date') }}</label>
                            <p class="text-gray-700">{{ formatDateTime(item.claimed_date) }}</p>
                        </div>
                    </div>

                    <div class="col-12 sm:col-6 md:col-4" v-if="item.disposed_date">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.disposed_date') }}</label>
                            <p class="text-gray-700">{{ formatDateTime(item.disposed_date) }}</p>
                        </div>
                    </div>

                    <div class="col-12" v-if="item.disposed_reason">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.disposed_reason') }}</label>
                            <p class="text-gray-700">{{ item.disposed_reason }}</p>
                        </div>
                    </div>

                    <div class="col-12" v-if="item.notes">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.notes') }}</label>
                            <p class="text-gray-700">{{ item.notes }}</p>
                        </div>
                    </div>

                    <div class="col-12" v-if="item.cover">
                        <div class="mb-4">
                            <label class="db-field-title font-semibold">{{ $t('label.image') }}</label>
                            <img :src="item.cover" alt="Beverage" class="w-64 h-64 object-cover rounded-lg mt-2">
                        </div>
                    </div>

                    <!-- Mark as Claimed Form -->
                    <div class="col-12" v-if="item.status === enums.customerBeverageStorageStatusEnum.STORED && permissionChecker('customer_beverage_storage_edit')">
                        <div class="border-t pt-6 mt-6">
                            <h4 class="font-semibold text-lg mb-4">{{ $t('button.mark_as_claimed') }}</h4>
                            <form @submit.prevent="markAsClaimed">
                                <div class="row">
                                    <div class="form-col-12 sm:form-col-6">
                                        <label for="claimed_date" class="db-field-title required">
                                            {{ $t('label.claimed_date') }}
                                        </label>
                                        <input v-model="claimForm.claimed_date" 
                                            v-bind:class="claimErrors.claimed_date ? 'invalid' : ''"
                                            type="datetime-local" id="claimed_date" class="db-field-control" />
                                        <small class="db-field-alert" v-if="claimErrors.claimed_date">
                                            {{ claimErrors.claimed_date[0] }}
                                        </small>
                                    </div>

                                    <div class="form-col-12">
                                        <label for="claim_notes" class="db-field-title">
                                            {{ $t('label.notes') }}
                                        </label>
                                        <textarea v-model="claimForm.notes" 
                                            v-bind:class="claimErrors.notes ? 'invalid' : ''"
                                            id="claim_notes" class="db-field-control" rows="3"></textarea>
                                        <small class="db-field-alert" v-if="claimErrors.notes">
                                            {{ claimErrors.notes[0] }}
                                        </small>
                                    </div>

                                    <div class="form-col-12">
                                        <button type="submit" class="db-btn py-2 text-white bg-success">
                                            <i class="lab lab-check-circle"></i>
                                            <span>{{ $t('button.mark_as_claimed') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Mark as Disposed Form -->
                    <div class="col-12" v-if="item.status === enums.customerBeverageStorageStatusEnum.STORED && permissionChecker('customer_beverage_storage_edit')">
                        <div class="border-t pt-6 mt-6">
                            <h4 class="font-semibold text-lg mb-4">{{ $t('button.mark_as_disposed') }}</h4>
                            <form @submit.prevent="markAsDisposed">
                                <div class="row">
                                    <div class="form-col-12 sm:form-col-6">
                                        <label for="disposed_date" class="db-field-title required">
                                            {{ $t('label.disposed_date') }}
                                        </label>
                                        <input v-model="disposeForm.disposed_date" 
                                            v-bind:class="disposeErrors.disposed_date ? 'invalid' : ''"
                                            type="datetime-local" id="disposed_date" class="db-field-control" />
                                        <small class="db-field-alert" v-if="disposeErrors.disposed_date">
                                            {{ disposeErrors.disposed_date[0] }}
                                        </small>
                                    </div>

                                    <div class="form-col-12">
                                        <label for="disposed_reason" class="db-field-title required">
                                            {{ $t('label.disposed_reason') }}
                                        </label>
                                        <textarea v-model="disposeForm.disposed_reason" 
                                            v-bind:class="disposeErrors.disposed_reason ? 'invalid' : ''"
                                            id="disposed_reason" class="db-field-control" rows="3" 
                                            placeholder="e.g., Expired, Damaged, Customer no-show"></textarea>
                                        <small class="db-field-alert" v-if="disposeErrors.disposed_reason">
                                            {{ disposeErrors.disposed_reason[0] }}
                                        </small>
                                    </div>

                                    <div class="form-col-12">
                                        <label for="dispose_notes" class="db-field-title">
                                            {{ $t('label.notes') }}
                                        </label>
                                        <textarea v-model="disposeForm.notes" 
                                            v-bind:class="disposeErrors.notes ? 'invalid' : ''"
                                            id="dispose_notes" class="db-field-control" rows="3"></textarea>
                                        <small class="db-field-alert" v-if="disposeErrors.notes">
                                            {{ disposeErrors.notes[0] }}
                                        </small>
                                    </div>

                                    <div class="form-col-12">
                                        <button type="submit" class="db-btn py-2 text-white bg-danger">
                                            <i class="lab lab-close-circle"></i>
                                            <span>{{ $t('button.mark_as_disposed') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import customerBeverageStorageStatusEnum from "../../../enums/modules/customerBeverageStorageStatusEnum";

export default {
    name: "CustomerBeverageStorageShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                customerBeverageStorageStatusEnum: customerBeverageStorageStatusEnum,
                customerBeverageStorageStatusEnumArray: {
                    [customerBeverageStorageStatusEnum.STORED]: this.$t("label.stored"),
                    [customerBeverageStorageStatusEnum.CLAIMED]: this.$t("label.claimed"),
                    [customerBeverageStorageStatusEnum.EXPIRED]: this.$t("label.expired"),
                    [customerBeverageStorageStatusEnum.DISPOSED]: this.$t("label.disposed")
                }
            },
            claimForm: {
                claimed_date: '',
                notes: ''
            },
            claimErrors: {},
            disposeForm: {
                disposed_date: '',
                disposed_reason: '',
                notes: ''
            },
            disposeErrors: {}
        }
    },
    computed: {
        item: function () {
            return this.$store.getters['customerBeverageStorage/show'];
        }
    },
    mounted() {
        this.claimForm.claimed_date = this.getCurrentDateTime();
        this.disposeForm.disposed_date = this.getCurrentDateTime();
        this.loading.isActive = true;
        this.$store.dispatch('customerBeverageStorage/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
            alertService.error(err.response.data.message);
        });
    },
    methods: {
        getCurrentDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },
        statusClass: function (status) {
            const statusClasses = {
                [customerBeverageStorageStatusEnum.STORED]: 'db-badge-info',
                [customerBeverageStorageStatusEnum.CLAIMED]: 'db-badge-success',
                [customerBeverageStorageStatusEnum.EXPIRED]: 'db-badge-warning',
                [customerBeverageStorageStatusEnum.DISPOSED]: 'db-badge-danger'
            };
            return statusClasses[status] || 'db-badge';
        },
        expiryClass: function (expiryDate, status) {
            if (status !== customerBeverageStorageStatusEnum.STORED) {
                return 'text-gray-700';
            }
            const today = new Date();
            const expiry = new Date(expiryDate);
            const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
            
            if (daysUntilExpiry < 0) {
                return 'text-red-600 font-semibold';
            } else if (daysUntilExpiry <= 3) {
                return 'text-orange-600 font-semibold';
            }
            return 'text-gray-700';
        },
        formatDate: function (date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString();
        },
        formatDateTime: function (datetime) {
            if (!datetime) return '-';
            return new Date(datetime).toLocaleString();
        },
        markAsClaimed: function () {
            try {
                const fd = new FormData();
                fd.append('claimed_date', this.claimForm.claimed_date);
                if (this.claimForm.notes) {
                    fd.append('notes', this.claimForm.notes);
                }

                this.loading.isActive = true;
                this.$store.dispatch('customerBeverageStorage/markAsClaimed', {
                    id: this.$route.params.id,
                    form: fd
                }).then((res) => {
                    this.loading.isActive = false;
                    this.claimErrors = {};
                    alertService.success(this.$t('message.item_marked_as_claimed'));
                    // Refresh the item data
                    this.$store.dispatch('customerBeverageStorage/show', this.$route.params.id);
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.claimErrors = err.response.data.errors || {};
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        markAsDisposed: function () {
            try {
                const fd = new FormData();
                fd.append('disposed_date', this.disposeForm.disposed_date);
                fd.append('disposed_reason', this.disposeForm.disposed_reason);
                if (this.disposeForm.notes) {
                    fd.append('notes', this.disposeForm.notes);
                }

                this.loading.isActive = true;
                this.$store.dispatch('customerBeverageStorage/markAsDisposed', {
                    id: this.$route.params.id,
                    form: fd
                }).then((res) => {
                    this.loading.isActive = false;
                    this.disposeErrors = {};
                    alertService.success(this.$t('message.item_marked_as_disposed'));
                    // Refresh the item data
                    this.$store.dispatch('customerBeverageStorage/show', this.$route.params.id);
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.disposeErrors = err.response.data.errors || {};
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        }
    }
}
</script>
