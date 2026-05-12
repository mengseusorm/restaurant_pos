<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('label.stock_record') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />

                    <FilterComponent />
                    <!-- <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div> -->

                    <StockRecordCreateComponent :props="props" />
                    <StockRecordCreateAdjustStockComponent :props="props" />
                    <StockRecordCreateTransfer :props="props" />
                    <!-- <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :method="xls" />
                        </div>
                    </div> -->
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="warehouse" class="db-field-title after:hidden">{{ $t('label.warehouse') }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="warehouse"
                                v-model="props.search.stock_id" :options="warehouses" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--"  />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t('button.search') }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
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
                            <th class="db-table-head-th">{{ $t('label.no') }}.</th>
                            <th class="db-table-head-th">{{ $t('label.item') }}</th>
                            <th class="db-table-head-th">{{ $t('label.warehouse') }}</th>
                            <th class="db-table-head-th">{{ $t('label.user') }}</th>
                            <th class="db-table-head-th">{{ $t('label.quantity') }}</th>
                            <th class="db-table-head-th">{{ $t('label.stock_record_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.note') }}</th>
                            <th class="db-table-head-th">{{ $t('label.from_warehouse') }}</th>
                            <th class="db-table-head-th">{{ $t('label.to_warehouse') }}</th>
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tr class="db-table-body-tr" v-for="(stockRecord, index) in stockRecords" :key="stockRecord">
                        <td class="db-table-body-td">{{ index + 1 }}</td>
                        <td class="db-table-body-td">{{ stockRecord.item_id ? stockRecord.item_id.name : 'Item is deleted' }}</td>
                        <td class="db-table-body-td">{{ stockRecord.stock_id?.name }}</td>
                        <td class="db-table-body-td">{{ stockRecord.user_id.name }}</td>
                        <td class="db-table-body-td">{{ stockRecord.quantity }}</td>
                        <td class="db-table-body-td">
                            <span :class="stockRecordTypeClass(stockRecord.record_type)">
                                {{ stockRecord.record_type }}
                            </span>
                        </td>
                        <td class="db-table-body-td">{{ stockRecord.transferType }}</td>
                        <td class="db-table-body-td">{{ stockRecord.from_warehouse?.name }}</td>
                        <td class="db-table-body-td">{{ stockRecord.to_warehouse?.name }}</td>
                        <td class="db-table-body-td">{{ stockRecord.created_at }}</td>
                        <td class="db-table-body-td">
                            <div v-if="stockRecord.record_type == 'ADJUST_STOCK' || stockRecord.record_type == 'STOCK_IN'" class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmModalEditComponent @click="edit(stockRecord)" />
                                <SmDeleteComponent @click="destroy(stockRecord.id)" />
                            </div>
                        </td>
                    </tr>
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
import ItemComponent from '../pos/ItemComponent.vue';
import LoadingComponent from '../components/LoadingComponent';
import StockRecordCreateComponent from './StockRecordCreateComponent.vue';
import StockRecordCreateAdjustStockComponent from './StockRecordCreateAdjustStockComponent.vue';
import StockRecordCreateTransfer from './StockRecordCreateTransfer.vue';
import alertService from '../../../services/alertService';
import PaginationTextComponent from '../components/pagination/PaginationTextComponent';
import PaginationBox from '../components/pagination/PaginationBox';
import PaginationSMBox from '../components/pagination/PaginationSMBox';
import appService from '../../../services/appService';
import TableLimitComponent from '../components/TableLimitComponent';
import SmDeleteComponent from '../components/buttons/SmDeleteComponent';
import SmModalEditComponent from '../components/buttons/SmModalEditComponent';
import ExportComponent from '../components/buttons/export/ExportComponent';
import ImportComponent from '../components/buttons/import/ImportComponent.vue';
import ExcelComponent from '../components/buttons/export/ExcelComponent';
import FilterComponent from '../components/buttons/collapse/FilterComponent';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

import PrintComponent from '../components/buttons/export/PrintComponent';

export default {
    name: 'StockRecordListComponent',
    components: {
        ItemComponent,
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        StockRecordCreateComponent,
        StockRecordCreateTransfer,
        StockRecordCreateAdjustStockComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        ExcelComponent,
        PrintComponent,
        ExportComponent,
        ImportComponent,
        FilterComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            first_date: null,
            last_date: null,
            props: {
                form: {
                    item: null,
                    item_id: null,
                    stock_id: null,
                    user_id: null,
                    quantity: null,
                    record_type: '',
                    to_warehouse_id: null,
                    from_warehouse_id: null,
                },
                form2: {
                    item_id: null,
                    stock_id: null,
                    user_id: null,
                    quantity: null,
                    record_type: '',
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'asc',
                    name: '',
                    stock_id: null,
                    from_warehouse_id: null,
                    to_warehouse_id: null,
                    from_date: '',
                    to_date: '',
                },
            },
        };
    },
    computed: {
        items: function () {
            return this.$store.getters['item/lists'];
        },
        pagination: function () {
            return this.$store.getters['stockRecord/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['stockRecord/page'];
        },
        itemStocks: function () {
            return this.$store.getters['ItemStock/lists'];
        },
        stockRecords: function () {
            return this.$store.getters['stockRecord/lists'];
        },
        warehouses: function () {
            return this.$store.getters['warehouse/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
    },
    mounted() {
        // Initialize dates with full month range
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
        const endDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);

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
        this.props.search.from_date = appService.formatDateTime(this.first_date);
        this.props.search.to_date = appService.formatDateTime(this.last_date);

        this.$store
            .dispatch('warehouse/lists', this.props.search)
            .then((res) => {
                this.loading.isActive = false;
            })
            .catch((err) => {
                this.loading.isActive = false;
            });

        this.list();
        this.itemStocksList();
        this.itemLists();

    },
    methods: {
        search: function () {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }

                console.log('Searching from', this.first_date, 'to', this.last_date);
                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = '';
                this.props.search.to_date = '';
            }
            this.list();
        },
        itemLists: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch('item/lists', {
                    order_column: 'id',
                    order_type: 'desc',
                    search: this.props.search,
                })
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        itemStocksList: function () {
            return this.$store.getters['ItemStock/lists'];
        },
        setCategory: function (id) {
            this.itemList();
        },
        stockRecordTypeClass: function (status) {
            return appService.stockRecordTypeClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch('stockRecord/lists', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        edit: function (stockRecord) {
            if (stockRecord.record_type == 'ADJUST_STOCK') {
                appService.modalShow('#adjust-stock-record-modal');
            } else if (stockRecord.record_type == 'STOCK_IN') {
                appService.modalShow('#create-stock-record-modal');
            } else {
                appService.modalShow('#create-stock-transfer-modal');
            }

            this.loading.isActive = true;
            this.$store.dispatch('stockRecord/edit', stockRecord.id);
            this.props.form = {
                item_id: stockRecord.item_id.id,
                stock_id: stockRecord.stock_id.id,
                user_id: stockRecord.user_id.id,
                quantity: stockRecord.quantity,
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch('stockRecord/destroy', { id: id, search: this.props.search })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t('menu.stock_record'));
                                this.list();
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        clear: function () {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
            const endDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);

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
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = '';
            this.props.search.stock_id = null;
            this.props.search.from_warehouse_id = null;
            this.props.search.to_warehouse_id = null;
            this.list();
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('stockRecord/export', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t('menu.item_categories');
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
