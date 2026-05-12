<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.reservations') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
                <ReservationCreateComponent :props="props" v-if="permissionChecker('reservations_create')" />
            </div>
        </div>

        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6">
                        <label for="searchStartDate" class="db-field-title after:hidden">
                            {{ $t('label.start_date') }}
                        </label>
                        <Datepicker
                            autoApply
                            v-model="first_date"
                            :enableTimePicker="false"
                            :monthChangeOnScroll="false"
                            :format="'yyyy-MM-dd'"
                            placeholder="Select date"
                        />
                    </div>
                    <div class="col-12 sm:col-6">
                        <label for="searchEndDate" class="db-field-title after:hidden">
                            {{ $t('label.end_date') }}
                        </label>
                        <Datepicker
                            autoApply
                            v-model="last_date"
                            :enableTimePicker="false"
                            :monthChangeOnScroll="false"
                            :format="'yyyy-MM-dd'"
                            placeholder="Select date"
                        />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="reservation_code" class="db-field-title after:hidden">{{ $t('label.reservation_code') }}</label>
                        <input id="reservation_code" v-model="props.search.reservation_code" type="text"
                            class="db-field-control">
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="customer_name" class="db-field-title after:hidden">{{ $t('label.customer_name') }}</label>
                        <input id="customer_name" v-model="props.search.customer_name" type="text"
                            class="db-field-control">
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="customer_phone" class="db-field-title after:hidden">{{ $t('label.phone') }}</label>
                        <input id="customer_phone" v-model="props.search.customer_phone" type="text"
                            class="db-field-control">
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchStatus" class="db-field-title after:hidden">
                            {{ $t('label.status') }}
                        </label>
                        <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                            v-model="props.search.status"
                            :options="[
                                { id: enums.reservationStatusEnum.PENDING, name: $t('label.pending') },
                                { id: enums.reservationStatusEnum.CONFIRMED, name: $t('label.confirmed') },
                                { id: enums.reservationStatusEnum.CHECKED_IN, name: $t('label.checked_in') },
                                { id: enums.reservationStatusEnum.CANCELLED, name: $t('label.cancelled') },
                                { id: enums.reservationStatusEnum.NO_SHOW, name: $t('label.no_show') },
                                { id: enums.reservationStatusEnum.COMPLETED, name: $t('label.completed') }
                            ]"
                            label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                            :clearOnClose="true" placeholder="--" search-placeholder="--" />
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

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">{{ $t('label.reservation_code') }}</th>
                        <th class="db-table-head-th">{{ $t('label.customer_name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.phone') }}</th>
                        <th class="db-table-head-th">{{ $t('label.date') }}</th>
                        <th class="db-table-head-th">{{ $t('label.time') }}</th>
                        <th class="db-table-head-th">{{ $t('label.guests') }}</th>
                        <th class="db-table-head-th">{{ $t('label.table') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="reservations.length > 0">
                    <tr class="db-table-body-tr" v-for="reservation in reservations" :key="reservation.id">
                        <td class="db-table-body-td">{{ reservation.reservation_code }}</td>
                        <td class="db-table-body-td">{{ reservation.customer_name }}</td>
                        <td class="db-table-body-td">{{ reservation.customer_phone }}</td>
                        <td class="db-table-body-td">{{ formatDate(reservation.reservation_date) }}</td>
                        <td class="db-table-body-td">{{ reservation.reservation_time }}</td>
                        <td class="db-table-body-td">{{ reservation.number_of_people }}</td>
                        <td class="db-table-body-td">{{ reservation.table ? reservation.table.name : '-' }}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(reservation.status)">
                                {{ enums.reservationStatusEnumArray[reservation.status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmViewComponent :link="'admin.reservation.show'" :id="reservation.id" v-if="permissionChecker('reservations_show')" />
                                <SmModalEditComponent @click="edit(reservation)" v-if="permissionChecker('reservations_edit') && reservation.status !== enums.reservationStatusEnum.COMPLETED && reservation.status !== enums.reservationStatusEnum.CANCELLED" />

                                <!-- Check In Button -->
                                <button
                                    v-if="permissionChecker('reservations_edit') && (reservation.status === enums.reservationStatusEnum.CONFIRMED || reservation.status === enums.reservationStatusEnum.PENDING)"
                                    @click="checkIn(reservation.id)"
                                    class="db-btn-outline db-btn-sm text-xs"
                                    title="Check In">
                                    <i class="lab lab-tick-circle-2"></i>
                                    {{ $t('button.check_in') }}
                                </button>

                                <!-- Check Out Button -->
                                <button
                                    v-if="permissionChecker('reservations_edit') && reservation.status === enums.reservationStatusEnum.CHECKED_IN"
                                    @click="checkOut(reservation.id)"
                                    class="db-btn-outline db-btn-sm text-xs"
                                    title="Check Out">
                                    <i class="lab lab-logout"></i>
                                    {{ $t('button.check_out') }}
                                </button>

                                <!-- Cancel Button -->
                                <button
                                    v-if="permissionChecker('reservations_edit') && reservation.status !== enums.reservationStatusEnum.COMPLETED && reservation.status !== enums.reservationStatusEnum.CANCELLED"
                                    @click="cancelReservation(reservation.id)"
                                    class="db-btn-outline db-btn-sm text-xs text-red-500"
                                    title="Cancel">
                                    <i class="lab lab-close"></i>
                                    {{ $t('button.cancel') }}
                                </button>

                                <SmDeleteComponent @click="destroy(reservation.id)" v-if="permissionChecker('reservations_delete')" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
            <PaginationSMBox :pagination="pagination" :method="list" />
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <PaginationTextComponent :props="{ page: paginationPage }" />
                <PaginationBox :pagination="pagination" :method="list" />
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import ReservationCreateComponent from "./ReservationCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import reservationStatusEnum from "../../../enums/modules/reservationStatusEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import SmViewComponent from "../components/buttons/SmViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "ReservationListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ReservationCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        SmViewComponent,
        FilterComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
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
            },
            props: {
                form: {
                    customer_name: "",
                    customer_phone: "",
                    customer_email: "",
                    reservation_date: "",
                    reservation_time: "",
                    number_of_people: 1,
                    table_id: null,
                    status: reservationStatusEnum.PENDING,
                    special_request: "",
                    branch_id: null,
                    deposit_amount: 0,
                    payment_status: paymentStatusEnum.UNPAID,
                    duration_minutes: 120
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    reservation_code: "",
                    customer_name: "",
                    customer_phone: "",
                    from_date: null,
                    to_date: null,
                    status: null
                }
            },
        }
    },
    created() {
        // Initialize dates to current day
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        this.first_date = today;
        this.last_date = today;
        this.props.search.from_date = `${yyyy}-${mm}-${dd}`;
        this.props.search.to_date = `${yyyy}-${mm}-${dd}`;
    },
    computed: {
        reservations: function () {
            return this.$store.getters['reservation/lists'];
        },
        pagination: function () {
            return this.$store.getters['reservation/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['reservation/page'];
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                const fromDate = new Date(this.first_date);
                const toDate = new Date(this.last_date);

                const fromYear = fromDate.getFullYear();
                const fromMonth = String(fromDate.getMonth() + 1).padStart(2, '0');
                const fromDay = String(fromDate.getDate()).padStart(2, '0');

                const toYear = toDate.getFullYear();
                const toMonth = String(toDate.getMonth() + 1).padStart(2, '0');
                const toDay = String(toDate.getDate()).padStart(2, '0');

                this.props.search.from_date = `${fromYear}-${fromMonth}-${fromDay}`;
                this.props.search.to_date = `${toYear}-${toMonth}-${toDay}`;
            } else {
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
            this.list();
        },
        clear: function () {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');

            this.first_date = today;
            this.last_date = today;
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.reservation_code = "";
            this.props.search.customer_name = "";
            this.props.search.customer_phone = "";
            this.props.search.from_date = `${yyyy}-${mm}-${dd}`;
            this.props.search.to_date = `${yyyy}-${mm}-${dd}`;
            this.props.search.status = null;
            this.list();
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
        formatDate: function (date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('reservation/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (reservation) {
            appService.modalShow("#reservationModal");
            this.loading.isActive = true;
            this.$store.dispatch('reservation/edit', reservation.id);
            this.props.form = {
                customer_name: reservation.customer_name,
                customer_phone: reservation.customer_phone,
                customer_email: reservation.customer_email,
                reservation_date: reservation.reservation_date,
                reservation_time: reservation.reservation_time,
                number_of_people: reservation.number_of_people,
                table_id: reservation.table_id,
                status: reservation.status,
                special_request: reservation.special_request,
                branch_id: reservation.branch_id,
                deposit_amount: reservation.deposit_amount,
                payment_status: reservation.payment_status,
                duration_minutes: reservation.duration_minutes
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('reservation/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.reservations'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        checkIn: function (id) {
            this.loading.isActive = true;
            this.$store.dispatch('reservation/checkIn', { id: id, search: this.props.search }).then((res) => {
                this.loading.isActive = false;
                alertService.success(this.$t('message.checked_in_successfully'));
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        checkOut: function (id) {
            this.loading.isActive = true;
            this.$store.dispatch('reservation/checkOut', { id: id, search: this.props.search }).then((res) => {
                this.loading.isActive = false;
                alertService.success(this.$t('message.checked_out_successfully'));
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        cancelReservation: function (id) {
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
                    this.$store.dispatch('reservation/cancel', { id: id, form: form, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.cancelled_successfully'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                }
            });
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
    }
}
</script>
