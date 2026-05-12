<template>
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">

            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-center capitalize">
                    {{ paymentCompleted ? $t("label.payment_complete") : $t("label.payment") }}
                </h3>
            </div>

            <!-- Payment Completed View -->
            <div v-if="paymentCompleted" class="p-6 flex flex-col items-center justify-center space-y-6">
                <!-- Success Icon -->
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-5xl text-green-600"></i>
                </div>

                <!-- Success Message -->
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600 mb-2">{{ $t("label.payment_successful") }}</p>
                    <!-- <p class="text-sm text-gray-600">{{ $t("label.payment_completed_please_confirm") }}</p> -->
                </div>

                <!-- Payment Amount Display -->
                <!-- <div class="text-center bg-gray-50 rounded-lg p-4 w-full">
                    <p class="text-sm text-gray-600">{{ $t("label.amount_paid") }}</p>
                    <p class="text-3xl font-bold text-primary">
                        {{ currencyFormat(totalAmount, setting.site_digit_after_decimal_point,
                            setting.site_default_currency_symbol, setting.site_currency_position) }}
                    </p>
                </div> -->

                <!-- OK Button -->
                <button
                    @click="handleOkClick"
                    type="button"
                    class="w-full px-6 py-4 text-center rounded-xl text-white bg-green-600 hover:bg-green-700 transition-colors font-semibold"
                >
                    {{ $t("button.ok") }}
                </button>
            </div>

            <!-- Waiting for Payment View -->
            <div v-else class="p-6 flex flex-col items-center justify-center space-y-6">
                <!-- QR Code Expired View -->
                <template v-if="qrExpired">
                    <div class="w-64 h-64 bg-orange-50 rounded-lg flex flex-col items-center justify-center gap-4 border-2 border-orange-200">
                        <i class="fa-solid fa-clock-rotate-left text-5xl text-orange-400"></i>
                        <p class="text-base font-semibold text-orange-600 text-center">{{ $t("label.qr_code_expired") }}</p>
                    </div>
                    <!-- Try Again Button -->
                    <button
                        @click="retryPayment"
                        type="button"
                        class="w-full px-6 py-4 text-center rounded-xl text-white bg-primary hover:bg-primary-dark transition-colors font-semibold"
                    >
                        <i class="fa-solid fa-rotate-right mr-2"></i>
                        {{ $t("button.try_again") }}
                    </button>
                    <!-- Cancel Button -->
                    <button
                        @click="confirmCancelPayment"
                        type="button"
                        class="w-full px-6 py-3 text-center rounded-xl text-white bg-red-500 hover:bg-red-600 transition-colors font-medium"
                    >
                        {{ $t("button.cancel") }}
                    </button>
                </template>

                <!-- Normal QR / Waiting View -->
                <template v-else>
                    <!-- QR Code Display -->
                    <div v-if="paymentQrCode" class="bg-white">
                        <img :src="paymentQrCode" alt="Payment QR Code" class="w-full object-contain" />
                    </div>
                    <div v-else class="w-64 h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-6xl text-gray-400"></i>
                    </div>

                    <!-- Waiting Text with Animation -->
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-spinner fa-spin text-primary text-xl"></i>
                        <p class="text-lg font-medium text-gray-900">{{ $t("label.waiting_customer_make_payment") }}</p>
                    </div>

                    <!-- Payment Amount Display -->
                    <!-- <div class="text-center">
                        <p class="text-sm text-gray-600">{{ $t("label.amount") }}</p>
                        <p class="text-2xl font-bold text-primary">
                            {{ currencyFormat(totalAmount, setting.site_digit_after_decimal_point,
                                setting.site_default_currency_symbol, setting.site_currency_position) }}
                        </p>
                    </div> --> 
                    <!-- Open ABA Pay Button -->
                    <button
                        v-if="paymentAbapayDeeplink"
                        @click="openAbaPayApp"
                        type="button"
                        class="w-full p-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-colors flex items-center justify-between shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Payment Method Logo -->
                            <div v-if="paymentAbapayDeeplink" class="w-10 h-10">
                                <img src="/images/payment-gateway/aba.png" alt="ABA Pay Logo" class="w-full h-full object-contain rounded-lg border"
                                />
                            </div>
                            <div v-else class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-bold text-sm">PAY</span>
                            </div>
                            <!-- Text Content -->
                            <span class="text-gray-800 font-medium text-sm flex items-center">
                                Tap to pay with ABA Mobile
                            </span>
                        </div>
                        <!-- Pay Now Button -->
                        <div class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                            Pay Now
                        </div>

                    </button> 
                    <!-- Cancel Button -->
                    <button
                        @click="confirmCancelPayment"
                        type="button"
                        class="w-full px-6 py-3 text-center rounded-xl text-white bg-red-500 hover:bg-red-600 transition-colors font-medium"
                    >
                        {{ $t("button.cancel") }}
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import appService from '../../../services/appService';
import alertService from '../../../services/alertService';

