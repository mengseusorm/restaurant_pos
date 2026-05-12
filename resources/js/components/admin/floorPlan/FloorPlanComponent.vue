<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="">
            <div v-if="false" class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.floor_plan') }}</h3>
                <div class="db-card-filter">
                    <div class="relative inline-block">
                        <button @click="showDropdown = !showDropdown"
                                class="db-btn items-center">
                            <span>More</span>
                        </button>

                        <div v-if="showDropdown"
                             @click.stop
                             class="absolute right-0 top-full mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                            <div class="py-2 items-center">
                                <div class="items-center">
                                    <FloorPlanGroupCreateComponent :props="groupProps" @groupCreated="refreshGroups" />
                                </div>

                                <button @click="handleToggleEditMode"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center"
                                    :class="editMode ? 'text-orange-600' : 'text-green-600'">
                                    <i :class="editMode ? 'lab lab-edit-off mr-2' : 'lab lab-edit mr-2'"></i>
                                    <span>{{ editMode ? $t('label.exit_edit') : $t('label.edit_mode') }}</span>
                                </button>

                                <!-- Save Changes Button - only show in edit mode and if there are changes -->
                                <button v-if="editMode && hasUnsavedChanges"
                                    @click="handleSaveChanges"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-blue-600">
                                    <i class="lab lab-save mr-2"></i>
                                    <span>{{ $t('label.save_changes') }}</span>
                                </button>

                                <!-- Cancel Changes Button - only show in edit mode and if there are changes -->
                                <button v-if="editMode && hasUnsavedChanges"
                                    @click="handleCancelChanges"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-gray-600">
                                    <i class="lab lab-cancel mr-2"></i>
                                    <span>{{ $t('label.cancel') }}</span>
                                </button>

                                <button @click="handleToggleAnalytics"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-blue-600">
                                        <i class="lab lab-bar-chart mr-2"></i>
                                        <span>{{ $t('label.analytics') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area with Sidebar -->
            <div class="flex h-full border-t">
                <!-- Left Sidebar - Floor Plan Groups -->
                <div class="w-80 bg-white border-r">
                    <!-- Group Management Header -->

                    <!-- <div class="p-4 border-b bg-white">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-800">{{ $t('label.floor_plan_groups') }}</h4>
                            <FloorPlanGroupCreateComponent :props="groupProps" @groupCreated="refreshGroups" />
                        </div>
                    </div> -->

                    <div class="p-4 border-b bg-white">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-gray-800">{{ $t('label.floor_plan_groups') }}</h4>

                            <div class="relative inline-block">
                                <button @click="showDropdown = !showDropdown"
                                        class="items-center">
                                    <span>More</span>
                                </button>

                                <div v-if="showDropdown"
                                    @click.stop
                                    class="absolute right-0 top-full mt-2 w-36 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                    <div class="py-2 items-center">
                                        <div class="items-center">
                                            <FloorPlanGroupCreateComponent :props="groupProps" @groupCreated="refreshGroups" />
                                        </div>

                                        <button @click="handleToggleEditMode"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center"
                                            :class="editMode ? 'text-orange-600' : 'text-green-600'">
                                            <i :class="editMode ? 'lab lab-edit-off mr-2' : 'lab lab-edit mr-2'"></i>
                                            <span>{{ editMode ? $t('label.exit_edit') : $t('label.edit_mode') }}</span>
                                        </button>

                                        <!-- Save Changes Button - only show in edit mode and if there are changes -->
                                        <button v-if="editMode && hasUnsavedChanges"
                                            @click="handleSaveChanges"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-blue-600">
                                            <i class="lab lab-save mr-2"></i>
                                            <span>{{ $t('label.save_changes') }}</span>
                                        </button>

                                        <!-- Cancel Changes Button - only show in edit mode and if there are changes -->
                                        <button v-if="editMode && hasUnsavedChanges"
                                            @click="handleCancelChanges"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-gray-600">
                                            <i class="lab lab-cancel mr-2"></i>
                                            <span>{{ $t('label.cancel') }}</span>
                                        </button>

                                        <button @click="handleToggleAnalytics"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 flex items-center text-blue-600">
                                                <i class="lab lab-bar-chart mr-2"></i>
                                                <span>{{ $t('label.analytics') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Groups List -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="floorPlanGroups.length === 0" class="p-4 text-center text-gray-500">
                            {{ $t('label.no_groups_found') }}
                        </div>
                        <div v-for="group in floorPlanGroups"
                             :key="group.id"
                             @click="selectGroup(group.id)"
                             class="group-item cursor-pointer p-4 border-b border-gray-200 hover:bg-blue-50 transition-colors"
                             :class="{ 'bg-blue-100 border-blue-300': selectedGroup === group.id }">

                            <!-- Group Info -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-900 mb-1">{{ group.name }}</h5>
                                    <p v-if="group.description" class="text-sm text-gray-600 mb-2">{{ group.description }}</p>

                                    <!-- Group Stats -->
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <span class="flex items-center">
                                            <i class="lab lab-table mr-1"></i>
                                            {{ group.tables_count || 0 }} {{ $t('label.tables') }}
                                        </span>
                                        <span v-if="group.occupied_tables_count" class="flex items-center text-red-600">
                                            <i class="lab lab-users mr-1"></i>
                                            {{ group.occupied_tables_count }} {{ $t('label.occupied') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Group Actions -->
                                <div class="ml-2 flex flex-col space-y-1">
                                    <button @click.stop="editGroup(group)"
                                            class="text-blue-600 hover:text-blue-800 text-xs p-1"
                                            :title="$t('button.edit_group')">
                                        <i class="lab lab-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Group Preview Image -->
                            <!-- <div v-if="group.floor_plan_photo" class="mt-2">
                                <img :src="group.floor_plan_photo"
                                     :alt="group.name"
                                     class="w-full h-20 object-cover rounded border">
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Right Content Area - Floor Plan Canvas -->
                <div class="flex-1 flex flex-col">
                    <!-- Selected Group Header -->
                    <div v-if="false && selectedGroup" class="bg-white border-b mt-5 px-5">
                        <div class="flex items-center justify-between mt-5">
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ selectedGroupName }}</h4>
                                <p v-if="selectedGroupDescription" class="text-sm text-gray-600">{{ selectedGroupDescription }}</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ currentTables.length }} {{ $t('label.tables') }}
                                <span v-if="occupiedTablesCount > 0" class="text-red-600 ml-2">
                                    ({{ occupiedTablesCount }} {{ $t('label.occupied') }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Panel -->
                    <div v-if="showAnalytics" class="p-4 bg-gray-50 border-b">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-2xl font-bold text-green-600">{{ analytics.overall.available_tables }}</div>
                                <div class="text-sm text-gray-600">{{ $t('label.available_tables') }}</div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-2xl font-bold text-red-600">{{ analytics.overall.occupied_tables }}</div>
                                <div class="text-sm text-gray-600">{{ $t('label.occupied_tables') }}</div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-2xl font-bold text-blue-600">{{ analytics.overall.total_tables }}</div>
                                <div class="text-sm text-gray-600">{{ $t('label.total_tables') }}</div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-2xl font-bold text-purple-600">{{ analytics.overall.occupancy_rate }}%</div>
                                <div class="text-sm text-gray-600">{{ $t('label.occupancy_rate') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Floor Plan Canvas -->
                    <div v-if="selectedGroup"
                         ref="floorPlanContainer"
                         class="floor-plan-container relative bg-gray-100 flex-1"
                         :style="floorPlanContainerStyle"
                         @drop="handleDrop"
                         @dragover.prevent
                         @click="clearSelection">

                        <!-- Tables -->
                        <DiningTableComponent
                            v-for="table in scaledTables"
                            :key="table.id"
                            :table="table"
                            :edit-mode="editMode"
                            :selected="selectedTable?.id === table.id"
                            :use-scaled-position="true"
                            @select="selectTable"
                            @update="updateTable"
                            @view-order="viewOrder"
                            @release="releaseTable"
                            @update-guests="updateGuests"
                        />

                        <!-- Grid lines for edit mode -->
                        <div v-if="editMode" class="grid-overlay">
                            <svg class="absolute inset-0 w-full h-full pointer-events-none">
                                <defs>
                                    <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                        <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#grid)" />
                            </svg>
                        </div>
                    </div>

                    <!-- No Group Selected State -->
                    <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                        <div class="text-center text-gray-500">
                            <i class="lab lab-floor-plan text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium mb-2">{{ $t('label.select_floor_group') }}</h3>
                            <p class="text-sm">{{ $t('label.select_group_to_view_floor_plan') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Properties Panel -->
            <div v-if="selectedTable && editMode" class="p-4 bg-white border-t">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="db-field-title">{{ $t('label.shape') }}</label>
                        <vue-select class="db-field-control f-b-custom-select"
                            v-model="selectedTable.shape"
                            :options="[
                                { id: 'rectangle', name: $t('label.rectangle') },
                                { id: 'circle', name: $t('label.circle') },
                                { id: 'square', name: $t('label.square') }
                            ]"
                            label-by="name"
                            value-by="id"
                            @update:modelValue="updateTableProperties" />
                    </div>
                    <div>
                        <label class="db-field-title">{{ $t('label.color') }}</label>
                        <input type="color" v-model="selectedTable.color"
                               @change="updateTableProperties"
                               class="db-field-control w-full h-10" />
                    </div>
                    <div>
                        <label class="db-field-title">{{ $t('label.size') }}</label>
                        <input type="number" v-model="selectedTable.size"
                               @change="updateTableProperties"
                               min="1" max="20"
                               class="db-field-control" />
                    </div>
                    <div>
                        <label class="db-field-title">{{ $t('label.group') }}</label>
                        <vue-select class="db-field-control f-b-custom-select"
                            v-model="selectedTable.floor_plan_group_id"
                            :options="floorPlanGroups"
                            label-by="name"
                            value-by="id"
                            @update:modelValue="updateTableProperties" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Details Modal -->
    <TableDetailsModal v-if="selectedTable"
                      :table="selectedTable"
                      @close="selectedTable = null"
                      @viewOrder="viewOrder"
                      @release="releaseTable" />

    <!-- Order View Modal -->
    <OrderViewModal v-if="showOrderView"
                   :order="currentOrder"
                   @close="showOrderView = false" />

    <!-- Floor Plan Group Edit Modal -->
    <FloorPlanGroupEditComponent v-if="showGroupEdit"
                                :group="groupToEdit"
                                @groupUpdated="handleGroupUpdated"
                                @close="closeGroupEdit" />
</template>

<script>
import LoadingComponent from '../components/LoadingComponent';
import DiningTableComponent from './DiningTableComponent.vue';
import FloorPlanGroupCreateComponent from './FloorPlanGroupCreateComponent.vue';
import FloorPlanGroupEditComponent from './FloorPlanGroupEditComponent.vue';
import TableDetailsModal from './TableDetailsModal.vue';
import OrderViewModal from './OrderViewModal.vue';
import alertService from '../../../services/alertService';
import appService from '../../../services/appService';

export default {
    name: "FloorPlanComponent",
    components: {
        LoadingComponent,
        DiningTableComponent,
        FloorPlanGroupCreateComponent,
        FloorPlanGroupEditComponent,
        TableDetailsModal,
        OrderViewModal
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            editMode: false,
            showAnalytics: false,
            showOrderView: false,
            showGroupEdit: false,
            selectedGroup: null,
            selectedTable: null,
            currentOrder: null,
            groupToEdit: null,
            floorPlanHeight: 600,
            containerWidth: 1000,
            containerHeight: 600,
            backgroundImageDimensions: {
                width: 1000,
                height: 600,
                naturalWidth: 1000,
                naturalHeight: 600
            },
            groupProps: {
                form: {
                    branch_id: null,
                    name: "",
                    description: "",
                    sort_order: 0
                }
            },
            analytics: {
                overall: {
                    total_tables: 0,
                    occupied_tables: 0,
                    available_tables: 0,
                    occupancy_rate: 0
                },
                groups: []
            },
            originalTablePositions: new Map(), // Store original positions for cancel functionality
            changedTables: new Map(), // Track which tables have been moved
            showDropdown: false,
        }
    },
    computed: {
        floorPlanGroups: function () {
            const groups = this.$store.getters['floorPlan/groups'];
            return Array.isArray(groups) ? groups : [];
        },
        currentTables: function () {
            if (!this.selectedGroup) return [];
            const tables = this.$store.getters['floorPlan/tablesForGroup'](this.selectedGroup);
            return Array.isArray(tables) ? tables : [];
        },
        defaultAccess: function () {
            return this.$store.getters['defaultAccess/show'];
        },
        hasUnsavedChanges: function () {
            return this.changedTables.size > 0;
        },
        floorPlanContainerStyle: function () {
            const style = {
                height: this.floorPlanHeight + 'px',
                width: '100%'
            };

            // Add background image if floor plan group has a photo
            if (this.selectedGroup) {
                const group = this.floorPlanGroups.find(g => g.id === this.selectedGroup);
                if (group && group.floor_plan_photo) {
                    style.backgroundImage = `url(${group.floor_plan_photo})`;
                    style.backgroundSize = 'contain';
                    style.backgroundRepeat = 'no-repeat';
                    style.backgroundPosition = 'top left';
                }
            }

            return style;
        },
        scaledTables: function() {
            if (!this.currentTables.length) return [];

            return this.currentTables.map(table => {
                const scaledTable = { ...table };

                // Get the actual background image display area
                const containerRect = this.getBackgroundImageDisplayArea();

                // Use fallback dimensions if natural dimensions aren't loaded
                const naturalWidth = this.backgroundImageDimensions.naturalWidth || 1000;
                const naturalHeight = this.backgroundImageDimensions.naturalHeight || 600;

                if (containerRect.width > 0 && containerRect.height > 0) {
                    // Calculate scale factors
                    const scaleX = containerRect.width / naturalWidth;
                    const scaleY = containerRect.height / naturalHeight;

                    // Use the same scale for both axes to maintain aspect ratio (contain behavior)
                    const scale = Math.min(scaleX, scaleY);

                    // Calculate the actual background image position within the container
                    const bgWidth = naturalWidth * scale;
                    const bgHeight = naturalHeight * scale;
                    const bgLeft = containerRect.left;
                    const bgTop = containerRect.top;

                    // Scale table position relative to background image
                    scaledTable.scaledPositionX = bgLeft + (table.position_x || 0) * scale;
                    scaledTable.scaledPositionY = bgTop + (table.position_y || 0) * scale;
                    scaledTable.scaledWidth = (table.width || 80) * scale;
                    scaledTable.scaledHeight = (table.height || 80) * scale;
                } else {
                    // Fallback to original positions if container not available
                    scaledTable.scaledPositionX = table.position_x || 0;
                    scaledTable.scaledPositionY = table.position_y || 0;
                    scaledTable.scaledWidth = table.width || 80;
                    scaledTable.scaledHeight = table.height || 80;
                }

                return scaledTable;
            });
        },
        selectedGroupName: function() {
            if (!this.selectedGroup) return '';
            const group = this.floorPlanGroups.find(g => g.id === this.selectedGroup);
            return group ? group.name : '';
        },
        selectedGroupDescription: function() {
            if (!this.selectedGroup) return '';
            const group = this.floorPlanGroups.find(g => g.id === this.selectedGroup);
            return group ? group.description : '';
        },
        occupiedTablesCount: function() {
            return this.currentTables.filter(table => table.status === 'occupied').length;
        }
    },
    mounted() {
        this.loadGroups();
        this.loadAnalytics();

        // Initialize container dimensions
        this.initializeContainerDimensions();

        // Add resize listener
        window.addEventListener('resize', this.handleResize);

        // Add click outside listener for dropdown
        document.addEventListener('click', this.handleClickOutside);

        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (!this.editMode) {
                this.refreshCurrentData();
            }
        }, 30000);

        this.$store.dispatch('defaultAccess/show').then(() => {
            const da = this.$store.getters['defaultAccess/show'];
            if (da && da.branch_id) {
                this.groupProps.form.branch_id = da.branch_id;
            }
        }).catch((err) => {
            console.error('Error loading default access:', err);
        });
    },
    methods: {
        handleToggleEditMode: function() {
            this.toggleEditMode();
            this.showDropdown = false;
        },

        handleSaveChanges: function() {
            this.saveAllChanges();
            this.showDropdown = false;
        },

        handleCancelChanges: function() {
            this.cancelChanges();
            this.showDropdown = false;
        },

        handleToggleAnalytics: function() {
            this.showAnalytics = !this.showAnalytics;
            this.showDropdown = false;
        },

        handleClickOutside: function(event) {
            // Close dropdown if clicking outside
            if (!event.target.closest('.relative.inline-block')) {
                this.showDropdown = false;
            }
        },

        loadGroups: function () {
            this.loading.isActive = true;
            this.$store.dispatch('floorPlan/loadGroups').then(() => {
                if (this.floorPlanGroups.length > 0 && !this.selectedGroup) {
                    this.selectedGroup = this.floorPlanGroups[0].id;
                    this.loadTablesForGroup();
                }
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response?.data?.message);
            });
        },

        loadTablesForGroup: function () {
            if (!this.selectedGroup) return;

            this.loading.isActive = true;
            this.$store.dispatch('floorPlan/loadTablesForGroup', { groupId: this.selectedGroup})
                .then(() => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
        },

        loadAnalytics: function () {
            this.$store.dispatch('floorPlan/loadAnalytics').then((response) => {
                this.analytics = response.data.data;
            }).catch((err) => {
                console.error('Error loading analytics:', err);
            });
        },

        changeGroup: function (groupId) {
            this.selectedGroup = groupId;
            this.selectedTable = null;
            this.loadTablesForGroup();
            // Reload background image dimensions when group changes
            this.$nextTick(() => {
                this.loadBackgroundImageDimensions();
            });
        },

        selectGroup: function (groupId) {
            this.changeGroup(groupId);
        },

        editGroup: function (group) {
            this.groupToEdit = group;
            this.showGroupEdit = true;
            appService.modalShow('#groupEditModal');
        },

        toggleEditMode: function () {
            if (this.editMode) {
                // Exiting edit mode - check for unsaved changes
                if (this.hasUnsavedChanges) {
                    if (confirm(this.$t('message.unsaved_changes_confirmation'))) {
                        this.cancelChanges();
                    } else {
                        return; // Don't exit edit mode
                    }
                }
                this.editMode = false;
                this.selectedTable = null;
            } else {
                // Entering edit mode - store original positions
                this.editMode = true;
                this.storeOriginalPositions();
            }
        },

        storeOriginalPositions: function () {
            // Store original positions of all current tables
            this.originalTablePositions.clear();
            this.changedTables.clear();

            this.currentTables.forEach(table => {
                this.originalTablePositions.set(table.id, {
                    position_x: table.position_x,
                    position_y: table.position_y,
                    width: table.width,
                    height: table.height,
                    rotation: table.rotation
                });
            });
        },

        selectTable: function (table) {
            if (this.editMode) {
                this.selectedTable = table;
            } else {
                this.selectedTable = table;
                appService.modalShow('#tableDetailsModal');
            }
        },

        clearSelection: function () {
            if (this.editMode) {
                this.selectedTable = null;
            }
        },

        updateTable: function (updatedTable) {
            // Only allow updates in edit mode
            if (!this.editMode) return;

            // Update local state immediately for smooth UI
            this.selectedTable = updatedTable;

            // Update the table in the store immediately for visual feedback
            this.$store.commit('floorPlan/updateTableInStore', {
                tableId: updatedTable.id,
                data: {
                    position_x: updatedTable.position_x || 0,
                    position_y: updatedTable.position_y || 0,
                    width: updatedTable.width || 80,
                    height: updatedTable.height || 80,
                    rotation: updatedTable.rotation || 0
                }
            });

            // Track this table as changed
            this.changedTables.set(updatedTable.id, {
                position_x: updatedTable.position_x || 0,
                position_y: updatedTable.position_y || 0,
                width: updatedTable.width || 80,
                height: updatedTable.height || 80,
                rotation: updatedTable.rotation || 0
            });
        },

        saveAllChanges: function () {
            if (!this.hasUnsavedChanges) return;

            this.loading.isActive = true;
            const savePromises = [];

            // Process all changed tables
            this.changedTables.forEach((tableData, tableId) => {
                const promise = this.$store.dispatch('floorPlan/updateTablePosition', {
                    tableId: tableId,
                    form: tableData
                });
                savePromises.push(promise);
            });

            // Wait for all saves to complete
            Promise.all(savePromises)
                .then(() => {
                    this.loading.isActive = false;
                    this.changedTables.clear();
                    this.originalTablePositions.clear();
                    alertService.success(this.$t('message.changes_saved_successfully'));
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || this.$t('message.error_saving_changes'));
                });
        },

        cancelChanges: function () {
            if (!this.hasUnsavedChanges) return;

            // Restore original positions for all changed tables
            this.changedTables.forEach((_, tableId) => {
                const originalPosition = this.originalTablePositions.get(tableId);
                if (originalPosition) {
                    // Update the table in the store to revert position
                    this.$store.commit('floorPlan/updateTableInStore', {
                        tableId: tableId,
                        data: originalPosition
                    });
                }
            });

            // Clear tracking
            this.changedTables.clear();
            this.selectedTable = null;

            alertService.info(this.$t('message.changes_cancelled'));
        },

        updateTableProperties: function () {
            if (!this.selectedTable) return;

            this.$store.dispatch('floorPlan/updateTableProperties', {
                tableId: this.selectedTable.id,
                form: {
                    shape: this.selectedTable.shape,
                    color: this.selectedTable.color,
                    size: this.selectedTable.size,
                    floor_plan_group_id: this.selectedTable.floor_plan_group_id
                }
            }).catch((err) => {
                alertService.error(err.response?.data?.message);
            });
        },

        updateGuests: function (tableId, guestCount) {
            this.$store.dispatch('floorPlan/updateCurrentGuests', {
                tableId: tableId,
                form: { current_guests: guestCount }
            }).catch((err) => {
                alertService.error(err.response?.data?.message);
            });
        },

        viewOrder: function (table) {
            if (table.current_order_id) {
                this.$store.dispatch('order/show', table.current_order_id).then((response) => {
                    this.currentOrder = response.data.data;
                    this.showOrderView = true;
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                });
            }
        },

        releaseTable: function (payload) {
            appService.destroyConfirmation().then(() => {
                this.$store.dispatch('floorPlan/releaseTable', payload).then(() => {
                    appService.modalHide();
                    this.selectedTable = null;
                    alertService.successFlip(null, this.$t('message.table_released'));
                    this.loadAnalytics();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                });
            });
        },

        refreshGroups: function () {
            this.loadGroups();
        },

        closeGroupEdit: function () {
            this.showGroupEdit = false;
            this.groupToEdit = null;
            appService.modalHide();
        },

        handleGroupUpdated: function (updatedGroup) {
            this.refreshGroups();
            alertService.success(this.$t('message.group_updated_successfully'));
        },

        initializeContainerDimensions: function() {
            this.$nextTick(() => {
                this.updateContainerDimensions();
                this.loadBackgroundImageDimensions();
            });
        },

        updateContainerDimensions: function() {
            if (this.$refs.floorPlanContainer) {
                const rect = this.$refs.floorPlanContainer.getBoundingClientRect();
                this.containerWidth = rect.width;
                this.containerHeight = rect.height;
            }
        },

        loadBackgroundImageDimensions: function() {
            const group = this.floorPlanGroups.find(g => g.id === this.selectedGroup);
            if (group && group.floor_plan_photo) {
                const img = new Image();
                img.onload = () => {
                    this.backgroundImageDimensions.naturalWidth = img.naturalWidth || 1000;
                    this.backgroundImageDimensions.naturalHeight = img.naturalHeight || 600;
                };
                img.onerror = () => {
                    // Set default dimensions if image fails to load
                    this.backgroundImageDimensions.naturalWidth = 1000;
                    this.backgroundImageDimensions.naturalHeight = 600;
                };
                img.src = group.floor_plan_photo;
            } else {
                // Set default dimensions if no photo
                this.backgroundImageDimensions.naturalWidth = 1000;
                this.backgroundImageDimensions.naturalHeight = 600;
            }
        },

        getBackgroundImageDisplayArea: function() {
            if (!this.$refs.floorPlanContainer) {
                return { left: 0, top: 0, width: this.containerWidth, height: this.containerHeight };
            }

            const rect = this.$refs.floorPlanContainer.getBoundingClientRect();
            return {
                left: 0,
                top: 0,
                width: rect.width,
                height: rect.height
            };
        },

        handleResize: function() {
            this.updateContainerDimensions();
        },

        refreshCurrentData: function () {
            if (this.selectedGroup) {
                this.loadTablesForGroup();
            }
            this.loadAnalytics();
        },

        handleDrop: function (event) {
            // Handle drag and drop functionality for creating new tables
            event.preventDefault();
            if (this.editMode) {
                // Implementation for dropping new tables
                console.log('Drop at position:', event.offsetX, event.offsetY);
            }
        }
    },

    beforeUnmount() {
        // Remove resize listener
        window.removeEventListener('resize', this.handleResize);

        // Remove click outside listener
        document.removeEventListener('click', this.handleClickOutside);

        // Warn about unsaved changes before component unmounts
        if (this.hasUnsavedChanges) {
            if (confirm(this.$t('message.unsaved_changes_will_be_lost'))) {
                this.cancelChanges();
            }
        }
    }
}
</script>

<style scoped>
.floor-plan-container {
    min-height: 600px;
    position: relative;
    overflow: hidden;
    background-position: top left !important;
}

.grid-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.table-item {
    position: absolute;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    border-radius: 4px;
}

.table-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.table-item.selected {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.table-item.occupied {
    border-color: #ef4444;
}

.table-item.available {
    border-color: #10b981;
}

.table-status {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
}

.status-occupied {
    background-color: #ef4444;
}

.status-available {
    background-color: #10b981;
}

.resize-handle {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: #3b82f6;
    cursor: se-resize;
}

/* Sidebar Styles */
.group-item {
    transition: all 0.2s ease;
}

.group-item:hover {
    background-color: #f0f9ff;
}

.group-item.selected {
    background-color: #dbeafe;
    border-left: 4px solid #3b82f6;
}

/* Main Content Layout */
.db-card {
    height: calc(100vh - 200px);
    min-height: 600px;
}

.db-card .flex {
    height: calc(100% - 80px); /* Account for header */
}

.w-80 {
    width: 20rem;
    max-width: 25%;
    min-width: 280px;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .w-80 {
        width: 16rem;
        min-width: 240px;
    }
}

@media (max-width: 768px) {
    .w-80 {
        width: 14rem;
        min-width: 200px;
    }

    .group-item {
        padding: 0.75rem;
    }
}

@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
