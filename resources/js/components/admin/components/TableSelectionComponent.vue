<template>
    <div class="mb-4 bg-white rounded-lg border border-gray-200 p-4" v-if="showTableSelection">
        <!-- Selected Tables Display -->
        <div v-if="selectedTables && selectedTables.length > 0" class="space-y-2">
            <div class="text-sm text-gray-600 mb-2">{{ $t('label.selected_tables') }}:</div>
            <div class="flex flex-wrap gap-2">
                <div v-for="(table, index) in selectedTables" :key="index"
                     class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 text-blue-800 rounded-lg border border-blue-200">
                    <i class="lab lab-dining-table text-sm"></i>
                    <span class="font-medium">{{ getTableNameById(table.id) }}</span>
                    <button @click="removeTable(table.id)" class="ml-1 text-blue-600 hover:text-blue-800">
                        <i class="lab lab-close text-xs"></i>
                    </button>
                </div>
                <button @click="openTableModal" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="lab lab-plus"></i>
                    {{ $t('button.add_table') }}
                </button>
            </div>
        </div>

        <div v-else class="text-center text-gray-500">
            <button @click="openTableModal" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <i class="lab lab-plus"></i>
                {{ $t('button.select_table') }}
            </button>
        </div>
    </div>
    
    <!-- Select Table Modal -->
    <component 
        v-if="isModalLoaded && SelectTableModalComponent"
        :is="SelectTableModalComponent"
        ref="selectTableModalRef"
        :modalId="'selectTableModal'" 
        :preSelectedTables="selectedTables"
        @selected-tables="updateTableSelected" 
    />
</template>

<script>
import appService from '../../../services/appService';
import { markRaw } from 'vue';

export default {
    name: 'TableSelectionComponent',
    components: {
        // SelectTableModalComponent will be loaded dynamically when needed
    },
    props: {
        modelValue: {
            type: Array,
            default: () => []
        },
        orderType: {
            type: Number,
            required: true
        },
        dineInType: {
            type: Number,
            required: true
        },
        showSelectTableList: {
            type: Number,
            required: true
        },
        activeStatus: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            SelectTableModalComponent: null,
            isModalLoaded: false
        };
    },
    computed: {
        selectedTables: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
        showTableSelection() {
            return this.orderType === this.dineInType && this.showSelectTableList === this.activeStatus;
        },
        diningtables() {
            return this.$store.getters['diningTable/lists'];
        }
    },
    methods: {
        getTableNameById(tableId) {
            const table = this.diningtables.find(t => t.id === tableId);
            return table ? table.name : '';
        },
        removeTable(tableId) {
            const updatedTables = this.selectedTables.filter(table => table.id !== tableId);
            this.selectedTables = updatedTables;
        },
        openTableModal() {
            if (!this.isModalLoaded) {
                // Load the modal component dynamically
                import('../pos/SelectTableModalComponent.vue').then(component => {
                    this.SelectTableModalComponent = markRaw(component.default);
                    this.isModalLoaded = true;
                    // Wait for next tick to ensure component is rendered
                    this.$nextTick(() => {
                        appService.modalShow('#selectTableModal');
                    });
                }).catch(error => {
                    console.error('Failed to load SelectTableModalComponent:', error);
                });
            } else {
                appService.modalShow('#selectTableModal');
            }
        },
        updateTableSelected(selectedTables) {
            // Update the selected tables from modal
            this.selectedTables = selectedTables.map(table => ({
                id: table.id
            }));
        }
    }
}
</script>

<style scoped>
/* Component-specific styles if needed */
</style>