export default {
    name: 'TelegramMiniAppPaywayCheckoutComponent',
    props: {
        showModal: {
            type: Boolean,
            default: false
        },
        orderData: {
            type: Object,
            required: true
        },
        totalAmount: {
            type: Number,
            required: true
        },
        paymentCurrency: {
            type: Object,
            default: null
        },
        currencyCode: {
            type: String,
            default: 'KHR'
        },
    },
    emits: ['close', 'orderCreated'],
    data() {
        return {
            paymentQrCode: null,
            paymentPollingInterval: null,
            paymentTransactionId: null,
            paymentAbapayDeeplink: null,
            maxPollingAttempts: 120, // 120 attempts * 5 seconds = 10 minutes
            pollingAttempts: 0,
            paymentCompleted: false,
            paymentCompletedData: null,
            createdOrderData: null,
            qrExpired: false,
            qrExpiryTimer: null,
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        branch: function () {
            return this.$store.getters['telegramMiniApp/branch/show'];
        }
    },
    watch: {
        showModal(newVal) {
            if (newVal) {
                this.initiatePayWayPayment();
            } else {
                this.stopPaymentPolling();
                this.stopQrExpiryTimer();
                this.qrExpired = false;
            }
        }
    },
    beforeUnmount() {
        this.stopPaymentPolling();
        this.stopQrExpiryTimer();
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        initiatePayWayPayment: async function () {
            this.qrExpired = false;
            this.stopQrExpiryTimer();

            // Step 1: Set order currencies before creating the order
            this.orderData.currency = this.branch?.currency_id?.code;
            this.orderData.currency_id = this.branch?.currency_id?.id;
            if (this.paymentCurrency) {
                this.orderData.receive_payment_currency = this.paymentCurrency.code;
                this.orderData.receive_payment_currency_id = this.paymentCurrency.id;
            } else if (this.currencyCode) {
                this.orderData.receive_payment_currency = this.currencyCode;
            } else {
                this.orderData.receive_payment_currency = this.branch?.currency_id?.code;
                this.orderData.receive_payment_currency_id = this.branch?.currency_id?.id;
            }

            // Step 2: Create order BEFORE generating QR so order_id is available
            try {
                const orderResponse = await this.$store.dispatch('telegramMiniApp/order/save', this.orderData);
                this.createdOrderData = orderResponse.data.data;
                console.log('Order created before QR. order_id:', this.createdOrderData.id);
                localStorage.setItem('telegram_mini_app_last_order_id', this.createdOrderData.id);
                this.$emit('orderCreated', this.createdOrderData);
            } catch (err) {
                const errorMessage = err.response?.data?.status?.message || this.$t('message.order_creation_failed');
                alertService.error(errorMessage);
                console.error('Order creation before QR failed:', err);
                this.$emit('close');
                return;
            }

            // Step 3: Generate QR with order_id
            this.generateQRCode(this.createdOrderData.id);
        },
        generateQRCode: function (orderId) {
            const paymentData = {
                amount: this.totalAmount,
                currency: this.currencyCode || this.orderData.currency || 'KHR',
                payment_method_id: this.orderData.payment_method_id,
                branch_id: this.orderData.branch_id,
                order_items: this.prepareOrderItems(),
                telegram_user_id: this.orderData.telegram_user_id || null,
                order_id: orderId,
            };

            this.$store.dispatch('telegramMiniApp/payway/generateQR', paymentData)
                .then((response) => {
                    this.paymentQrCode = response.data.data.qrImage;
                    this.paymentTransactionId = response.data.data.tran_id;
                    this.paymentAbapayDeeplink = response.data.data.abapay_deeplink;

                    const lifetime = response.data.data.lifetime ?? 3;
                    console.log('PayWay QR generated:', this.paymentTransactionId, 'lifetime:', lifetime, 'min');

                    this.startQrExpiryTimer(lifetime);
                    this.startPaymentPolling();
                })
                .catch((err) => {
                    const errorMessage = err.response?.data?.status?.message || this.$t('message.failed_to_initiate_payment');
                    alertService.error(errorMessage);
                    console.error('PayWay QR generation error:', err);
                    this.$emit('close');
                });
        },
        prepareOrderItems: function () {
            try {
                // Check if items is already an array or needs to be parsed
                let items = this.orderData.items;

                if (typeof items === 'string') {
                    items = JSON.parse(items);
                }

                // If items is not an array, return empty array
                if (!Array.isArray(items)) {
                    console.warn('Order items is not an array:', items);
                    return [];
                }

                return items.map(item => ({
                    name: item.item_name || `Item ${item.item_id}`,
                    quantity: item.quantity || 1,
                    price: parseFloat(item.item_price || item.price || 0)
                }));
            } catch (e) {
                console.error('Failed to parse order items:', e);
                return [];
            }
        },
        startQrExpiryTimer: function (lifetimeMinutes) {
            this.stopQrExpiryTimer();
            const ms = lifetimeMinutes * 60 * 1000;
            this.qrExpiryTimer = setTimeout(() => {
                this.stopPaymentPolling();
                this.paymentQrCode = null;
                this.qrExpired = true;
                console.log('PayWay QR code expired after', lifetimeMinutes, 'min');
            }, ms);
        },
        stopQrExpiryTimer: function () {
            if (this.qrExpiryTimer) {
                clearTimeout(this.qrExpiryTimer);
                this.qrExpiryTimer = null;
            }
        },
        retryPayment: function () {
            // Close old transaction (non-blocking) before requesting a new QR
            if (this.paymentTransactionId) {
                this.$store.dispatch('telegramMiniApp/payway/closeTransaction', {
                    tran_id: this.paymentTransactionId
                }).catch(() => {/* ignore */});
            }
            this.paymentTransactionId = null;
            this.paymentAbapayDeeplink = null;
            this.pollingAttempts = 0;
            this.qrExpired = false;
            // Order already created — regenerate QR with the same order_id
            this.generateQRCode(this.createdOrderData?.id ?? null);
        },
        startPaymentPolling: function () {
            this.pollingAttempts = 0;
            this.paymentPollingInterval = setInterval(() => {
                this.checkPaymentStatus();
            }, 5000); // Poll every 5 seconds
        },
        stopPaymentPolling: function () {
            if (this.paymentPollingInterval) {
                clearInterval(this.paymentPollingInterval);
                this.paymentPollingInterval = null;
            }
        },
        checkPaymentStatus: function () {
            this.pollingAttempts++;

            if (this.pollingAttempts >= this.maxPollingAttempts) {
                this.stopPaymentPolling();
                this.handlePaymentTimeout();
                return;
            }

            this.$store.dispatch('telegramMiniApp/payway/checkTransaction', {
                tran_id: this.paymentTransactionId
            })
                .then((response) => {
                    const data = response.data.data;
                    const statusCode = data.payment_status_code;
                    const paymentStatus = data.payment_status;

                    if (statusCode === 0 && (paymentStatus === 'APPROVED' || paymentStatus === 'PRE-AUTH')) {
                        this.handlePaymentSuccess(data);
                    } else if (statusCode === 3 || statusCode === 7) {
                        this.handlePaymentFailure(data);
                    }
                })
                .catch((err) => {
                    console.error('PayWay transaction status check error:', err);
                });
        },
        handlePaymentSuccess: function (paymentData) {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();

            this.paymentCompletedData = paymentData;
            console.log('Payment Successful:', paymentData);

            // Order was already created before QR — just show the success view
            this.paymentCompleted = true;
        },
        handleOkClick: function () {
            alertService.success(this.$t('message.payment_successful'));

            // Navigate directly to the order details page
            this.$router.push({
                name: 'telegram.mini.app.order.details',
                params: { slug: this.branch.telegram_mini_app_slug, id: this.createdOrderData.id }
            });

            // Close modal
            this.$emit('close');
        },
        handlePaymentFailure: function (paymentData) {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();

            alertService.error(this.$t('message.payment_failed'));

            this.paymentQrCode = null;
            this.paymentTransactionId = null;
            this.paymentCompleted = false;

            this.$emit('close');
        },
        handlePaymentTimeout: function () {
            this.stopQrExpiryTimer();
            alertService.error(this.$t('message.payment_timeout'));
            this.$emit('close');
        },
        confirmCancelPayment: function () {
            if (confirm(this.$t('message.confirm_cancel_payment'))) {
                this.cancelPayment();
            }
        },
        cancelPayment: function () {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();

            if (this.paymentTransactionId) {
                // Try to close the transaction, but don't block the cancellation if it fails
                this.$store.dispatch('telegramMiniApp/payway/closeTransaction', {
                    tran_id: this.paymentTransactionId
                }).then(() => {
                    console.log('PayWay transaction closed successfully');
                }).catch((err) => {
                    // Log the error but don't show it to the user
                    // The transaction might already be expired, cancelled, or in a state that cannot be closed
                    console.warn('PayWay transaction close failed (non-critical):', err.response?.data || err.message);
                });
            }

            // Reset payment state regardless of close transaction result
            this.paymentQrCode = null;
            this.paymentTransactionId = null;
            this.paymentAbapayDeeplink = null;
            this.pollingAttempts = 0;
            this.paymentCompleted = false;
            this.qrExpired = false;

            alertService.info(this.$t('message.payment_cancelled'));
            this.$emit('close');
        },
        openAbaPayApp: function () {
            if (this.paymentAbapayDeeplink) {
                // Try Telegram WebApp API first
                if (window.Telegram && window.Telegram.WebApp) {
                    try {
                        // Note: try_instant_view is not supported in Telegram WebApp v6.0
                        window.Telegram.WebApp.openLink(this.paymentAbapayDeeplink);
                    } catch (error) {
                        console.error('Error opening ABA Pay via Telegram:', error);
                        window.open(this.paymentAbapayDeeplink, '_blank');
                    }
                } else {
                    window.open(this.paymentAbapayDeeplink, '_blank');
                }
            }
        }
    }
};
</script>
