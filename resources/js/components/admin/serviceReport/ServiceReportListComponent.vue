<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.service_report') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label class="db-field-title after:hidden">{{ $t('label.from_date') }}</label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label class="db-field-title after:hidden">{{ $t('label.to_date') }}</label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="therapist_id" class="db-field-title after:hidden">{{ $t('label.therapist') }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="therapist_id"
                                v-model="props.search.therapist_id" :options="therapistOptions" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="therapist_code" class="db-field-title after:hidden">
                                {{ $t('label.therapist') }} {{ $t('label.code') }}
                            </label>
                            <input id="therapist_code" v-model="props.search.therapist_code" type="text"
                                class="db-field-control">
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
                            <th class="db-table-head-th text-xs">No.</th>
                            <th class="db-table-head-th">{{ $t('label.therapist') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_order') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_customer') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_hours') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="reports.length > 0">
                        <tr class="db-table-body-tr" v-for="(report, index) in reports" :key="report.therapist_id || 'not-assigned'">
                            <td class="db-table-body-td">{{ index + 1 }}</td>
                            <td class="db-table-body-td">{{ report.therapist_name || 'N/A' }}</td>
                            <td class="db-table-body-td">{{ report.total_orders }}</td>
                            <td class="db-table-body-td">{{ report.total_customers }}</td>
                            <td class="db-table-body-td">{{ report.total_hours }}</td>
                            <td class="db-table-body-td">{{ formatRevenue(report.total_revenue) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="db-table-foot border-t" v-if="reports.length > 0">
                        <tr class="db-table-foot-tr font-bold">
                            <td class="db-table-body-td" colspan="2">{{ $t('label.total') }}</td>
                            <td class="db-table-body-td">{{ sumField('total_orders') }}</td>
                            <td class="db-table-body-td">{{ sumField('total_customers') }}</td>
                            <td class="db-table-body-td">{{ sumField('total_hours').toFixed(2) }}</td>
                            <td class="db-table-body-td">{{ formatRevenue(sumField('total_revenue')) }}</td>
                        </tr>
                    </tfoot>
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
import TableLimitComponent from "../components/TableLimitComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import PdfComponent from "../components/buttons/export/PdfComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "ServiceReportListComponent",
    components: {
        LoadingComponent,
        PaginationTextComponent,
        PaginationBox,
        PaginationSMBox,
        TableLimitComponent,
        FilterComponent,
        ExportComponent,
        ExcelComponent,
        PdfComponent,
        Datepicker,
    },
    data() {
        return {
            loading: { isActive: false },
            first_date: null,
            last_date: null,
            props: {
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 25,
                    order_column: 'total_revenue',
                    order_type: 'desc',
                    from_date: '',
                    to_date: '',
                    therapist_id: null,
                    therapist_code: '',
                },
            },
        };
    },
    mounted() {
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

        if (this.branch && this.branch.open_time) {
            const [h, m] = this.branch.open_time.split(':');
            startDate.setHours(parseInt(h), parseInt(m), 0, 0);
        } else {
            startDate.setHours(0, 0, 0, 0);
        }
        if (this.branch && this.branch.close_time) {
            const [h, m] = this.branch.close_time.split(':');
            endDate.setHours(parseInt(h), parseInt(m), 59, 999);
        } else {
            endDate.setHours(23, 59, 59, 999);
        }

        this.first_date = startDate;
        this.last_date = endDate;
        this.props.search.from_date = appService.formatDateTime(this.first_date);
        this.props.search.to_date = appService.formatDateTime(this.last_date);

        this.list();
        this.$store.dispatch('therapistProfile/lists', {
            paginate: 0,
            order_column: 'id',
            order_type: 'asc',
        }).then().catch();

        this.$store.dispatch('defaultAccess/show').then((res) => {
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then().catch();
        }).catch();
    },
    computed: {
        reports() {
            return this.$store.getters['serviceReport/lists'];
        },
        pagination() {
            return this.$store.getters['serviceReport/pagination'];
        },
        paginationPage() {
            return this.$store.getters['serviceReport/page'];
        },
        direction() {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch() {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        currencySymbol() {
            return this.branch?.currency_id?.symbol || '';
        },
        therapistOptions() {
            return (this.$store.getters['therapistProfile/lists'] || [])
                .filter((therapist) => therapist && typeof therapist.user_id !== 'undefined')
                .map((therapist) => ({
                    id: therapist.user_id,
                    name: therapist.user?.name || '-',
                }));
        },
    },
    methods: {
        sumField(field) {
            return this.reports.reduce((acc, r) => acc + parseFloat(r[field] || 0), 0);
        },
        formatRevenue(amount) {
            const value = parseFloat(amount || 0);
            const formattedValue = Number.isFinite(value) ? value.toFixed(2) : '0.00';

            return this.currencySymbol ? `${formattedValue}${this.currencySymbol}` : formattedValue;
        },
        search() {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = '';
                this.props.search.to_date = '';
            }
            this.list(1);
        },
        clear() {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

            if (this.branch && this.branch.open_time) {
                const [h, m] = this.branch.open_time.split(':');
                startDate.setHours(parseInt(h), parseInt(m), 0, 0);
            } else {
                startDate.setHours(0, 0, 0, 0);
            }
            if (this.branch && this.branch.close_time) {
                const [h, m] = this.branch.close_time.split(':');
                endDate.setHours(parseInt(h), parseInt(m), 59, 999);
            } else {
                endDate.setHours(23, 59, 59, 999);
            }

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);
            this.props.search.therapist_id = null;
            this.props.search.therapist_code = '';
            this.props.search.page = 1;
            this.list();
        },
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('serviceReport/lists', this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => {
                this.loading.isActive = false;
            });
        },
        xls() {
            this.loading.isActive = true;
            this.$store.dispatch('serviceReport/export', this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t('menu.service_report');
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message);
            });
        },
        xlsAll() {
            const searchParams = { ...this.props.search, paginate: 1, per_page: 99999999 };
            this.loading.isActive = true;
            this.$store.dispatch('serviceReport/export', searchParams).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t('menu.service_report');
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message);
            });
        },
        pdf() {
            this.loading.isActive = true;
            this.$store.dispatch('serviceReport/pdf', this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t('menu.service_report') + '.pdf';
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message);
            });
        },
    },
};
</script>
