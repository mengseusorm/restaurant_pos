<template>
    <div>
        <div v-for="(labelSet, setIndex) in labelsToPrint" :key="'labelset-' + setIndex" class="label-set mb-4">
            <div v-for="(label, labelIndex) in labelSet" :key="'label-' + labelIndex"
                :style="{ width: printLabelSetting.label_width + 'mm', height: printLabelSetting.label_height + 'mm' }"
                class="border border-gray-300 p-1 mb-2 text-xs overflow-hidden bg-white">

                <!-- Company Name -->
                <div v-if="printLabelSetting.show_company_name == statusEnum.ACTIVE" class="text-center text-xs mb-1 label_company_name">
                    {{ company.company_name }}
                </div>

                <!-- Branch Name -->
                <div v-if="printLabelSetting.show_branch_name == statusEnum.ACTIVE" class="text-center text-xs mb-1 label_branch_name">
                    {{ branch.name }}
                </div>

                <!-- Phone Number -->
                <div v-if="printLabelSetting.show_phone_number == statusEnum.ACTIVE" class="text-center text-xs mb-1 label_phone_number">
                    {{ branch.phone }}
                </div>

                <!-- Order Number -->
                <div v-if="printLabelSetting.show_order_number == statusEnum.ACTIVE" class="text-xs mb-1 label_order_number">
                    {{ $t('label.order_number') }}: #{{ order.order_serial_no }}
                </div>

                <!-- Order QR Code -->
                <div v-if="printLabelSetting.show_order_qr_code == statusEnum.ACTIVE" class="text-center mb-1">
                    <div class="w-8 h-8 border border-gray-400 mx-auto flex items-center justify-center text-2xs">
                        QR
                    </div>
                </div>

                <!-- Order Barcode -->
                <div v-if="printLabelSetting.show_order_number_barcode == statusEnum.ACTIVE" class="text-center mb-1">
                    <div class="w-16 h-4 border border-gray-400 mx-auto flex items-center justify-center text-2xs">
                        ||||||
                    </div>
                </div>

                <!-- Customer Name -->
                <div v-if="printLabelSetting.show_customer_name == statusEnum.ACTIVE && order.customer_name" class="text-xs mb-1 label_customer_name">
                    {{ $t('label.customer') }}: {{ order.customer_name }}
                </div>

                <!-- Customer Phone -->
                <div v-if="printLabelSetting.show_customer_phone_number == statusEnum.ACTIVE && order.customer_phone_number" class="text-xs mb-1 label_customer_phone">
                    {{ $t('label.phone') }}: {{ order.customer_phone_number }}
                </div>

                <!-- Delivery Address -->
                <div v-if="printLabelSetting.show_delivery_address == statusEnum.ACTIVE && order.customer_address" class="text-xs mb-1 label_customer_address">
                    {{ $t('label.address') }}: {{ order.customer_address }}
                </div>

                <!-- Payment Status -->
                <div v-if="printLabelSetting.show_payment_status == statusEnum.ACTIVE" class="text-xs mb-1">
                    {{ $t('label.payment_status') }}: {{ order.payment_status == paymentStatusEnum.PAID ? $t('label.paid') : $t('label.unpaid') }}
                </div>

                <!-- Payment Method -->
                <div v-if="printLabelSetting.show_payment_method == statusEnum.ACTIVE && order.payment_method_name" class="text-xs mb-1">
                    {{ $t('label.payment_method') }}: {{ order.payment_method_name }}
                </div>

                <!-- Item Details -->
                <div v-if="printLabelSetting.show_item == statusEnum.ACTIVE" class="mb-1">
                    <!-- Item Name with dash line -->
                    <div class="flex items-center mb-1">
                        <div class="border-b border-dashed border-gray-400 flex-1 mx-2"></div>
                    </div>
                    <!-- For combined labels -->
                    <div v-if="label.items" class="text-xs">
                        <div class="font-semibold mb-1 label_items">{{ $t('label.items') }}:</div>
                        <div v-for="(item, itemIndex) in label.items" :key="itemIndex" class="mb-1">
                            <div class="font-medium label_item_name">{{ $t('label.item') }}: {{ item.item_name }}</div>
                            <div v-if="printLabelSetting.show_item_qty == statusEnum.ACTIVE" class="font-medium label_item_qty">
                                {{ $t('label.qty') }}: {{ item.quantity }}
                            </div>
                        </div>
                        <div v-if="printLabelSetting.show_item_qty == statusEnum.ACTIVE" class="font-semibold">
                            {{ $t('label.total_qty') }}: {{ label.quantity }}
                        </div>
                    </div>

                    <!-- For individual labels -->
                    <div v-else>
                        <div class="font-semibold text-xs label_item_name">{{ $t('label.item') }}: {{ label.item_name }}</div>
                        <!-- Item Quantity -->
                        <div v-if="printLabelSetting.show_item_qty == statusEnum.ACTIVE" class="text-xs label_item_qty">
                            {{ $t('label.qty') }}: {{ label.quantity }}
                        </div>

                        <!-- Item Price -->
                        <div v-if="printLabelSetting.show_item_price == statusEnum.ACTIVE" class="text-xs label_item_price">
                            {{ $t('label.price') }}: {{ label.price_currency || label.price }}
                        </div>

                        <!-- Item Variations -->
                        <div v-if="label.item_variations && Object.keys(label.item_variations).length > 0" class="text-xs">
                            <span v-for="(variation, varIndex) in label.item_variations" :key="varIndex">
                                {{ variation.variation_name }}: {{ variation.name }}
                                <span v-if="varIndex + 1 < Object.keys(label.item_variations).length">, </span>
                            </span>
                        </div>

                        <!-- Item Extras -->
                        <div v-if="label.item_extras && label.item_extras.length > 0" class="text-xs">
                            {{ $t('label.extras') }}:
                            <span v-for="(extra, extraIndex) in label.item_extras" :key="extraIndex">
                                {{ extra.name }}
                                <span v-if="extraIndex + 1 < label.item_extras.length">, </span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center mb-1">
                        <div class="border-b border-dashed border-gray-400 flex-1 mx-2"></div>
                    </div>
                </div>

                <!-- Payment QR Code -->
                <div v-if="printLabelSetting.show_payment_qr_code == statusEnum.ACTIVE" class="text-center mb-1">
                    <div class="w-8 h-8 border border-gray-400 mx-auto flex items-center justify-center text-2xs">
                        PAY
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import statusEnum from '../../../../enums/modules/statusEnum';
import paymentStatusEnum from '../../../../enums/modules/paymentStatusEnum';

export default {
    name: "LabelComponent",
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
        printer: {
            type: Object,
            required: true,
        },
        labelsToPrint: {
            type: Array,
            required: true,
        },
        printLabelSetting: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            statusEnum: statusEnum,
            paymentStatusEnum: paymentStatusEnum,
        };
    },
};
</script>
