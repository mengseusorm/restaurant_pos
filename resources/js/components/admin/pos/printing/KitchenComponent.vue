<template> 
    <div>
        <div class="text-center pb-3.5">
            <h1 class="text-xl font-bold">
                {{ printer.label }}
                <template v-if="order.dining_tables && order.dining_tables.length">
                    ( {{ $t('label.table') }} : <span v-for="(item, index) in order.dining_tables" :key="item.id">{{ item.name }}<span v-if="index < order.dining_tables.length - 1">, </span></span> )
                </template>
            </h1>
        </div>
        <table class="w-full my-1.5">
            <tbody>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">
                        {{ $t('label.order_type') }} : {{ enums.orderTypeEnumArray[order.order_type] }}
                    </td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.order_number') }} #{{ order.order_serial_no }}</td>
                </tr>
                <tr>
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.date') }}: {{ order.order_date }}, {{ order.order_time }}</td>
                </tr>
                <tr v-if="order.order_user?.name">
                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('label.cashier') }}: {{ order.order_user.name }}</td>
                </tr>
            </tbody>
        </table>
        <table class="w-full">
            <thead>
                <tr>
                    <th scope="col" class="py-1 font-normal text-xs capitalize flex items-center justify-between text-heading">
                        <span>{{ $t('label.item_description') }}</span>
                    </th>
                    <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading w-8">
                        {{ $t('label.qty') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td class="text-left font-normal align-top py-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-normal capitalize">
                                {{ item.item_name }}
                            </h4>
                        </div>
                        <p v-if="Object.keys(item.item_variations).length !== 0" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                            <span v-for="(variation, index) in item.item_variations" :key="index">
                                {{ variation.variation_name }}: {{ variation.name }}
                                <span v-if="index + 1 < Object.keys(item.item_variations).length">, </span>
                            </span>
                        </p>
                        <p v-if="item.item_extras.length > 0" class="text-xl leading-5 font-normal text-heading max-w-[200px]">
                            {{ $t('label.extras') }}:
                            <span v-for="(extra, index) in item.item_extras" :key="index">
                                {{ extra.name }}
                                <span v-if="index + 1 < item.item_extras.length">, </span>
                            </span>
                        </p>
                        <p v-if="item.instruction" class="text-xl leading-5 font-normal text-heading max-w-[200px]">
                            {{ $t('label.instruction') }}:
                            {{ item.instruction }}
                        </p>
                    </td>
                    <td class="text-left font-normal align-top py-1">
                        <p class="text-xl leading-5 text-heading">
                            {{ item.quantity }}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import orderTypeEnum from '../../../../enums/modules/orderTypeEnum';

export default {
    name: "KitchenComponent",
    props: {
        order: {
            type: Object,
            required: true,
        },
        printer: {
            type: Object,
            required: true,
        },
        items: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
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
};
</script>
