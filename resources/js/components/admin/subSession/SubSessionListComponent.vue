<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.massage_sessions') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
                <SubSessionCreateComponent :props="props" v-if="permissionChecker('massage_sessions_create')" />
            </div>
        </div>
        <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchFromDate" class="db-field-title after:hidden">{{ $t('label.from_date') }}</label>
                        <Datepicker id="searchFromDate" autoApply v-model="first_date" />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchToDate" class="db-field-title after:hidden">{{ $t('label.to_date') }}</label>
                        <Datepicker id="searchToDate" autoApply v-model="last_date" />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchGuestName" class="db-field-title after:hidden">{{ $t('label.guest_name')
                            }}</label>
                        <input id="searchGuestName" v-model="props.search.guest_name" type="text"
                            class="db-field-control" :placeholder="$t('label.guest_name')" />
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchSessionStatus" class="db-field-title after:hidden">{{ $t('label.status')
                            }}</label>
                        <select id="searchSessionStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="waiting">{{ $t('label.waiting') }}</option>
                            <option value="in_service">{{ $t('label.in_service') }}</option>
                            <option value="done">{{ $t('label.done') }}</option>
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

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">#</th>
                        <th class="db-table-head-th">{{ $t('label.guest_name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.phone') }}</th>
                        <th class="db-table-head-th">{{ $t('label.start_time') }}</th>
                        <th class="db-table-head-th">{{ $t('label.end_time') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.payment_status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="sessions.length > 0">
                    <tr class="db-table-body-tr" v-for="session in sessions" :key="session.id">
                        <td class="db-table-body-td">#{{ session.id }}</td>
                        <td class="db-table-body-td">{{ session.guest_name || '-' }}</td>
                        <td class="db-table-body-td">{{ session.phone || '-' }}</td>
                        <td class="db-table-body-td">{{ session.start_time || '-' }}</td>
                        <td class="db-table-body-td">{{ session.end_time || '-' }}</td>
                        <td class="db-table-body-td">
                            <span class="session-status-badge" :style="sessionStatusStyle(session.status)">{{ statusLabel(session.status) }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <span v-if="session.is_checked_out && session.order_payment_status != null"
                                :class="Number(session.order_payment_status) === 5 ? 'bg-green-100 text-green-700 border-green-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300'"
                                class="text-xs font-semibold px-2 py-0.5 rounded-full border inline-block">
                                {{ Number(session.order_payment_status) === 5 ? $t('label.paid') : $t('label.unpaid') }}
                            </span>
                            <span v-else class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5">
                                <button
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-blue-600 border-blue-600"
                                    @click="$router.push({ name: 'admin.group-session.detail', params: { id: session?.group_session_id } })">
                                    <i class="lab lab-eye-line mr-0.5"></i>{{ $t('label.view_detail') }}
                                </button>
                                <button
                                    v-if="session.status === 'waiting' && permissionChecker('massage_sessions_edit')"
                                    type="button" class="db-btn-outline py-1 px-2 text-xs text-blue-600 border-blue-600"
                                    @click="start(session.id)">{{ $t('button.start') }}</button>
                                <button
                                    v-if="session.status === 'in_service' && permissionChecker('massage_sessions_edit')"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-green-600 border-green-600"
                                    @click="complete(session.id)">{{ $t('button.complete') }}</button>
                                <button
                                    v-if="canCheckoutSession(session)"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-purple-600 border-purple-400"
                                    @click="checkout(session.id)">
                                    <i class="lab lab-price-tag mr-0.5"></i>{{ $t('button.checkout') }}
                                </button>
                                <button
                                    v-if="hasUnpaidOrder(session)"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-orange-600 border-orange-400"
                                    @click="goToOrder(session.resolved_order_id)">
                                    <i class="lab lab-price-tag mr-0.5"></i>{{ $t('button.checkout') }}
                                </button>
                                <SmModalEditComponent @click="edit(session)"
                                    v-if="permissionChecker('massage_sessions_edit')" />
                                <SmDeleteComponent @click="destroy(session.id)"
                                    v-if="permissionChecker('massage_sessions_delete')" />
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
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import SubSessionCreateComponent from "./SubSessionCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
export default {
    name: "SubSessionListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox,
        PaginationTextComponent, SubSessionCreateComponent, LoadingComponent,
        SmDeleteComponent, SmModalEditComponent, FilterComponent, Datepicker
    },
    data() {
        return {
            loading: { isActive: false },
            first_date: null,
            last_date: null,
            props: {
                form: { id: '', group_session_id: '', guest_name: '', phone: '', status: 'waiting', notes: '', share_group_bill: false },
                search: {
                    paginate: 1, page: 1, per_page: 25,
                    order_column: 'id', order_type: 'desc',
                    status: '',
                    guest_name: '',
                    from_date: '',
                    to_date: '',
                },
            },
        };
    },
    computed: {
        defaultAccess() { return this.$store.getters['defaultAccess/show']; },
        sessions() { return this.$store.getters['subSession/lists'] ?? []; },
        pagination() { return this.$store.getters['subSession/pagination'] ?? {}; },
        paginationPage() { return this.$store.getters['subSession/page'] ?? {}; },
        branch() { return this.$store.getters['backendGlobalState/branchShow']; },
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        this.$store.dispatch('groupSession/lists', { paginate: 0 });
        this.$store.dispatch('defaultAccess/show').then((res) => {
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then().catch();
        }).catch();
        this.resetDateRangeByBranch();
        this.list();
    },
    methods: {
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
            this.$store.dispatch('subSession/lists', this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
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
            this.props.search.guest_name = '';
            this.props.search.page = 1;
            this.resetDateRangeByBranch();
            this.list(1);
        },
        edit(session) {
            appService.modalShow('#subSessionModal');
            this.$store.dispatch('subSession/edit', session.id);
            this.props.form = {
                id: session.id,
                group_session_id: session.group_session_id,
                guest_name: session.guest_name,
                phone: session.phone || '',
                status: session.status,
                notes: session.notes || '',
                share_group_bill: session.share_group_bill || false,
            };
        },
        start(id) {
            appService.confirmDialog(this.$t('message.start_session_question'), '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('subSession/start', { id, search: this.props.search }).then(() => {
                    this.loading.isActive = false;
                    this.list(this.props.search.page);
                    alertService.success(this.$t('message.massage_session_start'));
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
            }).catch(() => {});
        },
        complete(id) {
            appService.confirmDialog(this.$t('message.complete_session_question'), '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('subSession/complete', { id, search: this.props.search }).then(() => {
                    this.loading.isActive = false;
                    this.list(this.props.search.page);
                    alertService.success(this.$t('message.massage_session_complete'));
                }).catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
            }).catch(() => {});
        },
        checkout(id) {
            appService.confirmDialog(this.$t('message.checkout_guest_question') || 'Checkout this guest?', '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('subSession/checkout', { id }).then((res) => {
                    alertService.success(this.$t('message.checkout_success'));
                    const order = res.data?.data?.order;
                    if (order?.id) {
                        this.$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } });
                    } else {
                        this.list(this.props.search.page);
                    }
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => {
                    this.loading.isActive = false;
                });
            }).catch(() => {});
        },
        destroy(id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('subSession/destroy', { id, search: this.props.search }).then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.massage_sessions'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => { this.loading.isActive = false; });
        },
        permissionChecker(permission) { return appService.permissionChecker(permission); },
        sessionStatusStyle(status) {
            const map = {
                waiting:     { color: '#F6A609', backgroundColor: '#FFEEC6' },
                in_service:  { color: '#008BBA', backgroundColor: '#BDEFFF' },
                done:        { color: '#2AC769', backgroundColor: '#CBFFE0' },
                checked_out: { color: '#7C3AED', backgroundColor: '#EDE9FE' },
            };
            return map[status] || { color: '#FB4E4E', backgroundColor: '#FFDADA' };
        },
        statusLabel(status) {
            const map = {
                waiting:     this.$t('label.waiting'),
                in_service:  this.$t('label.in_progress'),
                done:        this.$t('label.done'),
                checked_out: this.$t('label.checked_out'),
            };
            return map[status] || status;
        },
        canCheckoutSession(session) {
            return session.status === 'done'
                && !session.is_checked_out
                && this.permissionChecker('massage_sessions_edit');
        },
        hasUnpaidOrder(session) {
            return !!session.is_checked_out
                && !!session.resolved_order_id
                && Number(session.order_payment_status) !== 5;
        },
        goToOrder(orderId) {
            if (!orderId) return;
            this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
        },
    },
};
</script>

<style scoped>
.session-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 10px;
    font-family: 'Rubik', sans-serif;
    line-height: 1.6;
    text-transform: capitalize;
    white-space: nowrap;
}
</style>
