<template>
    <LoadingComponent :props="loading" />
    <section class="col-12 pt-4 pb-4">
        <router-link to="" @click="$router.go(-1)" class="mb-3 inline-flex items-center gap-2 text-primary">
            <i class="lab lab-undo lab-font-size-16"></i>
            <span class="text-xs font-medium leading-6">{{ $t('button.back') }}</span>
        </router-link>
        
        <div class="flex items-start flex-col lg:flex-row gap-6">
            <!-- Left Column: Image -->
            <div class="w-full lg:w-1/3" v-if="lostAndFound && lostAndFound.preview">
                <div class="p-4 rounded-2xl shadow-xs bg-white">
                    <div class="flex justify-center">
                        <img :src="lostAndFound.preview" :alt="lostAndFound.item_name" class="w-full h-auto rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="w-full" :class="lostAndFound && lostAndFound.preview ? 'lg:w-2/3' : 'lg:w-full'">
                <div class="p-4 mb-6 rounded-2xl shadow-xs bg-white" v-if="lostAndFound">
                    <!-- Header with Item Name and Status -->
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <h3 class="text-xl font-bold mb-2">{{ lostAndFound.item_name }}</h3>
                        <span :class="statusClass(lostAndFound.status)" class="inline-block">
                            {{ enums.lostAndFoundStatusEnumArray[lostAndFound.status] }}
                        </span>
                    </div>

                    <!-- Found Information -->
                    <div class="mb-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.found_information') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.found_date') }}</p>
                                <p class="text-sm text-gray-800">{{ formatDate(lostAndFound.found_date) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.found_location') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.found_location }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.found_by') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.found_by || '-' }}</p>
                            </div>
                            <div v-if="lostAndFound.storage_location">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.storage_location') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.storage_location }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-6" v-if="lostAndFound.customer_name || lostAndFound.customer_phone || lostAndFound.customer_email">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.customer_information') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-if="lostAndFound.customer_name">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.customer_name') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.customer_name }}</p>
                            </div>
                            <div v-if="lostAndFound.customer_phone">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.customer_phone') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.customer_phone }}</p>
                            </div>
                            <div v-if="lostAndFound.customer_email" class="sm:col-span-2">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.customer_email') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.customer_email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Claim Information -->
                    <div class="mb-6" v-if="lostAndFound.status === enums.lostAndFoundStatusEnum.CLAIMED">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.claim_information') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-if="lostAndFound.claimed_by">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.claimed_by') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.claimed_by }}</p>
                            </div>
                            <div v-if="lostAndFound.claimed_date">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.claimed_date') }}</p>
                                <p class="text-sm text-gray-800">{{ formatDateTime(lostAndFound.claimed_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Disposal Information -->
                    <div class="mb-6" v-if="lostAndFound.status === enums.lostAndFoundStatusEnum.DISPOSED">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.disposal_information') }}</h4>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">{{ $t('label.disposal_date') }}</p>
                            <p class="text-sm text-gray-800">{{ formatDate(lostAndFound.disposal_date) }}</p>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    <div class="mb-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.branch_information') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.branch') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.branch ? lostAndFound.branch.name : '-' }}</p>
                            </div>
                            <div v-if="lostAndFound.created_by_user">
                                <p class="text-xs text-gray-600 mb-1">{{ $t('label.created_by') }}</p>
                                <p class="text-sm text-gray-800">{{ lostAndFound.created_by_user.name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="lostAndFound.notes">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">{{ $t('label.notes') }}</h4>
                        <p class="text-sm text-gray-800 whitespace-pre-line bg-gray-50 p-3 rounded">{{ lostAndFound.notes }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 pt-6 border-t border-gray-200" v-if="permissionChecker('lost_and_found_edit') && lostAndFound.status === enums.lostAndFoundStatusEnum.FOUND">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">{{ $t('label.actions') }}</h4>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Mark as Claimed Form -->
                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <h5 class="text-sm font-semibold text-green-800 mb-3">{{ $t('button.mark_as_claimed') }}</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs text-gray-700 font-medium mb-1 block">{{ $t('label.claimed_by') }} <span class="text-red-500">*</span></label>
                                        <input 
                                            type="text" 
                                            v-model="claimForm.claimed_by"
                                            :placeholder="$t('label.claimed_by')"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-700 font-medium mb-1 block">{{ $t('label.claimed_date') }}</label>
                                        <input 
                                            type="datetime-local" 
                                            v-model="claimForm.claimed_date"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-700 font-medium mb-1 block">{{ $t('label.notes') }}</label>
                                        <textarea 
                                            v-model="claimForm.notes"
                                            :placeholder="$t('label.notes')"
                                            rows="3"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                                    </div>
                                    <button 
                                        @click="markAsClaimed"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        <i class="lab lab-tick-circle-2 mr-2"></i>{{ $t('button.mark_as_claimed') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Mark as Disposed Form -->
                            <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                                <h5 class="text-sm font-semibold text-orange-800 mb-3">{{ $t('button.mark_as_disposed') }}</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs text-gray-700 font-medium mb-1 block">{{ $t('label.disposal_date') }} <span class="text-red-500">*</span></label>
                                        <input 
                                            type="date" 
                                            v-model="disposeForm.disposal_date"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-700 font-medium mb-1 block">{{ $t('label.notes') }}</label>
                                        <textarea 
                                            v-model="disposeForm.notes"
                                            :placeholder="$t('label.notes')"
                                            rows="3"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                                    </div>
                                    <button 
                                        @click="markAsDisposed"
                                        class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        <i class="lab lab-trash mr-2"></i>{{ $t('button.mark_as_disposed') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import lostAndFoundStatusEnum from "../../../enums/modules/lostAndFoundStatusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "LostAndFoundShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                lostAndFoundStatusEnum: lostAndFoundStatusEnum,
                lostAndFoundStatusEnumArray: {
                    [lostAndFoundStatusEnum.FOUND]: this.$t("label.found"),
                    [lostAndFoundStatusEnum.CLAIMED]: this.$t("label.claimed"),
                    [lostAndFoundStatusEnum.DISPOSED]: this.$t("label.disposed")
                }
            },
            claimForm: {
                claimed_by: '',
                claimed_date: '',
                notes: ''
            },
            disposeForm: {
                disposal_date: '',
                notes: ''
            }
        }
    },
    computed: {
        lostAndFound: function () {
            return this.$store.getters['lostAndFound/show'];
        }
    },
    mounted() {
        this.loading.isActive = true;
        // Set default dates
        this.claimForm.claimed_date = this.getDefaultDateTime();
        this.disposeForm.disposal_date = this.getDefaultDate();
        
        this.$store.dispatch('lostAndFound/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
            // Pre-populate notes from existing item
            if (this.lostAndFound && this.lostAndFound.notes) {
                this.claimForm.notes = this.lostAndFound.notes;
                this.disposeForm.notes = this.lostAndFound.notes;
            }
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        statusClass: function (status) {
            const statusClasses = {
                [lostAndFoundStatusEnum.FOUND]: 'db-badge blue',
                [lostAndFoundStatusEnum.CLAIMED]: 'db-badge green',
                [lostAndFoundStatusEnum.DISPOSED]: 'db-badge yellow'
            };
            return statusClasses[status] || 'db-badge';
        },
        formatDate: function (date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
        formatDateTime: function (datetime) {
            if (!datetime) return '';
            return new Date(datetime).toLocaleString();
        },
        getDefaultDateTime: function () {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },
        getDefaultDate: function () {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        markAsClaimed: function () {
            if (!this.claimForm.claimed_by) {
                alertService.error(this.$t('message.please_enter_claimed_by'));
                return;
            }
            
            appService.confirmDialog(
                this.$t('message.mark_as_claimed_title'),
                this.$t('message.mark_as_claimed_text'),
                'question',
                this.$t('label.yes'),
                this.$t('label.no')
            ).then((result) => {
                console.log("result", result);
                if (result) {
                    alert("Claimed by: " + this.claimForm.claimed_by + "\nClaimed date: " + this.claimForm.claimed_date);
                    this.loading.isActive = true;
                    const form = new FormData();
                    form.append('claimed_by', this.claimForm.claimed_by);
                    form.append('claimed_date', this.claimForm.claimed_date);
                    if (this.claimForm.notes) {
                        form.append('notes', this.claimForm.notes);
                    }
                    
                    this.$store.dispatch('lostAndFound/markAsClaimed', { 
                        id: this.$route.params.id, 
                        form: form 
                    }).then((res) => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.marked_as_claimed_successfully'));
                        // Reload the data
                        this.$store.dispatch('lostAndFound/show', this.$route.params.id);
                    }).catch((err) => {
                        this.loading.isActive = false;
                        console.error('Mark as claimed error:', err);
                        const errorMessage = err.response?.data?.message || err.message || 'An error occurred';
                        alertService.error(errorMessage);
                    });
                }
            });
        },
        markAsDisposed: function () {
            if (!this.disposeForm.disposal_date) {
                alertService.error(this.$t('message.please_enter_disposal_date'));
                return;
            }
            
            appService.confirmDialog(
                this.$t('message.mark_as_disposed_title'),
                this.$t('message.mark_as_disposed_text'),
                'warning',
                this.$t('label.yes'),
                this.$t('label.no')
            ).then((result) => {
                if (result) {
                    this.loading.isActive = true;
                    const form = new FormData();
                    form.append('disposal_date', this.disposeForm.disposal_date);
                    if (this.disposeForm.notes) {
                        form.append('notes', this.disposeForm.notes);
                    }
                    
                    this.$store.dispatch('lostAndFound/markAsDisposed', { 
                        id: this.$route.params.id, 
                        form: form 
                    }).then((res) => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.marked_as_disposed_successfully'));
                        // Reload the data
                        this.$store.dispatch('lostAndFound/show', this.$route.params.id);
                    }).catch((err) => {
                        this.loading.isActive = false;
                        console.error('Mark as disposed error:', err);
                        const errorMessage = err.response?.data?.message || err.message || 'An error occurred';
                        alertService.error(errorMessage);
                    });
                }
            });
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        }
    }
}
</script>
