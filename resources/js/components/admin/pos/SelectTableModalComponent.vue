<template>
    <!-- Modal wrapper with teleport to body -->
    <teleport to="body">
        <div :id="modalId" class="modal select-table-modal-overlay">
            <div class="modal-dialog select-table-modal-dialog">
                <div class="modal-header hidden-print">
                    <h3 class="drawer-title">{{ $t("label.select_table") }}</h3>
                    <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
                </div>
                <div class="modal-body max-h-[80vh] overflow-hidden flex flex-col">
                <div class="statistics mb-6 flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-4 flex-shrink-0">
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
                <!-- selected tables content -->
                <div class="statistics mb-6 flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-4 flex-shrink-0">
                    <div class="stat-item">
                        <p class="text-sm text-gray-500">{{ $t("label.selected_table") }}:
                            <span class="ms-2 capitalize leading-5 px-3 rounded-xl bg-green-700 text-white" v-for="item in selectedTables" :key="item.id">{{ item.name }}</span>
                        </p>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="mb-4 flex-shrink-0">
                    <div class="relative">
                        <input
                            id="table-search-input"
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
                <KeyboardShortcutsComponent v-model="searchQuery" input-id="table-search-input" />

                <div class="flex-1 overflow-y-auto">
                    <div class="dining-tables grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6 pt-5">
                        <div
                            v-for="(table, index) in filteredTables"
                            :key="index"
                            :class="[
                                'dining-table-card border-2 p-4 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer transform hover:-translate-y-1',
                                table.current_order_id
                                    ? 'border-orange-300 bg-orange-50 hover:bg-orange-100'
                                    : isSelected(table)
                                        ? 'border-blue-400 bg-blue-50 hover:bg-blue-100'
                                        : 'border-green-400 bg-green-50 hover:bg-green-100'
                            ]"
                            style="min-width: 180px;"
                            @click="!table.current_order_id && toggleSelectTable(table)"
                        >
                            <div class="text-center mb-3">
                                <h3 class="text-lg font-bold text-gray-800 mb-1">{{ table.name }}</h3>
                                <p class="text-xs text-gray-600">{{ table.size }} {{ $t("label.seats") }}</p>
                                <div class="mt-1">
                                    <span :class="[
                                        'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                                        table.current_order_id
                                            ? 'bg-orange-200 text-orange-800'
                                            : isSelected(table)
                                                ? 'bg-blue-200 text-blue-800'
                                                : 'bg-green-200 text-green-800'
                                    ]">
                                        {{ table.current_order_id ? $t('label.occupied') : isSelected(table) ? $t('label.selected') : $t('label.available') }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <button v-if="table.current_order_id" class="table-btn warning w-full" @click.stop="showOrderDiningTable(table.current_order_id)">
                                    <i class="lab lab-view mr-2"></i>
                                    {{ $t("label.view_order") }}
                                </button>
                                <button v-if="table.current_order_id" class="table-btn danger w-full" @click.stop="releaseDiningTable(table)">
                                    <i class="lab lab-trash mr-2"></i>
                                    {{ $t("label.release_table") }}
                                </button>
                                <!-- <div v-if="!table.current_order_id && isSelected(table)" class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                                        <i class="lab lab-check mr-1"></i>
                                        {{ $t('label.selected') }}
                                    </span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer hidden-print">
                <button
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-primary"
                    @click="confirmSelectTable()" type="button">{{ $t('button.confirm') }}
                </button>
            </div>
        </div>
    </div>
</teleport>
</template>
<script>
import statusEnum from "../../../enums/modules/statusEnum";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import KeyboardShortcutsComponent from "../components/KeyboardShortcutsComponent.vue";

export default {
    name: "SelectTableModalComponent",
    components: {
        KeyboardShortcutsComponent,
    },
    emits: ['selected-tables'],
    props: {
        modalId: {
            type: String,
            default: 'diningtableModal'
        },
        preSelectedTables: {
            type: Array,
            default: () => []
        }
    },
    data(){
        return {
            loading: {
                isActive: false
            },
            selectedTables: [],
            searchQuery: "",
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
        filteredTables: function () {
            if (!this.searchQuery.trim()) {
                return this.diningtables;
            }
            const query = this.searchQuery.toLowerCase();
            return this.diningtables.filter(table =>
                table.name.toLowerCase().includes(query)
            );
        },
    },
    watch: {
        preSelectedTables: {
            handler(newVal) {
                if (newVal && newVal.length > 0) {
                    // Map the preSelectedTables to match the full table objects
                    this.selectedTables = this.diningtables.filter(table =>
                        newVal.some(preSelected => preSelected.id === table.id)
                    );
                }
            },
            immediate: true,
            deep: true
        },
        diningtables: {
            handler() {
                // Re-sync selected tables when dining tables list is loaded
                if (this.preSelectedTables && this.preSelectedTables.length > 0) {
                    this.selectedTables = this.diningtables.filter(table =>
                        this.preSelectedTables.some(preSelected => preSelected.id === table.id)
                    );
                }
            }
        }
    },
    mounted(){
        this.list()
        this.$store.dispatch('diningTable/lists',{
            order_column: "id",
            order_type: "asc",
        }).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },

    methods: {
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('diningTable/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        search: function () {
            this.list();
        },
        confirmSelectTable() {
            appService.modalHide('#' + this.modalId);
            this.$emit("selected-tables",this.selectedTables)
        },
        reset(){
            appService.modalHide('#' + this.modalId);
            this.selectedTables = [];
        },
        toggleSelectTable(table) {
            const index = this.selectedTables.findIndex(t => t.id === table.id);
            if (index !== -1) {
                this.selectedTables.splice(index, 1);
            } else {
                this.selectedTables.push(table);
            }
        },
        isSelected(table) {
            return this.selectedTables.some(t => t.id === table.id);
        },
        showOrderDiningTable: function (orderId) {
            if (orderId) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
                this.reset();
            }
        },
        releaseDiningTable: function (table) {
            appService.destroyConfirmation().then((result) => {
                this.loading.isActive = true;
                this.$store.dispatch("diningTable/releaseDiningTable", table)
                    .then(() => {
                        this.loading.isActive = false;
                        alertService.successFlip( 1, this.$t("label.table_released"));
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

    }
}
</script>

<style scoped>
/* Make modal wider to accommodate many tables */
.select-table-modal-dialog {
    max-width: 90vw !important;
    width: 1400px !important;
}

@media (max-width: 1500px) {
    .select-table-modal-dialog {
        width: 85vw !important;
    }
}

@media (max-width: 1024px) {
    .select-table-modal-dialog {
        width: 90vw !important;
    }
}

@media (max-width: 768px) {
    .select-table-modal-dialog {
        width: 95vw !important;
    }
}

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
</style>
