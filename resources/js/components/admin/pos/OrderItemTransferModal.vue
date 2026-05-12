<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4 max-h-[95vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-heading">{{ $t('label.transfer_order_items') }}</h3>
                <button @click.prevent="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Body - Two columns -->
            <div class="flex-1 overflow-hidden">
                <div class="grid grid-cols-2 gap-4 p-4 h-full">
                    <!-- Left Side: Source Order Items -->
                    <div class="border rounded-lg overflow-hidden flex flex-col">
                        
                        <div class="bg-gray-50 px-4 py-3 border-b">
                            <h4 class="font-semibold text-heading">{{ $t('label.source_order') }} #{{ sourceOrder?.order_serial_no }}</h4>
                            <p v-if="sourceOrder && sourceOrder.dining_tables">
                                <span v-for="item in sourceOrder.dining_tables" :key="item.id" class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full me-2" >{{ item.name }}</span>
                            </p>
                        </div>
                        <div class="flex-1 overflow-y-auto overflow-x-hidden" style="max-height: calc(95vh - 280px);">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-heading uppercase">{{ $t('label.item') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-heading uppercase">{{ $t('label.qty') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-heading uppercase">{{ $t('label.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="item in sourceOrderItems" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2">
                                                <img class="w-10 h-10 rounded object-cover" :src="item.item_image" alt="item" />
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-heading">{{ item.item_name }}</p>
                                                    <p v-if="item.item_variations && item.item_variations.length > 0" class="text-xs text-gray-500 truncate">
                                                        <span v-for="(variation, index) in item.item_variations" :key="index">
                                                            {{ variation.variation_name }}: {{ variation.name }}<span v-if="index + 1 < item.item_variations.length">, </span>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span :class="getAvailableQty(item.id) > 0 ? 'bg-primary' : 'bg-gray-400'" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white text-sm font-semibold">
                                                {{ getAvailableQty(item.id) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div v-if="getAvailableQty(item.id) > 0" class="flex items-center justify-center gap-1">
                                                <button @click.prevent="transferOne(item)" type="button"
                                                    class="px-2 py-1 text-xs font-medium text-white bg-primary rounded hover:bg-primary-dark">
                                                    {{ $t('button.transfer_one') }}
                                                </button>
                                                <button @click.prevent="transferAll(item)" type="button"
                                                    class="px-2 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                                    {{ $t('button.transfer_all') }}
                                                </button>
                                            </div>
                                            <div v-else class="text-center text-xs text-gray-400">
                                                {{ $t('label.transferred') }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="!sourceOrderItems || sourceOrderItems.length === 0" class="p-8 text-center text-gray-500">
                                <i class="lab lab-box lab-font-size-24 mb-2"></i>
                                <p>{{ $t('message.no_items_found') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Target Order and Transfer List -->
                    <div class="border rounded-lg overflow-hidden flex flex-col">
                        <div class="bg-gray-50 px-4 py-3 border-b">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-heading">{{ $t('label.target_order') }}
                                    <span v-if="targetOrder" class="text-sm text-gray-600"> #{{ targetOrder.order_serial_no }}</span>
                                </h4>
                                <button @click.prevent="selectTargetOrder" type="button"
                                    class="px-3 py-1 text-xs font-medium text-primary border border-primary rounded hover:bg-primary hover:text-white">
                                    {{ targetOrder ? $t('button.change_order') : $t('button.select_order') }}
                                </button>
                            </div>
                            <div v-if="targetOrder">
                                <p v-if="targetOrder.dining_tables && targetOrder.dining_tables.length > 0">
                                    <span v-for="(diningTable, idx) in targetOrder.dining_tables" :key="idx"
                                        class="inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                        <i class="lab lab-table text-xs mr-1"></i>{{ diningTable.name }}
                                    </span>
                                </p>
                                <p v-else class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full mt-1">No Table</p>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto overflow-x-hidden" style="max-height: calc(95vh - 280px);">
                            <div v-if="!targetOrder" class="h-full flex items-center justify-center p-8 text-center text-gray-500">
                                <div>
                                    <i class="lab lab-clipboard-text lab-font-size-24 mb-2"></i>
                                    <p>{{ $t('message.please_select_target_order') }}</p>
                                </div>
                            </div>
                            <div v-else>
                                <table class="w-full">
                                    <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-heading uppercase">{{ $t('label.item') }}</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-heading uppercase">{{ $t('label.qty') }}</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-heading uppercase">{{ $t('label.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(item, index) in transferList" :key="index" class="hover:bg-gray-50">
                                            <td class="px-3 py-2">
                                                <div class="flex items-center gap-2">
                                                    <img class="w-10 h-10 rounded object-cover" :src="item.item_image" alt="item" />
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-heading">{{ item.item_name }}</p>
                                                        <p v-if="item.item_variations && item.item_variations.length > 0" class="text-xs text-gray-500 truncate">
                                                            <span v-for="(variation, vIndex) in item.item_variations" :key="vIndex">
                                                                {{ variation.variation_name }}: {{ variation.name }}<span v-if="vIndex + 1 < item.item_variations.length">, </span>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button @click.prevent="decreaseTransferQty(index)" type="button"
                                                        class="w-6 h-6 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm">
                                                        <i class="fa-solid fa-minus"></i>
                                                    </button>
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-600 text-white text-sm font-semibold">
                                                        {{ item.transferQty }}
                                                    </span>
                                                    <button @click.prevent="increaseTransferQty(index)" type="button"
                                                        class="w-6 h-6 flex items-center justify-center bg-primary hover:bg-primary-dark text-white rounded text-sm">
                                                        <i class="fa-solid fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button @click.prevent="removeFromTransferList(index)" type="button"
                                                    class="text-red-600 hover:text-red-700">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="transferList.length === 0" class="p-8 text-center text-gray-500">
                                    <i class="lab lab-box lab-font-size-24 mb-2"></i>
                                    <p>{{ $t('message.no_items_to_transfer') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="px-4 pb-2">
                <div class="text-red-500 text-sm bg-red-50 border border-red-200 rounded p-3">
                    {{ errorMessage }}
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between gap-2 p-4 border-t bg-gray-50">
                <div class="text-sm text-gray-600">
                    <span class="font-semibold">{{ transferList.length }}</span> {{ $t('label.items_to_transfer') }}
                </div>
                <div class="flex gap-2">
                    <button @click.prevent="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        {{ $t('button.cancel') }}
                    </button>
                    <button @click.prevent="confirmTransfer"
                        :disabled="!canConfirmTransfer"
                        :class="canConfirmTransfer ? 'bg-primary hover:bg-primary-dark' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-4 py-2 text-sm font-medium text-white rounded-md">
                        {{ $t('button.confirm_transfer') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Order Selection Modal -->
    <OrderSelectionModal
        :show="showOrderSelection"
        :excludeOrderId="sourceOrder?.id"
        @close="showOrderSelection = false"
        @select="onTargetOrderSelected"
    />
</template>

<script>
import OrderSelectionModal from './OrderSelectionModal.vue';

export default {
    name: 'OrderItemTransferModal',
    components: {
        OrderSelectionModal
    },
    props: {
        show: {
            type: Boolean,
            default: false
        },
        sourceOrder: {
            type: Object,
            default: null
        }
    },
    emits: ['close', 'confirm'],
    data() {
        return {
            targetOrder: null,
            transferList: [],
            errorMessage: '',
            showOrderSelection: false
        };
    },
    computed: {
        sourceOrderItems() {
            if (!this.sourceOrder || !this.sourceOrder.order_items) {
                return [];
            }
            return this.sourceOrder.order_items;
        },
        canConfirmTransfer() {
            return this.targetOrder && this.transferList.length > 0;
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.initializeModal();
            }
        }
    },
    methods: {
        initializeModal() {
            this.targetOrder = null;
            this.transferList = [];
            this.errorMessage = '';
            this.showOrderSelection = false;
        },
        selectTargetOrder() {
            this.showOrderSelection = true;
        },
        onTargetOrderSelected(order) {
            this.targetOrder = order;
            this.showOrderSelection = false;
            this.errorMessage = '';
        },
        isItemFullyTransferred(itemId) {
            const sourceItem = this.sourceOrderItems.find(item => item.id === itemId);
            if (!sourceItem) return false;

            const transferredQty = this.transferList
                .filter(item => item.id === itemId)
                .reduce((sum, item) => sum + item.transferQty, 0);

            return transferredQty >= sourceItem.quantity;
        },
        getAvailableQty(itemId) {
            const sourceItem = this.sourceOrderItems.find(item => item.id === itemId);
            if (!sourceItem) return 0;

            const transferredQty = this.transferList
                .filter(item => item.id === itemId)
                .reduce((sum, item) => sum + item.transferQty, 0);

            return sourceItem.quantity - transferredQty;
        },
        transferOne(item) {
            this.errorMessage = '';
            
            if (!this.targetOrder) {
                this.errorMessage = this.$t('message.please_select_target_order');
                return;
            }

            const availableQty = this.getAvailableQty(item.id);
            if (availableQty <= 0) {
                this.errorMessage = this.$t('message.no_more_quantity_to_transfer');
                return;
            }

            // Check if item already exists in transfer list
            const existingIndex = this.transferList.findIndex(t => t.id === item.id);
            
            if (existingIndex >= 0) {
                // Increase quantity
                this.transferList[existingIndex].transferQty += 1;
            } else {
                // Add new item to transfer list
                this.transferList.push({
                    ...item,
                    transferQty: 1
                });
            }
        },
        transferAll(item) {
            this.errorMessage = '';
            
            if (!this.targetOrder) {
                this.errorMessage = this.$t('message.please_select_target_order');
                return;
            }

            const availableQty = this.getAvailableQty(item.id);
            if (availableQty <= 0) {
                this.errorMessage = this.$t('message.no_more_quantity_to_transfer');
                return;
            }

            // Check if item already exists in transfer list
            const existingIndex = this.transferList.findIndex(t => t.id === item.id);
            
            if (existingIndex >= 0) {
                // Set to full quantity
                this.transferList[existingIndex].transferQty += availableQty;
            } else {
                // Add new item to transfer list with full quantity
                this.transferList.push({
                    ...item,
                    transferQty: availableQty
                });
            }
        },
        increaseTransferQty(index) {
            const item = this.transferList[index];
            const availableQty = this.getAvailableQty(item.id);
            
            // Add back current item's qty to available since we're counting it
            const maxQty = availableQty + item.transferQty;
            
            if (item.transferQty < maxQty) {
                this.transferList[index].transferQty += 1;
                this.errorMessage = '';
            }
        },
        decreaseTransferQty(index) {
            if (this.transferList[index].transferQty > 1) {
                this.transferList[index].transferQty -= 1;
                this.errorMessage = '';
            } else {
                this.removeFromTransferList(index);
            }
        },
        removeFromTransferList(index) {
            this.transferList.splice(index, 1);
            this.errorMessage = '';
        },
        async confirmTransfer() {
            if (!this.canConfirmTransfer) {
                return;
            }

            this.errorMessage = '';

            // Prepare transfer data
            const transferData = {
                sourceOrderId: this.sourceOrder.id,
                targetOrderId: this.targetOrder.id,
                items: this.transferList.map(item => ({
                    orderItemId: item.id,
                    quantity: item.transferQty
                }))
            };

            // Emit the transfer event to parent component
            this.$emit('confirm', transferData);
        },
        closeModal() {
            this.errorMessage = '';
            this.$emit('close');
        }
    }
};
</script>

<style scoped>
/* Custom scrollbar for better appearance */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
