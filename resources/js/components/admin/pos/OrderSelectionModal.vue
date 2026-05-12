<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-heading">{{ $t('label.select_target_order') }}</h3>
                <button @click.prevent="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Search -->
            <div class="p-4 border-b bg-gray-50">
                <div class="relative">
                    <input v-model="searchQuery" type="text"
                        class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        :placeholder="$t('placeholder.search_order')" />
                    <i class="lab lab-search-normal absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex-1 flex items-center justify-center p-8">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <p class="mt-2 text-sm text-gray-500">{{ $t('message.loading') }}</p>
                </div>
            </div>

            <!-- Orders List -->
            <div v-else class="flex-1 overflow-y-auto">
                <div v-if="filteredOrders.length === 0" class="p-8 text-center text-gray-500">
                    <i class="lab lab-clipboard-text lab-font-size-24 mb-2"></i>
                    <p>{{ $t('message.no_orders_found') }}</p>
                </div>
                <div v-else class="divide-y divide-gray-200">
                    <div v-for="order in filteredOrders" :key="order.id"
                        @click="selectOrder(order)"
                        class="p-4 hover:bg-gray-50 cursor-pointer transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-semibold text-heading">{{ $t('label.order') }} #{{ order.order_serial_no }}</h4>
                                        </div>
                                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                            <span>
                                                <i class="lab lab-calendar lab-font-size-12"></i> {{ formatDate(order.order_datetime) }}
                                            </span>
                                            <span v-if="order.order_items">
                                                <i class="lab lab-clipboard-text lab-font-size-12"></i> {{ order.order_items.length }} {{ $t('label.items') }}
                                            </span>
                                        </div>
                                        <!-- Display dining tables as badges -->
                                        <div class="flex flex-wrap gap-1 mt-2" v-if="order.order_dinings && order.order_dinings.length > 0">
                                            <span
                                                v-for="(orderDining, idx) in order.order_dinings"
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
                            <div class="flex-shrink-0 ml-4">
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-heading">{{ order.total_currency_price }}</p>
                                    <span :class="getPaymentStatusClass(order.payment_status)" class="text-xs px-2 py-0.5 rounded-full inline-block mt-1">
                                        {{ getPaymentStatusText(order.payment_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 p-4 border-t bg-gray-50">
                <button @click.prevent="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ $t('button.cancel') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';

export default {
    name: 'OrderSelectionModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        excludeOrderId: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            loading: false,
            orders: [],
            searchQuery: '',
            orderStatusEnum: orderStatusEnum,
            paymentStatusEnum: paymentStatusEnum
        };
    },
    computed: {
        filteredOrders() {
            let filtered = this.orders;

            // Exclude source order
            if (this.excludeOrderId) {
                filtered = filtered.filter(order => order.id !== this.excludeOrderId);
            }

            // Search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(order => {
                    // Get table names from order_dinings
                    const tableNames = (order.order_dinings || [])
                        .map(od => od.dining_table?.name)
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();
                    
                    return (
                        order.order_serial_no?.toString().includes(query) ||
                        tableNames.includes(query) ||
                        order.customer_name?.toLowerCase().includes(query)
                    );
                });
            }

            return filtered;
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.loadOrders();
            } else {
                this.searchQuery = '';
            }
        }
    },
    methods: {
        async loadOrders() {
            this.loading = true;
            try {
                // Load unpaid orders from the store
                const response = await this.$store.dispatch('posOrderUnpaid/lists', {
                    paginate: 0,
                    payment_status: paymentStatusEnum.UNPAID
                });
                
                if (response.data && response.data.data) {
                    this.orders = response.data.data;
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                this.orders = [];
            } finally {
                this.loading = false;
            }
        },
        selectOrder(order) {
            this.$emit('select', order);
        },
        closeModal() {
            this.$emit('close');
        },
        getOrderStatusClass(status) {
            const statusClasses = {
                [orderStatusEnum.PENDING]: 'bg-yellow-100 text-yellow-800',
                [orderStatusEnum.CONFIRMED]: 'bg-blue-100 text-blue-800',
                [orderStatusEnum.PROCESSING]: 'bg-purple-100 text-purple-800',
                [orderStatusEnum.OUT_FOR_DELIVERY]: 'bg-indigo-100 text-indigo-800',
                [orderStatusEnum.DELIVERED]: 'bg-green-100 text-green-800',
                [orderStatusEnum.CANCELED]: 'bg-red-100 text-red-800',
                [orderStatusEnum.REJECTED]: 'bg-red-100 text-red-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        getOrderStatusText(status) {
            const statusTexts = {
                [orderStatusEnum.PENDING]: this.$t('label.pending'),
                [orderStatusEnum.CONFIRMED]: this.$t('label.confirmed'),
                [orderStatusEnum.PROCESSING]: this.$t('label.processing'),
                [orderStatusEnum.OUT_FOR_DELIVERY]: this.$t('label.out_for_delivery'),
                [orderStatusEnum.DELIVERED]: this.$t('label.delivered'),
                [orderStatusEnum.CANCELED]: this.$t('label.canceled'),
                [orderStatusEnum.REJECTED]: this.$t('label.rejected')
            };
            return statusTexts[status] || status;
        },
        getPaymentStatusClass(status) {
            const statusClasses = {
                [paymentStatusEnum.PAID]: 'bg-green-100 text-green-800',
                [paymentStatusEnum.UNPAID]: 'bg-red-100 text-red-800',
                [paymentStatusEnum.PARTIAL_PAID]: 'bg-yellow-100 text-yellow-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        getPaymentStatusText(status) {
            const statusTexts = {
                [paymentStatusEnum.PAID]: this.$t('label.paid'),
                [paymentStatusEnum.UNPAID]: this.$t('label.unpaid'),
                [paymentStatusEnum.PARTIAL_PAID]: this.$t('label.partial_paid')
            };
            return statusTexts[status] || status;
        },
        formatDate(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            return date.toLocaleString();
        }
    }
};
</script>

<style scoped>
/* Custom scrollbar */
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
