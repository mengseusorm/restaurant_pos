<template>
    <LoadingComponent :props="loading" />
    <section class="pt-4 pb-24 px-4 min-h-screen bg-gray-50">
        <div class="max-w-lg mx-auto">
            <!-- Back Button -->
            <!-- <router-link :to="{ name: 'telegram.mini.app.menu', params : {slug : this.$route.params.slug}}"
                         class="inline-flex items-center gap-3 mb-6 p-3 rounded-xl bg-white shadow-sm border border-gray-100 text-primary hover:bg-gray-50 transition-colors">
                <i class="lab lab-undo text-lg"></i>
                <span class="font-medium">{{ $t('label.back_to_home') }}</span>
            </router-link> -->

            <!-- Main Content -->
            <div class="space-y-4">
                <div v-if="telegramUserData && Object.keys(telegramUserData).length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                    <div class="flex items-center gap-3">
                        <i class="fab fa-telegram-plane text-blue-600 text-xl"></i>
                        <span class="text-sm font-medium text-blue-800">{{ $t('label.ordering_via_telegram') || 'Ordering via Telegram Mini App' }}</span>
                        <span v-if="telegramUserData.telegram_username" class="font-medium text-gray-800">@{{ telegramUserData.telegram_username }}</span>
                    </div>

                    <!-- <div class="space-y-2 text-sm">ssfsefsef
                        <div v-if="telegramUserData.telegram_username" class="flex items-center gap-2">
                            <i class="lab lab-user text-blue-600"></i>
                            <span class="text-gray-600">{{ $t('label.telegram_user') || 'User' }}:</span>
                            <span class="font-medium text-gray-800">@{{ telegramUserData.telegram_username }}</span>

                        <div v-if="telegramUserData.telegram_user_id" class="flex items-center gap-2">
                            <i class="lab lab-fingerprint text-blue-600"></i>
                            <span class="text-gray-600">{{ $t('label.user_id') || 'User ID' }}:</span>
                            <span class="font-medium text-gray-800">{{ telegramUserData.telegram_user_id }}</span>
                        </div>
                    </div> -->
                </div>

                <!-- Cart Summary Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <!-- Header -->
                    <div class="ps-2 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold flex items-center gap-2">
                            <i class="lab lab-shopping-cart text-primary"></i>
                            {{ $t('label.cart_summary') }}
                        </h3>
                    </div>

                    <!-- Cart Items -->
                    <div class="p-3">
                        <div class="space-y-4">
                            <div v-for="cart in carts" :key="cart.item_id" class="border border-gray-100 rounded-lg p-4 relative">
                                <!-- Quantity Badge -->
                                <div class="absolute -top-2 -left-2 w-6 h-6 bg-primary text-white text-xs font-bold rounded-full flex items-center justify-center shadow-md">
                                    {{ cart.quantity }}
                                </div>

                                <!-- Item Content -->
                                <div class="flex gap-4">
                                    <img :src="cart.image" alt="thumbnail" class="w-20 h-20 rounded-lg flex-shrink-0 object-cover border border-gray-200" />
                                    <div class="flex-1 min-w-0">
                                        <!-- Item Name -->
                                        <h4 class="font-semibold text-sm text-gray-900 mb-2 leading-tight">
                                            {{ cart.name }}
                                        </h4>

                                        <!-- Variations -->
                                        <div v-if="Object.keys(cart.item_variations.variations).length !== 0" class="mb-2">
                                            <div v-for="(variation, variationName) in cart.item_variations.names" :key="variationName" class="text-xs text-gray-600 mb-1">
                                                <span class="font-medium">{{ variationName }}:</span>
                                                <span class="ml-1">{{ variation }}</span>
                                            </div>
                                        </div>

                                        <!-- Extras -->
                                        <div v-if="cart.item_extras.extras.length > 0" class="mb-2">
                                            <span class="text-xs font-medium text-gray-600">{{ $t('label.extras') }}:</span>
                                            <span class="text-xs text-gray-700 ml-1">
                                                <span v-for="(extra, index) in cart.item_extras.names" :key="index"> {{ extra }}<span v-if="index + 1 < cart.item_extras.names.length">, </span> </span>
                                            </span>
                                        </div>

                                        <!-- Instructions -->
                                        <div v-if="cart.instruction !== ''" class="mb-3">
                                            <span class="text-xs font-medium text-gray-600">{{ $t('label.instruction') }}:</span>
                                            <p class="text-xs text-gray-700 mt-1 bg-gray-50 p-2 rounded">{{ cart.instruction }}</p>
                                        </div>

                                        <!-- Price -->
                                        <div class="text-start">
                                            <span class="text-base font-bold text-primary">
                                                {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point, branch?.currency_id?.symbol || '', setting.site_currency_position) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Order Summary -->
                    <div class="border-t border-gray-100 p-5">
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <!-- Subtotal -->
                            <div v-if="false" class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                <span class="text-xs font-medium text-gray-600">{{ $t('label.subtotal') }}</span>
                                <span class="text-xs font-semibold text-gray-900">
                                    {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                </span>
                            </div>

                            <!-- Total -->
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-gray-900">{{ $t('label.total') }}</span>
                                <span class="text-lg font-bold text-primary">
                                    {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, branch?.currency_id?.symbol || '', setting.site_currency_position) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold mb-6 flex items-center gap-2">
                        <i class="lab lab-edit text-primary"></i>
                        {{ $t('label.customer_info') || 'Customer Information' }}
                    </h2>

                    <!-- Customer Information Section -->
                    <div class="space-y-4">
                        <!-- Customer Name Field -->
                        <div v-if="branch.show_customer_name == statusEnum.ACTIVE">
                            <label class="block text-xs font-semibold mb-2 text-gray-900">
                                <i class="lab lab-user text-primary mr-2"></i>
                                {{ $t('label.customer_name') || 'Customer Name' }}
                            </label>
                            <input type="text" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-sm" :placeholder="$t('label.customer_name') || 'Enter customer name'" v-model="checkoutProps.form.customer_name" />
                        </div>

                        <!-- Customer Phone Number Field -->
                        <div v-if="branch.show_customer_phone_number == statusEnum.ACTIVE">
                            <label class="block text-xs font-semibold mb-2 text-gray-900">
                                <i class="lab lab-call text-primary mr-2"></i>
                                {{ $t('label.customer_phone_number') || 'Customer Phone Number' }}
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input
                                type="tel"
                                class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-sm"
                                :placeholder="$t('label.customer_phone_number') || 'Enter customer phone number'"
                                v-model="checkoutProps.form.customer_phone_number"
                                pattern="[0-9]*"
                                inputmode="numeric"
                            />
                        </div>

                        <!-- Customer Address Field -->
                        <div v-if="branch.show_customer_address == statusEnum.ACTIVE">
                            <label class="block text-xs font-semibold mb-2 text-gray-900">
                                <i class="lab lab-location text-primary mr-2"></i>
                                {{ $t('label.customer_address') || 'Customer Address' }}
                            </label>
                            <input type="text" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-sm" :placeholder="$t('label.customer_address') || 'Enter customer address'" v-model="checkoutProps.form.customer_address" />
                        </div>

                        <!-- Order Note Field -->
                        <div>
                            <label class="block text-xs font-semibold mb-2 text-gray-900">
                                <i class="lab lab-edit text-primary mr-2"></i>
                                {{ $t('label.note') || 'Note' }}
                            </label>
                            <textarea class="w-full p-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-sm resize-none" rows="4" :placeholder="$t('placeholder.note') || 'Leave your message here...'" v-model="checkoutProps.form.note"> </textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none">
                <div class="container px-4 pb-4">
                    <div class="pointer-events-auto w-full min-h-[72px] flex items-center justify-center px-5 py-4 bg-white/30 backdrop-blur-sm rounded-2xl shadow-2xl hover:shadow-primary/50 transition-all active:scale-95 border border-gray-200/50">
                        <div class="flex gap-3 justify-center w-full">
                            <!-- My Order Button -->
                            <!-- Home Button -->
                            <router-link :to="{ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } }" class="flex items-center gap-2 px-4 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 transition-all duration-200 shadow-md">
                                <i class="fa-solid fa-home text-gray-700"></i>
                                <span class="text-sm font-semibold text-gray-700">{{ $t('label.home') || 'Home' }}</span>
                            </router-link>

                            <!-- Place Order Button -->
                            <button type="button" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-primary hover:bg-primary-dark transition-all duration-200 shadow-md" @click="orderSubmit">
                                <i class="fa-solid fa-bag-shopping text-sm text-white"></i>
                                <span class="text-sm font-semibold text-white">{{ $t('button.place_order') }}</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Payment Method Selection Modal -->
        <div v-if="showPaymentMethodModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 p-5 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $t('label.select_payment_method') || 'Select Payment Method' }}</h3>
                        <button @click="closePaymentMethodModal" class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Payment Methods List -->
                <div class="p-5">
                    <div class="space-y-4 mb-6">
                        <div v-for="method in paymentMethods" :key="method.id" >
                            <!-- Logo Section (Left) -->
                            <div :class="[
                                    'cursor-pointer rounded-xl border transition-all p-2 relative flex items-center gap-4',
                                    selectedPaymentMethodInModal === method.id
                                        ? 'border-blue-500 bg-blue-50 shadow-md'
                                        : 'border-gray-300 bg-white hover:border-blue-400 hover:bg-blue-50/30'
                                ]" @click="selectPaymentMethodInModal(method.id)">

                                <div v-if="method.logo_thumb" class="flex-shrink-0 flex items-center justify-center">
                                    <div class=" w-16 h-16">
                                        <img
                                            :src="method.logo_thumb"
                                            :alt="method.name"
                                            class="w-full h-full object-fit rounded-lg border"
                                        />
                                    </div>
                                </div>
                                <div v-else class="flex-shrink-0 w-24 h-24 flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl bg-blue-100 text-primary">
                                        <i class="fa-solid fa-credit-card"></i>
                                    </div>
                                </div>

                                <!-- Content Section (Right) -->
                                <div class="flex-1 flex flex-col gap-1">
                                    <h3 class="text-base font-bold text-primary capitalize">
                                        {{ method.name }}
                                    </h3>
                                    <p v-if="method.short_description" class="text-sm text-gray-600">
                                        {{ method.short_description }}
                                    </p>
                                </div>

                                <!-- Check Icon -->
                                <div v-if="selectedPaymentMethodInModal === method.id" class="flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-sm"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- select currency  -->
                            <div v-if="availableCurrencies && availableCurrencies.length > 1 && selectedMethod && selectedMethod.id == method.id && selectedMethod.is_pos_bank_integrate_payment == statusEnum.ACTIVE" class="mb-6 mt-3">
                                <!-- <h3 class="capitalize font-medium mb-2 mt-2">{{ $t("label.select_currency") }}</h3> -->
                                <div class="grid grid-cols-2 gap-3">
                                    <!-- Currency Options -->
                                    <label
                                        v-for="currency in availableCurrencies"
                                        :key="currency.id"
                                        :class="[
                                            'cursor-pointer rounded-lg border-2 transition-all duration-200 flex flex-col items-center justify-center p-4 h-[100px] relative',
                                            selectedPaymentCurrency && selectedPaymentCurrency.id === currency.id
                                                ? 'border-primary bg-primary/10'
                                                : 'border-[#E4E4E7] bg-white hover:border-primary/50'
                                        ]"
                                        @click="selectPaymentCurrency(currency)"
                                    >
                                        <!-- Check Icon -->
                                        <div v-if="selectedPaymentCurrency && selectedPaymentCurrency.id === currency.id" class="absolute top-2 right-2">
                                            <div class="w-5 h-5 rounded-full bg-primary flex items-center justify-center">
                                                <i class="fa-solid fa-check text-white text-xs"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-col items-center gap-2 w-full">
                                            <span class="text-primary text-base font-medium">
                                                {{ calculateAmountInCurrency(currency) }} {{ currency.symbol }}
                                            </span>
                                            <h3
                                                :class="[
                                                    'text-sm font-medium text-center capitalize',
                                                    selectedPaymentCurrency && selectedPaymentCurrency.id === currency.id
                                                        ? 'text-primary'
                                                        : 'text-[#2E2F38]'
                                                ]"
                                            >
                                                {{ currency.code }}
                                            </h3>
                                        </div>
                                        <input
                                            type="radio"
                                            class="hidden"
                                            :checked="selectedPaymentCurrency && selectedPaymentCurrency.id === currency.id"
                                        />
                                    </label>
                                </div>
                            </div>
                            <!-- end  -->
                        </div>
                    </div>
                    

                    <!-- Make Payment Button -->
                    <button
                        @click="handleMakePayment"
                        :disabled="!selectedPaymentMethodInModal"
                        type="button"
                        :class="['w-full px-6 py-4 rounded-xl font-semibold transition-colors', selectedPaymentMethodInModal ? 'bg-primary text-white hover:bg-primary-dark' : 'bg-gray-300 text-gray-500 cursor-not-allowed']"
                    >
                        <i class="fa-solid fa-credit-card mr-2"></i>
                        {{ $t('button.make_payment') || 'Make Payment' }}
                    </button>
                    <!-- <pre>
                        {{ branch}}
                        {{ branch?.currency_id?.second_currency }}

                    </pre> -->
                </div>
            </div>
        </div>

        <!-- PayWay Payment Modal -->
        <TelegramMiniAppPaywayCheckoutComponent
            :showModal="showPaywayModal"
            :orderData="checkoutProps.form"
            :totalAmount="paymentAmount"
            :paymentCurrency="selectedPaymentCurrency"
            :currencyCode="selectedPaymentCurrency?.code || (branch?.currency_id?.code || 'KHR')"
            :paymentMethod="selectedMethod"
            @close="handlePaywayModalClose"
            @orderCreated="handleOrderCreated"
        />
    </section>
