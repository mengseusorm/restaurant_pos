<template>
    <div class="row py-2">
        <LoadingComponent :props="loading" />
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.item_order_deleted') }}</h3>
                    <div class="db-card-filter">
                        <TableLimitComponent :method="orderItemDeletedList" :search="searchItemsProps" :page="orderItemDeletedPaginationPage" />
                        <FilterComponentSecond />
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list">
                                <ExcelComponent :method="exportItemsXls" />
                                <ExcelComponent :title="'button.excel_export_all'" :method="exportItemsXlsAll" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-filter-div">
                    <form class="p-4 sm:p-5 mb-5" @submit.prevent="searchOrderItems">
                        <div class="row">
                            <div class="col-12 sm:col-6">
                                <label for="searchStartDateItems" class="db-field-title after:hidden">
                                    {{ $t('label.start_date') }}
                                </label>
                                <Datepicker autoApply v-model="firstDateItems"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6">
                                <label for="searchEndDateItems" class="db-field-title after:hidden">
                                    {{ $t('label.end_date') }}
                                </label>
                                <Datepicker autoApply v-model="lastDateItems"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="order_id_items" class="db-field-title after:hidden">{{
                                    $t('label.order_id') }}</label>
                                <input id="order_id_items" v-model="searchItemsProps.order_serial_no" type="text"
                                    class="db-field-control">
                            </div>
                            <div class="col-12">
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <button class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-search-line lab-font-size-16"></i>
                                        <span>{{ $t('button.search') }}</span>
                                    </button>
                                    <button class="db-btn py-2 text-white bg-gray-600" @click="clearOrderItems">
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
                                <th class="db-table-head-th">{{ $t('label.item_name') }}</th>
                                <th class="db-table-head-th">{{ $t('label.quantity') }}</th>
                                <th class="db-table-head-th">{{ $t('label.price') }}</th>
                                <th class="db-table-head-th">{{ $t('label.discount') }}</th>
                                <th class="db-table-head-th">{{ $t('label.tax') }}</th>
                                <th class="db-table-head-th">{{ $t('label.total') }}</th>
                                <th class="db-table-head-th">{{ $t('label.date') }}</th>
                                <th class="db-table-head-th hidden-print"
                                    v-if="permissionChecker('item-order-deleted')">{{
                                        $t('label.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="orderItemDeleteds.length > 0">
                            <tr class="db-table-body-tr cursor-pointer" v-for="item in orderItemDeleteds" :key="item.id">
                                <td class="db-table-body-td">
                                    {{ item.order_serial_no }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.item_name}}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.quantity }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.price_currency || item.price }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.discount || '0.00' }}{{ item.discount_percentage && item.discount_percentage > 0 ? ` (${item.discount_percentage}%)` : '' }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.tax_amount || '0.00' }}{{ item.tax_name ? ` (${item.tax_name})` : '' }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.total_price_currency || item.total_price }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ item.order_updated_at || item.order_created_at }}
                                </td>
                                <td class="db-table-body-td hidden-print"
                                    v-if="permissionChecker('item-order-deleted')">
                                    <div
                                        class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                        <SmIconDeleteComponent @click="destroyOrderItem(item.id)"
                                            v-if="((branch.show_delete_order_button == statusEnum.ACTIVE && permissionChecker('item-order-deleted')) || authInfo.id === 1) && branch.id" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                    <PaginationSMBox :pagination="orderItemDeletedPaginationPage" :method="orderItemDeletedList" />
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <PaginationTextComponent :props="{ page: orderItemDeletedPaginationPage }" />
                        <PaginationBox :pagination="orderItemDeletedPaginationPage" :method="orderItemDeletedList" />
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
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import FilterComponentSecond from "../components/buttons/collapse/FilterComponentSecond";
import ExportComponent from "../components/buttons/export/ExportComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "OrderItemsDeletedLists",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        FilterComponentSecond,
        ExportComponent,
        ExcelComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            statusEnum: statusEnum,
            enums: createEnumArrays(this.$t),
            firstDateItems: null,
            lastDateItems: null,
            searchItemsProps: {
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
                payment_status: null
            }
        }
    },
    mounted() {
        this.initializeDatesItems();
        this.orderItemDeletedList();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        orderItemDeleteds: function () {
            return this.$store.getters['orderItemDeleted/lists'];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        orderItemDeletedPaginationPage: function () {
            return this.$store.getters['orderItemDeleted/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
    },
    methods: {
        initializeDatesItems() {
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

            this.firstDateItems = startDate;
            this.lastDateItems = endDate;
            this.searchItemsProps.from_date = appService.formatDateTime(this.firstDateItems);
            this.searchItemsProps.to_date = appService.formatDateTime(this.lastDateItems);
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        searchOrderItems: function () {
            if (this.firstDateItems && this.lastDateItems) {
                if (this.firstDateItems > this.lastDateItems) {
                    [this.firstDateItems, this.lastDateItems] = [this.lastDateItems, this.firstDateItems];
                }

                this.searchItemsProps.from_date = appService.formatDateTime(this.firstDateItems);
                this.searchItemsProps.to_date = appService.formatDateTime(this.lastDateItems);
            } else {
                this.searchItemsProps.from_date = null;
                this.searchItemsProps.to_date = null;
            }
            this.orderItemDeletedList();
        },
        clearOrderItems: function () {
            this.initializeDatesItems();
            this.searchItemsProps.paginate = 1;
            this.searchItemsProps.page = 1;
            this.searchItemsProps.order_by = "desc";
            this.searchItemsProps.order_serial_no = "";
            this.searchItemsProps.status = null;
            this.searchItemsProps.user_id = null;
            this.orderItemDeletedList();
        },
        orderItemDeletedList: function (page = 1) {
            this.loading.isActive = true;
            this.searchItemsProps.page = page;
            this.$store.dispatch('orderItemDeleted/lists', this.searchItemsProps).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        destroyOrderItem: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('orderItemDeleted/destroy', { id: id, search: this.searchItemsProps }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.item_order_deleted'));
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
        exportItemsXls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("orderItemDeleted/export", this.searchItemsProps).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.item_order_deleted");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        exportItemsXlsAll: function () {
            this.loading.isActive = true;
            let searchParams = { ...this.searchItemsProps };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store.dispatch("orderItemDeleted/export", searchParams).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.item_order_deleted");
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
