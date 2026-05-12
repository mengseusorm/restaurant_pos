<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.therapist_profiles') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
                <TherapistProfileCreateComponent :props="props" v-if="permissionChecker('therapist_profiles_create')" />
            </div>
        </div>

        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchTherapistStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                        <select id="searchTherapistStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="available">{{ $t('label.available') }}</option>
                            <option value="busy">{{ $t('label.busy') }}</option>
                            <option value="away">{{ $t('label.away') }}</option>
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
                        <th class="db-table-head-th">{{ $t('label.code') }}</th>
                        <th class="db-table-head-th">{{ $t('label.name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.phone') }}</th>
                        <th class="db-table-head-th">{{ $t('label.commission_rate') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="therapists.length > 0">
                    <tr class="db-table-body-tr" v-for="(therapist, index) in therapists" :key="therapist.id">
                        <td class="db-table-body-td">{{ index + 1 }}</td>
                        <td class="db-table-body-td">{{ therapist.code || '-' }}</td>
                        <td class="db-table-body-td">{{ therapist.user ? therapist.user.name : '-' }}</td>
                        <td class="db-table-body-td">{{ therapist.user ? therapist.user.phone : '-' }}</td>
                        <td class="db-table-body-td">{{ therapist.commission_rate }}%</td>
                        <td class="db-table-body-td">
                            <span class="therapist-status-badge" :style="statusStyle(therapist.status)">{{ statusLabel(therapist.status) }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5">
                                <SmViewComponent v-if="permissionChecker('therapist_profiles_show')" :link="'admin.employees.show'" :id="therapist.user_id" />
                                <SmModalEditComponent @click="edit(therapist)" v-if="permissionChecker('therapist_profiles_edit')" />
                                <SmDeleteComponent @click="destroy(therapist.id)" v-if="permissionChecker('therapist_profiles_delete')" />
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
import TherapistProfileCreateComponent from "./TherapistProfileCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import SmViewComponent from "../components/buttons/SmViewComponent";

export default {
    name: "TherapistProfileListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox,
        PaginationTextComponent, TherapistProfileCreateComponent, LoadingComponent,
        SmDeleteComponent, SmModalEditComponent, FilterComponent,SmViewComponent
    },
    data() {
        return {
            loading: { isActive: false },
            props: {
                form: { branch_id: 0, user_id: '', code: '', verify_code: '', commission_rate: 0, status: 'available' },
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: 'id', order_type: 'asc',
                    status: '',
                },
            },
        };
    },
    computed: {
        therapists()    { return this.$store.getters['therapistProfile/lists'] ?? []; },
        pagination()    { return this.$store.getters['therapistProfile/pagination'] ?? {}; },
        paginationPage(){ return this.$store.getters['therapistProfile/page'] ?? {}; },
    },
    mounted() { this.list(); },
    methods: {
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('therapistProfile/lists', this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        },
        search() { this.list(1); },
        clear() {
            this.props.search.status = '';
            this.list(1);
        },
        edit(therapist) {
            appService.modalShow('#therapistProfileModal');
            this.$store.dispatch('therapistProfile/edit', therapist.id);
            this.props.form = {
                name:            therapist.user?.name || '',
                email:           therapist.user?.email || '',
                phone:           therapist.user?.phone || '',
                country_code:    therapist.user?.country_code || '',
                password:        '',
                password_confirmation: '',
                branch_id:       therapist.branch_id,
                user_id:         therapist.user_id,
                code:            therapist.code || '',
                verify_code:     therapist.verify_code || '',
                commission_rate: therapist.commission_rate,
                status:          therapist.status,
            };
        },
        destroy(id) {
            appService.destroyConfirmation().then(() => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('therapistProfile/destroy', { id, search: this.props.search }).then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.therapist_profiles'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch(() => { this.loading.isActive = false; });
        },
        permissionChecker(permission) { return appService.permissionChecker(permission); },
        statusStyle(status) {
            const map = {
                available: { color: '#2AC769', backgroundColor: '#CBFFE0' },
                busy:      { color: '#F6A609', backgroundColor: '#FFEEC6' },
                away:      { color: '#FB4E4E', backgroundColor: '#FFDADA' },
            };
            return map[status] || { color: '#008BBA', backgroundColor: '#BDEFFF' };
        },
        statusLabel(status) {
            const map = { available: 'Available', busy: 'Busy', away: 'Away' };
            return map[status] || status;
        },
    },
};
</script>

<style scoped>
.therapist-status-badge {
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