</template>
<script>
import LoadingComponent from '../../table/components/LoadingComponent.vue';
import TelegramMiniAppPaywayCheckoutComponent from './TelegramMiniAppPaywayCheckoutComponent.vue';
import appService from '../../../services/appService';
import sourceEnum from '../../../enums/modules/sourceEnum';
import _ from 'lodash';
import OrderTypeEnum from '../../../enums/modules/orderTypeEnum';
import IsAdvanceOrderEnum from '../../../enums/modules/isAdvanceOrderEnum';
import router from '../../../router';
import alertService from '../../../services/alertService';
import telegramScriptLoader from '../../../services/telegramScriptLoader';
import statusEnum from '../../../enums/modules/statusEnum';

export default {
    name: 'TelegramMiniAppCheckoutComponent',
    components: { LoadingComponent, TelegramMiniAppPaywayCheckoutComponent },
    data() {
        return {
            selectedMethod: null,
            loading: {
                isActive: false,
            },
            placeOrderShow: false,
            paymentMethod: null,
            statusEnum: statusEnum,
            showPaywayModal: false,
            showPaymentMethodModal: false,
            selectedPaymentMethodInModal: null,
            selectedPaymentCurrency: null, // Selected payment currency object
            receivedAmounts: {}, // Object to track received amounts per currency ID
            receiveAmount: 0,
            changeAmount: 0,
            checkoutProps: {
                form: {
                    // dining_table_id: null,
                    customer_id: 2,
                    branch_id: null,
                    subtotal: 0,
                    discount: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: OrderTypeEnum.ONLINE_ORDER,
                    is_advance_order: IsAdvanceOrderEnum.NO,
                    source: sourceEnum.TELEGRAM_MINI_APP,
                    address_id: null,
                    items: [],
                    order_note: '',
                    payment_method: null, // This is the payment method name that will be used to save in the order
                    payment_method_name: null, // This is the payment method name that will be used to display in the order
                    payment_method_id: null, // This is the payment method id that will be used to save in the order

                    phone_number: '',
                    address_or_location: '',
                    customer_name: '',
                    customer_phone_number: '',
                    customer_address: '',

                    currency: null,
                    currency_id: null,
                    receive_payment_currency: null,
                    receive_payment_currency_id: null,

                },
            },
        };
    },
    mounted() {
        if (this.$store.getters['telegramMiniApp/cart/lists'].length === 0) {
            this.$router.push({ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } });
        }

        // Pre-fill customer data from Telegram if available
        this.prefillCustomerDataFromTelegram();

        // Load payment methods
        this.$store
            .dispatch('paymentMethod/listOnlinePayment', {
                order_column: 'id',
                order_type: 'asc',
            })
            .then()
            .catch();
        
        // Load all currencies
        this.$store.dispatch('telegramMiniApp/currency/lists', {
            order_column: 'id',
            order_type: 'asc'
        }).then(() => {
            // Set default payment currency to branch base currency
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }
        }).catch();

        // Load exchange rates
        this.$store.dispatch('telegramMiniApp/exchangeRate/lists', {
            order_column: 'id',
            order_type: 'desc',
            paginate: 0
        }).catch();
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        carts: function () {
            return this.$store.getters['telegramMiniApp/cart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['telegramMiniApp/cart/subtotal'];
        },
        // branch: function () {
        //     return this.$store.getters['backendGlobalState/branchShow'];
        // },
        branch: function () {
            return this.$store.getters['telegramMiniApp/branch/show'];
        },
        paymentMethods: function () {
            return this.$store.getters['paymentMethod/lists'];
        },
        currencies: function () {
            return this.$store.getters['telegramMiniApp/currency/lists'];
        },
        exchangeRates: function () {
            return this.$store.getters['telegramMiniApp/exchangeRate/lists'];
        },
        telegramUserData: function () {
            return this.$store.getters['telegramMiniApp/order/telegramUserData'] || {};
        },
        totalInBaseCurrency: function () {
            return parseFloat(this.subtotal.toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        paymentAmount: function () {
            // Calculate payment amount based on selected currency
            if (!this.selectedPaymentCurrency || this.selectedPaymentCurrency.code === this.branch?.currency_id?.code) {
                return this.totalInBaseCurrency;
            }
            
            // Convert using exchange rate system
            const baseCurrencyCode = this.branch?.currency_id?.code;
            const targetCurrencyCode = this.selectedPaymentCurrency.code;
            const exchangeRate = this.getExchangeRate(baseCurrencyCode, targetCurrencyCode);
            return parseFloat((this.totalInBaseCurrency * exchangeRate).toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        availableCurrencies: function () {
            // If payment method has POS bank integration and supported currencies defined, use only those
            if (this.selectedMethod && this.selectedMethod.is_pos_bank_integrate_payment === this.statusEnum.ACTIVE) {
                if (this.selectedMethod.supported_currencies && this.selectedMethod.supported_currencies.length > 0) {
                    return this.selectedMethod.supported_currencies;
                }
            }
            // Otherwise, use all available currencies
            return this.currencies;
        },
    },
    methods: {
        getExchangeRate: function (baseCurrencyCode, targetCurrencyCode) {
            // If same currency, rate is 1
            if (baseCurrencyCode === targetCurrencyCode) {
                return 1;
            }

            // Find exchange rate from exchange_rates table
            const exchangeRate = this.exchangeRates?.find(
                rate => rate.base_currency === baseCurrencyCode && rate.target_currency === targetCurrencyCode
            );

            if (exchangeRate && exchangeRate.rate) {
                return parseFloat(exchangeRate.rate);
            }

            // Try reverse lookup (if we have target->base, calculate base->target)
            const reverseRate = this.exchangeRates?.find(
                rate => rate.base_currency === targetCurrencyCode && rate.target_currency === baseCurrencyCode
            );

            if (reverseRate && reverseRate.rate) {
                const rate = parseFloat(reverseRate.rate);
                return rate > 0 ? 1 / rate : 0;
            }

            // Fallback to legacy exchange_rate field from currency table
            const targetCurrency = this.currencies.find(c => c.code === targetCurrencyCode);
            if (targetCurrency && targetCurrency.exchange_rate) {
                return parseFloat(targetCurrency.exchange_rate);
            }

            // Default to 1 if no rate found
            return 1;
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        prefillCustomerDataFromTelegram: async function () {
            try {
                // Load Telegram script first
                await telegramScriptLoader.loadScript();

                // Initialize Telegram WebApp
                telegramScriptLoader.initializeWebApp();

                // Pre-fill customer name from Telegram user data if available and field is enabled
                if (this.branch.show_customer_name == this.statusEnum.ACTIVE && !this.checkoutProps.form.customer_name && this.telegramUserData.telegram_username) {
                    // Use the stored username as customer name
                    this.checkoutProps.form.customer_name = this.telegramUserData.telegram_username;
                }
            } catch (error) {
                console.error('Error loading Telegram script or initializing:', error);
            }
        },
        getTelegramDataForOrder: function () {
            // Return Telegram data from store for order submission
            // Convert numeric IDs to strings as required by backend validation
            return {
                telegram_user_id: this.telegramUserData.telegram_user_id ? String(this.telegramUserData.telegram_user_id) : null,
                telegram_chat_id: this.telegramUserData.telegram_chat_id ? String(this.telegramUserData.telegram_chat_id) : null,
                telegram_username: this.telegramUserData.telegram_username || null,
            };
        },
        orderSubmit: function () {
            // Prevent double submission
            if (this.loading.isActive) {
                return;
            }

            // Set currency based on selected currency option
            this.checkoutProps.form.currency = this.branch?.currency_id?.code || 'KHR';
            this.checkoutProps.form.currency_id = this.branch?.currency_id?.id;
            
            // Set receive payment currency (currency customer actually pays in)
            if (this.selectedPaymentCurrency) {
                this.checkoutProps.form.receive_payment_currency = this.selectedPaymentCurrency.code;
                this.checkoutProps.form.receive_payment_currency_id = this.selectedPaymentCurrency.id;
            } else {
                this.checkoutProps.form.receive_payment_currency = this.branch?.currency_id?.code || 'KHR';
                this.checkoutProps.form.receive_payment_currency_id = this.branch?.currency_id?.id;
            }

            console.log('Selected currency:', this.selectedPaymentCurrency, 'Currency code:', this.checkoutProps.form.receive_payment_currency);
            // Validate required phone number
            if (this.branch.show_customer_phone_number == this.statusEnum.ACTIVE && (this.checkoutProps.form.customer_phone_number === '' || this.checkoutProps.form.customer_phone_number === null)) {
                alertService.error(this.$t('message.phone_number_required') || 'Phone number is required');
                return;
            }

            // Validate cart is not empty
            if (!this.carts || this.carts.length === 0) {
                alertService.error(this.$t('message.cart_empty') || 'Your cart is empty');
                return;
            }

            // Validate branch is selected
            if (!this.branch || !this.branch.branch_id) {
                alertService.error(this.$t('message.branch_required') || 'Branch information is missing. Please refresh and try again.');
                return;
            }

            // Show payment method selection modal
            this.showPaymentMethodModal = true;
        },
        handleMakePayment: function () {
            // Close payment method modal
            this.showPaymentMethodModal = false;

            if (!this.selectedPaymentMethodInModal) {
                alertService.error(this.$t('message.payment_method_required') || 'Please select a payment method');
                return;
            }

            // Get selected payment method details
            const selectedPaymentMethod = this.paymentMethods.find(method => method.id === this.selectedPaymentMethodInModal);
            if (!selectedPaymentMethod) {
                alertService.error(this.$t('message.invalid_payment_method') || 'Invalid payment method selected');
                return;
            }

            // Set payment method in form
            this.paymentMethod = selectedPaymentMethod.name;
            this.checkoutProps.form.payment_method = selectedPaymentMethod.id;
            this.checkoutProps.form.payment_method_name = selectedPaymentMethod.name;
            this.checkoutProps.form.payment_method_id = selectedPaymentMethod.id;

            this.loading.isActive = true;
            // this.checkoutProps.form.dining_table_id = this.table.id;
            this.checkoutProps.form.branch_id = this.branch.branch_id;

            // Set subtotal and total based on selected currency
            // Always store in base currency for backend - subtotal is already in base currency from cart
            this.checkoutProps.form.subtotal = this.subtotal;
            this.checkoutProps.form.total = parseFloat(this.subtotal).toFixed(this.setting.site_digit_after_decimal_point);

            this.checkoutProps.form.items = [];

            _.forEach(this.carts, (item, index) => {
                let item_variations = [];
                if (Object.keys(item.item_variations.variations).length > 0) {
                    _.forEach(item.item_variations.variations, (value, index) => {
                        item_variations.push({
                            id: value,
                            item_id: item.item_id,
                            item_attribute_id: index,
                        });
                    });
                }

                if (Object.keys(item.item_variations.names).length > 0) {
                    let i = 0;
                    _.forEach(item.item_variations.names, (value, index) => {
                        item_variations[i].variation_name = index;
                        item_variations[i].name = value;
                        i++;
                    });
                }

                let item_extras = [];
                if (item.item_extras.extras.length) {
                    _.forEach(item.item_extras.extras, (value) => {
                        item_extras.push({
                            id: value,
                            item_id: item.item_id,
                        });
                    });
                }

                if (item.item_extras.names.length) {
                    let i = 0;
                    _.forEach(item.item_extras.names, (value) => {
                        item_extras[i].name = value;
                        i++;
                    });
                }

                this.checkoutProps.form.items.push({
                    item_id: item.item_id,
                    item_price: item.convert_price, // Keep in base currency
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: item.total, // Keep in base currency
                    item_variation_total: item.item_variation_total, // Keep in base currency
                    item_extra_total: item.item_extra_total, // Keep in base currency
                    item_variations: item_variations,
                    item_extras: item_extras,
                });
            });

            // Stringify items BEFORE checking for PayWay - needed for both flows
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            // Check if selected payment method requires bank integration
            if (selectedPaymentMethod.is_pos_bank_integrate_payment === this.statusEnum.ACTIVE) {

                // Add Telegram data to order data
                const telegramData = this.getTelegramDataForOrder();

                // Use payment amount computed property (already handles exchange rates)
                const paymentCurrency = this.selectedPaymentCurrency?.code || this.branch?.currency_id?.code || 'KHR';

                console.log('PayWay Payment - Payment Currency:', this.selectedPaymentCurrency, 'Amount:', this.paymentAmount, 'Currency Code:', paymentCurrency);

                // Create order data with selected currency
                const orderDataForPayway = {
                    ...this.checkoutProps.form,
                    ...telegramData,
                    currency: paymentCurrency,
                    payment_currency: this.selectedPaymentCurrency,
                    payment_amount: this.paymentAmount
                };

                // Show PayWay modal
                this.showPaywayModal = true;
                this.loading.isActive = false;
                return;
            }

            // Add Telegram data from store to the order
            const telegramData = this.getTelegramDataForOrder();
            const orderData = { ...this.checkoutProps.form, ...telegramData };

            this.$store
                .dispatch('telegramMiniApp/order/save', orderData)
                .then((orderResponse) => {
                    // Validate order response structure
                    if (!orderResponse || !orderResponse.data || !orderResponse.data.data || !orderResponse.data.data.id) {
                        console.error('Invalid order response structure:', orderResponse);
                        throw new Error('Invalid order response from server');
                    }
                    // Reset form data
                    this.checkoutProps.form.subtotal = 0;
                    this.checkoutProps.form.discount = 0;
                    this.checkoutProps.form.delivery_charge = 0;
                    this.checkoutProps.form.delivery_time = null;
                    this.checkoutProps.form.total = 0;
                    this.checkoutProps.form.items = [];
                    this.checkoutProps.form.order_note = '';
                    this.checkoutProps.form.source = sourceEnum.TELEGRAM_MINI_APP; // Reset source to telegram mini app
                    this.checkoutProps.form.currency = null;
                    this.checkoutProps.form.currency_id = null;
                    this.checkoutProps.form.receive_payment_currency = null;
                    this.checkoutProps.form.receive_payment_currency_id = null;

                    //TODO: Should send by payment method

                    this.$store
                        .dispatch('telegramMiniApp/cart/resetCart')
                        .then((res) => {
                            this.loading.isActive = false;
                            this.$store.dispatch('telegramMiniApp/cart/paymentMethod', this.paymentMethod).then().catch();

                            // Show success message
                            alertService.success(this.$t('message.order_placed_success') || 'Order placed successfully!');

                            router.push({ name: 'telegram.mini.app.menu', params: { slug: this.branch.telegram_mini_app_slug }, query: { id: orderResponse.data.data.id } });
                            // router.push({name: "table.menu.table", params: {slug : this.table.slug}, query: {id: orderResponse.data.data.id}});
                            // router.push({name: "table.make.payment", params: {slug : this.table.slug, id: orderResponse.data.data.id}});
                        })
                        .catch((cartResetError) => {
                            console.error('Cart reset failed:', cartResetError);
                            this.loading.isActive = false;

                            // Order was successful but cart reset failed - still show success but warn user
                            alertService.success(this.$t('message.order_placed_success') || 'Order placed successfully!');
                            alertService.warning(this.$t('message.cart_reset_failed') || 'Order successful, but cart couldn\'t be cleared. Please refresh the page.');

                            // Still navigate to menu page
                            router.push({ name: 'telegram.mini.app.menu', params: { slug: this.branch.telegram_mini_app_slug }, query: { id: orderResponse.data.data.id } });
                        });
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    console.error('Order submission failed:', err);

                    // Handle different types of errors
                    if (err.response) {
                        // Server responded with error status
                        const response = err.response;

                        if (response.data) {
                            // Handle validation errors
                            if (typeof response.data.errors === 'object') {
                                _.forEach(response.data.errors, (error) => {
                                    alertService.error(error[0]);
                                });
                            }
                            // Handle single error message
                            else if (response.data.message) {
                                alertService.error(response.data.message);
                            }
                            // Handle generic server errors
                            else {
                                alertService.error(this.$t('message.order_failed') || 'Failed to place order. Please try again.');
                            }
                        } else {
                            // No response data
                            alertService.error(`${this.$t('message.server_error') || 'Server error'} (${response.status})`);
                        }
                    } else if (err.request) {
                        // Network error - request made but no response
                        alertService.error(this.$t('message.network_error') || 'Network error. Please check your connection and try again.');
                    } else {
                        // Something else happened
                        alertService.error(this.$t('message.unexpected_error') || 'An unexpected error occurred. Please try again.');
                    }
                });
        },
        handlePaywayModalClose: function () {
            this.showPaywayModal = false;
            this.loading.isActive = false;
        },
        selectPaymentMethodInModal: function (paymentMethodId) {
            this.selectedPaymentMethodInModal = paymentMethodId;
            // Find the selected payment method object
            this.selectedMethod = this.paymentMethods.find(method => method.id === paymentMethodId);

            // Reset currency selection to branch currency when selecting a new payment method
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }
        },
        selectPaymentCurrency: function (currency) {
            this.selectedPaymentCurrency = currency;
        },
        calculateAmountInCurrency: function (currency, amount = null) {
            const baseAmount = amount !== null ? amount : this.totalInBaseCurrency;
            const baseCurrencyCode = this.branch?.currency_id?.code;
            
            // If currency is the same as base currency, return base amount
            if (currency.code === baseCurrencyCode) {
                return parseFloat(baseAmount.toFixed(this.setting.site_digit_after_decimal_point || 2));
            }
            
            // Calculate amount using exchange rate system
            const exchangeRate = this.getExchangeRate(baseCurrencyCode, currency.code);
            const convertedAmount = baseAmount * exchangeRate;
            return parseFloat(convertedAmount.toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        calcReceiveAmount: function () {
            // Calculate total receive amount by converting all currencies to base currency
            let totalInBaseCurrency = 0;
            const baseCurrencyCode = this.branch?.currency_id?.code;
            
            Object.keys(this.receivedAmounts).forEach(currencyId => {
                const amount = parseFloat(this.receivedAmounts[currencyId] || 0);
                if (amount > 0) {
                    const currency = this.currencies.find(c => c.id == currencyId);
                    if (currency) {
                        if (currency.code === baseCurrencyCode) {
                            // Base currency - no conversion needed
                            totalInBaseCurrency += amount;
                        } else {
                            // Convert to base currency using exchange rate system
                            const exchangeRate = this.getExchangeRate(currency.code, baseCurrencyCode);
                            totalInBaseCurrency += amount * exchangeRate;
                        }
                    }
                }
            });

            this.receiveAmount = parseFloat(totalInBaseCurrency.toFixed(this.setting.site_digit_after_decimal_point || 2));
            this.changeAmount = parseFloat((parseFloat(this.receiveAmount) - parseFloat(this.totalInBaseCurrency)).toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        setFullReceiveInBaseCurrency: function () {
            // Set full amount in base currency, clear others
            if (this.branch?.currency_id?.id) {
                this.receivedAmounts = {
                    [this.branch.currency_id.id]: this.totalInBaseCurrency
                };
            }
            this.calcReceiveAmount();
        },
        setFullReceiveInPaymentCurrency: function () {
            // Set full amount in selected payment currency, clear others
            if (this.selectedPaymentCurrency) {
                const baseCurrencyCode = this.branch?.currency_id?.code;
                const targetCurrencyCode = this.selectedPaymentCurrency.code;
                const exchangeRate = this.getExchangeRate(baseCurrencyCode, targetCurrencyCode);
                const totalInPaymentCurrency = this.totalInBaseCurrency * exchangeRate;
                this.receivedAmounts = {
                    [this.selectedPaymentCurrency.id]: parseFloat(totalInPaymentCurrency.toFixed(this.setting.site_digit_after_decimal_point || 2))
                };
                this.calcReceiveAmount();
            }
        },
        handleOrderCreated: function (order) {
            // Reset cart and form
            this.checkoutProps.form.subtotal = 0;
            this.checkoutProps.form.discount = 0;
            this.checkoutProps.form.delivery_charge = 0;
            this.checkoutProps.form.delivery_time = null;
            this.checkoutProps.form.total = 0;
            this.checkoutProps.form.items = [];
            this.checkoutProps.form.order_note = '';
            this.checkoutProps.form.source = sourceEnum.TELEGRAM_MINI_APP;
            this.checkoutProps.form.currency = null;
            this.checkoutProps.form.currency_id = null;
            this.checkoutProps.form.receive_payment_currency = null;
            this.checkoutProps.form.receive_payment_currency_id = null;

            // Reset payment currency to branch currency
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }

            // Reset currency selection
            this.selectedPaymentMethodInModal = null;
            this.selectedMethod = null;

            this.$store.dispatch('telegramMiniApp/cart/resetCart').catch();
        },
        closePaymentMethodModal: function () {
            this.showPaymentMethodModal = false;
            this.selectedPaymentMethodInModal = null;
            this.selectedMethod = null;
            // Reset payment currency to branch currency
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }
        },
    },
};
</script>
