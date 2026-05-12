<template>
    <div>
        <div class="text-center pb-3.5">
            <h3 class="text-xl font-bold mb-1">
                {{ company.company_name }}
            </h3>
            <h4 class="text-sm font-normal">
                {{ branch.address }}
            </h4>
            <h5 class="text-sm font-normal">Tel: {{ branch.phone }}</h5>
        </div>
        <div class="text-center pb-3.5">
            <h1 class="text-sm font-bold">{{ $t('label.invoice') }}</h1>
        </div>
        <div class="text-center pt-2 pb-4" v-if="order.dining_tables && Array.isArray(order.dining_tables) && order.dining_tables.length > 0">
            <h3 class="text-xl font-medium">
                ( {{ $t('label.table') }}: <span v-for="(item, index) in order.dining_tables" :key="item.id">{{ item.name }}<span v-if="index < order.dining_tables.length - 1">, </span></span> )
            </h3>
        </div>
        <table class="w-full my-1.5">
            <tbody>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.order_number') }}: #{{ order.order_serial_no }}</td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.date') }}: {{ order.order_date }}, {{ order.order_time }}</td>
                </tr>
                <tr v-if="order.invoice_number">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.invoice_number') }}: {{ order.invoice_number }}</td>
                </tr>
                <tr v-if="order.order_user?.name">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.cashier') }}: {{ order.order_user.name }}</td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.check_in_time') }}: {{ receiptCheckInTime }}</td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.check_out_time') }}: {{ receiptCheckOutTime }}</td>
                </tr>
                <tr v-if="order.customer_name">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.customer_name') }}: {{ order.customer_name }}</td>
                </tr>
                <tr v-if="order.customer_phone_number">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.phone_number') }}: {{ order.customer_phone_number }}</td>
                </tr>
                <tr v-if="order.customer_address">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.address') }}: {{ order.customer_address }}</td>
                </tr>
            </tbody>
        </table>
        <table class="w-full">
            <thead>
                <tr>
                    <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading">
                        {{ $t('label.item_description') }}
                    </th>
                    <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading">
                        {{ $t('label.qty') }}
                    </th>
                    <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading">
                        {{ $t('label.unit') }} {{ $t('label.price') }}
                    </th>
                    <th scope="col" class="py-1 font-normal text-xs capitalize text-right text-heading">
                        {{ $t('label.amount') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in order.order_items_unique" :key="item.id">
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xs font-normal text-heading">
                            {{ index + 1 }}. {{ item.item_name }}
                        </p>
                        <p v-if="Object.keys(item.item_variations).length !== 0" class="text-xs leading-4 font-normal text-heading">
                            <span v-for="(variation, vIndex) in item.item_variations" :key="vIndex">
                                {{ variation.variation_name }}: {{ variation.name }}<span v-if="vIndex + 1 < Object.keys(item.item_variations).length">, </span>
                            </span>
                        </p>
                        <p v-if="item.item_extras.length > 0" class="text-xs leading-4 font-normal text-heading">
                            {{ $t('label.extras') }}: <span v-for="(extra, eIndex) in item.item_extras" :key="eIndex">{{ extra.name }}<span v-if="eIndex + 1 < item.item_extras.length">, </span></span>
                        </p>
                        <p v-if="item.instruction" class="text-xs leading-4 font-normal text-heading">
                            {{ $t('label.instruction') }}: {{ item.instruction }}
                        </p>
                        <p v-if="item.discount > 0" class="text-xs leading-4 font-normal text-heading">
                            {{ $t('label.discount') }}: {{ currencyFormat(item.discount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}<span v-if="item.discount_percentage > 0"> ({{ item.discount_percentage }}%)</span>
                        </p>
                    </td>
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xs leading-5 text-heading">
                            {{ item.quantity }}
                        </p>
                    </td>
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xs leading-5 text-heading">
                            {{ currencyFormat(item.price, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                        </p>
                    </td>
                    <td class="text-right font-normal align-top py-1">
                        <p class="text-xs leading-5 text-heading">
                            {{ item.total_without_tax_currency_price }}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="py-2">
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.subtotal') }}:</td>
                        <td class="text-xs text-right py-0.5 text-heading">
                            {{ order.subtotal_without_tax_currency_price }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.discount') }} ({{ order?.discount_percentage }}%):</td>
                        <td class="text-xs text-right py-0.5 text-heading">
                            {{ order.discount_currency_price }}
                        </td>
                    </tr>
                    <tr v-if="parseFloat(order.total_tax_price) > 0">
                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.total_tax') }}:</td>
                        <td class="text-xs text-right py-0.5 text-heading">
                            {{ order.total_tax_currency_price }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.total') }}({{ branch.currency_id ? branch.currency_id.symbol : '' }}):</td>
                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                            {{ order.total_currency_price }}
                        </td>
                    </tr>
                    <tr v-if="order.branch?.currency_id?.second_currency">
                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.total') }}({{ order.branch?.currency?.second_currency }}):</td>
                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                            {{ formatSecondCurrency(order.branch?.currency_id?.second_currency_exchange_rate, order.total_amount_price, order.branch?.currency?.second_decimal) }} {{ order.branch?.currency?.second_currency }}
                        </td>
                    </tr>
                    <tr v-if="parseFloat(order.pos_received_amount) > 0">
                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.received') }}</td>
                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                            <span class="text-xs">{{ currencyFormat(order.pos_received_amount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}</span>
                        </td>
                    </tr>
                    <tr v-if="parseFloat(order.pos_received_amount) > 0 && parseFloat(order.pos_change_amount) !== 0">
                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.change') }}</td>
                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                            <span class="text-xs">{{ order.pos_change_amount }} {{ branch.currency?.symbol }}</span>
                            &asymp; <span class="text-xs">{{ formatSecondCurrency(order.branch?.currency?.second_currency_exchange_rate, order.pos_change_amount, order.branch?.currency?.second_decimal) }} {{ order.branch?.currency_id?.second_currency }}</span>
                        </td>
                    </tr>
                    <!-- <tr v-if="order.branch?.currency?.second_currency">
                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.exchange_rate') }}</td>
                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                            <span class="text-xs">{{ formatExchangeRate(branch?.currency_id) }}</span>
                        </td>
                    </tr> -->
                </tbody>
            </table>
        </div>
        <p class="text-xs py-2 text-heading">
            {{ isPrintUpdate ? $t('label.update') : $t('label.bill') }} <span v-if="branch.show_waiting_number == statusEnum.ACTIVE && order.waiting_number && order.waiting_number > 0">(#{{ order.waiting_number }})</span>
            <br>
            {{ $t('label.payment_type') }} : {{ order.payment_status == paymentStatusEnum.PAID && order.payment_method_name ? order.payment_method_name : $t('label.unpaid') }}
            <br />
            {{ $t('label.order_type') }} : {{ enums.orderTypeEnumArray[order.order_type] }}
        </p>
        <p class="py-2 text-heading" v-if="order.order_note">{{ $t('label.note') }} : {{ order.order_note }}</p>
        <h4 v-if="order.token" class="py-2 capitalize text-xl font-bold text-center">{{ $t('label.token') }} #{{ order.token }}</h4>
        <div class="text-center pt-2 pb-4" v-if="order.dining_tables && order.dining_tables.length">
            <p class="text-[11px] leading-[14px] capitalize text-heading">
                {{ $t('message.thank_you') }}
            </p>
            <p class="text-[11px] leading-[14px] capitalize text-heading">
                {{ $t('message.please_come_again') }}
            </p>
        </div>
        <div class="flex flex-col items-end">
            <h5 class="text-[8px] font-normal text-left w-[46px] leading-[10px]">
                {{ $t('label.powered_by') }}
            </h5>
            <h6 class="text-xs font-normal leading-4">Chilly POS</h6>
        </div>
    </div>
</template>

<script>
import statusEnum from '../../../../enums/modules/statusEnum';
import paymentStatusEnum from '../../../../enums/modules/paymentStatusEnum';
import orderTypeEnum from '../../../../enums/modules/orderTypeEnum';
import appService from '../../../../services/appService';

export default {
    name: "InvoiceComponent",
    props: {
        order: {
            type: Object,
            required: true,
        },
        company: {
            type: Object,
            required: true,
        },
        branch: {
            type: Object,
            required: true,
        },
        setting: {
            type: Object,
            required: true,
        },
        isPrintUpdate: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            statusEnum: statusEnum,
            paymentStatusEnum: paymentStatusEnum,
            enums: {
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.TAKEAWAY]: this.$t('label.takeaway'),
                    [orderTypeEnum.DELIVERY]: this.$t('label.delivery'),
                    [orderTypeEnum.POS]: this.$t('label.pos'),
                    [orderTypeEnum.TOKEN]: this.$t('label.token'),
                    [orderTypeEnum.DINING_TABLE]: this.$t('label.dining_table'),
                },
            },
        };
    },
    computed: {
        receiptCheckInTime() {
            return this.order.check_in_time || 'N/A';
        },
        receiptCheckOutTime() {
            console.log(this.order);
            return this.order.order_datetime || 'N/A';
        },
    },
    methods: {
        formatExchangeRate: function (currency) {
            return appService.formatExchangeRate(currency);
        },
        formatSecondCurrency: function (exchangeRate, totalPrice, decimal) {
            console.log(appService.secondExchangeRate(exchangeRate, totalPrice, decimal ?? 0));

            return appService.secondExchangeRate(exchangeRate, totalPrice, decimal ?? 0);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
    },
};
</script>
