<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.pos_orders') }}</h3>
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
                    <PrintContentComponent modal-id="printPosOrderModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printPosOrderModal-print-content" style="padding: 8px; font-size: 12px;">
                                <div style="text-align: center; padding-bottom: 8px; border-bottom: 1px dashed #666; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 9px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date ?? props.search.from_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <table style="width: 100%; font-size: 9px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.order_id') }}</th>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.order_type') }}</th>
                                            <th style="text-align: right; padding: 3px 2px; font-weight: bold;">{{ $t('label.amount') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.date') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="orders.length > 0">
                                        <tr v-for="order in orders" :key="order">
                                            <td style="padding: 4px 2px;">{{ order.order_serial_no }}</td>
                                            <td style="padding: 4px 2px;">
                                                {{ enums.orderTypeEnumArray[order.order_type] }}
                                            </td>
                                            <td style="text-align: right; padding: 4px 2px;">{{ order.total_amount_price }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ order.order_datetime }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ order.order_status_info ? order.order_status_info.name : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </PrintContentComponent>
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
                            <label for="order_id" class="db-field-title after:hidden">{{ $t('label.order_id') }}</label>
                            <input id="order_id" v-model="props.search.order_serial_no" type="text"
                                class="db-field-control">
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">
                                {{ $t('label.status') }}
                            </label>
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
                            <th class="db-table-head-th">{{ $t('label.invoice_number') }}</th> 
                            <th class="db-table-head-th">{{ $t('label.waiting_number') }} / {{ $t('label.token') }}</th>
                            <th class="db-table-head-th">{{ $t('label.table') }}</th>
                            <th class="db-table-head-th">{{ $t('label.order_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.customer') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.status') }}</th>
                            <th class="db-table-head-th">{{ $t('label.source') }}</th>
                            <th class="db-table-head-th">{{ $t('label.payment_status') }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('pos-orders')">{{
                                $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="orders.length > 0">
                        <tr class="db-table-body-tr cursor-pointer" v-for="order in orders" :key="order">
                            <!-- @click="$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } })"> --> 
                            <td class="db-table-body-td"
                                @click="$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } })">
                                {{ order.order_serial_no }}
                            </td>
                            <td class="db-table-body-td">{{ order.invoice_number }}</td> 
                            <td class="db-table-body-td"
                                @click="$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } })">
                                #{{ order.waiting_number }} {{ order.token ? '/ ' + order.token : '' }}
                            </td>
                            <td class="db-table-body-td">
                                <div v-if="order.order_dinings && order.order_dinings.length > 0">
                                    <span v-for="dining in order.order_dinings" :key="dining.id"
                                        class="db-badge blue ml-2">
                                        <span>{{ dining.dining_table_name }}</span>
                                    </span>
                                </div>
                            </td> 
                            <td class="db-table-body-td">
                                <span class="db-table-badge text-green-600 bg-green-100">
                                    {{ order.order_type_info ? (order.order_type_info['name_' + language_code] || order.order_type_info?.name) : 'N/A' }}
                                </span>
                            </td> 
                            <td class="db-table-body-td">
                                {{ order.customer_name }}
                            </td>
                            <td class="db-table-body-td">{{ order.total_currency_price }}</td>
                            <td class="db-table-body-td">{{ order.total_tax_currency_price }}</td>
                            <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                            <td class="db-table-body-td">{{ order.order_datetime }}</td>
                            <td class="db-table-body-td">
                                <span :class="orderStatusClass(order.order_status_info?.name)">
                                    {{ order.order_status_info ? (order.order_status_info['name_' + language_code] || order.order_status_info?.name) : 'N/A' }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                {{ enums.sourceEnumArray[order.source] }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                                    {{ enums.paymentStatusEnumArray[order.payment_status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('pos-orders')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconViewComponent :link="'admin.pos.orders.show'" :id="order.id"
                                        v-if="permissionChecker('pos-orders')" />
                                    <SmIconDeleteComponent @click="destroy(order.id)"
                                        v-if="((branch.show_delete_order_button == statusEnum.ACTIVE && permissionChecker('pos-orders')) || authInfo.id === 1) && branch.id" />
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

            <div id="receipt-container" class="modal">
                <div class="modal-dialog max-w-max rounded-none" id="print" :dir="direction">
                    <div class="modal-header hidden-print">
                        <button type="button" @click="close()"
                            class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                            <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                            <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                        </button>
                        <button type="button" @click="printInvoice()" :disabled="isPrinting" :class="[
                            'flex items-center justify-center gap-1.5 py-2 px-4 rounded',
                            isPrinting ? 'bg-gray-600 cursor-not-allowed' : 'bg-[#1AB759]'
                        ]">
                            <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                            <span class="text-xs leading-5 capitalize text-white">
                                {{ isPrinting ? $t('button.print_loading') : $t('button.print') }}
                            </span>
                        </button>
                    </div>
                    <div class="modal-body p-4" ref="htmlContent" id="print-item">
                        <div
                            style="text-align: center; padding-bottom: 8px; border-bottom: 1px dashed #666; margin-bottom: 8px;">
                            <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                            <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                            <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                        </div>

                        <div class="db-table-responsive">
                            <table class="" id="print" :dir="direction">
                                <thead class="">
                                    <tr class="">
                                        <th>{{ $t('label.order_id') }}</th>
                                        <th>{{ $t('label.waiting_number') }}</th>
                                        <th>{{ $t('label.customer') }}</th>
                                        <th>{{ $t('label.amount') }}</th>
                                        <th>{{ $t('label.vat') }}</th>
                                        <th>{{ $t('label.amount') }} (VAT)</th>
                                        <th>{{ $t('label.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="db-table-body" v-if="orders.length > 0">
                                    <tr class="db-table-body" v-for="order in orders" :key="order">
                                        <td class="db-table-body-td">
                                            {{ order.order_serial_no }}
                                        </td>
                                        <td class="db-table-body-td">
                                            #{{ order.waiting_number }}
                                        </td>
                                        <td class="db-table-body-td">
                                            {{ order.customer_name }}
                                        </td>
                                        <td class="db-table-body-td">{{ order.total_currency_price }}</td>
                                        <td class="db-table-body-td">{{ order.total_tax_currency_price }}</td>
                                        <td class="db-table-body-td">{{ order.total_amount_price }}</td>
                                        <td class="db-table-body-td">{{ order.order_datetime }}</td>
                                    </tr>
                                </tbody>
                            </table>
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
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import { createEnumArrays } from "../../../enums/enumArrays";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import sourceEnum from '../../../enums/modules/sourceEnum';
import statusEnum from "../../../enums/modules/statusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import printerTypeEnum from "../../../enums/modules/printerTypeEnum";
import printService from "../../../services/PrintService";
import printerMethodEnum from '../../../enums/modules/printerMethodEnum';
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: "PosOrderListComponent",
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
        PrintContentComponent,
    },
    setup() {
        const date = ref();
        const today = new Date()
        return {
            date,
            today
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            statusEnum: statusEnum,
            enums: createEnumArrays(this.$t),
            payment_status: null,
            printLoading: true,
            first_date: null,
            last_date: null,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.online_orders"),
            },
            props: {
                form: {
                    date: new Date(),
                },
                search: {
                    // paginate: 1,
                    // page: 1,
                    // per_page: 10,
                    // order_column: 'id',
                    // order_by: "desc",
                    // order_serial_no: "",
                    // user_id: null,
                    // status: null,
                    // from_date: null,
                    // to_date: null,
                    // payment_status: null
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_by: "asc",
                    order_serial_no: "",
                    source: sourceEnum.POS,
                    user_id: null,
                    status: null,
                    from_date: "",
                    to_date: "",
                    payment_status: ""
                }
            },
            isPrinting: false
        }
    },
    mounted() {
        // Initialize dates: from yesterday to today
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

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

        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;

        this.$store.dispatch('orderStatus/lists',{
            order_column: 'id',
            status: statusEnum.ACTIVE
        }).then().catch();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.$store.dispatch("company/lists").then().catch();
        this.$store.dispatch('printer/lists').then().catch();

        // this.$store.dispatch("defaultAccess/show").then(res => {
        //     this.defaultBranch = res.data.data.branch_id;
        //     this.$store.dispatch('backendGlobalState/branchShow', res.data?.data?.branch_id).then().catch();
        // }).catch();
    },
    computed: {
        orders: function () {
            return this.$store.getters['posOrder/lists'];
        },
        customers: function () {
            return this.$store.getters['user/lists'];
        },
        pagination: function () {
            return this.$store.getters['posOrder/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['posOrder/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
        printerShow: function () {
            return this.$store.getters['backendGlobalState/printerShow'];
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printPosOrderModal-print-content'
                }));
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
            // Reset to yesterday to today dates with branch time range
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

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

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.order_serial_no = "";
            this.props.search.status = null;
            this.props.search.excepts = orderTypeEnum.DELIVERY + '|' + orderTypeEnum.TAKEAWAY;
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);
            this.props.search.user_id = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('posOrder/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        destroy: function (id) {
            // Find the order to check if it has transaction data
            const order = this.orders.find(o => o.id === id);
            
            // If order has transaction data, route to details page instead of deleting
            if (order && order.transaction) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: id } });
                alertService.error(this.$t('message.cannot_delete_order_with_transaction'));
                return;
            }

            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('posOrder/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.pos_orders'));
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
            this.$store.dispatch("posOrder/export", this.props.search).then((res) => {
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

        xlsAll: function () {
            this.loading.isActive = true;

            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store.dispatch("posOrder/export", searchParams).then((res) => {
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
        itemDate(param) {
            var date = param.split("T")
            return date[0]
        },
        close() {
            appService.modalHide()
        },
        printInvoice: async function () {
            this.isPrinting = true;
            const element = document.getElementById('print-item');
            if (element) {
                const invoicePrinters = this.kitchenPrinters.filter((p) => p.printer_type == printerTypeEnum.PRINTINVOICE);
                for (let printer of invoicePrinters) {
                    if (printer.printer_method == printerMethodEnum.IP) {
                        await printService.printIPChreyThom(element, 'POS_ORDER_LIST');
                    }
                }
            }
            this.isPrinting = false;
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
