<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.order_print_logs') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
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
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="order_serial_number" class="db-field-title after:hidden">{{ $t('label.order_serial_number') }}</label>
                            <input id="order_serial_number" v-model="props.search.order_serial_number" type="text"
                                class="db-field-control">
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchPrintType" class="db-field-title after:hidden">
                                {{ $t('label.print_type') }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchPrintType"
                                v-model="props.search.print_type" :options="[
                                    { id: enums.printTypeEnum.MENU, name: $t('label.menu') },
                                    { id: enums.printTypeEnum.INVOICE, name: $t('label.invoice') },
                                    { id: enums.printTypeEnum.BILL, name: $t('label.bill') },
                                    { id: enums.printTypeEnum.LABEL, name: $t('label.label') },
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchPrintSuccess" class="db-field-title after:hidden">
                                {{ $t('label.print_status') }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchPrintSuccess"
                                v-model="props.search.print_success" :options="[
                                    { id: true, name: $t('label.success') },
                                    { id: false, name: $t('label.failed') },
                                ]" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="user_id" class="db-field-title">
                                {{ $t("label.user") }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="user_id"
                                v-model="props.search.user_id" :options="users" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="branch_id" class="db-field-title">
                                {{ $t("label.branch") }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="branch_id"
                                v-model="props.search.branch_id" :options="branches" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
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
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.id') }}</th>
                            <th class="db-table-head-th">{{ $t('label.user') }}</th>
                            <th class="db-table-head-th">{{ $t('label.order_serial_number') }}</th>
                            <th class="db-table-head-th">{{ $t('label.print_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.print_status') }}</th>
                            <th class="db-table-head-th">{{ $t('label.error_message') }}</th>
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('order-print-logs')">{{
                                $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="printLogs.length > 0">
                        <tr class="db-table-body-tr" v-for="printLog in printLogs" :key="printLog.id">

                        <td class="db-table-body-td">
                            {{ printLog.id }}
                        </td>
                        <td class="db-table-body-td">
                            {{ printLog.user_id }}
                        </td>
                        <td class="db-table-body-td">
                            {{ printLog.order_serial_number }}
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-blue-600 bg-blue-100">
                                {{ printLog.print_type_name }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span :class="printStatusClass(printLog.print_success)">
                                {{ printLog.print_success_text }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="text-red-600 text-sm" v-if="printLog.error_message">
                                {{ printLog.error_message }}
                            </span>
                            <span class="text-gray-400 text-sm" v-else>-</span>
                        </td>
                        <td class="db-table-body-td">{{ printLog.created_at }}</td>
                        <td class="db-table-body-td hidden-print" v-if="permissionChecker('order-print-logs')">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmIconDeleteComponent @click="destroy(printLog.id)" v-if="permissionChecker('order-print-logs')"/>
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
import LoadingComponent from "../../components/LoadingComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import { createEnumArrays } from "../../../../enums/enumArrays";
import statusEnum from "../../../../enums/modules/statusEnum";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmIconDeleteComponent from "../../components/buttons/SmIconDeleteComponent";
import FilterComponent from "../../components/buttons/collapse/FilterComponent";
import ExportComponent from "../../components/buttons/export/ExportComponent";
import PrintComponent from "../../components/buttons/export/PrintComponent";
import ExcelComponent from "../../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import displayModeEnum from "../../../../enums/modules/displayModeEnum";
import printTypeEnum from "../../../../enums/modules/printTypeEnum";

export default {
    name: "OrderPrintLogListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
    },
    setup() {
        const date = ref();

        const presetRanges = ref([
            { label: 'Today', range: [new Date(), new Date()] },
            { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: 'Last month',
                range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
            },
            { label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
            {
                label: 'This year (slot)',
                range: [startOfYear(new Date()), endOfYear(new Date())],
                slot: 'yearly',
            },
        ]);

        return {
            date,
            presetRanges,
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
            statusEnum: statusEnum,
            enums: {
                ...createEnumArrays(this.$t),
                printTypeEnum: printTypeEnum
            },
            printObj: {
                id: "print",
                popTitle: this.$t("menu.order_print_logs"),
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_by: "desc",
                    order_serial_number: "",
                    user_id: null,
                    branch_id: null,
                    print_type: null,
                    print_success: null,
                    from_date: "",
                    to_date: "",
                }
            }
        }
    },
    created() {
        // Initialize dates to current day with time
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        startDate.setHours(0, 0, 0, 0);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        endDate.setHours(23, 59, 59, 999);

        this.first_date = startDate;
        this.last_date = endDate;
        this.props.search.from_date = appService.formatDateTime(this.first_date);
        this.props.search.to_date = appService.formatDateTime(this.last_date);
    },
    mounted() {
        this.list();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.$store.dispatch('branch/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        printLogs: function () {
            return this.$store.getters['orderPrintLog/lists'];
        },
        users: function () {
            return this.$store.getters['user/lists'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        pagination: function () {
            return this.$store.getters['orderPrintLog/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['orderPrintLog/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        printStatusClass: function (success) {
            return success
                ? 'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#1AB759] bg-[#DAF7E3]'
                : 'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]';
        },
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }
            this.list();
        },
        clear: function () {
            // Reset to current day dates with full time range
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            startDate.setHours(0, 0, 0, 0);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            endDate.setHours(23, 59, 59, 999);

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = startDate.toISOString();
            this.props.search.to_date = endDate.toISOString();

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.order_serial_number = "";
            this.props.search.print_type = null;
            this.props.search.print_success = null;
            this.props.search.user_id = null;
            this.props.search.branch_id = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('orderPrintLog/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('orderPrintLog/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.order_print_logs'));
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
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("orderPrintLog/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.order_print_logs");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
    }
}
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
