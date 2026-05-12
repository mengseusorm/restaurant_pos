<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.beds') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
                <BedCreateComponent :props="props" v-if="permissionChecker('rooms_create')" />
            </div>
        </div>

        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchBedName" class="db-field-title after:hidden">{{ $t('label.name') }}</label>
                        <input id="searchBedName" v-model="props.search.name" type="text" class="db-field-control">
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchBedStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                        <select id="searchBedStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="available">{{ $t('label.available') }}</option>
                            <option value="occupied">{{ $t('label.occupied') }}</option>
                            <option value="cleaning">{{ $t('label.cleaning') }}</option>
                        </select>
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchBedRoom" class="db-field-title after:hidden">{{ $t('label.room') }}</label>
                        <select id="searchBedRoom" v-model="props.search.room_id" class="db-field-control">
                            <option value="">-- {{ $t('label.room') }} --</option>
                            <option v-for="room in allRooms" :key="room.id" :value="room.id">{{ room.name }}</option>
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
                        <th class="db-table-head-th">{{ $t('label.no') }}</th>
                        <th class="db-table-head-th">{{ $t('label.name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.room') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="beds.length > 0">
                    <tr class="db-table-body-tr" v-for="(bed, index) in beds" :key="bed.id">
                        <td class="db-table-body-td">{{ index + 1 }}</td>
                        <td class="db-table-body-td font-medium">{{ bed.name }}</td>
                        <td class="db-table-body-td">{{ bed.room ? bed.room.name : '-' }}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(bed.status)">{{ bed.status }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5">
                                <SmModalEditComponent @click="edit(bed)" v-if="permissionChecker('rooms_edit')" />
                                <SmDeleteComponent @click="destroy(bed.id)" v-if="permissionChecker('rooms_delete')" />
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
import BedCreateComponent from "./BedCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";

export default {
    name: "BedListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox,
        PaginationTextComponent, BedCreateComponent, LoadingComponent,
        SmDeleteComponent, SmModalEditComponent, FilterComponent,
    },
    data() {
        return {
            loading: { isActive: false },
            props: {
                form: { branch_id: this.authBranch || '', room_id: '', name: '', status: 'available' },
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: 'id', order_type: 'asc',
                    name: '', status: '', room_id: '', branch_id: this.authBranch || '',
                },
            },
        };
    },
    computed: {
        beds()          { return this.$store.getters['bed/lists'] ?? []; },
        pagination()    { return this.$store.getters['bed/pagination'] ?? {}; },
        paginationPage(){ return this.$store.getters['bed/page'] ?? {}; },
        allRooms()      { return this.$store.getters['room/lists'] ?? []; },
        authBranch()    { return this.$store.getters.authBranchId; },
    },
    mounted() {
        this.list();
        this.$store.dispatch('room/lists', { paginate: 0 });
        // Auto-select branch for search and form
        if (!this.props.form.branch_id && this.authBranch) {
            this.props.form.branch_id = this.authBranch;
        }
        if (!this.props.search.branch_id && this.authBranch) {
            this.props.search.branch_id = this.authBranch;
        }
    },
    methods: {
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('bed/lists', this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        },
        search() { this.list(1); },
        clear() {
            this.props.search.name    = '';
            this.props.search.status  = '';
            this.props.search.room_id = '';
            this.props.search.branch_id = this.authBranch || '';
            this.list(1);
        },
        edit(bed) {
            appService.modalShow('#bedModal');
            this.$store.dispatch('bed/edit', bed.id);
            this.props.form = { branch_id: bed.branch_id || this.authBranch || '', room_id: bed.room_id, name: bed.name, status: bed.status };
        },
        destroy(id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('bed/destroy', { id, search: this.props.search }).then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.beds'));
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
        permissionChecker(permission) {
            return appService.permissionChecker(permission);
        },
        statusClass(status) {
            const map = {
                available: 'db-badge-success',
                occupied:  'db-badge-warning',
                cleaning:  'db-badge-info',
            };
            return map[status] || 'db-badge';
        },
    },
};
</script>
