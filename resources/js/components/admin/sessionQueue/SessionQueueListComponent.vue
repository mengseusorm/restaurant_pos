<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.session_queue') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
            </div>
        </div>

        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchQueueStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                        <select id="searchQueueStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="waiting">{{ $t('label.waiting') }}</option>
                            <option value="called">{{ $t('label.called') }}</option>
                            <option value="seated">{{ $t('label.seated') }}</option>
                            <option value="cancelled">{{ $t('label.cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchCustomerName" class="db-field-title after:hidden">{{ $t('label.customer_name') }}</label>
                        <input id="searchCustomerName" v-model="props.search.customer_name" type="text" class="db-field-control">
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
                        <th class="db-table-head-th">{{ $t('label.customer_name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.phone') }}</th>
                        <th class="db-table-head-th">{{ $t('label.preferred_room') }}</th>
                        <th class="db-table-head-th">{{ $t('label.preferred_therapist') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="queues.length > 0">
                    <tr class="db-table-body-tr" v-for="queue in queues" :key="queue.id">
                        <td class="db-table-body-td">{{ queue.position }}</td>
                        <td class="db-table-body-td">{{ queue.customer_name }}</td>
                        <td class="db-table-body-td">{{ queue.customer_phone || '-' }}</td>
                        <td class="db-table-body-td">{{ queue.room ? queue.room.name : '-' }}</td>
                        <td class="db-table-body-td">{{ queue.therapist ? queue.therapist.name : '-' }}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(queue.status)">{{ queue.status }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5">
                                <button
                                    v-if="queue.status === 'waiting' && permissionChecker('session_queue_edit')"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-blue-600 border-blue-600"
                                    @click="call(queue.id)"
                                >{{ $t('button.call') }}</button>
                                <button
                                    v-if="queue.status === 'called' && permissionChecker('session_queue_edit')"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-green-600 border-green-600"
                                    @click="seat(queue.id)"
                                >{{ $t('button.seat') }}</button>
                                <button
                                    v-if="['waiting','called'].includes(queue.status) && permissionChecker('session_queue_edit')"
                                    type="button"
                                    class="db-btn-outline py-1 px-2 text-xs text-red-600 border-red-600"
                                    @click="cancel(queue.id)"
                                >{{ $t('button.cancel') }}</button>
                                <SmDeleteComponent @click="destroy(queue.id)" v-if="permissionChecker('session_queue_delete')" />
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
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";

export default {
    name: "SessionQueueListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox,
        PaginationTextComponent, LoadingComponent,
        SmDeleteComponent, FilterComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            props: {
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: 'id', order_type: 'desc',
                    status: '',
                    customer_name: '',
                },
            },
        };
    },
    computed: {
        queues()        { return this.$store.getters['sessionQueue/lists'] ?? []; },
        pagination()    { return this.$store.getters['sessionQueue/pagination'] ?? {}; },
        paginationPage(){ return this.$store.getters['sessionQueue/page'] ?? {}; },
    },
    mounted() { this.list(); },
    methods: {
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('sessionQueue/lists', this.props.search)
                .then(() => { this.loading.isActive = false; })
                .catch(() => { this.loading.isActive = false; });
        },
        search() { this.list(1); },
        clear() {
            this.props.search.status        = '';
            this.props.search.customer_name = '';
            this.list(1);
        },
        call(id) {
            this.loading.isActive = true;
            this.$store.dispatch('sessionQueue/call', { id, search: this.props.search })
                .then(() => {
                    this.loading.isActive = false;
                    alertService.success(this.$t('message.customer_called'));
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
        },
        seat(id) {
            this.loading.isActive = true;
            this.$store.dispatch('sessionQueue/seat', { id, search: this.props.search })
                .then(() => {
                    this.loading.isActive = false;
                    alertService.success(this.$t('message.customer_seated'));
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
        },
        cancel(id) {
            appService.confirmDialog(this.$t('message.queue_cancel_question'), '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('sessionQueue/cancel', { id, search: this.props.search })
                    .then(() => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.queue_cancelled'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response?.data?.message);
                    });
            }).catch(() => {});
        },
        destroy(id) {
            appService.confirmDialog(this.$t('message.are_you_sure'), '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('sessionQueue/destroy', { id, search: this.props.search })
                    .then(() => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.deleted'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response?.data?.message);
                    });
            }).catch(() => {});
        },
        permissionChecker(permission) {
            return appService.permissionChecker(permission);
        },
        statusClass(status) {
            const map = {
                waiting:   'db-badge-warning',
                called:    'db-badge-info',
                seated:    'db-badge-success',
                cancelled: 'db-badge-danger',
            };
            return map[status] || 'db-badge';
        },
    },
};
</script>
