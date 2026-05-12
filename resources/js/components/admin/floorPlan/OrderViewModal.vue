<template>
    <div class="modal active">
        <div class="modal-dialog max-w-4xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{ $t('label.order_details') }} - #{{ order.order_serial_no }}</h3>
                    <button class="modal-close" @click="$emit('close')">
                        <i class="lab lab-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Order Summary -->
                    <div class="order-summary mb-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="info-card">
                                <div class="info-label">{{ $t('label.order_number') }}</div>
                                <div class="info-value">#{{ order.order_serial_no }}</div>
                            </div>
                            <div class="info-card">
                                <div class="info-label">{{ $t('label.table') }}</div>
                                <div class="info-value">{{ order.dining_table?.name || 'N/A' }}</div>
                            </div>
                            <div class="info-card">
                                <div class="info-label">{{ $t('label.status') }}</div>
                                <div class="info-value">
                                    <span :class="getStatusClass(order.order_status)">
                                        {{ getStatusText(order.order_status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-label">{{ $t('label.total') }}</div>
                                <div class="info-value text-lg font-bold">${{ order.total || 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div v-if="order.customer" class="customer-info mb-6">
                        <h4 class="text-lg font-semibold mb-3">{{ $t('label.customer_information') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-sm text-gray-600">{{ $t('label.name') }}:</span>
                                    <span class="ml-2 font-medium">{{ order.customer.name }}</span>
                                </div>
                                <div v-if="order.customer.phone">
                                    <span class="text-sm text-gray-600">{{ $t('label.phone') }}:</span>
                                    <span class="ml-2 font-medium">{{ order.customer.phone }}</span>
                                </div>
                                <div v-if="order.customer.email">
                                    <span class="text-sm text-gray-600">{{ $t('label.email') }}:</span>
                                    <span class="ml-2 font-medium">{{ order.customer.email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.customer_name || order.customer_phone_number" class="customer-info mb-6">
                        <h4 class="text-lg font-semibold mb-3">{{ $t('label.customer_information') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-sm text-gray-600">{{ $t('label.name') }}:</span>
                                    <span class="ml-2 font-medium">{{ order.customer_name }}</span>
                                </div>
                                <div v-if="order.customer_phone_number">
                                    <span class="text-sm text-gray-600">{{ $t('label.phone') }}:</span>
                                    <span class="ml-2 font-medium">{{ order.customer_phone_number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="order-items mb-6">
                        <h4 class="text-lg font-semibold mb-3">{{ $t('label.order_items') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">{{ $t('label.item') }}</th>
                                        <th class="px-4 py-2 text-center">{{ $t('label.quantity') }}</th>
                                        <th class="px-4 py-2 text-right">{{ $t('label.unit_price') }}</th>
                                        <th class="px-4 py-2 text-right">{{ $t('label.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in order.order_items" :key="item.id" class="border-b">
                                        <td class="px-4 py-3">
                                            <div>
                                                <div class="font-medium">{{ item.item?.name }}</div>
                                                <div v-if="item.item_extras && item.item_extras.length > 0" 
                                                     class="text-sm text-gray-600">
                                                    Extras: {{ item.item_extras.map(e => e.name).join(', ') }}
                                                </div>
                                                <div v-if="item.instruction" class="text-sm text-orange-600">
                                                    Note: {{ item.instruction }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 text-right">${{ item.item_price }}</td>
                                        <td class="px-4 py-3 text-right font-medium">${{ item.total_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="order-totals">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>{{ $t('label.subtotal') }}:</span>
                                    <span>${{ order.subtotal || 0 }}</span>
                                </div>
                                <div v-if="order.tax" class="flex justify-between">
                                    <span>{{ $t('label.tax') }}:</span>
                                    <span>${{ order.tax }}</span>
                                </div>
                                <div v-if="order.discount" class="flex justify-between text-green-600">
                                    <span>{{ $t('label.discount') }}:</span>
                                    <span>-${{ order.discount }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>{{ $t('label.total') }}:</span>
                                    <span>${{ order.total || 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timestamps -->
                    <div class="order-timestamps mt-6">
                        <h4 class="text-lg font-semibold mb-3">{{ $t('label.timestamps') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="info-card">
                                <div class="info-label">{{ $t('label.order_placed') }}</div>
                                <div class="info-value">{{ formatDateTime(order.created_at) }}</div>
                            </div>
                            <div v-if="order.updated_at !== order.created_at" class="info-card">
                                <div class="info-label">{{ $t('label.last_updated') }}</div>
                                <div class="info-value">{{ formatDateTime(order.updated_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="db-btn py-2 text-white bg-gray-600" @click="$emit('close')">
                        {{ $t('button.close') }}
                    </button>
                    <button v-if="canPrintReceipt" class="db-btn py-2 text-white bg-blue-500" @click="printReceipt">
                        {{ $t('button.print_receipt') }}
                    </button>
                    <button v-if="canEditOrder" class="db-btn py-2 text-white bg-green-500" @click="editOrder">
                        {{ $t('button.edit_order') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// import orderStatusEnum from "../../../../enums/modules/orderStatusEnum";
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';

export default {
    name: "OrderViewModal",
    props: {
        order: {
            type: Object,
            required: true
        }
    },
    computed: {
        canPrintReceipt() {
            return this.order && this.order.order_status !== orderStatusEnum.PENDING;
        },
        
        canEditOrder() {
            return this.order && [orderStatusEnum.PENDING, orderStatusEnum.CONFIRMED].includes(this.order.order_status);
        }
    },
    methods: {
        formatDateTime(timestamp) {
            if (!timestamp) return '';
            return new Date(timestamp).toLocaleString();
        },
        
        getStatusClass(status) {
            switch (status) {
                case orderStatusEnum.PENDING:
                    return 'text-yellow-600 bg-yellow-100 px-2 py-1 rounded';
                case orderStatusEnum.CONFIRMED:
                    return 'text-blue-600 bg-blue-100 px-2 py-1 rounded';
                case orderStatusEnum.PROCESSING:
                    return 'text-orange-600 bg-orange-100 px-2 py-1 rounded';
                case orderStatusEnum.OUT_FOR_DELIVERY:
                    return 'text-purple-600 bg-purple-100 px-2 py-1 rounded';
                case orderStatusEnum.DELIVERED:
                    return 'text-green-600 bg-green-100 px-2 py-1 rounded';
                case orderStatusEnum.CANCELED:
                    return 'text-red-600 bg-red-100 px-2 py-1 rounded';
                default:
                    return 'text-gray-600 bg-gray-100 px-2 py-1 rounded';
            }
        },
        
        getStatusText(status) {
            switch (status) {
                case orderStatusEnum.PENDING:
                    return this.$t('label.pending');
                case orderStatusEnum.CONFIRMED:
                    return this.$t('label.confirmed');
                case orderStatusEnum.PROCESSING:
                    return this.$t('label.processing');
                case orderStatusEnum.OUT_FOR_DELIVERY:
                    return this.$t('label.out_for_delivery');
                case orderStatusEnum.DELIVERED:
                    return this.$t('label.delivered');
                case orderStatusEnum.CANCELED:
                    return this.$t('label.canceled');
                default:
                    return this.$t('label.unknown');
            }
        },
        
        printReceipt() {
            // Implement receipt printing logic
            window.print();
        },
        
        editOrder() {
            // Navigate to order edit page or emit event
            this.$router.push({ name: 'admin.order.edit', params: { id: this.order.id } });
        }
    }
}
</script>

<style scoped>
.modal {
    position: fixed;
    /* z-index: 1000; */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-dialog {
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #6b7280;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
}

.info-card {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.info-value {
    font-weight: 600;
    color: #374151;
}

.table-auto th {
    font-weight: 600;
    color: #374151;
}

.table-auto td {
    color: #6b7280;
}
</style>
