<template>
    <LoadingComponent :props="loading" />
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t('menu.reservation_details') }}</h3>
            <div class="db-card-filter">
                <router-link :to="{ name: 'admin.reservation' }" class="db-btn-outline">
                    <i class="lab lab-arrow-left"></i>
                    <span>{{ $t('button.back') }}</span>
                </router-link>
            </div>
        </div>
        <div class="db-card-body">
            <div class="row">
                <div class="col-12">
                    <!-- Header Section -->
                    <div class="mb-6 p-6 bg-gradient-to-r from-primary/10 to-primary/5 rounded-lg border border-primary/20">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div>
                                <h4 class="text-2xl font-bold text-gray-800 mb-2">{{ reservation.reservation_code }}</h4>
                                <p class="text-sm text-gray-600">
                                    <i class="lab lab-calendar mr-1"></i>
                                    {{ formatDate(reservation.reservation_date) }} at {{ reservation.reservation_time }}
                                </p>
                            </div>
                            <span class="db-badge text-sm px-4 py-2" :class="statusClass(reservation.status)">
                                {{ enums.reservationStatusEnumArray[reservation.status] }}
                            </span>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-6 p-5 bg-white rounded-lg shadow-sm border border-gray-200">
                        <h5 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
                            <i class="lab lab-user mr-2"></i>{{ $t('label.customer_information') }}
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start">
                                <i class="lab lab-user text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.customer_name') }}</label>
                                    <p class="text-gray-800 font-medium">{{ reservation.customer_name }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="lab lab-phone text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.phone') }}</label>
                                    <p class="text-gray-800">{{ reservation.customer_phone }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.customer_email">
                                <i class="lab lab-mail text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.email') }}</label>
                                    <p class="text-gray-800">{{ reservation.customer_email }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="lab lab-users text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.number_of_guests') }}</label>
                                    <p class="text-gray-800 font-medium">{{ reservation.number_of_people }} {{ $t('label.guests') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="mb-6 p-5 bg-white rounded-lg shadow-sm border border-gray-200">
                        <h5 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
                            <i class="lab lab-calendar-check mr-2"></i>{{ $t('label.reservation_details') }}
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="flex items-start">
                                <i class="lab lab-calendar text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.reservation_date') }}</label>
                                    <p class="text-gray-800 font-medium">{{ formatDate(reservation.reservation_date) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="lab lab-clock text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.reservation_time') }}</label>
                                    <p class="text-gray-800 font-medium">{{ reservation.reservation_time }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.duration_minutes">
                                <i class="lab lab-hourglass text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.duration') }}</label>
                                    <p class="text-gray-800">{{ reservation.duration_minutes }} {{ $t('label.minutes') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.table">
                                <i class="lab lab-grid text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.table') }}</label>
                                    <p class="text-gray-800 font-medium">{{ reservation.table.name }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.branch">
                                <i class="lab lab-location text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.branch') }}</label>
                                    <p class="text-gray-800">{{ reservation.branch.name }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.creator">
                                <i class="lab lab-user-check text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.created_by') }}</label>
                                    <p class="text-gray-800">{{ reservation.creator.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mb-6 p-5 bg-white rounded-lg shadow-sm border border-gray-200">
                        <h5 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
                            <i class="lab lab-dollar mr-2"></i>{{ $t('label.payment_information') }}
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start">
                                <i class="lab lab-credit-card text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.deposit_amount') }}</label>
                                    <p class="text-gray-800 font-semibold text-lg">{{ formatCurrency(reservation.deposit_amount) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="lab lab-receipt text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.payment_status') }}</label>
                                    <span class="db-badge text-sm px-3 py-1 mt-1 inline-block" :class="paymentStatusClass(reservation.payment_status)">
                                        {{ enums.paymentStatusEnumArray[reservation.payment_status] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in/Check-out Times -->
                    <div class="mb-6 p-5 bg-white rounded-lg shadow-sm border border-gray-200" v-if="reservation.check_in_time || reservation.check_out_time">
                        <h5 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
                            <i class="lab lab-activity mr-2"></i>{{ $t('label.activity_log') }}
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start" v-if="reservation.check_in_time">
                                <i class="lab lab-login text-success mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.check_in_time') }}</label>
                                    <p class="text-gray-800">{{ formatDateTime(reservation.check_in_time) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start" v-if="reservation.check_out_time">
                                <i class="lab lab-logout text-primary mt-1 mr-2"></i>
                                <div>
                                    <label class="db-field-title text-xs">{{ $t('label.check_out_time') }}</label>
                                    <p class="text-gray-800">{{ formatDateTime(reservation.check_out_time) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Request & Cancel Reason -->
                    <div class="mb-6 p-5 bg-white rounded-lg shadow-sm border border-gray-200" v-if="reservation.special_request || reservation.cancel_reason">
                        <h5 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">
                            <i class="lab lab-note mr-2"></i>{{ $t('label.additional_information') }}
                        </h5>
                        
                        <div class="mb-4" v-if="reservation.special_request">
                            <label class="db-field-title text-xs flex items-center">
                                <i class="lab lab-message-square text-primary mr-2"></i>{{ $t('label.special_request') }}
                            </label>
                            <p class="text-gray-800 mt-2 p-3 bg-gray-50 rounded">{{ reservation.special_request }}</p>
                        </div>

                        <div v-if="reservation.cancel_reason">
                            <label class="db-field-title text-xs flex items-center">
                                <i class="lab lab-alert-circle text-danger mr-2"></i>{{ $t('label.cancel_reason') }}
                            </label>
                            <p class="text-gray-800 mt-2 p-3 bg-red-50 rounded border-l-4 border-danger">{{ reservation.cancel_reason }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 p-5 bg-gray-50 rounded-lg flex gap-3 flex-wrap">
                        <button 
                            v-if="reservation.status === enums.reservationStatusEnum.CONFIRMED || reservation.status === enums.reservationStatusEnum.PENDING"
                            @click="checkIn"
                            class="db-btn py-3 px-6 text-white bg-green-600 hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 rounded-lg">
                            <i class="lab  lab-tick-circle-2 text-lg"></i>
                            <span>{{ $t('button.check_in') }}</span>
                        </button>
                        
                        <button 
                            v-if="reservation.status === enums.reservationStatusEnum.CHECKED_IN"
                            @click="checkOut"
                            class="db-btn py-3 px-6 text-white bg-orange-600 hover:bg-orange-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 rounded-lg">
                            <i class="lab lab-logout text-lg"></i>
                            <span>{{ $t('button.check_out') }}</span>
                        </button>
                        
                        <button 
                            v-if="reservation.status !== enums.reservationStatusEnum.COMPLETED && reservation.status !== enums.reservationStatusEnum.CANCELLED"
                            @click="cancelReservation"
                            class="db-btn py-3 px-6 text-white bg-gray-600 hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 rounded-lg">
                            <i class="lab lab-close text-lg"></i>
                            <span>{{ $t('button.cancel') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import reservationStatusEnum from "../../../enums/modules/reservationStatusEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "ReservationShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                reservationStatusEnum: reservationStatusEnum,
                reservationStatusEnumArray: {
                    [reservationStatusEnum.PENDING]: this.$t("label.pending"),
                    [reservationStatusEnum.CONFIRMED]: this.$t("label.confirmed"),
                    [reservationStatusEnum.CHECKED_IN]: this.$t("label.checked_in"),
                    [reservationStatusEnum.CANCELLED]: this.$t("label.cancelled"),
                    [reservationStatusEnum.NO_SHOW]: this.$t("label.no_show"),
                    [reservationStatusEnum.COMPLETED]: this.$t("label.completed")
                },
                paymentStatusEnum: paymentStatusEnum,
                paymentStatusEnumArray: {
                    [paymentStatusEnum.UNPAID]: this.$t("label.unpaid"),
                    [paymentStatusEnum.PARTIAL]: this.$t("label.partial"),
                    [paymentStatusEnum.PAID]: this.$t("label.paid")
                }
            }
        }
    },
    computed: {
        reservation: function () {
            return this.$store.getters['reservation/show'];
        }
    },
    mounted() {
        this.show();
    },
    methods: {
        show: function () {
            this.loading.isActive = true;
            this.$store.dispatch('reservation/show', this.$route.params.id).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        statusClass: function (status) {
            const statusClasses = {
                [reservationStatusEnum.PENDING]: 'db-badge-warning',
                [reservationStatusEnum.CONFIRMED]: 'db-badge-info',
                [reservationStatusEnum.CHECKED_IN]: 'db-badge-success',
                [reservationStatusEnum.CANCELLED]: 'db-badge-danger',
                [reservationStatusEnum.NO_SHOW]: 'db-badge-danger',
                [reservationStatusEnum.COMPLETED]: 'db-badge-success'
            };
            return statusClasses[status] || 'db-badge';
        },
        paymentStatusClass: function (status) {
            const statusClasses = {
                [paymentStatusEnum.UNPAID]: 'db-badge-danger',
                [paymentStatusEnum.PARTIAL]: 'db-badge-warning',
                [paymentStatusEnum.PAID]: 'db-badge-success'
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
        formatCurrency: function (amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
        checkIn: function () {
            this.loading.isActive = true;
            this.$store.dispatch('reservation/checkIn', { id: this.$route.params.id, search: {} }).then((res) => {
                this.loading.isActive = false;
                this.show();
                alertService.success(this.$t('message.checked_in_successfully'));
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        checkOut: function () {
            this.loading.isActive = true;
            this.$store.dispatch('reservation/checkOut', { id: this.$route.params.id, search: {} }).then((res) => {
                this.loading.isActive = false;
                this.show();
                alertService.success(this.$t('message.checked_out_successfully'));
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        cancelReservation: function () {
            appService.swal().fire({
                title: this.$t('message.are_you_sure'),
                text: this.$t('message.cancel_reservation_warning'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: this.$t('button.yes_cancel'),
                cancelButtonText: this.$t('button.no')
            }).then((result) => {
                if (result.isConfirmed) {
                    this.loading.isActive = true;
                    const form = new FormData();
                    this.$store.dispatch('reservation/cancel', { id: this.$route.params.id, form: form, search: {} }).then((res) => {
                        this.loading.isActive = false;
                        this.show();
                        alertService.success(this.$t('message.cancelled_successfully'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                }
            });
        }
    }
}
</script>
