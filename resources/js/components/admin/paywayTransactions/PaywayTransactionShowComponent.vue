<template>
    <LoadingComponent :props="loading" />
    
    <div class="col-12 mt-2">
        <div class="db-card p-3">
            <div class="flex flex-wrap gap-3 justify-between items-center">
                <h3 class="db-card-title">{{ $t('label.payway_transaction_details') }}</h3>
                <div class="flex gap-3">
                    <button 
                        v-if="canRefund"
                        @click="initiateRefund" 
                        :disabled="loading.isActive"
                        class="db-btn py-2 text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="lab lab-refund lab-font-size-16"></i>
                        <span>{{ $t('button.refund') }}</span>
                    </button>
                    <button 
                        @click="goBack"
                        class="db-btn py-2 text-white bg-gray-600">
                        <i class="lab lab-arrow-left lab-font-size-16"></i>
                        <span>{{ $t('button.back') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PayWay Transaction Details -->
    <div class="col-12 md:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.transaction_information') }}</h3>
            </div>
            <div class="db-card-body">
                <ul class="flex flex-col gap-3">
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.transaction_id') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction?.tran_id || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.amount') }}:</span>
                        <span class="text-sm text-heading flex-1 font-semibold">
                            {{ currencyFormat(paywayTransaction?.amount, 2, paywayTransaction?.currency, 'right') }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.payment_status') }}:</span>
                        <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl ' + statusClass(paywayTransaction?.payment_status_code)">
                            {{ paywayTransaction?.payment_status || 'N/A' }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction?.payment_amount">
                        <span class="w-40 text-sm font-medium">{{ $t('label.payment_amount') }}:</span>
                        <span class="text-sm text-heading flex-1">
                            {{ currencyFormat(paywayTransaction?.payment_amount, 2, paywayTransaction?.payment_currency, 'right') }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.payment_method') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction?.payment_method?.name || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.apv') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction?.apv || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction?.transaction_date">
                        <span class="w-40 text-sm font-medium">{{ $t('label.transaction_date') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction?.transaction_date }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.created_at') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction?.created_at }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Related Transaction Details (if exists) -->
    <div class="col-12 md:col-6" v-if="relatedTransaction">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.related_transaction') }}</h3>
            </div>
            <div class="db-card-body">
                <ul class="flex flex-col gap-3">
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.transaction_no') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ relatedTransaction?.transaction_no || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.amount') }}:</span>
                        <span class="text-sm text-heading flex-1 font-semibold">
                            {{ currencyFormat(relatedTransaction?.amount, 2, relatedTransaction?.currency, 'right') }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.type') }}:</span>
                        <span class="text-sm text-heading flex-1 capitalize">{{ relatedTransaction?.type || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="relatedTransaction?.sign">
                        <span class="w-40 text-sm font-medium">{{ $t('label.sign') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ relatedTransaction?.sign }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="relatedTransaction?.change_amount">
                        <span class="w-40 text-sm font-medium">{{ $t('label.change_amount') }}:</span>
                        <span class="text-sm text-heading flex-1">
                            {{ currencyFormat(relatedTransaction?.change_amount, 2, relatedTransaction?.change_currency, 'right') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Order Information (if exists) -->
    <div class="col-12 md:col-6" v-if="paywayTransaction?.order">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.order_information') }}</h3>
            </div>
            <div class="db-card-body">
                <ul class="flex flex-col gap-3">
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.order_id') }}:</span>
                        <span class="text-sm text-heading flex-1">
                            <router-link 
                                :to="{ name: 'admin.pos.orders.show', params: { id: paywayTransaction.order.id } }"
                                class="text-blue-600 hover:text-blue-800 underline">
                                #{{ paywayTransaction.order?.order_serial_no }}
                            </router-link>
                        </span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction.order?.invoice_number">
                        <span class="w-40 text-sm font-medium">{{ $t('label.invoice_number') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.order?.invoice_number }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.order_total') }}:</span>
                        <span class="text-sm text-heading flex-1">
                            {{ currencyFormat(paywayTransaction.order?.total, 2, paywayTransaction.order?.currency, 'right') }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.order_status') }}:</span>
                        <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl ' + orderStatusClass(paywayTransaction.order?.status)">
                            {{ getOrderStatusName(paywayTransaction.order?.status) }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.payment_status') }}:</span>
                        <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl ' + statusClass(paywayTransaction.order?.payment_status)">
                            {{ enums.paymentStatusEnumArray[paywayTransaction.order?.payment_status] }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.order_type') }}:</span>
                        <span class="text-sm text-heading flex-1">
                            {{ enums.orderTypeEnumArray[paywayTransaction.order?.order_type] }}
                        </span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.order_datetime') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.order?.order_datetime }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction.order?.customer_name">
                        <span class="w-40 text-sm font-medium">{{ $t('label.customer_name') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.order?.customer_name }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction.order?.customer_phone_number">
                        <span class="w-40 text-sm font-medium">{{ $t('label.phone') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.order?.customer_phone_number }}</span>
                    </li>
                </ul>
                <div class="mt-4">
                    <button 
                        @click="goToOrder" 
                        class="db-btn w-full py-2 text-white bg-primary">
                        <span>{{ $t('button.view_order_details') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Information -->
    <div class="col-12 md:col-6" v-if="paywayTransaction?.branch">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.branch_information') }}</h3>
            </div>
            <div class="db-card-body">
                <ul class="flex flex-col gap-3">
                    <li class="flex items-start gap-2">
                        <span class="w-40 text-sm font-medium">{{ $t('label.branch_name') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.branch?.name || 'N/A' }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction.branch?.email">
                        <span class="w-40 text-sm font-medium">{{ $t('label.email') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.branch?.email }}</span>
                    </li>
                    <li class="flex items-start gap-2" v-if="paywayTransaction.branch?.phone">
                        <span class="w-40 text-sm font-medium">{{ $t('label.phone') }}:</span>
                        <span class="text-sm text-heading flex-1">{{ paywayTransaction.branch?.phone }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from '../components/LoadingComponent';
import alertService from '../../../services/alertService';
import appService from '../../../services/appService';
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';

export default {
    name: 'PaywayTransactionShowComponent',
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                paymentStatusEnum: paymentStatusEnum,
                orderStatusEnum: orderStatusEnum,
                orderTypeEnum: orderTypeEnum,
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t('label.paid'),
                    [paymentStatusEnum.UNPAID]: this.$t('label.unpaid'),
                    [paymentStatusEnum.PARTIALLY_PAID]: this.$t('label.partially_paid'),
                },
                orderStatusEnumArray: {
                    [orderStatusEnum.PENDING]: this.$t('label.pending'),
                    [orderStatusEnum.PROCESSING]: this.$t('label.processing'),
                    [orderStatusEnum.OUT_FOR_DELIVERY]: this.$t('label.out_for_delivery'),
                    [orderStatusEnum.DELIVERED]: this.$t('label.delivered'),
                    [orderStatusEnum.CANCELED]: this.$t('label.canceled'),
                    [orderStatusEnum.RETURNED]: this.$t('label.returned'),
                    [orderStatusEnum.REJECTED]: this.$t('label.rejected'),
                },
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t('label.delivery'),
                    [orderTypeEnum.TAKEAWAY]: this.$t('label.takeaway'),
                    [orderTypeEnum.POS]: this.$t('label.pos'),
                    [orderTypeEnum.DINE_IN]: this.$t('label.dine_in'),
                },
            },
        };
    },
    mounted() {
        this.getTransactionDetails();
    },
    computed: {
        paywayTransaction: function () {
            return this.$store.getters['paywayTransaction/show'];
        },
        relatedTransaction: function () {
            // Return the transaction if it exists in the response
            return this.paywayTransaction?.transaction || null;
        },
        canRefund: function () {
            // Can refund if payment status is successful (code 00 = success in PayWay)
            if (!this.paywayTransaction) return false;
            
            // Check if payment was successful
            const isSuccessful = this.paywayTransaction.payment_status_code === 0 || 
                               this.paywayTransaction.payment_status === 'Success';
            
            // Check if not already refunded (you might want to add a refund status check here)
            return isSuccessful;
        }
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        getOrderStatusName: function (status) {
            return this.enums.orderStatusEnumArray[status] || 'N/A';
        },
        getTransactionDetails: function () {
            this.loading.isActive = true;
            const transactionId = this.$route.params.id;
            
            this.$store.dispatch('paywayTransaction/show', transactionId)
                .then(res => {
                    this.loading.isActive = false;
                    // Debug: Log the response to check if transaction is included
                    console.log('PaywayTransaction Response:', res.data.data);
                    console.log('Transaction exists?', !!res.data.data?.transaction);
                    console.log('Related Transaction:', this.relatedTransaction);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || this.$t('message.something_went_wrong'));
                });
        },
        goBack: function () {
            this.$router.push({ name: 'admin.payway-transactions.list' });
        },
        goToOrder: function () {
            if (this.paywayTransaction?.order?.id) {
                this.$router.push({ 
                    name: 'admin.pos.orders.show', 
                    params: { id: this.paywayTransaction.order.id } 
                });
            }
        },
        initiateRefund: async function () {
            try {
                // Check if there's a related transaction and order
                if (this.relatedTransaction && this.paywayTransaction?.order) {
                    // Alert and redirect to order detail page
                    const result = await appService.confirmDialog(
                        this.$t('message.refund_must_be_done_from_order_page'),
                        this.$t('label.refund_redirect_required')
                    );
                    
                    if (result) {
                        this.goToOrder();
                    }
                } else {
                    // No transaction exists, ask to confirm refund
                    const result = await appService.confirmDialog(
                        this.$t('message.are_you_sure_refund_transaction'),
                        this.$t('label.confirm_refund')
                    );

                    if (result) {
                        this.processRefund();
                    }
                }
            } catch (error) {
                // User cancelled
                console.log('Refund cancelled');
            }
        },
        processRefund: function () {
            this.loading.isActive = true;
            
            const refundData = {
                tran_id: this.paywayTransaction.tran_id,
                refund_amount: this.paywayTransaction.amount,
            };

            this.$store.dispatch('paywayTransaction/refund', refundData)
                .then(res => {
                    this.loading.isActive = false;
                    alertService.success(res.data?.message || this.$t('message.refund_processed_successfully'));
                    // Refresh transaction details
                    this.getTransactionDetails();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || this.$t('message.refund_failed'));
                });
        },
    },
};
</script>
