<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.online_orders") }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date" :key="'from-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date" :key="'to-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="order_id" class="db-field-title after:hidden">{{
                                $t("label.order_id")
                            }}</label>
                            <input id="order_id" v-model="props.search.order_serial_no" type="text"
                                class="db-field-control" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title">{{
                                $t("label.status")
                            }}</label>
                            <!-- <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="enums.orderStatusOptions" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" /> -->
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="orderStatus" :label-by="(option) => option['name_' + language_code] || option.name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="user_id" class="db-field-title">
                                {{ $t("label.customer") }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="user_id"
                                v-model="props.search.user_id" :options="customers" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.order_id") }}</th>
                            <th class="db-table-head-th">{{ $t("label.order_note") }}</th>

                            <th class="db-table-head-th">{{ $t("label.amount") }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t("label.date") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>

                            <th class="db-table-head-th">{{ $t('label.source') }}</th>
                            <th class="db-table-head-th">{{ $t('label.payment_status') }}</th>

                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('table-orders')">
                                {{ $t("label.action") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="orders.length > 0">
                        <tr class="db-table-body-tr cursor-pointer"
                            v-for="order in orders"
                            :key="order"
                            @click="$router.push({ name: 'admin.online.order.show', params: { id: order.id } })" >
                            <td class="db-table-body-td">
                                {{ order.order_serial_no }}
                            </td>
                            <td class="db-table-body-td">
                                {{ textShortener(order.order_note, 30) }}
                            </td>

                            <td class="db-table-body-td">{{ order.total_currency_price }}</td>
                            <td class="db-table-body-td">{{ order.total_tax_currency_price }}</td>
                            <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                            <td class="db-table-body-td">
                                {{ order.order_datetime }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="orderStatusClass(order.order_status_info?.name)">
                                    {{ order.order_status_info ? (order.order_status_info['name_' + language_code] || order.order_status_info?.name) : 'N/A' }}
                                </span>
                                <span :class="orderStatusClass(order.is_advance_order)"
                                    v-if="order.is_advance_order === enums.isAdvanceOrderEnum.YES">
                                    {{ $t("label.advance") }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                {{ enums.sourceEnumArray[order.source] }}
                            </td>

                            <td class="db-table-body-td">
                                <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                                {{ enums.paymentStatusEnumArray[order.payment_status] }} </span>
                            </td>

                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('table-orders')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconViewComponent :link="'admin.table.order.show'" :id="order.id"
                                        v-if="permissionChecker('table-orders')" />
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
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import sourceEnum from '../../../enums/modules/sourceEnum';
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import statusEnum from "../../../enums/modules/statusEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import { createEnumArrays } from "../../../enums/enumArrays";

export default {
    name: "OnlineOrderListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconViewComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: createEnumArrays(this.$t),
            printLoading: true,
            first_date: null,
            last_date: null,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.online_orders"),
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    // paginate: 1,
                    // page: 1,
                    // per_page: 10,
                    // order_column: "id",
                    // order_by: "desc",
                    // order_serial_no: "",
                    // user_id: null,
                    // source: sourceEnum.ONLINE_ORDER,
                    // status: orderStatusEnum.PENDING,
                    // payment_status: null,
                    // from_date: "",
                    // to_date: "",
                        paginate: 1,
                        page: 1,
                        per_page: 10,
                        order_column: 'id',
                        order_by: "asc",
                        order_serial_no: "",
                        user_id: null,
                        source: sourceEnum.ONLINE_ORDER,
                        status: orderStatusEnum.PENDING,
                        from_date: "",
                        to_date: "",
                        payment_status: ""
                },
            },
        };
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        // Initialize dates: from yesterday to today
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        // Use branch open_time and close_time if available
        if (this.branch && this.branch.open_time) {
            const [hours, minutes] = this.branch.open_time.split(':');
            startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        } else {
            startDate.setHours(0, 0, 0, 0);
        }

        if (this.branch && this.branch.close_time) {
            const [hours, minutes] = this.branch.close_time.split(':');
            endDate.setHours(parseInt(hours), parseInt(minutes), 59, 999);
        } else {
            endDate.setHours(23, 59, 59, 999);
        }

        this.first_date = startDate;
        this.last_date = endDate;
        this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
        this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);

        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;

        this.$store.dispatch('orderStatus/lists',{
            order_column: 'id',
            status: statusEnum.ACTIVE
        }).then().catch();
        this.$store.dispatch("user/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });

        // this.$store.dispatch("defaultAccess/show").then(res => {
        //     this.defaultBranch = res.data.data.branch_id;
        //     this.$store.dispatch('backendGlobalState/branchShow', res.data?.data?.branch_id).then().catch();
        // }).catch();

        this.handleQueryParams();
    },
    watch: {
        '$route': {
            handler(newRoute) {
                this.handleQueryParams();
                this.list();
            },
            immediate: false
        }
    },
    computed: {
        setting() {
            return this.$store.getters['frontendSetting/lists'] ?? {};
        },
        phpDateFormat() {
            return this.setting.site_date_format || 'd/m/Y';
        },
        phpTimeFormat() {
            return this.setting.site_time_format || 'h:i A';
        },
        datePickerFormat() {
            return appService.datepickerDateTimeFormat(this.phpDateFormat, this.phpTimeFormat);
        },
        datePickerKey() {
            return `${this.datePickerFormat}-${this.isTimePicker24Hour}`;
        },
        dateOnlyPickerFormat() {
            return appService.phpDateToDatepickerFormat(this.phpDateFormat);
        },
        dateOnlyPickerKey() {
            return this.dateOnlyPickerFormat;
        },
        isTimePicker24Hour() {
            return appService.is24HourTimeFormat(this.phpTimeFormat);
        },
        orders: function () {
            return this.$store.getters["onlineOrder/lists"];
        },
        customers: function () {
            return this.$store.getters["user/lists"];
        },
        pagination: function () {
            return this.$store.getters["onlineOrder/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["onlineOrder/page"];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        orderStatus: function () {
            return this.$store.getters['orderStatus/lists'];
        },
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        handleQueryParams: function () {
            // Reset to defaults first
            this.props.search.status = orderStatusEnum.PENDING;
            this.props.search.payment_status = null;

            // Check for query parameters and set search filters
            if (this.$route.query.status) {
                this.props.search.status = parseInt(this.$route.query.status);
            }
            if (this.$route.query.payment_status) {
                this.props.search.payment_status = parseInt(this.$route.query.payment_status);
            }
            if (this.$route.query.from_date && this.$route.query.from_date !== '') {
                this.props.search.from_date = this.$route.query.from_date;
                // Parse the ISO string back to a Date object for the datepicker
                this.first_date = new Date(this.$route.query.from_date);
            } else if (this.$route.query.from_date === '') {
                this.props.search.from_date = "";
            }
            if (this.$route.query.to_date && this.$route.query.to_date !== '') {
                this.props.search.to_date = this.$route.query.to_date;
                // Parse the ISO string back to a Date object for the datepicker
                this.last_date = new Date(this.$route.query.to_date);
            } else if (this.$route.query.to_date === '') {
                this.props.search.to_date = "";
            }
        },
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
                this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }
            this.list();
        },
        clear: function () {
            // Reset to yesterday to today dates with branch time range
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            // Use branch open_time and close_time if available
            if (this.branch && this.branch.open_time) {
                const [hours, minutes] = this.branch.open_time.split(':');
                startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
            } else {
                startDate.setHours(0, 0, 0, 0);
            }

            if (this.branch && this.branch.close_time) {
                const [hours, minutes] = this.branch.close_time.split(':');
                endDate.setHours(parseInt(hours), parseInt(minutes), 59, 999);
            } else {
                endDate.setHours(23, 59, 59, 999);
            }

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTimeForFilter(startDate);
            this.props.search.to_date = appService.formatDateTimeForFilter(endDate);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.order_serial_no = "";
            this.props.search.status = null;
            this.props.search.payment_status = null;
            this.props.search.order_type = orderTypeEnum.DINING_TABLE;
            this.props.search.user_id = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("onlineOrder/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("onlineOrder/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.online_orders");
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        xlsAll: function () {

            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;


            this.loading.isActive = true;
            this.$store
                .dispatch("onlineOrder/export", searchParams)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.online_orders");
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
    },
};
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
