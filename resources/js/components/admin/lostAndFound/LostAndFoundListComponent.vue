<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.lost_and_found') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <LostAndFoundCreateComponent :props="props" v-if="permissionChecker('lost_and_found_create')" />
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date" :enableTimePicker="false" :key="'from-' + dateOnlyPickerKey" :format="dateOnlyPickerFormat"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date" :enableTimePicker="false" :key="'to-' + dateOnlyPickerKey" :format="dateOnlyPickerFormat"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="statusOptions" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t('button.search') }}</span>
                                </button>
                                <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-circle-line lab-font-size-16"></i>
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
                            <th class="db-table-head-th">{{ $t('label.image') }}</th>
                            <th class="db-table-head-th">{{ $t('label.item_name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.found_date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.found_location') }}</th>
                            <th class="db-table-head-th">{{ $t('label.found_by') }}</th>
                            <th class="db-table-head-th">{{ $t('label.status') }}</th>
                            <th class="db-table-head-th">{{ $t('label.action') }}</th>
                        </tr>
                    </thead>
                <tbody class="db-table-body" v-if="lostAndFounds.length > 0">
                    <tr class="db-table-body-tr" v-for="item in lostAndFounds" :key="item.id">
                        <td class="db-table-body-td">
                            <img v-if="item.thumb" :src="item.thumb" alt="item" class="w-12 h-12 object-cover rounded">
                            <div v-else class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded">
                                <i class="lab lab-image text-gray-400 lab-font-size-24"></i>
                            </div>
                        </td>
                        <td class="db-table-body-td">{{ item.item_name }}</td>
                        <td class="db-table-body-td">{{ formatDate(item.found_date) }}</td>
                        <td class="db-table-body-td">{{ item.found_location }}</td>
                        <td class="db-table-body-td">{{ item.found_by || '-' }}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(item.status)">
                                {{ enums.lostAndFoundStatusEnumArray[item.status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmViewComponent :link="'admin.lostAndFound.show'" :id="item.id" v-if="permissionChecker('lost_and_found_show')" />
                                <SmModalEditComponent @click="edit(item)" v-if="permissionChecker('lost_and_found_edit') && item.status !== enums.lostAndFoundStatusEnum.DISPOSED" />

                                <SmDeleteComponent @click="destroy(item.id)" v-if="permissionChecker('lost_and_found_delete')" />
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody class="db-table-body" v-else>
                    <tr class="db-table-body-tr">
                        <td class="db-table-body-td text-center" colspan="7">
                            <div class="py-8 text-gray-500">
                                {{ $t('message.no_data_found') }}
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
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import LostAndFoundCreateComponent from "./LostAndFoundCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import lostAndFoundStatusEnum from "../../../enums/modules/lostAndFoundStatusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import SmViewComponent from "../components/buttons/SmViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "LostAndFoundListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LostAndFoundCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        SmViewComponent,
        FilterComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
            enums: {
                lostAndFoundStatusEnum: lostAndFoundStatusEnum,
                lostAndFoundStatusEnumArray: {
                    [lostAndFoundStatusEnum.FOUND]: this.$t("label.found"),
                    [lostAndFoundStatusEnum.CLAIMED]: this.$t("label.claimed"),
                    [lostAndFoundStatusEnum.DISPOSED]: this.$t("label.disposed")
                }
            },
            statusOptions: [
                { id: lostAndFoundStatusEnum.FOUND, name: this.$t("label.found") },
                { id: lostAndFoundStatusEnum.CLAIMED, name: this.$t("label.claimed") },
                { id: lostAndFoundStatusEnum.DISPOSED, name: this.$t("label.disposed") }
            ],
            props: {
                form: {
                    item_name: "",
                    found_date: "",
                    found_by: "",
                    found_location: "",
                    customer_name: "",
                    customer_phone: "",
                    customer_email: "",
                    status: lostAndFoundStatusEnum.FOUND,
                    claimed_by: "",
                    claimed_date: "",
                    notes: "",
                    branch_id: null,
                    storage_location: "",
                    disposal_date: ""
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    from_date: null,
                    to_date: null,
                    status: null
                }
            },
        }
    },
    computed: {
        setting() {
            return this.$store.getters['frontendSetting/lists'] ?? {};
        },
        phpDateFormat() {
            return this.setting.site_date_format || 'd/m/Y';
        },
        phpTimeFormat() {
            return this.setting.site_time_format || 'h:i A';
        },
        datePickerFormat() {
            return appService.datepickerDateTimeFormat(this.phpDateFormat, this.phpTimeFormat);
        },
        datePickerKey() {
            return `${this.datePickerFormat}-${this.isTimePicker24Hour}`;
        },
        dateOnlyPickerFormat() {
            return appService.phpDateToDatepickerFormat(this.phpDateFormat);
        },
        dateOnlyPickerKey() {
            return this.dateOnlyPickerFormat;
        },
        isTimePicker24Hour() {
            return appService.is24HourTimeFormat(this.phpTimeFormat);
        },
        lostAndFounds: function () {
            return this.$store.getters['lostAndFound/lists'];
        },
        pagination: function () {
            return this.$store.getters['lostAndFound/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['lostAndFound/page'];
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        const currentBranchId = this.defaultAccess?.branch_id || null;
        this.props.form.branch_id = currentBranchId;
        this.list();
    },
    methods: {
        statusClass: function (status) {
            const statusClasses = {
                [lostAndFoundStatusEnum.FOUND]: 'db-badge-info',
                [lostAndFoundStatusEnum.CLAIMED]: 'db-badge-success',
                [lostAndFoundStatusEnum.DISPOSED]: 'db-badge-danger'
            };
            return statusClasses[status] || 'db-badge';
        },
        formatDate: function (date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString();
        },
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateByPattern(this.first_date, 'Y-m-d');
                this.props.search.to_date = appService.formatDateByPattern(this.last_date, 'Y-m-d');
            } else {
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
            this.list();
        },
        clear: function () {
            this.first_date = null;
            this.last_date = null;
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.per_page = 10;
            this.props.search.order_column = 'id';
            this.props.search.order_type = 'desc';
            this.props.search.from_date = null;
            this.props.search.to_date = null;
            this.props.search.status = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('lostAndFound/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (item) {
            appService.modalShow("#lostAndFoundModal");
            this.loading.isActive = true;
            this.$store.dispatch('lostAndFound/edit', item.id);
            this.props.form = {
                item_name: item.item_name,
                found_date: item.found_date,
                found_by: item.found_by,
                found_location: item.found_location,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_email: item.customer_email,
                status: item.status,
                claimed_by: item.claimed_by,
                claimed_date: item.claimed_date ? item.claimed_date.substring(0, 16) : '',
                notes: item.notes,
                branch_id: item.branch_id,
                storage_location: item.storage_location,
                disposal_date: item.disposal_date
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('lostAndFound/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.lost_and_found'));
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
        permissionChecker(e) {
            return appService.permissionChecker(e);
        }
    }
}
</script>
