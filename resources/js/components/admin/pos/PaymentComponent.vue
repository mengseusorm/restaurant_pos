<template>
    <LoadingComponent :props="loading" />

    <div id="orderpayment" class="modal">
        <div class="modal-dialog max-w-[1050px] w-full">
            <div class="modal-header pb-3 border-b border-[#D9DBE9]">
                <h3 class="capitalize font-medium">{{ $t("label.order_payment") }}</h3>
                <button class="modal-close fa-regular fa-circle-xmark" @click="reset"></button>
            </div>
            <div class="modal-body">
                <div class="row m-2">
                    <div :class="['col-12 md:col-6']">
                        <!-- <div class="mb-6">
                            <h3 class="capitalize font-medium mb-2">{{ $t("label.select_payment_method") }}</h3>
                            <div v-if="paymentMethods.length > 0">
                                <div class="grid grid-cols-2 gap-3">
                                    <div v-for="(paymentMethod, index) in paymentMethods" :key="index">
                                        <div
                                            v-if="paymentMethod.logo_cover"
                                            :class="[
                                                'cursor-pointer rounded-lg border-2 transition-all duration-200 flex items-center p-3 h-[100px] gap-3',
                                                props.form.pos_payment_method == paymentMethod.id
                                                    ? 'border-primary bg-primary/10'
                                                    : 'border-[#E4E4E7] bg-white hover:border-primary/50'
                                            ]"
                                            @click="selectPaymentMethod(paymentMethod.id)"
                                        >
                                            <div>
                                                <img
                                                    :src="paymentMethod.logo_cover"
                                                    :alt="paymentMethod.name"
                                                    style="width: 80px;height: 80px;border-radius: 10px;"
                                                />
                                            </div>
                                            <div>
                                                <h3
                                                    :class="[
                                                        'text-sm font-medium capitalize line-clamp-2',
                                                        props.form.pos_payment_method == paymentMethod.id
                                                            ? 'text-primary'
                                                            : 'text-[#2E2F38]'
                                                    ]"
                                                >
                                                    {{ paymentMethod.name }}
                                                </h3>
                                                <input
                                                    type="radio"
                                                    class="hidden"
                                                    :checked="props.form.pos_payment_method == paymentMethod.id"
                                                />
                                            </div>
                                        </div>
                                        <label
                                            v-else
                                            :class="[
                                                'cursor-pointer rounded-lg border-2 transition-all duration-200 flex flex-col items-center justify-center p-4 h-[100px]',
                                                props.form.pos_payment_method == paymentMethod.id
                                                    ? 'border-primary bg-primary/10'
                                                    : 'border-[#E4E4E7] bg-white hover:border-primary/50'
                                            ]"
                                            @click="selectPaymentMethod(paymentMethod.id)"
                                        >
                                            <div class="flex flex-col items-center gap-2 w-full">
                                                <div
                                                    :class="[
                                                        'w-10 h-10 rounded-full flex items-center justify-center text-xl transition-all',
                                                        props.form.pos_payment_method == paymentMethod.id
                                                            ? 'bg-primary text-white'
                                                            : 'bg-[#F7F7FC] text-[#6E7191]'
                                                    ]"
                                                >
                                                    <i :class="getPaymentMethodIcon(paymentMethod)"></i>
                                                </div>
                                                <h3
                                                    :class="[
                                                        'text-sm font-medium text-center capitalize',
                                                        props.form.pos_payment_method == paymentMethod.id
                                                            ? 'text-primary'
                                                            : 'text-[#2E2F38]'
                                                    ]"
                                                >
                                                    {{ paymentMethod.name }}
                                                </h3>
                                            </div>
                                            <input
                                                type="radio"
                                                class="hidden"
                                                :checked="props.form.pos_payment_method == paymentMethod.id"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                           <div class="mb-6">
                            <h3 class="capitalize font-medium mb-4">{{ $t("label.select_payment_method") }}</h3>
                            <div v-if="paymentMethods.length > 0">
                                <div class="grid grid-cols-2 gap-3">
                                    <div v-for="(paymentMethod, index) in paymentMethods" :key="index">
                                        <!-- Card layout with logo on top, text below -->
                                        <div
                                            :class="[
                                                'cursor-pointer rounded-xl border transition-all duration-200 p-4 relative flex flex-col items-center gap-3',
                                                props.form.pos_payment_method == paymentMethod.id
                                                    ? 'border-blue-500 bg-blue-50 shadow-md'
                                                    : 'border-gray-300 bg-white hover:border-blue-400 hover:bg-blue-50/50'
                                            ]"
                                            @click="selectPaymentMethod(paymentMethod.id)"
                                        >
                                            <!-- Logo Section (Top) -->
                                            <div v-if="paymentMethod.logo_thumb" class="flex-shrink-0 w-20 h-20 flex items-center justify-center">
                                                <img
                                                    :src="paymentMethod.logo_thumb"
                                                    :alt="paymentMethod.name"
                                                    class="w-full h-full object-fit rounded-lg border"
                                                />
                                            </div>
                                            <div v-else class="flex-shrink-0 w-20 h-20 flex items-center justify-center">
                                                <div
                                                    :class="[
                                                        'w-10 h-10 rounded-full flex items-center justify-center text-xl transition-all',
                                                        props.form.pos_payment_method == paymentMethod.id
                                                            ? 'bg-primary text-white'
                                                            : 'bg-blue-100 text-primary'
                                                    ]"
                                                >
                                                    <i :class="getPaymentMethodIcon(paymentMethod)"></i>
                                                </div>
                                            </div>

                                            <!-- Content Section (Center) -->
                                            <div class="flex-1 flex flex-col gap-1 text-center w-full">
                                                <h3
                                                    :class="[
                                                        'text-sm font-bold capitalize line-clamp-2',
                                                        props.form.pos_payment_method == paymentMethod.id
                                                            ? 'text-primary'
                                                            : 'text-primary'
                                                    ]"
                                                >
                                                    {{ paymentMethod.name }}
                                                </h3>
                                                <p v-if="paymentMethod.short_description"
                                                    class="text-xs text-gray-600 line-clamp-2"
                                                >
                                                    {{ paymentMethod.short_description }}
                                                </p>
                                                <input
                                                    type="radio"
                                                    class="hidden"
                                                    :checked="props.form.pos_payment_method == paymentMethod.id"
                                                />
                                            </div>

                                            <!-- Check Icon (Top Right) -->
                                            <div v-if="props.form.pos_payment_method == paymentMethod.id" class="absolute top-2 right-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center shadow-md">
                                                    <i class="fa-solid fa-check text-white text-sm"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div :class="['col-12 md:col-6']">
                        <!-- Currency Selection -->
                        <div v-if="availableCurrencies.length > 0 && selectedMethod && selectedMethod.is_pos_bank_integrate_payment == statusEnum.ACTIVE" class="mb-6">
                            <h3 class="capitalize font-medium mb-4">{{ $t("label.select_payment_currency") }}</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    v-for="currency in availableCurrencies"
                                    :key="currency.id"
                                    :class="[
                                        'cursor-pointer rounded-xl border transition-all duration-200 p-4 relative flex flex-col items-center justify-center h-[100px]',
                                        selectedPaymentCurrency?.id === currency.id
                                            ? 'border-blue-500 bg-blue-50 shadow-md'
                                            : 'border-gray-300 bg-white hover:border-blue-400 hover:bg-blue-50/50'
                                    ]"
                                    @click="selectPaymentCurrency(currency)"
                                >
                                    <div class="flex flex-col items-center gap-2 w-full">
                                        <span class="text-primary text-base font-medium">
                                            {{ calculateAmountInCurrency(currency) }}
                                        </span>
                                        <h3
                                            :class="[
                                                'text-sm font-medium text-center capitalize',
                                                selectedPaymentCurrency?.id === currency.id
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
                                        :checked="selectedPaymentCurrency?.id === currency.id"
                                    />
                                    
                                    <!-- Check Icon (Top Right) -->
                                    <div v-if="selectedPaymentCurrency?.id === currency.id" class="absolute top-2 right-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center shadow-md">
                                            <i class="fa-solid fa-check text-white text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!(selectedMethod && selectedMethod.is_pos_bank_integrate_payment == statusEnum.ACTIVE)">
                            <div v-if="!(selectedMethod && selectedMethod.is_pos_bank_integrate_payment == statusEnum.ACTIVE)" class="mb-4">
                                <h3 class="capitalize font-medium mb-4">{{ $t("label.payment") }}</h3>
                                <div
                                    class="flex justify-between items-center h-20 w-full rounded-lg py-1.5 px-2 placeholder:text-[10px] placeholder:text-[#6E7191] bg-[#F7F7FC] border border-[#D9DBE9]">
                                    <div>
                                        <span class="text-sm font-normal text-[#2E2F38]">{{ $t("label.total") }} ({{
                                            branch.currency_id?.code }})</span>
                                        <div v-if="selectedPaymentCurrency && selectedPaymentCurrency.id !== branch?.currency_id?.id">
                                            <span class="text-xs">1 {{ branch.currency_id?.code }} = {{ getExchangeRate(branch.currency_id?.code, selectedPaymentCurrency.code) }} {{ selectedPaymentCurrency.code }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-primary text-base font-medium" @click="setFullReceiveInBaseCurrency">
                                            {{ currencyFormat(totalInBaseCurrency, setting.site_digit_after_decimal_point,
                                                branch.currency_id?.symbol, setting.site_currency_position) }}
                                        </span>
                                        <div v-if="selectedPaymentCurrency && selectedPaymentCurrency.id !== branch?.currency_id?.id">
                                            <span class="text-xs" @click="setFullReceiveInPaymentCurrency">
                                                {{ calculateAmountInCurrency(selectedPaymentCurrency) }} {{ selectedPaymentCurrency.code }}
                                            </span>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            <!-- <div v-if="props.form.pos_payment_method === posPaymentMethodEnum.CASH && branch?.show_receive_amount == statusEnum.ACTIVE"> -->
                            <div v-if="branch?.show_receive_amount == statusEnum.ACTIVE">
                                <div class="mb-4">
                                    <div
                                        class="flex justify-between items-center h-20 w-full rounded-lg py-1.5 px-2 placeholder:text-[10px] placeholder:text-[#6E7191] bg-[#F7F7FC] border border-[#D9DBE9]">
                                        <div>
                                            <span class="text-sm font-normal text-[#2E2F38]">{{ $t("label.received") }} ({{
                                                branch.currency_id?.code }})</span>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="text-primary text-base font-medium">
                                                {{ receiveAmount }} {{ branch.currency_id?.symbol }}
                                            </span>
                                            <div v-if="hasReceivedAmounts" class="flex flex-wrap gap-1 justify-end mt-1">
                                                <template v-for="(amount, currencyId) in receivedAmounts" :key="currencyId">
                                                    <span v-if="amount && parseFloat(amount) > 0"
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                                                        {{ amount }} {{ getCurrencyCode(currencyId) }}
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div id="cash">
                                    <div class="mb-4">
                                        <!-- <h3 class="capitalize font-medium mb-2">{{ $t("label.received_amount") }}</h3> -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div v-for="currency in availableCurrencies" :key="currency.id" class="w-full">
                                                <label :for="'cashInput' + currency.code" class="block text-xs font-medium mb-1">
                                                    {{ $t("label.received_amount") }} {{ currency.code }}
                                                </label>
                                                <input 
                                                    :id="'cashInput' + currency.code" 
                                                    :ref="'cashInput' + currency.code" 
                                                    type="number"
                                                    v-on:keypress="floatNumber($event)"
                                                    class="h-12 w-full rounded-lg py-1.5 px-4 border border-[#D9DBE9] text-black"
                                                    :placeholder="calculateNumericAmountInCurrency(currency)"
                                                    @focus="inputIdName = 'cashInput' + currency.code" 
                                                    @input="calcReceiveAmount"
                                                    v-model="receivedAmounts[currency.id]" 
                                                    step="any"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div
                                        class="flex justify-between items-center h-20 w-full rounded-lg py-1.5 px-2 placeholder:text-[10px] placeholder:text-[#6E7191] bg-[#F7F7FC] border border-[#D9DBE9]">
                                        <div>
                                            <span class="text-sm font-normal text-[#2E2F38]">{{ $t("label.change") }} ({{
                                                branch.currency_id?.code }})</span>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="text-primary text-base font-medium">
                                                {{ currencyFormat(changeAmount, setting.site_digit_after_decimal_point,
                                                    branch.currency_id?.symbol, setting.site_currency_position) }}
                                            </span>
                                            <div v-if="selectedPaymentCurrency && selectedPaymentCurrency.id !== branch?.currency_id?.id && changeAmount">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                                                    {{ calculateAmountInCurrency(selectedPaymentCurrency, changeAmount) }} {{ selectedPaymentCurrency.code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    

                </div>

                <div class="row m-2">
                    <div class="col-12">
                        <button @click="handleConfirmOrder" type="button" :disabled="isButtonDisabled"
                            class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-3 text-white bg-[#1AB759] disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ props.form.order_id ? $t('button.pay_now') : $t('button.create_order_and_make_payment') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- <ReceiptComponent :order="order" /> -->
    <!-- <ReceiptComponent :order="order" :isPrintMenu="true" /> -->

    <ReceiptComponent :order="order" :isPrintMenu="true" :isPrintLabel="true"
        :isNewOrder="isNewOrder" :isAutoPrint="true" :autoCloseModalTime="3" :modalId="'paidOrderReceiptModal'" />


    <!-- Payment Waiting Modal -->
    <div id="paymentWaitingModal" class="modal">
        <div class="modal-dialog max-w-[500px] w-full">
            <div class="modal-header pb-3 border-b border-[#D9DBE9]">
                <h3 class="capitalize font-medium">
                    {{ paymentCompleted ? $t("label.payment_complete") : $t("label.payment") }}
                </h3>
            </div>
            <div class="modal-body">
                <!-- Payment Completed View -->
                <div v-if="paymentCompleted" class="flex flex-col items-center justify-center py-8 space-y-6">
                    <!-- Success Icon -->
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-check text-5xl text-green-600"></i>
                    </div>

                    <!-- Success Message -->
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 mb-2">{{ $t("label.successful_payment") }}</p>
                        <p class="text-sm text-gray-600">{{ $t("label.payment_completed_please_confirm") }}</p>
                    </div>

                    <!-- Payment Amount Display -->
                    <div class="text-center bg-gray-50 rounded-lg p-4 w-full">
                        <p class="text-sm text-gray-600">{{ $t("label.amount_paid") }}</p>
                        <p class="text-3xl font-bold text-primary">
                            {{ currencyFormat(totalInBaseCurrency, setting.site_digit_after_decimal_point,
                                branch.currency_id?.symbol, setting.site_currency_position) }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="hidePaymentWaitingModal()"
                        type="button"
                        class="capitalize text-sm font-medium leading-2 font-rubik px-12 py-4 text-center rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors w-full"
                    >
                        {{ $t("button.close") }}
                    </button>
                </div>

                <!-- Waiting for Payment View -->
                <div v-else class="flex flex-col items-center justify-center py-8 space-y-6">
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
                            class="capitalize text-sm font-medium font-rubik px-12 py-4 text-center rounded-md text-white bg-primary hover:bg-primary-dark transition-colors w-full"
                        >
                            <i class="fa-solid fa-rotate-right mr-2"></i>
                            {{ $t("button.try_again") }}
                        </button>
                        <!-- Cancel Button -->
                        <button
                            @click="confirmCancelPayment"
                            type="button"
                            class="capitalize text-sm font-medium font-rubik px-8 py-3 text-center rounded-md text-white bg-red-500 hover:bg-red-600 transition-colors"
                        >
                            {{ $t("button.cancel") }}
                        </button>
                    </template>

                    <!-- Normal QR / Waiting View -->
                    <template v-else>
                        <!-- QR Code Display -->
                        <div v-if="paymentQrCode" class="bg-white p-4 rounded-lg shadow-md">
                            <img :src="paymentQrCode" alt="Payment QR Code" class="w-160 h-300 object-contain" />
                        </div>
                        <div v-else class="w-64 h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-qrcode text-6xl text-gray-400"></i>
                        </div>

                        <!-- Waiting Text with Animation -->
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-spinner fa-spin text-primary text-xl"></i>
                            <p class="text-lg font-medium text-[#2E2F38]">{{ $t("label.waiting_customer_make_payment") }}</p>
                        </div>

                        <!-- Payment Amount Display -->
                        <!-- <div class="text-center">
                            <p class="text-sm text-gray-600">{{ $t("label.amount") }}</p>
                            <p class="text-2xl font-bold text-primary">
                                {{ currencyFormat(totalInBaseCurrency, setting.site_digit_after_decimal_point,
                                    branch.currency_id?.symbol, setting.site_currency_position) }}
                            </p>
                        </div> -->

                        <!-- Cancel Button -->
                        <button
                            @click="confirmCancelPayment"
                            type="button"
                            class="capitalize text-sm font-medium leading-2 font-rubik px-8 py-3 text-center rounded-md text-white bg-red-500 hover:bg-red-600 transition-colors"
                        >
                            {{ $t("button.cancel") }}
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import posCartSyncService from "../../../services/posCartSyncService";
import ReceiptComponent from "./ReceiptComponent";
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import statusEnum from '../../../enums/modules/statusEnum';
import sourceEnum from "../../../enums/modules/sourceEnum";
import isAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';

export default {
    name: "PaymentComponent",
    components: { LoadingComponent, ReceiptComponent },
    props: {
        props: Object,
        isNewOrder: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['orderPaid'],
    data() {
        return {
            loading: {
                isActive: false,
            },
            order: {},
            statusEnum: statusEnum,
            posPaymentMethodEnum: posPaymentMethodEnum,
            inputIdName: "cashInput",
            receivedAmounts: {}, // Object to track received amounts per currency ID
            receiveAmount: 0,
            changeAmount: 0,
            isProcessingOrder: false,
            lastClickTime: 0,
            debounceDelay: 5000, // 5 seconds debounce
            selectedPaymentCurrency: null, // Selected payment currency object
            paymentQrCode: null,
            paymentPollingInterval: null,
            paymentTransactionId: null,
            paymentAbapayDeeplink: null,
            maxPollingAttempts: 120, // 120 attempts * 5 seconds = 10 minutes
            pollingAttempts: 0,
            paymentCompleted: false,
            paymentCompletedData: null,
            selectedMethod: null,
            qrExpired: false,
            qrExpiryTimer: null,
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        paymentMethods: function () {
            return this.$store.getters['backendGlobalState/paymentMethods'];
            // return this.$store.getters['paymentMethod/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        currencies: function () {
            return this.$store.getters['currency/lists'];
        },
        exchangeRates: function () {
            return this.$store.getters['exchangeRate/lists'];
        },
        totalInBaseCurrency: function () {
            // If order already exists (has order_id), use balance_due
            // Otherwise calculate from total + total_tax
            if (this.$props.props.form.order_id && this.$props.props.form.balance_due !== undefined) {
                return parseFloat(parseFloat(this.$props.props.form.balance_due).toFixed(this.setting.site_digit_after_decimal_point || 2));
            }
            return parseFloat((parseFloat(this.$props.props.form.total) + parseFloat(this.$props.props.form.total_tax)).toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        isButtonDisabled: function () {
            return this.loading.isActive || this.isProcessingOrder;
        },
        hasReceivedAmounts: function () {
            return Object.values(this.receivedAmounts).some(amount => amount && parseFloat(amount) > 0);
        },
        availableCurrencies: function () {
            // If a payment method is selected with bank integration
            if (this.selectedMethod) {
                // If the payment method has supported currencies defined, use them
                if (this.selectedMethod.supported_currencies && this.selectedMethod.supported_currencies.length > 0) {
                    return this.selectedMethod.supported_currencies;
                }
            }
            // Otherwise, return all currencies
            return this.currencies;
        },
    },
    mounted() {
        // Load all currencies
        this.$store.dispatch('currency/lists', {
            order_column: 'id',
            order_type: 'asc'
        }).then(() => {
            // Set default payment currency to branch base currency
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }
        }).catch();

        // Load exchange rates
        this.$store.dispatch('exchangeRate/lists', {
            order_column: 'id',
            order_type: 'desc',
            paginate: 0
        }).catch();

    },
    beforeUnmount() {
        // Clear polling interval when component is destroyed
        this.stopPaymentPolling();
        this.stopQrExpiryTimer();
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
        calcReceiveAmount: function () {
            if (this.branch.show_receive_amount == statusEnum.ACTIVE) {
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
            }
        },
        getCurrencyCode: function (currencyId) {
            const currency = this.currencies.find(c => c.id == currencyId);
            return currency ? currency.code : '';
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

        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        floatNumber(e) {
            return appService.floatNumber(e);
        },
        reset: function () {
            this.clearData();
            posCartSyncService.clearPaymentMethod();

            appService.modalHide('#orderpayment');
        },
        selectPaymentMethod: function (paymentMethodId) {
            this.$props.props.form.pos_payment_method = paymentMethodId;
            this.$props.props.form.payment_method = paymentMethodId;

            // Find the selected payment method object
            const selectedMethod = this.paymentMethods.find(method => method.id === paymentMethodId);
            this.selectedMethod = selectedMethod;

            // Sync selected payment method to CustomerView
            if (selectedMethod) {
                posCartSyncService.syncPaymentMethod(selectedMethod);
            }
        },
        handleConfirmOrder: function () {
            const now = Date.now();
            // Debounce protection - prevent rapid clicks
            if (now - this.lastClickTime < this.debounceDelay) {
                return;
            }
            // Immediate state protection
            if (this.loading.isActive || this.isProcessingOrder) {
                return;
            }
            this.lastClickTime = now;
            this.confirmOrder();
        },
        confirmOrder: function () {
            console.log("Confirm Order Triggered");

            // Final protection check
            if (this.loading.isActive || this.isProcessingOrder) {
                return;
            }

            this.loading.isActive = true;
            this.isProcessingOrder = true;

            // Clean up receivedAmounts - remove empty or zero values
            Object.keys(this.receivedAmounts).forEach(currencyId => {
                const amount = parseFloat(this.receivedAmounts[currencyId] || 0);
                if (amount <= 0) {
                    delete this.receivedAmounts[currencyId];
                }
            });

            // If no amounts received, set full amount in order currency (base currency)
            if (!this.hasReceivedAmounts) {
                if (this.branch?.currency_id?.id) {
                    // If payment currency is selected (different from base), set amount in that currency
                    if (this.selectedPaymentCurrency && this.selectedPaymentCurrency.id !== this.branch.currency_id.id) {
                        // Calculate amount in selected payment currency
                        const exchangeRate = this.getExchangeRate(this.branch.currency_id.code, this.selectedPaymentCurrency.code);
                        const amountInPaymentCurrency = this.totalInBaseCurrency * exchangeRate;
                        this.receivedAmounts = {
                            [this.selectedPaymentCurrency.id]: parseFloat(amountInPaymentCurrency.toFixed(this.setting.site_digit_after_decimal_point || 2))
                        };
                    } else {
                        // Set full payment in base currency
                        this.receivedAmounts = {
                            [this.branch.currency_id.id]: this.totalInBaseCurrency
                        };
                        
                        // Only set selected payment currency to base currency if none is selected
                        if (!this.selectedPaymentCurrency) {
                            this.selectedPaymentCurrency = this.branch.currency_id;
                        }
                    }
                }
                this.calcReceiveAmount();
            } 

            if (this.branch.show_receive_amount == statusEnum.ACTIVE && parseFloat(this.receiveAmount) < parseFloat(this.totalInBaseCurrency)) {
                this.loading.isActive = false;
                this.isProcessingOrder = false;
                alertService.error(this.$t("message.received_amount_is_less_than_total"));
                return;
            }

            try {
                this.$props.props.form.payment_status = paymentStatusEnum.PAID;
                this.$props.props.form.check_out_time = new Date().toISOString();

                this.$props.props.form.pos_received_amount = this.receiveAmount;
                
                // Send multi-currency received amounts to backend
                // Format: { currencyId: amount, currencyId: amount, ... }
                this.$props.props.form.received_amounts = this.receivedAmounts;

                // Set order currency (base currency from branch)
                this.$props.props.form.currency = this.branch.currency_id?.code;
                this.$props.props.form.currency_id = this.branch.currency_id?.id;

                // Set receive payment currency (currency customer actually pays in)
                if (this.selectedPaymentCurrency) {
                    this.$props.props.form.receive_payment_currency = this.selectedPaymentCurrency.code;
                    this.$props.props.form.receive_payment_currency_id = this.selectedPaymentCurrency.id;
                } else {
                    // Default to same as order currency
                    this.$props.props.form.receive_payment_currency = this.branch.currency_id?.code;
                    this.$props.props.form.receive_payment_currency_id = this.branch.currency_id?.id;
                }

                console.log('Setting payment currencies:', {
                    order_currency: this.$props.props.form.currency,
                    order_currency_id: this.$props.props.form.currency_id,
                    receive_payment_currency: this.$props.props.form.receive_payment_currency,
                    receive_payment_currency_id: this.$props.props.form.receive_payment_currency_id,
                    selectedPaymentCurrency: this.selectedPaymentCurrency
                });

                // Ensure preparation_time has a default value
                if (!this.$props.props.form.preparation_time) {
                    this.$props.props.form.preparation_time = 0;
                }

                // Check if selected payment method requires bank integration
                const selectedMethod = this.paymentMethods.find(method => method.id === this.$props.props.form.pos_payment_method);
                if (selectedMethod && selectedMethod.is_pos_bank_integrate_payment === this.statusEnum.ACTIVE) {
                    console.log("Initiating integrated payment for method:", selectedMethod);
                    this.initiateIntegratedPayment();
                    return;
                }

                // Proceed with normal order completion
                this.proceedWithOrderCompletion();

            } catch (err) {
                this.loading.isActive = false;
                this.isProcessingOrder = false;
                alertService.error(err);
            }
        },
        clearData: function () {
            this.receivedAmounts = {};
            this.receiveAmount = 0;
            this.changeAmount = 0;
            this.isProcessingOrder = false;
            this.lastClickTime = 0;
            this.paymentQrCode = null;
            this.paymentTransactionId = null;
            this.paymentAbapayDeeplink = null;
            this.pollingAttempts = 0;
            this.qrExpired = false;
            this.stopQrExpiryTimer();

            this.paymentCompleted = false;
            this.paymentCompletedData = null;
            
            // Reset payment currency to branch currency
            if (this.branch?.currency_id) {
                this.selectedPaymentCurrency = this.currencies.find(c => c.id === this.branch.currency_id.id) || this.branch.currency_id;
            }

            this.$props.props.form.token = "";
            this.$props.props.form.subtotal = null;
            this.$props.props.form.discount = 0;
            this.$props.props.form.total_tax = 0;
            this.$props.props.form.delivery_time = null;
            this.$props.props.form.delivery_charge = null;
            this.$props.props.form.total = 0;
            this.$props.props.form.order_type = orderTypeEnum.DINING_TABLE;
            this.$props.props.form.is_advance_order = isAdvanceOrderEnum.NO;
            this.$props.props.form.source = sourceEnum.POS;
            this.$props.props.form.address_id = null;
            this.$props.props.form.dining_table_id = null;
            this.$props.props.form.coupon_id = null;
            this.$props.props.form.items = [];
            this.$props.props.form.pos_payment_method = this.posPaymentMethodEnum.CASH;
            this.$props.props.form.payment_method = this.posPaymentMethodEnum.CASH;
            this.$props.props.form.preparation_time = 0;
            this.$props.props.form.pos_payment_note = null;
            this.$props.props.form.pos_received_amount = null;
            this.$props.props.form.currency = null;
            this.$props.props.form.currency_id = null;
            this.$props.props.form.receive_payment_currency = null;
            this.$props.props.form.receive_payment_currency_id = null;
            this.$props.props.form.discount_percentage = 0;
            this.$props.props.form.member_id = null;
            this.$props.props.form.order_note = null;
        },
        selectPaymentCurrency: function (currency) {
            this.selectedPaymentCurrency = currency;
            console.log('Payment currency selected:', {
                id: currency.id,
                code: currency.code,
                name: currency.name
            });
        },
        calculateAmountInCurrency: function (currency, amount = null) {
            const baseAmount = amount !== null ? amount : this.totalInBaseCurrency;
            const baseCurrencyCode = this.branch?.currency_id?.code;
            
            // If selected currency is the same as base currency, return base amount
            if (currency.code === baseCurrencyCode) {
                return this.currencyFormat(baseAmount, this.setting.site_digit_after_decimal_point, currency.symbol, this.setting.site_currency_position);
            }
            
            // Calculate amount using exchange rate system
            const exchangeRate = this.getExchangeRate(baseCurrencyCode, currency.code);
            const convertedAmount = baseAmount * exchangeRate;
            return this.currencyFormat(convertedAmount, this.setting.site_digit_after_decimal_point, currency.symbol, this.setting.site_currency_position);
        },
        calculateNumericAmountInCurrency: function (currency, amount = null) {
            const baseAmount = amount !== null ? amount : this.totalInBaseCurrency;
            const baseCurrencyCode = this.branch?.currency_id?.code;
            
            // If selected currency is the same as base currency, return base amount
            if (currency.code === baseCurrencyCode) {
                return parseFloat(baseAmount.toFixed(this.setting.site_digit_after_decimal_point || 2));
            }
            
            // Calculate amount using exchange rate system
            const exchangeRate = this.getExchangeRate(baseCurrencyCode, currency.code);
            const convertedAmount = baseAmount * exchangeRate;
            return parseFloat(convertedAmount.toFixed(this.setting.site_digit_after_decimal_point || 2));
        },
        getPaymentMethodIcon: function(paymentMethod) {
            // If payment method has an icon field, use it
            if (paymentMethod.icon) {
                return paymentMethod.icon;
            }

            // Otherwise, map by name or ID to default icons
            const name = paymentMethod.name?.toLowerCase() || '';

            if (name.includes('cash')) {
                return 'fa-solid fa-money-bill-wave';
            } else if (name.includes('card') || name.includes('credit')) {
                return 'fa-solid fa-credit-card';
            } else if (name.includes('bank') || name.includes('transfer')) {
                return 'fa-solid fa-building-columns';
            } else if (name.includes('mobile') || name.includes('wallet') || name.includes('digital')) {
                return 'fa-solid fa-mobile-screen';
            } else if (name.includes('qr')) {
                return 'fa-solid fa-qrcode';
            } else if (name.includes('paypal')) {
                return 'fa-brands fa-paypal';
            } else if (name.includes('stripe')) {
                return 'fa-brands fa-stripe';
            } else {
                return 'fa-solid fa-wallet';
            }
        },
        initiateIntegratedPayment: function () {
            this.qrExpired = false;
            this.stopQrExpiryTimer();
            console.log("Initiating Integrated Payment Process..");
            // Generate PayWay QR payment
            this.$store.dispatch('defaultAccess/show').then(async (res) => {

                console.log("Prepare order items for PayWay", res);
                console.log("Props: ", this.$props);
                console.log("Items: ", this.$props.props.form.items);

                // Prepare order items for PayWay
                // Ensure items is an array and map correctly
                let itemsData = this.$props.props.form.items;

                // Convert from string if necessary
                if (typeof itemsData === 'string') {
                    try {
                        itemsData = JSON.parse(itemsData);
                    } catch (e) {
                        console.error('Failed to parse items from string:', e);
                        itemsData = [];
                    }
                }

                const items = Array.isArray(itemsData)
                    ? itemsData.map(item => ({
                        name: item.order_item_custom_name || item.item_name || `Item ${item.item_id}`,
                        quantity: item.quantity || 1,
                        price: parseFloat(item.item_price || item.price || 0)
                    }))
                    : [];

                console.log("Mapped items for PayWay:", items);
                console.log("This is running 01")

                // Calculate amount in selected payment currency
                const paymentCurrency = this.selectedPaymentCurrency || this.branch.currency_id;
                let paymentAmount = this.totalInBaseCurrency;
                
                // If payment currency is different from base currency, convert using exchange rate
                if (paymentCurrency.code !== this.branch?.currency_id?.code) {
                    const exchangeRate = this.getExchangeRate(this.branch.currency_id.code, paymentCurrency.code);
                    paymentAmount = this.totalInBaseCurrency * exchangeRate;
                }

                const paymentData = {
                    amount: paymentAmount,
                    currency: paymentCurrency.code || 'USD',
                    payment_method_id: this.$props.props.form.pos_payment_method,
                    branch_id: res.data?.data?.branch_id,
                    order_items: items,
                    customer_info: {
                        first_name: this.$props.props.form.customer_first_name || '',
                        last_name: this.$props.props.form.customer_last_name || '',
                        email: this.$props.props.form.customer_email || '',
                        phone: this.$props.props.form.customer_phone || '',
                    }
                };

                // Step 1: Create pending order before generating QR
                // This ensures order_id is stored in payway_transactions from the start
                if (!this.$props.props.form.order_id) {
                    try {
                        this.$props.props.form.branch_id = res.data?.data?.branch_id;
                        // Save form's current payment_status to restore it after pre-creation
                        const savedPaymentStatus = this.$props.props.form.payment_status;
                        this.$props.props.form.payment_status = paymentStatusEnum.UNPAID;

                        const pendingOrderResponse = await this.$store.dispatch('posOrder/save', this.$props.props.form);
                        this.$props.props.form.order_id = pendingOrderResponse.data.data.id;
                        console.log('Pending order created for QR. order_id:', this.$props.props.form.order_id);

                        // Restore payment_status so proceedWithOrderCompletion marks it as PAID later
                        this.$props.props.form.payment_status = savedPaymentStatus;
                    } catch (createErr) {
                        this.loading.isActive = false;
                        this.isProcessingOrder = false;
                        alertService.error(this.$t('message.order_creation_failed') || 'Failed to create order');
                        console.error('Failed to create pending order before QR:', createErr);
                        return;
                    }
                }

                // Step 2: Include order_id so payway_transactions is linked from creation
                paymentData.order_id = this.$props.props.form.order_id;

                console.log("This is running 02")

                // Step 3: Generate QR code
                this.$store.dispatch('posOrder/initiatePayWayQRPayment', paymentData)
                    .then((response) => {
                        console.log("This is running 03")

                        // PayWay returns: qrImage, qrString, abapay_deeplink, amount, currency
                        this.paymentQrCode = response.data.data.qrImage; // Base64 QR image
                        this.paymentTransactionId = response.data.data.tran_id;
                        this.paymentAbapayDeeplink = response.data.data.abapay_deeplink;

                        const lifetime = response.data.data.lifetime ?? 3; // minutes

                        // Sync QR code to CustomerView
                        posCartSyncService.syncToCustomerView('showPaymentQR', {
                            qrCode: this.paymentQrCode,
                            qrString: response.data.data.qrString,
                            amount: this.totalInBaseCurrency,
                            currency: this.branch.currency_id,
                            transactionId: this.paymentTransactionId,
                            abapayDeeplink: this.paymentAbapayDeeplink
                        });

                        // Close payment modal and show waiting modal
                        appService.modalHide('#orderpayment');
                        appService.modalShow('#paymentWaitingModal');

                        // Hide fullscreen loading during payment waiting
                        this.loading.isActive = false;

                        console.log("This is running 04")

                        // Start QR expiry timer
                        this.startQrExpiryTimer(lifetime);

                        // Start polling for payment status
                        this.startPaymentPolling();
                        console.log("This is running 05")
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.isProcessingOrder = false;
                        const errorMessage = err.response?.data?.status?.message || this.$t('message.failed_to_initiate_payment');
                        alertService.error(errorMessage);
                        console.error('PayWay QR generation error:', err);
                    });
            }).catch((err) => {
                this.loading.isActive = false;
                this.isProcessingOrder = false;
                alertService.error(this.$t('message.failed_to_get_branch_info'));
                console.error('Branch info error:', err);
            });
        },
        startQrExpiryTimer: function (lifetimeMinutes) {
            this.stopQrExpiryTimer();
            const ms = lifetimeMinutes * 60 * 1000;
            this.qrExpiryTimer = setTimeout(() => {
                this.stopPaymentPolling();
                this.paymentQrCode = null;
                this.qrExpired = true;
                // Notify customer view
                posCartSyncService.syncToCustomerView('hidePaymentQR', {});
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
            // Close the old (expired) transaction before requesting a new QR
            if (this.paymentTransactionId) {
                this.$store.dispatch('posOrder/cancelPayWayTransaction', {
                    tran_id: this.paymentTransactionId
                }).catch(() => {/* ignore */});
            }
            this.paymentTransactionId = null;
            this.paymentAbapayDeeplink = null;
            this.pollingAttempts = 0;
            this.qrExpired = false;
            this.isProcessingOrder = true;
            this.initiateIntegratedPayment();
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

            // Stop polling if max attempts reached
            if (this.pollingAttempts >= this.maxPollingAttempts) {
                this.stopPaymentPolling();
                this.handlePaymentTimeout();
                return;
            }

            // Call PayWay Check Transaction API
            this.$store.dispatch('posOrder/checkPayWayTransactionStatus', {
                tran_id: this.paymentTransactionId
            })
                .then((response) => {
                    const data = response.data.data;
                    const statusCode = data.payment_status_code;
                    const paymentStatus = data.payment_status;

                    // PayWay status codes:
                    // 0: APPROVED or PRE-AUTH
                    // 2: PENDING
                    // 3: DECLINED
                    // 4: REFUNDED
                    // 7: CANCELLED

                    console.log("Payment Transaction ID:", this.paymentTransactionId);
                    console.log("Polled PayWay Status:", response.data);

                    if (statusCode === 0 && (paymentStatus === 'APPROVED' || paymentStatus === 'PRE-AUTH')) {
                        this.handlePaymentSuccess(data);
                    } else if (statusCode === 3 || statusCode === 7) {
                        // DECLINED or CANCELLED
                        this.handlePaymentFailure(data);
                    }
                    // If status is PENDING (2), continue polling
                })
                .catch((err) => {
                    console.error('PayWay transaction status check error:', err);
                    // Continue polling even if there's an error
                });
        },
        handlePaymentSuccess: function (paymentData) {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();

            // Store payment data for confirmation
            this.paymentCompleted = true;
            this.paymentCompletedData = paymentData;
            this.$props.props.form.payment_transaction_id = this.paymentTransactionId;
            // Convert paymentData object to JSON string for Laravel validation
            this.$props.props.form.payment_transaction_data = JSON.stringify(paymentData);

            console.log("Payment Successful Data:", paymentData);
            console.log("Payment Transaction Details:", {
                transaction_id: this.paymentTransactionId,
                payment_amount: paymentData.payment_amount,
                payment_currency: paymentData.payment_currency,
                receive_payment_currency: this.$props.props.form.receive_payment_currency,
                receive_payment_currency_id: this.$props.props.form.receive_payment_currency_id
            });

            this.confirmPaymentAndCreateOrder();

            // Broadcast payment complete to customer view
            posCartSyncService.syncToCustomerView('paymentComplete', {
                status: 'success',
                amount: this.totalInBaseCurrency,
                currency: this.branch.currency_id
            });

            // Modal stays open to show confirmation screen
            // Cashier can click close button to proceed

        },
        confirmPaymentAndCreateOrder: function () {
            // Cashier confirmed, proceed with order creation
            this.hidePaymentWaitingModal();
            // Proceed with order completion

            this.proceedWithOrderCompletion();

            alertService.success(this.$t('message.payment_successful'));
            // Clear customer view
            posCartSyncService.syncToCustomerView('hidePaymentQR', {});


            // Reset payment states
            this.paymentCompleted = false;
            this.paymentCompletedData = null;
        },
        handlePaymentFailure: function (paymentData) {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();
            appService.modalHide('#paymentWaitingModal');

            this.loading.isActive = false;
            this.isProcessingOrder = false;

            // Clear customer view
            posCartSyncService.syncToCustomerView('hidePaymentQR', {});

            alertService.error(this.$t('message.payment_failed'));

            // Reset payment data
            this.paymentQrCode = null;
            this.paymentTransactionId = null;
            this.paymentCompleted = false;
            this.paymentCompletedData = null;
        },
        handlePaymentTimeout: function () {
            this.stopQrExpiryTimer();
            appService.modalHide('#paymentWaitingModal');

            this.loading.isActive = false;
            this.isProcessingOrder = false;

            alertService.error(this.$t('message.payment_timeout'));

            // Reset payment data
            this.paymentQrCode = null;
            this.paymentTransactionId = null;
        },
        confirmCancelPayment: function () {
            appService.cancelPayment(this.$t('message.confirm_cancel_payment')).then(() => {
                this.cancelPayment();
            }).catch((err) => {
                this.loading.isActive = false;
            });
            // appService.warning(this.$t('message.payment_cancelled')).then(() => {
            // });
            // this.cancelPayment();
        },
        hidePaymentWaitingModal: function () {
            appService.modalHide('#paymentWaitingModal');
        },
        cancelPayment: function () {
            this.stopPaymentPolling();
            this.stopQrExpiryTimer();

            // Call PayWay Close Transaction API to cancel
            // This is a non-blocking operation - we proceed with cancellation regardless of API response
            if (this.paymentTransactionId) {
                this.$store.dispatch('posOrder/cancelPayWayTransaction', {
                    tran_id: this.paymentTransactionId
                })
                    .then((response) => {
                        // Successfully cancelled via API
                        console.log('PayWay transaction cancelled successfully:', response);
                    })
                    .catch((err) => {
                        // Log warning but don't block cancellation
                        // PayWay API might legitimately fail for expired/already-closed transactions
                        console.warn('PayWay transaction cancellation API error (non-critical):', err);
                    });
            }

            // Always proceed with UI cleanup and state reset
            appService.modalHide('#paymentWaitingModal');

            this.loading.isActive = false;
            this.isProcessingOrder = false;

            // Clear customer view
            posCartSyncService.syncToCustomerView('hidePaymentQR', {});

            // Reset payment data
            this.paymentQrCode = null;
            this.paymentTransactionId = null;
            this.paymentAbapayDeeplink = null;
            this.pollingAttempts = 0;
            this.paymentCompleted = false;
            this.paymentCompletedData = null;
            this.qrExpired = false;

            alertService.info(this.$t('message.payment_cancelled'));
        },
        proceedWithOrderCompletion: function () {
            // This contains the original order completion logic from confirmOrder
            this.$store.dispatch("defaultAccess/show").then((res) => {
                this.$props.props.form.branch_id = res.data?.data?.branch_id;

                if (this.$props.props.form.order_id) {
                    this.$store.dispatch('posOrder/payOrder', this.$props.props.form).then((orderResponse) => {
                        alertService.success(this.$t('message.order_successfully'));
                        this.order = orderResponse.data?.data;
                        this.clearData();
                        posCartSyncService.clearPaymentMethod();
                        appService.modalHide('#orderpayment');

                        // Reset cart and show receipt (same behaviour as new order flow)
                        this.$store.dispatch('posCart/resetCart').then(res => {
                            posCartSyncService.clearCartData();
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;
                        }).catch();

                        this.$store.dispatch('posOrder/show', orderResponse.data.data.id).then((res) => {
                            this.order = res.data.data;
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;
                            appService.modalShow('#paidOrderReceiptModal');
                        }).catch((error) => {
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;
                            alertService.error(error.response?.data?.message);
                        });

                        this.$emit('orderPaid', orderResponse);
                    }).catch((err) => {
                        this.loading.isActive = false;
                        this.isProcessingOrder = false;
                        console.error('Error paying order:', err);
                    });
                } else {
                    this.$store.dispatch('posOrder/save', this.$props.props.form).then((orderResponse) => {
                        alertService.success(this.$t('message.order_successfully'));

                        this.clearData();
                        posCartSyncService.clearPaymentMethod();
                        appService.modalHide('#orderpayment');
                        this.$store.dispatch('posCart/resetCart').then(res => {
                            posCartSyncService.clearCartData();
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;
                        }).catch();

                        this.$store.dispatch('posOrder/show', orderResponse.data.data.id).then((res) => {
                            this.order = res.data.data;
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;

                            appService.modalShow('#paidOrderReceiptModal');
                        }).catch((error) => {
                            this.loading.isActive = false;
                            this.isProcessingOrder = false;
                            alertService.error(error.response.data.message);
                        });


                    }).catch((err) => {
                        this.loading.isActive = false;
                        this.isProcessingOrder = false;
                        if (typeof err.response.data.errors === 'object') {
                            _.forEach(err.response.data.errors, (error) => {
                                alertService.error(error[0]);
                            });
                        }
                    });
                }
            }).catch((err) => {
                this.loading.isActive = false;
                this.isProcessingOrder = false;
            });
        },
    },
};
</script>
