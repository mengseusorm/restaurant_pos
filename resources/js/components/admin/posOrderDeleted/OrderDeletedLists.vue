<template>
    <div class="row py-2">
        <LoadingComponent :props="loading" />
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.order_deleted') }}</h3>
                    <div class="db-card-filter">
                        <TableLimitComponent :method="orderDeletedList" :search="searchProps" :page="orderDeletedPaginationPage" />
                        <FilterComponent />
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list">
                                <ExcelComponent :method="exportXls" />
                                <ExcelComponent :title="'button.excel_export_all'" :method="exportXlsAll" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-filter-div">
                    <form class="p-4 sm:p-5 mb-5" @submit.prevent="searchOrders">
                        <div class="row">
                            <div class="col-12 sm:col-6">
                                <label for="searchStartDate" class="db-field-title after:hidden">
                                    {{ $t('label.start_date') }}
                                </label>
                                <Datepicker autoApply v-model="firstDate"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6">
                                <label for="searchEndDate" class="db-field-title after:hidden">
                                    {{ $t('label.end_date') }}
                                </label>
                                <Datepicker autoApply v-model="lastDate"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="order_id" class="db-field-title after:hidden">{{
                                    $t('label.order_id') }}</label>
                                <input id="order_id" v-model="searchProps.order_serial_no" type="text"
                                    class="db-field-control">
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchStatus" class="db-field-title after:hidden">
                                    {{ $t('label.status') }}
                                </label>
                                <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                    v-model="searchProps.status" :options="enums.orderStatusOptions"
                                    label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                    :clearOnClose="true" placeholder="--" search-placeholder="--" />
                            </div>
                            <div class="col-12">
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <button class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-search-line lab-font-size-16"></i>
                                        <span>{{ $t('button.search') }}</span>
                                    </button>
                                    <button class="db-btn py-2 text-white bg-gray-600" @click="clearOrders">
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
                                
                                <th class="db-table-head-th">{{ $t('label.order_id') }}</th>
                                <th class="db-table-head-th">{{ $t('label.invoice_number') }}</th> 
                                <th class="db-table-head-th">{{ $t('label.waiting_number') }} / {{
                                    $t('label.token') }}</th>
                                <th class="db-table-head-th">{{ $t('label.table') }}</th>
                                <th class="db-table-head-th">{{ $t('label.order_type') }}</th>
                                <th class="db-table-head-th">{{ $t('label.discount') }}</th>
                                <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                                <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                                <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                                <th class="db-table-head-th">{{ $t('label.date') }}</th>
                                <th class="db-table-head-th">{{ $t('label.status') }}</th>
                                <th class="db-table-head-th">{{ $t('label.source') }}</th>
                                <th class="db-table-head-th">{{ $t('label.payment_status') }}</th>
                                <th class="db-table-head-th hidden-print"
                                    v-if="permissionChecker('order-deleted')">{{
                                        $t('label.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="orderDeleteds.length > 0">
                            <tr class="db-table-body-tr cursor-pointer" v-for="order in orderDeleteds" :key="order">
                                <td class="db-table-body-td">
                                    {{ order.order_serial_no }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ order.invoice_number }}
                                </td>
                                <td class="db-table-body-td">
                                    #{{ order.waiting_number }} {{ order.token ? '/ ' + order.token : '' }}
                                </td>
                                <td class="db-table-body-td">
                                    <div v-if="order.dining_table && order.dining_table.length > 0">
                                        <span v-for="dining in order.dining_table" :key="dining"
                                            class="db-badge blue ml-2">
                                            <span>{{ dining }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="db-table-body-td">
                                    <span class="db-table-badge text-green-600 bg-green-100">
                                        {{ enums.orderTypeEnumArray[order.order_type] }}
                                    </span>
                                </td>
                                <td class="db-table-body-td">
                                    {{ order.discount }}
                                </td>
                                <td class="db-table-body-td">{{ order.total }}</td>
                                <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                                <td class="db-table-body-td">{{ order.total_tax }}</td>
                                <td class="db-table-body-td">{{ order.order_datetime }}</td>
                                <td class="db-table-body-td">
                                    <span :class="orderStatusClass(order.status)">
                                        {{ enums.orderStatusEnumArray[order.status] }}
                                    </span>
                                </td>
                                <td class="db-table-body-td">
                                    {{ enums.sourceEnumArray[order.source] }}
                                </td>
                                <td class="db-table-body-td">
                                    <span
                                        :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                                        {{ enums.paymentStatusEnumArray[order.payment_status] }}
                                    </span>
                                </td>
                                <td class="db-table-body-td hidden-print"
                                    v-if="permissionChecker('order-deleted')">
                                    <div
                                        class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                        <SmIconViewComponent :link="'admin.posOrderDeleted.show'"
                                            :id="order.id" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                    <PaginationSMBox :pagination="orderDeletedPaginationPage" :method="orderDeletedList" />
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <PaginationTextComponent :props="{ page: orderDeletedPaginationPage }" />
                        <PaginationBox :pagination="orderDeletedPaginationPage" :method="orderDeletedList" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import { createEnumArrays } from "../../../enums/enumArrays";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "OrderDeletedLists",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconViewComponent,
        FilterComponent,
        ExportComponent,
        ExcelComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: createEnumArrays(this.$t),
            firstDate: null,
            lastDate: null,
            searchProps: {
                paginate: 1,
                page: 1,
                per_page: 10,
                order_column: 'id',
                order_by: "desc",
                order_serial_no: "",
                user_id: null,
                status: null,
                from_date: null,
                to_date: null,
                payment_status: null,
                excepts: null
            }
        }
    },
    mounted() {
        this.initializeDates();
        this.orderDeletedList();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        orderDeleteds: function () {
            return this.$store.getters['orderDeleted/lists'];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        orderDeletedPaginationPage: function () {
            return this.$store.getters['orderDeleted/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
    },
    methods: {
        initializeDates() {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

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

            this.firstDate = startDate;
            this.lastDate = endDate;
            this.searchProps.from_date = appService.formatDateTime(this.firstDate);
            this.searchProps.to_date = appService.formatDateTime(this.lastDate);
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        searchOrders: function () {
            if (this.firstDate && this.lastDate) {
                if (this.firstDate > this.lastDate) {
                    [this.firstDate, this.lastDate] = [this.lastDate, this.firstDate];
                }

                this.searchProps.from_date = appService.formatDateTime(this.firstDate);
                this.searchProps.to_date = appService.formatDateTime(this.lastDate);
            } else {
                this.searchProps.from_date = null;
                this.searchProps.to_date = null;
            }
            this.orderDeletedList();
        },
        clearOrders: function () {
            this.initializeDates();
            this.searchProps.paginate = 1;
            this.searchProps.page = 1;
            this.searchProps.order_by = "desc";
            this.searchProps.order_serial_no = "";
            this.searchProps.status = null;
            this.searchProps.payment_status = null;
            this.searchProps.excepts = orderTypeEnum.DELIVERY + '|' + orderTypeEnum.TAKEAWAY;
            this.searchProps.user_id = null;
            this.orderDeletedList();
        },
        orderDeletedList: function (page = 1) {
            this.loading.isActive = true;
            this.searchProps.page = page;
            this.$store.dispatch('orderDeleted/lists', this.searchProps).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        exportXls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("orderDeleted/export", this.searchProps).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.pos_orders");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        exportXlsAll: function () {
            this.loading.isActive = true;
            let searchParams = { ...this.searchProps };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store.dispatch("orderDeleted/export", searchParams).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.order_deleted");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        }
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
