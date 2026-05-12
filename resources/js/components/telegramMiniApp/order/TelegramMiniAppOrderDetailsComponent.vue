<template>
    <TelegramMiniAppLoadingComponent :props="loading" />
    <section class="pt-4 pb-28 px-4 min-h-screen bg-gray-50">
        <div class="max-w-lg mx-auto space-y-4">
            <!-- Order Header Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex justify-between gap-3">
                    <div class="flex gap-3 items-center">
                        <div class="w-10 h-10 flex items-center justify-center shadow-sm bg-white/80 backdrop-blur-sm rounded-xl hover:shadow-gray-300/50 border border-gray-200/50 flex-shrink-0">
                            <i class="fa-solid fa-shop text-primary text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-gray-900 leading-tight">{{ orderBranch.name }}</h1>
                            <p class="text-xs text-gray-500">{{ orderBranch.address }}</p>
                        </div>
                    </div>
                    
                    <div class="justify-end flex-1 flex">
                        <div v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)">
                            <a :href="'tel:' + orderBranch.phone" 
                               class="w-11 h-11 rounded-full flex items-center justify-center bg-primary text-white shadow-md hover:bg-primary-dark transition-colors">
                                <i class="lab lab-call-calling text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
             </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <!-- Order ID and Date -->
                <div class="mb-4">
                    <h1 class="text-base font-semibold mb-1">
                        {{ $t("label.order_id") }}: 
                        <span class="text-primary font-bold">#{{ order.order_serial_no }}</span>
                    </h1>
                    <p class="text-xs text-gray-600">{{ order.order_datetime }}</p>
                </div>

                <!-- Order Status -->
                <div class="mb-4">
                    <OrderStatusComponent :props="order" />
                </div>

                <!-- Restaurant Info -->
                <!-- <div class="border-t pt-4">
                    <h3 class="font-semibold text-sm mb-3">{{ orderBranch.name }}</h3>
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 flex-1">
                            <i class="lab lab-location text-primary mt-1 flex-shrink-0 text-sm"></i>
                            <span class="text-xs leading-relaxed text-gray-700">{{ orderBranch.address }}</span>
                        </div>
                        <div v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)">
                            <a :href="'tel:' + orderBranch.phone" 
                               class="w-11 h-11 rounded-full flex items-center justify-center bg-primary text-white shadow-md hover:bg-primary-dark transition-colors">
                                <i class="lab lab-call-calling text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div> -->

                <!-- Rejection Info -->
                <div v-if="parseInt(order.status) === parseInt(enums.orderStatusEnum.REJECTED)" 
                     class="border-t pt-4 mt-4">
                    <h3 class="font-semibold text-sm mb-3 text-red-600">{{ $t("label.reason") }}:</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-2">
                        <p class="text-sm text-red-800 mb-2">{{ order.rejection_reason }}</p>
                        <p class="text-sm text-red-700">
                            {{ $t("label.rejected_at") }}: {{ order.rejected_at }}
                        </p>
                        <div class="bg-orange-100 border border-orange-300 rounded-lg p-3 mt-3">
                            <p class="text-sm text-orange-800 flex items-start gap-2">
                                <i class="lab lab-info-circle mt-0.5 flex-shrink-0"></i>
                                <span>{{ $t("label.note") }}: {{ $t("label.if_paid_check_refund_amount") }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <!-- Header -->
                <div class="ps-2 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <i class="lab lab-shopping-cart text-primary"></i>
                        {{ $t('label.order_details') }}
                    </h3>
                </div>

                <!-- Order Items -->
                <div class="p-3">
                    <div class="space-y-4" v-if="orderItems.length > 0">
                        <div v-for="item in orderItems" :key="item" 
                             class="border border-gray-100 rounded-lg p-4 relative">
                            <!-- Quantity Badge -->
                            <div class="absolute -top-2 -left-2 w-7 h-7 bg-primary text-white text-sm font-bold rounded-full flex items-center justify-center shadow-md">
                                {{ item.quantity }}
                            </div>

                            <!-- Item Content -->
                            <div class="flex gap-4">
                                <img class="w-20 h-20 rounded-lg flex-shrink-0 object-cover border border-gray-200" 
                                     :src="item.item_image" 
                                     alt="thumbnail">
                                <div class="flex-1 min-w-0">
                                    <!-- Item Name -->
                                    <h4 class="font-semibold text-sm text-gray-900 mb-2 leading-tight">
                                        {{ item.item_name }}
                                    </h4>

                                    <!-- Variations -->
                                    <div v-if="item.item_variations.length > 0" class="mb-2">
                                        <div v-for="variation in item.item_variations" :key="variation" 
                                             class="text-xs text-gray-600 mb-1">
                                            <span class="font-medium">{{ variation.variation_name }}:</span>
                                            <span class="ml-1">{{ variation.name }}</span>
                                        </div>
                                    </div>

                                    <!-- Extras -->
                                    <div v-if="item.item_extras.length > 0" class="mb-2">
                                        <span class="text-xs font-medium text-gray-600">{{ $t('label.extras') }}:</span>
                                        <span class="text-xs text-gray-700 ml-1">
                                            <span v-for="(extra, index) in item.item_extras" :key="index">
                                                {{ extra.name }}<span v-if="index + 1 < item.item_extras.length">, </span>
                                            </span>
                                        </span>
                                    </div>

                                    <!-- Instructions -->
                                    <div v-if="item.instruction" class="mb-3">
                                        <span class="text-xs font-medium text-gray-600">{{ $t('label.instruction') }}:</span>
                                        <p class="text-xs text-gray-700 mt-1 bg-gray-50 p-2 rounded">{{ item.instruction }}</p>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-right">
                                        <span class="text-base font-bold text-primary">{{ item.total_currency_price }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Order Summary -->
                <div class="border-t border-gray-100 p-4">
                    <!-- Subtotal -->
                    <!-- <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                        <span class="text-xs font-medium text-gray-600">{{ $t("label.subtotal") }}</span>
                        <span class="text-xs font-semibold text-gray-900">
                            {{ order.subtotal_currency_price }}
                        </span>
                    </div> -->
                    
                    <!-- Total -->
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-gray-900">{{ $t("label.total") }}</span>
                        <span class="text-base font-bold text-primary">
                            {{ order.total_currency_price }}
                        </span>
                    </div>

                    <!-- <div class="flex items-center justify-between" v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)">
                        <span class="text-base font-bold text-gray-600">{{ $t("label.payment_method") }}</span>
                        <span class="text-base font-bold">
                            {{ order.payment_method_name }} 
                        </span>
                    </div> -->

                    <!-- Transaction Details -->
                    <div v-if="order.transactions && order.transactions.length > 0" class="border-t border-gray-200 mt-4 pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="lab lab-receipt text-primary text-sm"></i>
                            <!-- {{ $t("label.transaction_details") || "Transaction Details" }} -->
                            {{ $t("label.payment_info") || "Payment Info" }}
                        </h4>
                        <div class="space-y-3">
                            <div v-for="transaction in order.transactions" :key="transaction.id" 
                                 class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <!-- Transaction Number -->
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-600">{{ $t("label.transaction_no") || "Transaction No" }}:</span>
                                    <span class="text-xs font-semibold text-gray-900">{{ transaction.transaction_no }}</span>
                                </div>
                                
                                <!-- Transaction Amount (Actual Payment) -->
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-600">{{ $t("label.paid_amount") || "Paid Amount" }}:</span>
                                    <div>
                                        <span class="text-xs font-bold text-green-600">
                                            {{ transaction.transaction_amount }} {{ transaction.transaction_currency }}
                                        </span>
                                        <span v-if="transaction.currency != transaction.transaction_currency" class="text-xs font-bold text-green-600 ps-1">
                                            ( {{ transaction.amount }} {{ transaction.currency }} )
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Payment Method -->
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-600">{{ $t("label.method") }}:</span>
                                    <span class="text-xs font-semibold text-gray-900">{{ transaction.payment_method }}</span>
                                </div>
                                
                                <!-- Transaction Date -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-600">{{ $t("label.date") }}:</span>
                                    <span class="text-xs text-gray-700">{{ transaction.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info Card -->
            <!-- <div v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)" 
                 class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                    <i class="lab lab-credit-card text-primary"></i>
                    {{ $t("label.payment_info") }}
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-600">{{ $t("label.method") }}:</span>
                        <span v-if="order" class="text-xs font-semibold text-gray-900">
                            {{ order.payment_method_name }} 
                        </span>
                        <span v-else class="text-xs font-semibold text-gray-900">
                            {{ enums.paymentTypeEnumArray[order.payment_method] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-600">{{ $t("label.status") }}:</span>
                        <span class="text-xs font-bold px-3 py-1 rounded-full" 
                              :class="enums.paymentStatusEnum.PAID === order.payment_status ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'">
                            {{ enums.paymentStatusEnumArray[order.payment_status] }}
                        </span>
                    </div>
                </div>
            </div> -->

            <!-- Floating Action Buttons -->
            <div class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none">
                <div class="container px-4 pb-4">
                    <div class="pointer-events-auto w-full min-h-[72px] flex items-center justify-center px-5 py-4 bg-white/30 backdrop-blur-sm rounded-2xl shadow-2xl hover:shadow-primary/50 transition-all active:scale-95 border border-gray-200/50">
                        <!-- My Orders Button (only show if user has orders) -->
                        <div class="flex gap-3 justify-center w-full">
                            <router-link 
                                v-if="orderCount > 0"
                                :to="{ name: 'telegram.mini.app.orders', params: { slug: this.$route.params.slug } }"
                                class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 transition-all duration-200 shadow-md">
                                <i class="fa-solid fa-list text-sm text-white"></i>
                                <span class="text-sm font-semibold text-white whitespace-nowrap">{{ $t('label.my_orders') || 'My Orders' }}</span>
                            </router-link>
                            
                            <!-- Home Button -->
                            <button 
                                @click="goHome"
                                class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-primary hover:bg-primary-dark transition-all duration-200 shadow-md">
                                <i class="fa-solid fa-home text-sm text-white"></i>
                                <span class="text-sm font-semibold text-white whitespace-nowrap">{{ $t('label.home') || 'Home' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </section>
</template>

<script>
import TelegramMiniAppLoadingComponent from "../../telegramMiniApp/components/TelegramMiniAppLoadingComponent.vue";
import OrderStatusComponent from "../../table/components/OrderStatusComponent.vue";
import OrderReceiptComponent from "../../table/order/OrderReceiptComponent.vue";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import paymentTypeEnum from "../../../enums/modules/paymentTypeEnum";
import activityEnum from "../../../enums/modules/activityEnum";

export default {
    name : "TelegramMiniAppOrderDetailsComponent",
    components: {OrderReceiptComponent, OrderStatusComponent, TelegramMiniAppLoadingComponent},
    data() {
        return {
            loading: {
                isActive: false,
            },
            isFromNotification: false,
            enums: {
                activityEnum: activityEnum,
                orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                    [orderTypeEnum.TELEGRAM_MINI_APP]: this.$t("label.telegram_mini_app"),
                },
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t("label.paid"),
                    [paymentStatusEnum.UNPAID]: this.$t("label.unpaid")
                },
                paymentTypeEnumArray: {
                    [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash"),
                    [paymentTypeEnum.E_WALLET]: this.$t("label.e_wallet"),
                    [paymentTypeEnum.PAYPAL]: this.$t("label.paypal")
                },
            }
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        order: function () {
            return this.$store.getters['telegramMiniApp/order/show'];
        },
        orderBranch: function () {
            return this.$store.getters['telegramMiniApp/order/orderBranch'];
        },
        orderItems: function () {
            return this.$store.getters['telegramMiniApp/order/orderItems'];
        },
        orderCount: function () {
            return this.$store.getters['telegramMiniApp/order/orderCount'];
        },
        telegramUserData: function () {
            return this.$store.getters['telegramMiniApp/order/telegramUserData'] || {};
            // return { telegram_user_id: '708383989' }
        }
    },
    mounted() {
        this.loading.isActive = true;
        const orderId = this.$route.params.id;
        
        // Validate order ID exists and is not undefined/null/empty
        if (!orderId || orderId === 'undefined' || orderId === undefined) {
            console.error('Invalid order ID:', orderId);
            this.loading.isActive = false;
            return;
        }

        console.log('Loading order details for ID:', orderId);

        this.$store.dispatch("telegramMiniApp/order/show", String(orderId)).then(res => {
            this.$store.dispatch("telegramMiniApp/cart/resetPaymentMethod").then().catch();
            
            // Load order count for "My Orders" button visibility
            this.loadOrderCount();
            
            this.loading.isActive = false;
            
            // Handle Telegram notification context
            this.handleNotificationContext();
        }).catch((error) => {
            console.error('Error loading order details:', error);
            this.loading.isActive = false;
        });
    },
    methods: {
        handleNotificationContext() {
            try {
                const action = this.$route.query.action;
                const fromNotification = this.$route.query.from_notification;
                
                if (fromNotification === 'true') {
                    this.isFromNotification = true;
                }

                if (fromNotification === 'true' && action) {
                    // Show context-specific message based on the action
                    let message = '';
                    
                    switch (action) {
                        case 'track':
                            message = '📍 You can track your order status here. We\'ll update you when it\'s ready!';
                            break;
                        case 'delivery':
                            message = '🚗 Your order is out for delivery! You can see the details below.';
                            break;
                        case 'receipt':
                            message = '🧾 Here\'s your order receipt. Thank you for your order!';
                            break;
                        case 'cancelled':
                            message = '❌ Your order has been cancelled. Details are shown below.';
                            break;
                        case 'rejected':
                            message = '⚠️ Your order was rejected. Please see the reason below.';
                            break;
                        default:
                            message = '📱 Order details loaded from notification.';
                    }
                    
                    // Show the message if we're in Telegram WebApp
                    if (typeof Telegram !== 'undefined' && Telegram.WebApp && message) {
                        // Use requestAnimationFrame for better performance than setTimeout
                        requestAnimationFrame(() => {
                            try {
                                Telegram.WebApp.showAlert(message);
                            } catch (error) {
                                console.error('Error showing alert:', error);
                            }
                        });
                    }
                    
                    console.log('Order loaded from Telegram notification:', {
                        action,
                        orderId: this.$route.params.id,
                        message
                    });
                }
            } catch (error) {
                console.error('Error handling notification context:', error);
            }
        },
        goHome() {
            if (this.isFromNotification) {
                const miniAppUrl = process.env.MIX_TELEGRAM_MINI_APP_URL;
                if (miniAppUrl) {
                    if (window.Telegram?.WebApp?.openTelegramLink) {
                        window.Telegram.WebApp.openTelegramLink(miniAppUrl);
                    } else {
                        window.location.href = miniAppUrl;
                    }
                    return;
                }
            }
            this.$router.push({ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } });
        },
        loadOrderCount() {
            if (this.telegramUserData && this.telegramUserData.telegram_user_id) {
                this.$store.dispatch('telegramMiniApp/order/getOrderCount', {
                    telegram_user_id: this.telegramUserData.telegram_user_id
                }).catch((error) => {
                    console.error('Failed to load order count:', error);
                });
            }
        }
    },
}
</script>