<template>
    <div class="row">
        <div class="col-12">
            <BreadcrumbComponent />
        </div>
        <LoadingComponent :props="loading" />
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.transactions') }}</h3>
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
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchStartDate" class="db-field-title after:hidden">{{
                                    $t('label.start_date')
                                }}</label>
                                <Datepicker autoApply v-model="first_date"></Datepicker>
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchEndDate" class="db-field-title after:hidden">{{
                                    $t('label.end_date')
                                }}</label>
                                <Datepicker autoApply v-model="last_date"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="transaction_id" class="db-field-title after:hidden">{{
                                    $t('label.transaction_id')
                                }}</label>
                                <input id="transaction_id" v-model="props.search.transaction_no" type="text"
                                    class="db-field-control">
                            </div> 
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="order_serial_no" class="db-field-title after:hidden">{{
                                    $t('label.order_serial_no')
                                }}</label>
                                <input id="order_serial_no" v-model="props.search.order_serial_no" type="text"
                                    class="db-field-control">
                            </div>  
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="payment_method" class="db-field-title after:hidden">{{
                                    $t('label.payment_method')
                                }}</label>
                                <vue-select class="db-field-control f-b-custom-select" id="payment_method"
                                    v-model="props.search.payment_method" :options="paymentMethods" label-by="name"
                                    value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--" />
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
                    <table class="db-table stripe" id="print" :dir="direction">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th">{{ $t('label.order_id') }}</th>
                                <th class="db-table-head-th">{{ $t('label.order_serial_no') }}</th>
                                <th class="db-table-head-th">{{ $t('label.transaction_id') }}</th>
                                <th class="db-table-head-th">{{ $t('label.date') }}</th>
                                <th class="db-table-head-th">{{ $t('label.payment') }}</th>
                                <th class="db-table-head-th">{{ $t('label.type') }}</th>
                                <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                                <th class="db-table-head-th">{{ $t('label.transaction_amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="transactions.length > 0">
                            <tr class="db-table-body-tr" v-for="transaction in transactions" :key="transaction">
                                <td class="db-table-body-td">
                                    {{ transaction.order_id }}
                                </td>
                                <td class="db-table-body-td">
                                    #{{ transaction.order_serial_no }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.transaction_no }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.date }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.payment_method }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.type }}
                                </td>
                                <td class="db-table-body-td">
                                    <span class="text-[#2AC769]" v-if="transaction.sign == '+'">
                                        {{ transaction.sign }} {{ transaction.amount }} {{ transaction.currency }}
                                    </span>
                                    <span class="text-[#FB4E4E]" v-else>
                                        {{ transaction.sign }} {{ transaction.amount }} {{ transaction.currency }}
                                    </span>
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.transaction_amount }} {{ transaction.transaction_currency }}
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
    </div> 
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import BreadcrumbComponent from "../components/BreadcrumbComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "TransactionListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
        BreadcrumbComponent
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
            enums: {},
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.transactions"),
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
                    order_type: "desc",
                    branch_id: null,
                    order_serial_no: "",
                    transaction_no: "",
                    payment_method: null, 
                    from_date: "",
                    to_date: ""
                }
            }
        }
    },
    mounted() {
        this.$store.dispatch("defaultAccess/show").then(res => {
            this.props.search.branch_id = res.data.data.branch_id;
            
            // Initialize dates: from yesterday to today
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);
            
            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);
            
            this.list();
        }).catch(); 
        this.$store.dispatch('paymentMethod/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        transactions: function () {
            return this.$store.getters['transaction/lists'];
        }, 
        paymentMethods: function () {
            return this.$store.getters['paymentMethod/lists'];
        },
        pagination: function () {
            return this.$store.getters['transaction/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['transaction/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
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
            // Reset to yesterday to today dates
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);
            
            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(startDate);
            this.props.search.to_date = appService.formatDateTime(endDate);
            
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_type = "desc";
            this.props.search.order_serial_no = "";
            this.props.search.transaction_no = "";
            this.props.search.payment_method = null;
            this.props.form.date = "";
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('transaction/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("transaction/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.transactions");
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
