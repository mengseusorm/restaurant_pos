<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="mb-4 p-4">
                <div class="statistics mb-6 flex flex-wrap gap-4 justify-between items-center">
                    <div class="flex gap-4">
                        <div class="stat-item">
                            <p class="text-sm text-gray-500">{{ $t("label.total_guests") }}: {{ diningtables.reduce((sum, table) => sum + (table.current_order_id ? table.size : 0), 0) }}</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-sm text-gray-500">{{ $t("label.occupied_tables") }}: {{ diningtables.filter(table => table.current_order_id).length }}</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-sm text-gray-500">{{ $t("label.non_occupied_tables") }}: {{ diningtables.filter(table => !table.current_order_id).length }}</p>
                        </div>
                    </div>
                    <div>
                        <button
                            @click="openCombineOrderModal"
                            class="db-btn py-2 px-4 text-white bg-primary hover:bg-primary-dark"
                            :disabled="unpaidOrders.length < 2"
                            :class="{ 'opacity-50 cursor-not-allowed': unpaidOrders.length < 2 }"
                        >
                            <i class="lab lab-merge mr-2"></i>
                            {{ $t("button.combine_order") }}
                        </button>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="mb-4">
                    <div class="relative">
                        <input
                            id="dining-table-search-input"
                            type="text"
                            v-model="searchQuery"
                            :placeholder="$t('label.search_tables')"
                            class="w-full h-10 px-4 pl-10 text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="lab lab-search-normal text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Keyboard Shortcuts -->
                <KeyboardShortcutsComponent v-model="searchQuery" input-id="dining-table-search-input" />

                <!-- Table List as grid Section -->
                <div class="dining-tables grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                    <div
                        v-for="(table, index) in filteredTables"
                        :key="index"
                        :class="[
                            'dining-table-card border-2 p-4 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer transform hover:-translate-y-1',
                            table.current_order_id
                                ? 'border-orange-300 bg-orange-50 hover:bg-orange-100'
                                : 'border-green-400 bg-green-50 hover:bg-green-100'
                        ]"
                        style="min-width: 180px;"
                    >
                        <div class="text-center mb-3">
                            <h3 class="text-lg font-bold text-gray-800 mb-1">{{ table.name }}</h3>
                            <p class="text-xs text-gray-600">{{ table.size }} {{ $t("label.seats") }}</p>
                            <div class="mt-1">
                                <span :class="[
                                    'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                                    table.current_order_id
                                        ? 'bg-orange-200 text-orange-800'
                                        : 'bg-green-200 text-green-800'
                                ]">
                                    {{ table.current_order_id ? $t('label.occupied') : $t('label.available') }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <button v-if="table.current_order_id" class="table-btn warning w-full" @click.stop="showOrderDiningTable(table.current_order_id)">
                                <i class="lab lab-view mr-2"></i>
                                {{ $t("label.view_order") }}
                            </button>
                            <button
                                v-if="table.current_order_id"
                                class="table-btn danger w-full"
                                @click.stop="!isOrderPaid(table) && releaseDiningTable(table)"
                                :disabled="isOrderPaid(table)"
                                :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isOrderPaid(table) }"
                            >
                                <i class="lab lab-trash mr-2"></i>
                                {{ $t("label.release_table") }}
                            </button>
                            <button v-if="!table.current_order_id" class="table-btn success w-full" @click.stop="createOrderForTable(table.id)">
                                <i class="lab lab-plus mr-2"></i>
                                {{ $t("button.open_table") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Combine Order Modal -->
    <div id="combineOrderModal" class="modal">
        <div class="modal-dialog max-w-7xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.combine_order") }}</h3>
                <button type="button" class="modal-close" @click="closeCombineOrderModal">
                    <i class="lab lab-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Display selected tables/orders summary at the top -->
                <div v-if="sourceOrders.length > 0 || targetOrder" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="sourceOrders.length > 0">
                            <h4 class="font-semibold text-gray-700 mb-2">
                                <i class="lab lab-source mr-1"></i>
                                {{ $t("label.selected_source_orders") }} ({{ sourceOrders.length }})
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="order in sourceOrders"
                                    :key="order.order_id"
                                    class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium"
                                >
                                    Order #{{ order.order_serial_no }}
                                    <span v-if="order.table_names" class="ml-1">({{ order.table_names }})</span>
                                    <button
                                        @click="removeSourceOrder(order.order_id)"
                                        class="ml-2 hover:text-orange-900"
                                    >
                                        <i class="lab lab-close text-xs"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div v-if="targetOrder">
                            <h4 class="font-semibold text-gray-700 mb-2">
                                <i class="lab lab-target mr-1"></i>
                                {{ $t("label.target_order") }}
                            </h4>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    Order #{{ targetOrder.order_serial_no }}
                                    <span v-if="targetOrder.table_names" class="ml-1">({{ targetOrder.table_names }})</span>
                                    <button
                                        @click="targetOrder = null"
                                        class="ml-2 hover:text-green-900"
                                    >
                                        <i class="lab lab-close text-xs"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Side: Source Orders (Select Multiple) -->
                    <div class="border border-gray-300 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="lab lab-source mr-2 text-orange-600"></i>
                            {{ $t("label.select_orders_to_combine") }}
                            <span class="ml-2 text-sm text-gray-500">({{ $t("label.select_multiple") }})</span>
                        </h4>

                        <!-- Search Input for Source Orders -->
                        <div class="mb-4">
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="sourceOrdersSearch"
                                    :placeholder="$t('label.search_by_order_or_table')"
                                    class="w-full h-10 px-4 pl-10 text-sm rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-colors"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="lab lab-search-normal text-gray-400"></i>
                                </div>
                                <button
                                    v-if="sourceOrdersSearch"
                                    @click="sourceOrdersSearch = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <i class="lab lab-close text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <div
                                v-for="order in availableSourceOrders"
                                :key="order.order_id"
                                @click="toggleSourceOrder(order)"
                                :class="[
                                    'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200',
                                    isSourceSelected(order.order_id)
                                        ? 'border-orange-500 bg-orange-50'
                                        : 'border-gray-300 hover:border-orange-300 hover:bg-orange-50'
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <input
                                                type="checkbox"
                                                :checked="isSourceSelected(order.order_id)"
                                                class="mr-3 h-5 w-5 text-orange-600 rounded"
                                                @click.stop="toggleSourceOrder(order)"
                                            />
                                            <div>
                                                <h5 class="font-bold text-gray-900">Order #{{ order.order_serial_no }}</h5>
                                                <p class="text-xs text-gray-500 mt-1">{{ order.items_count }} items · ${{ formatPrice(order.order_total) }}</p>
                                                <!-- Display dining tables as badges -->
                                                <div class="flex flex-wrap gap-1 mt-2" v-if="order.dining_tables && order.dining_tables.length > 0">
                                                    <span
                                                        v-for="(orderDining, idx) in order.dining_tables"
                                                        :key="idx"
                                                        class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full"
                                                    >
                                                        <i class="lab lab-table text-xs mr-1"></i>{{ orderDining.dining_table?.name || 'Table' }}
                                                    </span>
                                                </div>
                                                <div v-else class="mt-2">
                                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                        No Table
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="availableSourceOrders.length === 0" class="text-center py-8 text-gray-500">
                                <i class="lab lab-empty text-4xl mb-2"></i>
                                <p>{{ $t("message.no_available_orders") }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Target Order (Select One) -->
                    <div class="border border-gray-300 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="lab lab-target mr-2 text-green-600"></i>
                            {{ $t("label.select_target_order") }}
                            <span class="ml-2 text-sm text-gray-500">({{ $t("label.select_one") }})</span>
                        </h4>

                        <!-- Search Input for Target Orders -->
                        <div class="mb-4">
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="targetOrdersSearch"
                                    :placeholder="$t('label.search_by_order_or_table')"
                                    class="w-full h-10 px-4 pl-10 text-sm rounded-lg border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="lab lab-search-normal text-gray-400"></i>
                                </div>
                                <button
                                    v-if="targetOrdersSearch"
                                    @click="targetOrdersSearch = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <i class="lab lab-close text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <div
                                v-for="order in availableTargetOrders"
                                :key="order.order_id"
                                @click="selectTargetOrder(order)"
                                :class="[
                                    'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200',
                                    targetOrder && targetOrder.order_id === order.order_id
                                        ? 'border-green-500 bg-green-50'
                                        : 'border-gray-300 hover:border-green-300 hover:bg-green-50'
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <input
                                                type="radio"
                                                :checked="targetOrder && targetOrder.order_id === order.order_id"
                                                class="mr-3 h-5 w-5 text-green-600"
                                                @click.stop="selectTargetOrder(order)"
                                            />
                                            <div>
                                                <h5 class="font-bold text-gray-900">Order #{{ order.order_serial_no }}</h5>
                                                <p class="text-xs text-gray-500 mt-1">{{ order.items_count }} items · ${{ formatPrice(order.order_total) }}</p>
                                                <!-- Display dining tables as badges -->
                                                <div class="flex flex-wrap gap-1 mt-2" v-if="order.dining_tables && order.dining_tables.length > 0">
                                                    <span
                                                        v-for="(orderDining, idx) in order.dining_tables"
                                                        :key="idx"
                                                        class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full"
                                                    >
                                                        <i class="lab lab-table text-xs mr-1"></i>{{ orderDining.dining_table?.name || 'Table' }}
                                                    </span>
                                                </div>
                                                <div v-else class="mt-2">
                                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                        No Table
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="availableTargetOrders.length === 0" class="text-center py-8 text-gray-500">
                                <i class="lab lab-empty text-4xl mb-2"></i>
                                <p>{{ $t("message.no_available_orders") }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validation Messages -->
                <div v-if="validationError" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <i class="lab lab-warning mr-2"></i>
                    {{ validationError }}
                </div>
            </div>
            <div class="modal-footer">
                <div class="flex-1 text-left">
                    <button
                        type="button"
                        class="db-btn-outline py-2 px-4"
                        @click="closeCombineOrderModal"
                    >
                        {{ $t("button.cancel") }}
                    </button>
                    <button
                        type="button"
                        class="db-btn py-2 px-4 ms-3 text-white bg-primary"
                        @click="confirmCombineOrders"
                        :disabled="!canCombine"
                        :class="{ 'opacity-50 cursor-not-allowed': !canCombine }"
                    >
                        {{ $t("button.combine") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { ref } from 'vue';
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import alertService from "../../../services/alertService";
import LoadingComponent from "../../admin/components/LoadingComponent.vue";
import KeyboardShortcutsComponent from "../components/KeyboardShortcutsComponent.vue";
import 'swiper/css';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { routes } from "vue-router/auto-routes";
import { tableOrder } from '../../../store/modules/tableOrder';

export default {
    name: 'PosOrderListComponent',
    components: {
        LoadingComponent,
        SwiperSlide,
        Swiper,
        KeyboardShortcutsComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            searchQuery: "",
            sourceOrdersSearch: "",
            targetOrdersSearch: "",
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props: {
                form: {
                    name: "",
                    email: "",
                    phone: "",
                    password: "",
                    password_confirmation: "",
                    country_code: "",
                    status: statusEnum.ACTIVE,
                }
            },
            errors: {},
            flag: "",
            country_code: "",
            // Combine Order Modal Data
            sourceOrders: [],
            targetOrder: null,
            validationError: "",
            paymentStatusEnum: paymentStatusEnum,
        }
    },
    computed: {
        diningtables: function () {
            return this.$store.getters["diningTable/lists"];
        },
        unpaidOrders: function () {
            return this.$store.getters["posOrderUnpaid/lists"];
        },
        filteredTables: function () {
            if (!this.searchQuery.trim()) {
                return this.diningtables;
            }
            const query = this.searchQuery.toLowerCase();
            return this.diningtables.filter(table =>
                table.name.toLowerCase().includes(query)
            );
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        occupiedTables: function () {
            return this.diningtables.filter(table => table.current_order_id);
        },
        availableSourceOrders: function () {
            const targetId = this.targetOrder ? this.targetOrder.order_id : null;

            // Filter unpaid orders, excluding the target order
            let filteredOrders = this.unpaidOrders
                .filter(order => order.id !== targetId)
                .map(order => ({
                    id: order.id,
                    order_id: order.id,
                    order_serial_no: order.order_serial_no,
                    items_count: order.order_items?.length || 0,
                    order_total: order.total || 0,
                    dining_tables: order.order_dinings || [],
                    table_names: (order.order_dinings || []).map(od => od.dining_table?.name).filter(Boolean).join(', ') || 'No Table'
                }));

            // Apply search filter
            if (this.sourceOrdersSearch.trim()) {
                const query = this.sourceOrdersSearch.toLowerCase();
                filteredOrders = filteredOrders.filter(order =>
                    order.order_serial_no.toString().includes(query) ||
                    order.table_names.toLowerCase().includes(query)
                );
            }

            return filteredOrders;
        },
        availableTargetOrders: function () {
            const sourceIds = this.sourceOrders.map(order => order.order_id);

            // Filter unpaid orders, excluding source orders
            let filteredOrders = this.unpaidOrders
                .filter(order => !sourceIds.includes(order.id))
                .map(order => ({
                    id: order.id,
                    order_id: order.id,
                    order_serial_no: order.order_serial_no,
                    items_count: order.order_items?.length || 0,
                    order_total: order.total || 0,
                    dining_tables: order.order_dinings || [],
                    table_names: (order.order_dinings || []).map(od => od.dining_table?.name).filter(Boolean).join(', ') || 'No Table'
                }));

            // Apply search filter
            if (this.targetOrdersSearch.trim()) {
                const query = this.targetOrdersSearch.toLowerCase();
                filteredOrders = filteredOrders.filter(order =>
                    order.order_serial_no.toString().includes(query) ||
                    order.table_names.toLowerCase().includes(query)
                );
            }

            return filteredOrders;
        },
        canCombine: function () {
            return this.sourceOrders.length > 0 && this.targetOrder !== null;
        },
    },
    mounted() {
        this.$store.dispatch("diningTable/lists", {
            order_column: "id",
            order_type: "asc",
        });
        this.fetchUnpaidOrders();
    },
    methods: {
        fetchUnpaidOrders: function () {
            this.loading.isActive = true;
            this.$store.dispatch('posOrderUnpaid/lists', {
                paginate: 1
            }).then(() => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
                console.error('Error fetching unpaid orders:', err);
            });
        },
        selectDiningTable: function () {
            // alert('hello')
        },
        createOrderForTable: function (tableId) {
            // Check if open_table_confirm is ACTIVE
            if (this.branch.open_table_confirm === statusEnum.ACTIVE) {
                // Show confirmation before creating order for table
                appService.confirmDialog(
                    this.$t('message.create_order_for_table_title'),
                    this.$t('message.create_order_for_table_confirm_message'),
                    "info",
                    this.$t('label.yes'),
                    this.$t('label.no')
                ).then(() => {
                    // Route to POS component with table_id as query parameter
                    this.$router.push({ name: 'admin.pos', query: { table_id: tableId } });
                }).catch(() => {
                    // User cancelled, do nothing
                });
            } else {
                // Route directly without confirmation
                this.$router.push({ name: 'admin.pos', query: { table_id: tableId } });
            }
        },
        showOrderDiningTable: function (orderId) {
            if (orderId) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
                this.reset();
            }
        },
        isOrderPaid: function(table) {
            const checkOrderUnpaid = table.orders?.payment_status;
            if(checkOrderUnpaid === paymentStatusEnum.PAID) {
                return false;
            } else {
                return true;
            }
        },
        releaseDiningTable: function (table) {
            if (this.isOrderPaid(table)) {
                alertService.error(this.$t("message.cannot_release_paid_table"));
                return;
            }

            appService.destroyConfirmation().then((result) => {
                this.loading.isActive = true;
                this.$store.dispatch("diningTable/releaseDiningTable", table)
                    .then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip( 1, this.$t("message.table_released"));
                        this.$store.dispatch("diningTable/lists", {
                            order_column: "id",
                            order_type: "asc",
                        });
                    })
                    .catch((err) => {
                        alertService.error(err);
                        this.loading.isActive = false;
                    });
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        reset: function () {
            appService.modalHide();
            this.errors = {};
            this.props.form = {
                name: "",
                email: "",
                phone: "",
                password: "",
                password_confirmation: "",
                status: statusEnum.ACTIVE,
                country_code: this.country_code,
            };
        },

        save: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("posOrder/saveCustomer", this.props)
                    .then((res) => {
                        appService.sideDrawerHide();
                        this.loading.isActive = false;
                        alertService.successFlip(0,
                            this.$t("menu.customers")
                        );
                        this.props.form = {
                            name: "",
                            email: "",
                            phone: "",
                            password: "",
                            password_confirmation: "",
                            status: statusEnum.ACTIVE,
                            country_code: this.country_code,
                        };
                        this.errors = {};
                        this.$emit('onCustomverCreate', res.data.data.id);
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },

        // Combine Order Methods
        openCombineOrderModal: function () {
            if (this.unpaidOrders.length < 2) {
                alertService.error(this.$t("message.need_at_least_two_orders"));
                return;
            }
            this.sourceOrders = [];
            this.targetOrder = null;
            this.validationError = "";
            appService.modalShow('#combineOrderModal');
        },
        closeCombineOrderModal: function () {
            this.sourceOrders = [];
            this.targetOrder = null;
            this.validationError = "";
            this.sourceOrdersSearch = "";
            this.targetOrdersSearch = "";
            appService.modalHide('#combineOrderModal');
        },
        mapTableToOrder: function (table) {
            return {
                id: table.id,
                order_id: table.current_order_id,
                table_name: table.name,
                order_serial_no: table.order_serial_no || `#${table.current_order_id}`,
                size: table.size,
                items_count: table.items_count || 0,
                order_total: table.order_total || 0,
            };
        },
        toggleSourceOrder: function (order) {
            const index = this.sourceOrders.findIndex(o => o.order_id === order.order_id);

            if (index !== -1) {
                this.sourceOrders.splice(index, 1);
            } else {
                // Check if this order is selected as target
                if (this.targetOrder && this.targetOrder.order_id === order.order_id) {
                    this.validationError = this.$t("message.cannot_select_same_order_as_source_and_target");
                    return;
                }
                this.sourceOrders.push(order);
                this.validationError = "";
            }
        },
        removeSourceOrder: function (orderId) {
            const index = this.sourceOrders.findIndex(o => o.order_id === orderId);
            if (index !== -1) {
                this.sourceOrders.splice(index, 1);
            }
        },
        isSourceSelected: function (orderId) {
            return this.sourceOrders.some(o => o.order_id === orderId);
        },
        selectTargetOrder: function (order) {
            // Check if this order is already in source orders
            if (this.sourceOrders.some(o => o.order_id === order.order_id)) {
                this.validationError = this.$t("message.cannot_select_same_order_as_source_and_target");
                return;
            }

            this.targetOrder = order;
            this.validationError = "";
        },
        confirmCombineOrders: function () {
            if (!this.canCombine) {
                this.validationError = this.$t("message.please_select_orders_to_combine");
                return;
            }

            // Show confirmation dialog
            appService.confirmDialog(
                this.$t('message.combine_orders_title'),
                this.$t('message.combine_orders_confirmation', {
                    count: this.sourceOrders.length,
                    targetOrderNo: this.targetOrder.order_serial_no
                }),
                "warning",
                this.$t('label.combine_order'),
                this.$t('label.cancel')
            ).then(() => {
                this.performCombineOrders();
            }).catch(() => {
                // User cancelled
            });
        },
        performCombineOrders: function () {
            this.loading.isActive = true;

            const payload = {
                source_order_ids: this.sourceOrders.map(o => o.order_id),
                target_order_id: this.targetOrder.order_id,
            };

            this.$store.dispatch("posOrder/combineOrders", payload)
                .then((res) => {
                    this.loading.isActive = false;
                    this.closeCombineOrderModal();
                    alertService.successFlip(1, this.$t("message.orders_combined_successfully"));

                    // Refresh dining tables list and unpaid orders
                    this.$store.dispatch("diningTable/lists", {
                        order_column: "id",
                        order_type: "asc",
                    });
                    this.fetchUnpaidOrders();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.validationError = err.response?.data?.message || this.$t("message.combine_orders_failed");
                    alertService.error(this.validationError);
                });
        },
        formatPrice: function (price) {
            return parseFloat(price || 0).toFixed(2);
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

.border-red-300 {
    border-color: #f87171 !important;
}
.border-green-300 {
    border-color: #34d399 !important;
}

/* Enhanced table card styles */
.dining-table-card {
    transition: all 0.2s ease-in-out;
}

.dining-table-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Custom button styles for better usability */
.table-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.8125rem;
    transition: all 0.2s ease-in-out;
    border-width: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 36px; /* Smaller touch target */
    cursor: pointer;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.table-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 50%, rgba(0, 0, 0, 0.1) 100%);
    pointer-events: none;
}

.table-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.table-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1), inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table-btn:focus {
    outline: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.table-btn.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: #10b981;
    color: white;
}

.table-btn.success:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-color: #059669;
}

.table-btn.warning {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    border-color: #f97316;
    color: white;
}

.table-btn.warning:hover {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    border-color: #ea580c;
}

.table-btn.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-color: #ef4444;
    color: white;
}

.table-btn.danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #dc2626;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .dining-table-card {
        min-width: 160px;
    }

    .table-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        min-height: 32px;
        font-weight: 600;
        letter-spacing: 0.025em;
    }

    .table-btn::before {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 50%, rgba(0, 0, 0, 0.08) 100%);
    }
}

