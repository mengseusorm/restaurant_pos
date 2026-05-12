<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.customer_beverage_storage') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <CustomerBeverageStorageCreateComponent :props="props" v-if="permissionChecker('customer_beverage_storage_create')" />
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
                            <th class="db-table-head-th">{{ $t('label.storage_code') }}</th>
                            <th class="db-table-head-th">{{ $t('label.customer_name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.beverage_name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.quantity') }}</th>
                            <th class="db-table-head-th">{{ $t('label.store_date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.expiry_date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.status') }}</th>
                            <th class="db-table-head-th">{{ $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="customerBeverageStorages.length > 0">
                        <tr class="db-table-body-tr" v-for="item in customerBeverageStorages" :key="item.id">
                            <td class="db-table-body-td">
                                <img v-if="item.thumb" :src="item.thumb" alt="beverage" class="w-12 h-12 object-cover rounded">
                                <div v-else class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded">
                                    <i class="lab lab-image text-gray-400 lab-font-size-24"></i>
                                </div>
                            </td>
                            <td class="db-table-body-td">{{ item.storage_code }}</td>
                            <td class="db-table-body-td">
                                <div class="font-medium">{{ item.customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ item.customer_phone }}</div>
                            </td>
                            <td class="db-table-body-td">{{ item.beverage_name }}</td>
                            <td class="db-table-body-td">{{ item.quantity }} {{ item.unit }}</td>
                            <td class="db-table-body-td">{{ formatDate(item.store_date) }}</td>
                            <td class="db-table-body-td">
                                <span :class="expiryClass(item.expiry_date, item.status)">
                                    {{ formatDate(item.expiry_date) }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(item.status)">
                                    {{ enums.customerBeverageStorageStatusEnumArray[item.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmViewComponent :link="'admin.customerBeverageStorage.show'" :id="item.id" v-if="permissionChecker('customer_beverage_storage_show')" />
                                    <SmModalEditComponent @click="edit(item)" v-if="permissionChecker('customer_beverage_storage_edit') && item.status !== enums.customerBeverageStorageStatusEnum.DISPOSED" />
                                    <SmDeleteComponent @click="destroy(item.id)" v-if="permissionChecker('customer_beverage_storage_delete')" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody class="db-table-body" v-else>
                        <tr class="db-table-body-tr">
                            <td class="db-table-body-td text-center" colspan="9">
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
import CustomerBeverageStorageCreateComponent from "./CustomerBeverageStorageCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import customerBeverageStorageStatusEnum from "../../../enums/modules/customerBeverageStorageStatusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import SmViewComponent from "../components/buttons/SmViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "CustomerBeverageStorageListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        CustomerBeverageStorageCreateComponent,
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
                customerBeverageStorageStatusEnum: customerBeverageStorageStatusEnum,
                customerBeverageStorageStatusEnumArray: {
                    [customerBeverageStorageStatusEnum.STORED]: this.$t("label.stored"),
                    [customerBeverageStorageStatusEnum.CLAIMED]: this.$t("label.claimed"),
                    [customerBeverageStorageStatusEnum.EXPIRED]: this.$t("label.expired"),
                    [customerBeverageStorageStatusEnum.DISPOSED]: this.$t("label.disposed")
                }
            },
            statusOptions: [
                { id: customerBeverageStorageStatusEnum.STORED, name: this.$t("label.stored") },
                { id: customerBeverageStorageStatusEnum.CLAIMED, name: this.$t("label.claimed") },
                { id: customerBeverageStorageStatusEnum.EXPIRED, name: this.$t("label.expired") },
                { id: customerBeverageStorageStatusEnum.DISPOSED, name: this.$t("label.disposed") }
            ],
            props: {
                form: {
                    customer_name: "",
                    customer_phone: "",
                    beverage_name: "",
                    quantity: "",
                    original_quantity: "",
                    unit: "bottle",
                    store_date: "",
                    expiry_date: "",
                    status: customerBeverageStorageStatusEnum.STORED,
                    storage_location: "",
                    claimed_date: "",
                    disposed_date: "",
                    disposed_reason: "",
                    notes: "",
                    branch_id: null
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
        customerBeverageStorages: function () {
            return this.$store.getters['customerBeverageStorage/lists'];
        },
        pagination: function () {
            return this.$store.getters['customerBeverageStorage/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['customerBeverageStorage/page'];
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() {
        const currentBranchId = this.defaultAccess?.branch_id || null;

        // Set today's date for store_date
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        this.props.form.branch_id = currentBranchId;
        this.props.form.store_date = `${year}-${month}-${day}`;
        this.props.form.status = customerBeverageStorageStatusEnum.STORED;

        this.list();
    },
    methods: {
        statusClass: function (status) {
            const statusClasses = {
                [customerBeverageStorageStatusEnum.STORED]: 'db-badge-info',
                [customerBeverageStorageStatusEnum.CLAIMED]: 'db-badge-success',
                [customerBeverageStorageStatusEnum.EXPIRED]: 'db-badge-warning',
                [customerBeverageStorageStatusEnum.DISPOSED]: 'db-badge-danger'
            };
            return statusClasses[status] || 'db-badge';
        },
        expiryClass: function (expiryDate, status) {
            if (status !== customerBeverageStorageStatusEnum.STORED) {
                return '';
            }
            const today = new Date();
            const expiry = new Date(expiryDate);
            const daysUntilExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

            if (daysUntilExpiry < 0) {
                return 'text-red-600 font-semibold'; // Expired
            } else if (daysUntilExpiry <= 3) {
                return 'text-orange-600 font-semibold'; // Expiring soon
            }
            return '';
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
                const fromDate = new Date(this.first_date);
                const toDate = new Date(this.last_date);

                const fromYear = fromDate.getFullYear();
                const fromMonth = String(fromDate.getMonth() + 1).padStart(2, '0');
                const fromDay = String(fromDate.getDate()).padStart(2, '0');

                const toYear = toDate.getFullYear();
                const toMonth = String(toDate.getMonth() + 1).padStart(2, '0');
                const toDay = String(toDate.getDate()).padStart(2, '0');

                this.props.search.from_date = `${fromYear}-${fromMonth}-${fromDay}`;
                this.props.search.to_date = `${toYear}-${toMonth}-${toDay}`;
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
            this.$store.dispatch('customerBeverageStorage/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (item) {
            appService.modalShow("#customerBeverageStorageModal");
            this.loading.isActive = true;
            this.$store.dispatch('customerBeverageStorage/edit', item.id);
            this.props.form = {
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                beverage_name: item.beverage_name,
                quantity: item.quantity,
                original_quantity: item.original_quantity,
                unit: item.unit,
                store_date: item.store_date,
                expiry_date: item.expiry_date,
                status: item.status,
                storage_location: item.storage_location,
                claimed_date: item.claimed_date ? item.claimed_date.substring(0, 16) : '',
                disposed_date: item.disposed_date ? item.disposed_date.substring(0, 16) : '',
                disposed_reason: item.disposed_reason,
                notes: item.notes,
                branch_id: item.branch_id
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('customerBeverageStorage/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.customer_beverage_storage'));
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
