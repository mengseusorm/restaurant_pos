<template>
    <div>
        <div class="text-center pb-2.5">
            <h2 class="text-2xl font-bold mb-1">
                {{ company.company_name }}
            </h2>
        </div>
        <div class="text-center pb-2.5">
            <h1 class="text-xl font-bold">
                <template v-if="order.dining_tables && order.dining_tables.length">
                    ( {{ $t('label.table') }} : <span v-for="(item, index) in order.dining_tables" :key="item.id">{{ item.name }}<span v-if="index < order.dining_tables.length - 1">, </span></span> )
                </template>
            </h1>
        </div>
        <table class="w-full my-1.5">
            <tbody>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.order_number') }}: #{{ order.order_serial_no }}</td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.date') }}: {{ order.order_date }}, {{ order.order_time }}</td>
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
                            {{ $t('label.discount') }}: {{ item.discount }} {{ branch.currency_id?.symbol }}<span v-if="item.discount_percentage > 0"> ({{ item.discount_percentage }}%)</span>
                        </p>
                    </td>
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xs leading-5 text-heading">
                            {{ item.quantity }}
                        </p>
                    </td>
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xs leading-5 text-heading">
                            {{ item.price }} {{ branch.currency_id?.symbol }}
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
                </tbody>
            </table>
        </div>
        <p class="text-xs py-2 text-heading" v-if="order.payment_status == 5">
            {{ isPrintUpdate ? $t('label.update') : $t('label.bill') }} <span v-if="branch.show_waiting_number == statusEnum.ACTIVE && order.waiting_number && order.waiting_number > 0">(#{{ order.waiting_number }})</span>
            <br>
            {{ $t('label.payment_type') }} : {{ order.payment_status == paymentStatusEnum.PAID && order.payment_method_name ? order.payment_method_name : $t('label.unpaid') }}
            <br />
            {{ $t('label.order_type') }} : {{ enums.orderTypeEnumArray[order.order_type] }}
        </p>
        <p class="text-xs py-2 text-heading" v-if="order.order_note">{{ $t('label.table_order_note') }} : {{ order.order_note }}</p>
        <h4 v-if="order.token" class="py-2 capitalize text-xl font-bold text-center border-b border-dashed border-gray-400">{{ $t('label.token') }} #{{ order.token }}</h4>
        <p class="text-xs py-2 text-heading">
            {{ isPrintUpdate ? $t('label.update') : $t('label.bill') }} <span v-if="branch.show_waiting_number == statusEnum.ACTIVE && order.waiting_number && order.waiting_number > 0">(#{{ order.waiting_number }})</span>
            <br />
            {{ $t('label.payment_type') }} : {{ order.payment_status == paymentStatusEnum.PAID && order.payment_method_name ? order.payment_method_name : $t('label.unpaid') }}
            <br />
            {{ $t('label.order_type') }} : {{ enums.orderTypeEnumArray[order.order_type] }}
        </p>
    </div>
</template>

<script>
import statusEnum from '../../../../enums/modules/statusEnum';
import paymentStatusEnum from '../../../../enums/modules/paymentStatusEnum';
import orderTypeEnum from '../../../../enums/modules/orderTypeEnum';

export default {
    name: "BillComponent",
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
            return this.order.check_out_time || this.order.checkout || 'N/A';
        },
    },
};
</script>
