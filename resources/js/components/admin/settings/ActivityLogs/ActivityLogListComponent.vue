<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.activity_logs') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
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
                            <label for="searchLogName" class="db-field-title after:hidden">
                                {{ $t('label.log_type') }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchLogName"
                                v-model="props.search.log_name" :options="logTypeOptions" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
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

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3" v-if="authInfo.branch_id === 0">
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
                            <th class="db-table-head-th">{{ $t('label.log_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.description') }}</th>
                            <th class="db-table-head-th">{{ $t('label.user') }}</th>
                            <th class="db-table-head-th">{{ $t('label.subject') }}</th>
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('activity_log_view')">{{
                                $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="activityLogs.length > 0">
                        <tr class="db-table-body-tr" v-for="activityLog in activityLogs" :key="activityLog.id">

                        <td class="db-table-body-td">
                            {{ activityLog.id }}
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-blue-600 bg-blue-100">
                                {{ activityLog.log_name || '-' }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="max-w-xs truncate" :title="activityLog.description">
                                {{ activityLog.description || '-' }}
                            </div>
                        </td>
                        <td class="db-table-body-td">
                            {{ activityLog.causer ? activityLog.causer.name : '-' }}
                        </td>
                        <td class="db-table-body-td">
                            <div v-if="activityLog.subject">
                                <span class="text-xs text-gray-500">{{ activityLog.subject.type }}</span><br>
                                <span class="text-sm">ID: {{ activityLog.subject.id }}</span>
                            </div>
                            <span class="text-gray-400" v-else>-</span>
                        </td>
                        <td class="db-table-body-td">{{ activityLog.created_at }}</td>
                        <td class="db-table-body-td hidden-print" v-if="permissionChecker('activity_log_view')">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmIconViewComponent  :link="'admin.activity.logs.show'" :id="activityLog.id" />
                                <SmIconDeleteComponent @click="destroy(activityLog.id)" v-if="permissionChecker('activity_log_delete')"/>
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

    <!-- Activity Log Details Modal -->
    <div class="modal" id="activityLogModal">
        <div class="modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.activity_log_details') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="hideModal">
                    <i class="lab lab-close"></i>
                </button>
            </div>
            <div class="modal-body">
                    <div v-if="selectedLog" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="db-field-title">{{ $t('label.id') }}</label>
                                <p class="text-sm">{{ selectedLog.id }}</p>
                            </div>
                            <div>
                                <label class="db-field-title">{{ $t('label.log_type') }}</label>
                                <p class="text-sm">{{ selectedLog.log_name || '-' }}</p>
                            </div>
                            <div>
                                <label class="db-field-title">{{ $t('label.event') }}</label>
                                <p class="text-sm">{{ selectedLog.event || '-' }}</p>
                            </div>
                            <div>
                                <label class="db-field-title">{{ $t('label.date') }}</label>
                                <p class="text-sm">{{ selectedLog.created_at }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="db-field-title">{{ $t('label.description') }}</label>
                            <p class="text-sm">{{ selectedLog.description || '-' }}</p>
                        </div>
                        <div v-if="selectedLog.causer">
                            <label class="db-field-title">{{ $t('label.user') }}</label>
                            <p class="text-sm">{{ selectedLog.causer.name }} ({{ selectedLog.causer.email }})</p>
                        </div>
                        <div v-if="selectedLog.subject">
                            <label class="db-field-title">{{ $t('label.subject') }}</label>
                            <p class="text-sm">{{ selectedLog.subject.type }} - ID: {{ selectedLog.subject.id }}</p>
                            <div v-if="selectedLog.subject.name" class="text-sm mt-1">
                                <strong>{{ $t('label.name') }}:</strong> {{ selectedLog.subject.name }}
                            </div>
                        </div>
                        <div v-if="selectedLog.properties && Object.keys(selectedLog.properties).length > 0">
                            <label class="db-field-title">{{ $t('label.properties') }}</label>
                            <div class="bg-gray-50 p-3 rounded text-xs">
                                <pre>{{ JSON.stringify(selectedLog.properties, null, 2) }}</pre>
                            </div>
                        </div>
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
import statusEnum from "../../../../enums/modules/statusEnum";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmIconDeleteComponent from "../../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../../components/buttons/SmIconViewComponent";
import FilterComponent from "../../components/buttons/collapse/FilterComponent";
import ExportComponent from "../../components/buttons/export/ExportComponent";
import PrintComponent from "../../components/buttons/export/PrintComponent";
import displayModeEnum from "../../../../enums/modules/displayModeEnum";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "ActivityLogListComponent",
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
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
            selectedLog: null,
            statusEnum: statusEnum,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.activity_logs"),
            },
            logTypeOptions: [
                { id: 'auth', name: this.$t('label.authentication') },
                { id: 'system', name: this.$t('label.system') },
                { id: 'order', name: this.$t('label.order') },
                { id: 'user', name: this.$t('label.user') },
                { id: 'branch', name: this.$t('label.branch') },
                { id: 'product', name: this.$t('label.product') },
                { id: 'category', name: this.$t('label.category') },
                { id: 'setting', name: this.$t('label.setting') },
            ],
            props: {
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 50,
                    order_column: 'id',
                    order_by: "desc",
                    log_name: null,
                    user_id: null,
                    branch_id: null,
                    start_date: "",
                    end_date: "",
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
        // this.props.search.start_date = startDate.toISOString();
        // this.props.search.end_date = endDate.toISOString();

        this.props.search.start_date = appService.formatDateTime(this.first_date);
        this.props.search.end_date = appService.formatDateTime(this.last_date);
    },
    mounted() {
        this.list();
        this.$store.dispatch('user/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        if (this.authInfo.branch_id === 0) {
            this.$store.dispatch('branch/lists', {
                order_column: 'id',
                order_type: 'asc',
                status: statusEnum.ACTIVE
            });
        }
    },
    computed: {
        activityLogs: function () {
            return this.$store.getters['activityLog/lists'];
        },
        users: function () {
            return this.$store.getters['user/lists'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        pagination: function () {
            return this.$store.getters['activityLog/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['activityLog/page'];
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
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.start_date = appService.formatDateTime(this.first_date);
                this.props.search.end_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.start_date = "";
                this.props.search.end_date = "";
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
            this.props.search.start_date = startDate.toISOString();
            this.props.search.end_date = endDate.toISOString();

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_by = "desc";
            this.props.search.log_name = null;
            this.props.search.user_id = null;
            this.props.search.branch_id = null;
            this.list();
        },
        list: function (page = 1) {
            console.log("Getting data...")
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('activityLog/lists', this.props.search).then(res => {
                this.loading.isActive = false;
                console.log("After getting data", res);
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        show: function (activityLog) {
            this.selectedLog = activityLog;
            appService.modalShow('#activityLogModal');
        },
        hideModal: function () {
            appService.modalHide('#activityLogModal');
            this.selectedLog = null;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('activityLog/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.activity_logs'));
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
    }
}
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}

.max-w-xs {
    max-width: 20rem;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

pre {
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