/* Ensure gap is applied across all screen sizes */
.dining-tables {
    gap: 1.5rem !important; /* 24px gap */
}

@media (min-width: 640px) {
    .dining-tables {
        gap: 1.5rem !important;
    }
}

@media (min-width: 768px) {
    .dining-tables {
        gap: 1.5rem !important;
    }
}

@media (min-width: 1024px) {
    .dining-tables {
        gap: 1.5rem !important;
    }
}

/* Specific fix for 1000-1200px range */
@media (min-width: 1000px) and (max-width: 1200px) {
    .dining-tables {
        gap: 1.5rem !important;
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
}

@media (min-width: 1280px) {
    .dining-tables {
        gap: 1.5rem !important;
    }
}

@media (min-width: 1536px) {
    .dining-tables {
        gap: 1.5rem !important;
    }
}

/* Number shortcut buttons */
.number-shortcut-btn {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    user-select: none;
}

.number-shortcut-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.number-shortcut-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.number-shortcut-btn:focus {
    outline: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.3);
}

/* Combine Order Modal Styles */
#combineOrderModal .modal-dialog {
    max-width: 1400px;
}

#combineOrderModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

/* Custom scrollbar for modal */
#combineOrderModal .modal-body::-webkit-scrollbar {
    width: 8px;
}

#combineOrderModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#combineOrderModal .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#combineOrderModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
