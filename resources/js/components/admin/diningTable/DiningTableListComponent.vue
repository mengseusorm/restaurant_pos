<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.dining_tables') }}</h3>
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
                    <router-link :to="{ name: 'admin.floorPlan' }" class="db-btn py-2 text-white bg-purple-600 hover:bg-purple-700">
                        <i class="lab lab-floor-plan lab-font-size-16"></i>
                        <span>{{ $t('label.floor_plan') }}</span>
                    </router-link>

                    <DiningTableCreateComponent :props="props" />
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title after:hidden">{{ $t('label.name') }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="size" class="db-field-title after:hidden">{{ $t('label.size') }}</label>
                            <input id="size" v-on:keypress="numberOnly($event)" v-model="props.search.size" type="text" class="db-field-control" />
                        </div>

                        <!-- <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="floor_plan_group_id" class="db-field-title after:hidden">{{ $t('label.floor_plan_group') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="floor_plan_group_id"
                                v-model="props.search.floor_plan_group_id"
                                :options="floorPlanGroups"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            />
                        </div> -->

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="searchStatus"
                                v-model="props.search.status"
                                :options="[
                                    { id: enums.statusEnum.ACTIVE, name: $t('label.active') },
                                    { id: enums.statusEnum.INACTIVE, name: $t('label.inactive') },
                                ]"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            />
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
                <table class="db-table stripe" id="print">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.image') }}</th>
                            <th class="db-table-head-th">{{ $t('label.size') }}</th>
                            <th class="db-table-head-th">{{ $t('label.floor_plan_group') }}</th>
                            <th class="db-table-head-th">{{ $t('label.status') }}</th>
                            <th class="db-table-head-th hidden-print" v-if="permissionChecker('dining_tables_show') || permissionChecker('dining_tables_edit') || permissionChecker('dining_tables_delete')">
                                {{ $t('label.action') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="diningTables.length > 0">
                        <tr class="db-table-body-tr" v-for="diningTable in diningTables" :key="diningTable">
                            <td class="db-table-body-td">{{ diningTable.name }}</td>
                            <td class="db-table-body-td">
                                <div class="flex justify-center">
                                    <img
                                        v-if="diningTable.table_thumb || diningTable.table_photo"
                                        :src="diningTable.table_thumb || diningTable.table_photo"
                                        :alt="diningTable.name"
                                        :title="`${$t('label.click_to_view')} ${diningTable.name}`"
                                        class="w-12 h-12 object-cover rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                        @click="showImageModal(diningTable)"
                                        @error="handleImageError"
                                    />
                                    <div v-else class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center" :title="`${$t('label.no_image')} - ${diningTable.name}`">
                                        <i class="lab lab-image text-gray-400 text-lg"></i>
                                    </div>
                                </div>
                            </td>
                            <td class="db-table-body-td">{{ diningTable.size }}</td>
                            <td class="db-table-body-td">
                                <span v-if="diningTable.floor_plan_group">{{ diningTable.floor_plan_group.name }}</span>
                                <span v-else class="text-gray-500">{{ $t('label.no_group') }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(diningTable.status)">
                                    {{ enums.statusEnumArray[diningTable.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print" v-if="permissionChecker('dining_tables_show') || permissionChecker('dining_tables_edit') || permissionChecker('dining_tables_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconQrCodeComponent v-if="diningTable.qr" :link="diningTable.qr" />
                                    <SmIconViewComponent :link="'admin.diningTable.show'" :id="diningTable.id" v-if="permissionChecker('dining_tables_show')" />
                                    <SmIconSidebarModalEditComponent @click="edit(diningTable)" v-if="permissionChecker('dining_tables_edit')" />
                                    <SmIconDeleteComponent @click="destroy(diningTable.id)" v-if="permissionChecker('dining_tables_delete') && demoChecker(diningTable?.id)" />
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

    <!-- Image Modal -->
    <div v-if="imageModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" @click="closeImageModal">
        <div class="relative max-w-4xl max-h-[90vh] p-4">
            <button @click="closeImageModal" class="absolute top-2 right-2 text-white text-2xl hover:text-gray-300 z-10">
                <i class="lab lab-cross-line-2"></i>
            </button>
            <img :src="imageModal.url" :alt="imageModal.title" class="max-w-full max-h-full object-contain rounded-lg shadow-lg" @click.stop />
            <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white px-3 py-1 rounded">
                {{ imageModal.title }}
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from '../components/LoadingComponent';
import DiningTableCreateComponent from './DiningTableCreateComponent';
import alertService from '../../../services/alertService';
import PaginationTextComponent from '../components/pagination/PaginationTextComponent';
import PaginationBox from '../components/pagination/PaginationBox';
import PaginationSMBox from '../components/pagination/PaginationSMBox';
import appService from '../../../services/appService';
import statusEnum from '../../../enums/modules/statusEnum';
import TableLimitComponent from '../components/TableLimitComponent';
import SmIconDeleteComponent from '../components/buttons/SmIconDeleteComponent';
import SmIconSidebarModalEditComponent from '../components/buttons/SmIconSidebarModalEditComponent';
import SmIconQrCodeComponent from '../components/buttons/SmIconQrCodeComponent';
import SmIconViewComponent from '../components/buttons/SmIconViewComponent';
import ExportComponent from '../components/buttons/export/ExportComponent';
import PrintComponent from '../components/buttons/export/PrintComponent';
import ExcelComponent from '../components/buttons/export/ExcelComponent';
import FilterComponent from '../components/buttons/collapse/FilterComponent';
import ENV from '../../../config/env';

export default {
    name: 'DiningTableListComponent',
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        DiningTableCreateComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconSidebarModalEditComponent,
        SmIconQrCodeComponent,
        SmIconViewComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        FilterComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            printLoading: true,
            printObj: {
                id: 'print',
                popTitle: this.$t('menu.dining_tables'),
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t('label.active'),
                    [statusEnum.INACTIVE]: this.$t('label.inactive'),
                },
            },
            props: {
                form: {
                    branch_id: null,
                    name: '',
                    size: '',
                    status: statusEnum.ACTIVE,
                    floor_plan_group_id: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    name: '',
                    size: '',
                    floor_plan_group_id: null,
                    status: null,
                },
            },
            imageModal: {
                show: false,
                url: '',
                title: '',
            },
            demo: ENV.DEMO,
        };
    },
    computed: {
        diningTables: function () {
            return this.$store.getters['diningTable/lists'];
        },
        pagination: function () {
            return this.$store.getters['diningTable/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['diningTable/page'];
        },
        floorPlanGroups: function () {
            return this.$store.getters['floorPlan/groups'];
        },
    },
    mounted() {
        this.list();
        this.$store
            .dispatch('floorPlan/loadGroups')
            .then()
            .catch((err) => {
                console.error('Error loading floor plan groups:', err);
            });

        // Add keyboard event listener for ESC key
        document.addEventListener('keydown', this.handleKeydown);
    },
    beforeUnmount() {
        // Remove keyboard event listener
        document.removeEventListener('keydown', this.handleKeydown);
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        demoChecker: function (tableId) {
            return ((this.demo === 'true' || this.demo === 'TRUE' || this.demo === '1' || this.demo === 1) && tableId !== 1 && tableId !== 2) || this.demo === 'false' || this.demo === 'FALSE' || this.demo === '';
        },
        numberOnly: function (e) {
            return appService.floatNumber(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        handleImageError: function (event) {
            // Replace broken image with placeholder
            event.target.style.display = 'none';
            const parent = event.target.parentElement;
            parent.innerHTML = '<div class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center"><i class="lab lab-image text-gray-400 text-lg"></i></div>';
        },
        showImageModal: function (diningTable) {
            if (diningTable.table_photo || diningTable.table_thumb) {
                this.imageModal = {
                    show: true,
                    url: diningTable.table_photo || diningTable.table_thumb,
                    title: diningTable.name,
                };
            }
        },
        closeImageModal: function () {
            this.imageModal = {
                show: false,
                url: '',
                title: '',
            };
        },
        handleKeydown: function (event) {
            if (event.key === 'Escape' && this.imageModal.show) {
                this.closeImageModal();
            }
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch('diningTable/lists', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    console.log(err);
                    this.loading.isActive = false;
                });
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = '';
            this.props.search.size = '';
            this.props.search.floor_plan_group_id = null;
            this.props.search.status = null;
            this.list();
        },
        edit: function (diningTable) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch('diningTable/edit', diningTable.id);
            this.props.form = {
                branch_id: diningTable.branch_id,
                name: diningTable.name,
                size: diningTable.size,
                floor_plan_group_id: diningTable.floor_plan_group_id,
                status: diningTable.status,
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch('diningTable/destroy', { id: id, search: this.props.search })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t('menu.dining_tables'));
                            })
                            .catch((err) => { 
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    console.log(err);
                    this.loading.isActive = false;
                });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('diningTable/export', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t('menu.dining_tables');
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    console.log(err);
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
    },
};
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}

/* Ensure consistent table row height */
.db-table-body-tr {
    min-height: 60px;
}

/* Image modal animations */
.fixed.inset-0 {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Image hover effects */
.db-table-body-td img:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
}
</style>
