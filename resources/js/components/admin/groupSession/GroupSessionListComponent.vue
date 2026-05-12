<template>
    <LoadingComponent :props="loading" />

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.group_sessions') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
            </div>
        </div>

        <!-- Search Filter -->
        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="gsFromDate" class="db-field-title after:hidden">{{ $t('label.from_date') }}</label>
                        <Datepicker id="gsFromDate" autoApply v-model="first_date" />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="gsToDate" class="db-field-title after:hidden">{{ $t('label.to_date') }}</label>
                        <Datepicker id="gsToDate" autoApply v-model="last_date" />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="gsSearchStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                        <select id="gsSearchStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="open">{{ $t('label.open') }}</option>
                            <option value="in_service">{{ $t('label.in_service') }}</option>
                            <option value="in_progress">{{ $t('label.in_progress') }}</option>
                            <option value="completed">{{ $t('label.completed') }}</option>
                            <option value="cancelled">{{ $t('label.cancelled') }}</option>

                        </select>
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

        <!-- Table -->
        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">{{ $t('label.id') }}</th>
                        <th class="db-table-head-th">{{ $t('label.code') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.members') }}</th>
                        <th class="db-table-head-th">{{ $t('label.notes') }}</th>
                        <th class="db-table-head-th">{{ $t('label.total') }}</th>
                        <th class="db-table-head-th">{{ $t('label.arrival_time') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="groups.length > 0">
                    <tr class="db-table-body-tr" v-for="group in groups" :key="group.id">
                        <td class="db-table-body-td font-medium">#{{ group.id }}</td>
                        <td class="db-table-body-td font-mono text-xs">{{ group.code || '-' }}</td>
                        <td class="db-table-body-td">
                            <span class="text-xs rounded-full px-2 py-0.5 capitalize" :class="statusBadge(group.status)">
                                {{ group.status }}
                            </span>
                        </td>
                        <td class="db-table-body-td">{{ group.total_guests ?? 0 }}</td>
                        <td class="db-table-body-td">{{ group.notes || '-' }}</td>
                        <td class="db-table-body-td font-semibold">{{ formatPrice(group.total_amount) }}</td>
                        <!-- <td class="db-table-body-td">{{ formatDateTime(group.arrival_time) }}</td> -->
                        <td class="db-table-body-td">{{ group.arrival_time }}</td>

                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5"> 
                                <SmViewComponent @click="viewDetail(group.id)" />
                                <SmDeleteComponent v-if="permissionChecker('massage_sessions_delete') && group.status === 'open'" @click="destroy(group.id)"/> 
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody class="db-table-body" v-else>
                    <tr class="db-table-body-tr">
                        <td class="db-table-body-td text-center py-8 text-gray-400" colspan="8">
                            {{ $t('label.no_data') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmViewComponent from "../components/buttons/SmViewComponent.vue";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent.vue";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "GroupSessionListComponent",
    components: {
        TableLimitComponent,
        LoadingComponent,
        PaginationTextComponent,
        PaginationBox,
        PaginationSMBox,
        SmViewComponent,
        SmDeleteComponent,
        FilterComponent,
        Datepicker
    },
    data() {
        return {
            loading:       { isActive: false },
            createLoading: false,
            createForm:    { notes: '' },
            first_date: null,
            last_date: null,
            props: {
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: 'id', order_type: 'desc',
                    status: '',
                    from_date: '',
                    to_date: '',
                },
            },
        };
    },
    computed: {
        groups()        { return this.$store.getters['groupSession/lists'] ?? []; },
        pagination()    { return this.$store.getters['groupSession/pagination'] ?? {}; },
        paginationPage(){ return this.$store.getters['groupSession/page'] ?? {}; },
        currentBranchId() {
            const branch = this.$store.getters['backendGlobalState/branchShow'];
            return branch?.id || this.$store.getters.authBranchId || 0;
        },
        setting() { return this.$store.getters['frontendSetting/lists']; },
        branch()  { return this.$store.getters['backendGlobalState/branchShow']; },
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        this.$store.dispatch('defaultAccess/show').then((res) => {
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then().catch();
            this.resetDateRangeByBranch();
            this.list();
        }).catch(() => {
            this.resetDateRangeByBranch();
            this.list();
        });
    },
    methods: {
        permissionChecker(permission) { return appService.permissionChecker(permission); },
        resetDateRangeByBranch() {
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
        },
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('groupSession/lists', this.props.search)
                .finally(() => { this.loading.isActive = false; });
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
            this.props.search.status = '';
            this.props.search.page = 1;
            this.resetDateRangeByBranch();
            this.list(1);
        },
        viewDetail(id) {
            this.$router.push({ name: 'admin.group-session.detail', params: { id } });
        },
        openCreateModal() {
            this.createForm = { notes: '' };
            appService.modalShow('#groupSessionCreateModal');
        },
        closeCreateModal() {
            appService.modalHide('#groupSessionCreateModal');
        }, 
        destroy(id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('groupSession/destroy', { id, search: this.props.search })
                        .then(() => {
                            this.loading.isActive = false;
                            alertService.successFlip(null, this.$t('menu.group_sessions'));
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            alertService.error(err.response?.data?.message);
                        });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                }
            }).catch((err) => { this.loading.isActive = false; });
        },
        formatDateTime(dt) {
            if (!dt) return '—';
            return new Date(dt).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
        },
        formatPrice(val) {
            return appService.currencyFormat(
                parseFloat(val) || 0,
                this.setting?.site_digit_after_decimal_point,
                this.branch?.currency_id?.symbol,
                this.setting?.site_currency_position
            );
        },
        statusBadge(status) {
            const map = {
                open:        'bg-blue-100 text-blue-700',
                in_progress: 'bg-indigo-100 text-indigo-700',
                completed:   'bg-green-100 text-green-700',
                cancelled:   'bg-red-100 text-red-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },
    },
};
</script>
