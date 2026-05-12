<template>
    <div v-if="diningtables && diningtables.length" class="dining-tables grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 text-xs p-2">
        <div v-for="(table, index) in diningtables" :key="index" @click="handleTableClick(table)"
            :class="[
                'table-shape border p-2 flex flex-col items-center justify-center rounded-md transition-colors duration-200 text-center',
                getTableClass(table)
            ]"
        >
            <div class="flex items-center justify-center w-full mb-1">
                <i class="text-sm lab lab-dining-table"></i>
                <h3 class="font-bold text-sm capitalize break-words ms-1">{{ table.name }}</h3>
            </div>
            <p class="text-xs text-gray-500">{{ table.size }} {{ $t('label.seats') }}</p>
            <span v-if="table.current_order_id" class="text-xs text-red-500 mt-1">{{ $t('label.occupied') }}</span>
            <span v-else class="text-xs text-green-600 mt-1">{{ $t('label.available') }}</span>
            <div class="mt-1 flex gap-1" v-if="table.current_order_id">
                <button class="db-table-action view" @click.stop="showOrderDiningTable(table.current_order_id)">
                    <i class="lab lab-view"></i>
                </button>
                <button class="db-table-action delete" @click.stop="removeDiningTable(table)">
                    <i class="lab lab-delete"></i>
                </button>
            </div>
        </div>
    </div>
    <div v-else class="flex items-center justify-center h-5 text-gray-400">
        {{ $t('label.no_dining_table') }}
    </div>
</template>
<script>
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
export default {
    emits: ['selected-tables'],
    props: {
        initialSelectedTables: {
            type: Array,
            default: () => []
        }
    },
    name: "SelectTableComponent",
    data(){
        return {
            loading: {
                isActive: false
            },
            selectedTables: [],
            props: {
                form: {
                    branch_id: null,
                    name: "",
                    size: "",
                    status: statusEnum.ACTIVE,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    name: "",
                    size: "",
                    status: null,
                }
            },
        }
    },
    computed: {
        diningtables: function () {
            return this.$store.getters["diningTable/lists"];
        },
    },
    mounted(){
        this.list()
        this.$store.dispatch('diningTable/lists',{
            order_column: "id",
            order_type: "asc",
        }).then(res => {
            this.loading.isActive = false;
            // Initialize selected tables from props
            if (this.initialSelectedTables && this.initialSelectedTables.length > 0) {
                this.selectedTables = [...this.initialSelectedTables];
                this.$emit("selected-tables", this.selectedTables);
            }
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    watch: {
        diningtables: {
            handler(newTables) {
                // Validate selected tables whenever the table list updates
                this.validateSelectedTables();
            },
            deep: true
        }
    },
    methods: {
        getTableClass(table) {
            if (table.current_order_id) {
                return 'border-red-500 bg-red-50 cursor-not-allowed';
            }
            return this.isSelected(table)
                ? 'border-primary bg-primary-light cursor-pointer'
                : 'border-gray-300 cursor-pointer';
        },

        handleTableClick(table) {
            if (table.current_order_id) {
                alertService.warning(this.$t('message.table_occupied'));
                return;
            }
            this.toggleSelectTable(table);
        },

        toggleSelectTable(table) {
            // Double check table is not occupied
            if (table.current_order_id) {
                return;
            }

            // Check if table exists in current selection
            const index = this.selectedTables.findIndex(t => t.id === table.id);
            if (index !== -1) {
                this.selectedTables.splice(index, 1);
            } else {
                // Verify table is still available before adding
                const currentTable = this.diningtables.find(t => t.id === table.id);
                if (currentTable && !currentTable.current_order_id) {
                    this.selectedTables.push(table);
                } else {
                    alertService.warning(this.$t('message.table_occupied'));
                    return;
                }
            }
            this.$emit("selected-tables", this.selectedTables);
        },
        refreshTables() {
            this.loading.isActive = true;
            this.$store.dispatch('diningTable/lists', {
                order_column: "id",
                order_type: "asc",
            }).then(res => {
                // After refresh, validate selected tables
                this.validateSelectedTables();
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        clearSelectedTables() {
            this.selectedTables = [];
            this.refreshTables();
            this.$emit("selected-tables", this.selectedTables);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.refreshTables();
        },
        search: function () {
            this.list();
        },
        isSelected(table) {
            return this.selectedTables.some(t => t.id === table.id);
        },
        showOrderDiningTable: function (orderId) {
            if (orderId) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
            }
        },
        removeDiningTable: function (dining_table) {
            appService.tableReleaseSuccess().then((res) => {
                try {
                    this.loading.isActive = true;
                     this.$store.dispatch('posOrder/releaseDiningTable_',{
                        id: dining_table.current_order_id,
                        dining_table_id: dining_table.id,
                    }).then((res) => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.table_release'));
                        this.$store.dispatch('diningTable/lists', {
                            order_column: "id",
                            order_type: "asc",
                        });
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
        validateSelectedTables() {
            const previousLength = this.selectedTables.length;
            // Remove any selected tables that are now occupied
            this.selectedTables = this.selectedTables.filter(selected => {
                const currentTable = this.diningtables.find(t => t.id === selected.id);
                return currentTable && !currentTable.current_order_id;
            });

            // Only emit if there was a change
            if (previousLength !== this.selectedTables.length) {
                this.$emit("selected-tables", this.selectedTables);
            }
        },
    }
}
</script>
