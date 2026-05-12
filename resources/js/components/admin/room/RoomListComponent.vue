<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.rooms') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <FilterComponent />
                <RoomCreateComponent :props="props" v-if="permissionChecker('rooms_create')" />
            </div>
        </div> 
        <div class="table-filter-div">
            <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                <div class="row">
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchName" class="db-field-title after:hidden">{{ $t('label.name') }}</label>
                        <input id="searchName" v-model="props.search.name" type="text" class="db-field-control">
                    </div>
                    <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                        <label for="searchStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                        <select id="searchStatus" v-model="props.search.status" class="db-field-control">
                            <option value="">--</option>
                            <option value="available">{{ $t('label.available') }}</option>
                            <option value="occupied">{{ $t('label.occupied') }}</option>
                            <option value="cleaning">{{ $t('label.cleaning') }}</option>
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
                        <th class="db-table-head-th">{{ $t('label.qr_code_token') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th text-center">{{ $t('label.no') }}. {{ $t('label.beds') }}</th>
                        <th class="db-table-head-th">{{ $t('label.beds') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="rooms.length > 0">
                    <template v-for="(room, index) in rooms" :key="room.id">
                    <tr class="db-table-body-tr">
                        <td class="db-table-body-td">{{ index + 1 }}</td>
                        <td class="db-table-body-td">{{ room.name }}</td>
                        <td class="db-table-body-td">
                            <span class="text-xs font-mono">{{ room.qr_code_token }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="room-status-badge" :style="statusStyle(room.status)">{{ statusLabel(room.status) }}</span>
                        </td>
                        <td class="db-table-body-td text-center font-medium">
                            {{ (bedsByRoom[room.id] || []).length }}
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex flex-wrap gap-1">
                                <span v-for="bed in (bedsByRoom[room.id] || [])" :key="bed.id"
                                    class="text-xs rounded-full px-2 py-0.5 bg-blue-100 text-blue-700 whitespace-nowrap">
                                    {{ bed.name }}
                                </span>
                                <span v-if="!(bedsByRoom[room.id] || []).length" class="text-xs text-gray-400">—</span>
                            </div>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center gap-1.5 flex-wrap">
                                <button v-if="permissionChecker('rooms_edit')"
                                    class="db-btn-outline sm primary m-0.5"
                                    @click="toggleQuickAddBed(room.id)"
                                    :title="$t('button.add_bed')">
                                    <i class="lab lab-add-line"></i>
                                    <span>{{ $t('button.add_bed') }}</span>
                                </button>
                                <button class="db-btn-outline sm primary m-0.5" @click="toggleRoomBeds(room)" :title="$t('label.manage_beds')">
                                    <i class="lab lab-setting-line"></i>
                                    <span>{{ $t('label.beds') }}</span>
                                </button>
                                <SmModalEditComponent @click="edit(room)" v-if="permissionChecker('rooms_edit')" />
                                <SmDeleteComponent @click="destroy(room.id)" v-if="permissionChecker('rooms_delete')" />
                            </div>
                        </td>
                    </tr>
                    <!-- Quick Add Bed row -->
                    <tr v-if="quickAddBedRoomId === room.id" class="db-table-body-tr bg-indigo-50">
                        <td colspan="7" class="db-table-body-td py-3 px-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <input v-model="quickAddBedName" type="text"
                                    class="db-field-control text-sm py-1 max-w-xs"
                                    :class="quickAddBedErrors.name ? 'invalid' : ''"
                                    :placeholder="$t('label.name') + ' *'" />
                                <button type="button"
                                    class="db-btn py-1.5 px-3 text-sm text-white bg-primary"
                                    :disabled="quickAddBedLoading"
                                    @click="saveQuickAddBed(room)">
                                    <i class="lab lab-save mr-0.5"></i>
                                    {{ quickAddBedLoading ? $t('button.loading') : $t('button.save') }}
                                </button>
                                <button type="button"
                                    class="db-btn py-1.5 px-3 text-sm text-white bg-gray-500"
                                    @click="toggleQuickAddBed(room.id)">
                                    {{ $t('button.cancel') }}
                                </button>
                            </div>
                            <small v-if="quickAddBedErrors.name" class="db-field-alert">{{ quickAddBedErrors.name[0] }}</small>
                        </td>
                    </tr>
                    <!-- Expandable beds panel -->
                    <tr v-if="expandedRoom === room.id" class="db-table-body-tr bg-gray-50">
                        <td colspan="7" class="db-table-body-td py-3 px-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-gray-600">{{ $t('label.beds_in_room') }}: {{ room.name }}</span>
                            </div>
                            <div v-if="(bedsByRoom[room.id] || []).length > 0" class="flex flex-wrap gap-2">
                                <div v-for="bed in (bedsByRoom[room.id] || [])" :key="bed.id"
                                    class="flex items-center gap-2 border rounded-lg px-3 py-1.5 text-sm bg-white">
                                    <span class="font-medium">{{ bed.name }}</span>
                                    <span :class="statusClass(bed.status)" class="text-xs">{{ bed.status }}</span>
                                    <button v-if="permissionChecker('rooms_delete')" class="text-red-400 hover:text-red-600 ml-1" @click="destroyBed(bed.id, room.id)">
                                        <i class="lab lab-delete-line text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-xs text-gray-400">{{ $t('label.no_beds_found') }}</p>
                        </td>
                    </tr>
                    </template>
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
import RoomCreateComponent from "./RoomCreateComponent";
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
    name: "RoomListComponent",
    components: {
        TableLimitComponent, PaginationSMBox, PaginationBox,
        PaginationTextComponent, RoomCreateComponent, LoadingComponent,
        SmDeleteComponent, SmModalEditComponent, FilterComponent,
    },
    data() {
        return {
            loading:            { isActive: false },
            expandedRoom:       null,
            allBeds:            [],
            quickAddBedRoomId:  null,
            quickAddBedName:    '',
            quickAddBedLoading: false,
            quickAddBedErrors:  {},
            props: {
                form: { name: '', status: 'available', branch_id: null },
                search: {
                    paginate: 1, page: 1, per_page: 10,
                    order_column: 'id', order_type: 'asc',
                    name: '', status: '',
                },
            },
        };
    },
    computed: {
        rooms()         { return this.$store.getters['room/lists'] ?? []; },
        pagination()    { return this.$store.getters['room/pagination'] ?? {}; },
        paginationPage(){ return this.$store.getters['room/page'] ?? {}; },
        bedsByRoom() {
            const map = {};
            this.allBeds.forEach(b => {
                if (!map[b.room_id]) map[b.room_id] = [];
                map[b.room_id].push(b);
            });
            return map;
        },
    },
    mounted() {
        this.list();
        this.loadAllBeds();
    },
    methods: {
        list(page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('room/lists', this.props.search).then(() => {
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        },
        search() { this.list(1); },
        clear() {
            this.props.search.name   = '';
            this.props.search.status = '';
            this.list(1);
        },
        edit(room) {
            appService.modalShow('#roomModal');
            this.$store.dispatch('room/edit', room.id);
            this.props.form = { name: room.name, status: room.status, branch_id: room.branch_id ?? null };
        },
        destroy(id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('room/destroy', { id, search: this.props.search }).then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.rooms'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        loadAllBeds() {
            this.$store.dispatch('bed/lists', { paginate: 0, vuex: false }).then((res) => {
                this.allBeds = res.data.data || [];
            });
        },
        toggleRoomBeds(room) {
            this.expandedRoom = this.expandedRoom === room.id ? null : room.id;
        },
        toggleQuickAddBed(roomId) {
            this.quickAddBedRoomId = this.quickAddBedRoomId === roomId ? null : roomId;
            this.quickAddBedName  = '';
            this.quickAddBedErrors = {};
        },
        saveQuickAddBed(room) {
            this.quickAddBedErrors = {};
            if (!this.quickAddBedName.trim()) {
                this.quickAddBedErrors = { name: [this.$t('label.name') + ' required'] };
                return;
            }
            this.quickAddBedLoading = true;
            this.$store.dispatch('bed/save', {
                form: {
                    room_id:   room.id,
                    name:      this.quickAddBedName.trim(),
                    status:    'available',
                    branch_id: room.branch_id || '',
                },
                search: { paginate: 0, vuex: false },
            }).then(() => {
                this.quickAddBedRoomId = null;
                this.quickAddBedName   = '';
                this.loadAllBeds();
                alertService.success(this.$t('message.bed_created'));
            }).catch((err) => {
                this.quickAddBedErrors = err.response?.data?.errors ?? {};
            }).finally(() => { this.quickAddBedLoading = false; });
        },
        destroyBed(bedId, roomId) {
            appService.confirmDialog(this.$t('message.bed_delete'), '', 'question').then(() => {
                this.$store.dispatch('bed/destroy', { id: bedId, search: { paginate: 0, vuex: false } }).then(() => {
                    this.loadAllBeds();
                    alertService.success(this.$t('message.bed_delete'));
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                });
            }).catch(() => {});
        },
        permissionChecker(permission) {
            return appService.permissionChecker(permission);
        },
        statusStyle(status) {
            const map = {
                available: { color: '#2AC769', backgroundColor: '#CBFFE0' },
                occupied:  { color: '#F6A609', backgroundColor: '#FFEEC6' },
                cleaning:  { color: '#008BBA', backgroundColor: '#BDEFFF' },
            };
            return map[status] || { color: '#FB4E4E', backgroundColor: '#FFDADA' };
        },
        statusLabel(status) {
            const map = { available: 'Available', occupied: 'Occupied', cleaning: 'Cleaning' };
            return map[status] || status;
        },
        statusClass(status) {
            const map = {
                available: 'text-green-600 bg-green-50 rounded px-1',
                occupied:  'text-yellow-600 bg-yellow-50 rounded px-1',
                cleaning:  'text-blue-600 bg-blue-50 rounded px-1',
            };
            return map[status] || 'text-gray-500';
        },
    },
};
</script>

<style scoped>
.room-status-badge {
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
