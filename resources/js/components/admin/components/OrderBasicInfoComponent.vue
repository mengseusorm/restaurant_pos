<template>
    <div v-if="order">
        <!-- Title and Status Section -->
        <div v-if="!isEditing" class="flex flex-wrap items-start gap-y-2 gap-x-6 mb-3 border-b pb-3">
            <p class="text-md font-medium">
                {{ $t('label.order_id') }}:
                <span class="text-md"> #{{ order.order_serial_no }} </span>
            </p>
            <div class="flex items-center gap-2">
                <!-- <span :class="'text-xs capitalize px-2 rounded-3xl ' + orderStatusClass(order.status)">
                    {{ enums.orderStatusEnumArray[order.status] }}
                </span> -->
                <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-orange-500 bg-orange-100'">
                    {{ order.status ? (order.status['name_' + language_code] || order.status?.name) : 'N/A' }}
                </span>
                <button v-if="order.payment_status == enums.paymentStatusEnum.UNPAID && showTopEditButton"
                    @click="startEdit">
                    <i class="lab lab-edit-line"></i>
                </button>
            </div>
        </div> 
        <!-- Edit Mode Title -->
        <div v-if="isEditing" class="mb-3">
            <h3 class="db-card-title">{{ $t('label.edit_order_information') }}</h3>
        </div>

        <!-- Order Type Selector in Edit Mode -->
        <div v-if="isEditing">
            <OrderTypeSelectorComponent v-model="editForm" />
        </div>

        <!-- Order Information List -->
        <ul v-if="!isEditing" class="flex flex-col gap-2">
            <li class="flex items-start gap-2" v-if="order.waiting_number">
                <span class="w-32 text-sm">{{ $t('label.waiting_number') }}:</span>
                <span class="text-sm text-heading flex-1">#{{ order.waiting_number }}</span>
            </li>
             <li class="flex items-start gap-2" v-if="order.invoice_number">
                <span class="w-32 text-sm">{{ $t('label.invoice_number') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order.invoice_number }}</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.date') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order.order_datetime }}</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.payment_status') }}:</span>
                <span :class="'text-xs capitalize h-5 leading-5 px-2 rounded-3xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                    {{ enums.paymentStatusEnumArray[order.payment_status] }}
                </span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.source') }}:</span>
                <span class="text-sm text-heading flex-1">{{ enums.sourceEnumArray[order.source] }}</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.order_type') }}:</span>
                <span class="text-sm text-heading flex-1">
                    {{ enums.orderTypeEnumArray[order.order_type] }}
                </span>
            </li>


            <!-- Dining Tables - View Mode -->
            <li v-if="order.dining_tables && order.dining_tables.length" class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.table') }}:</span>
                <span class="text-sm text-heading flex-1">
                    <span class="ms-2 capitalize leading-5 px-3 rounded-xl bg-green-700 text-white"
                        v-for="item in order.dining_tables" :key="item.id">{{ item.name }}</span>
                </span>
            </li>

            <li class="flex items-start gap-2" v-if="order.table_name">
                <span class="w-32 text-sm">{{ $t('label.table_name') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order.table_name }}</span>
            </li>
            <li class="flex items-start gap-2" v-if="order.token">
                <span class="w-32 text-sm">{{ $t('label.token_no') }}:</span>
                <span class="text-sm text-heading flex-1">#{{ order.token }}</span>
            </li>

            <li class="flex items-start gap-2" v-if="order.payment_status == enums.paymentStatusEnum.PAID">
                <span class="w-32 text-sm">{{ $t('label.payment_type') }}:</span>
                <span class="text-sm text-heading flex-1">
                    {{ order.payment_method_name ? order.payment_method_name : 'Unpaid' }}
                    <span
                        :class="'ms-2 capitalize leading-5 px-3 rounded-xl text-[#FB4E4E] bg-[#FFDADA]' + statusClass(order.payment_status)">
                        {{ enums.paymentStatusEnumArray[order.payment_status] }}
                    </span>
                </span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.number_of_people') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order?.number_of_people }}</span>
            </li>

            <!-- Check In/Out Time for Dining Table Orders -->
            <li v-if="order.order_type == orderTypeEnums.dineIn && order.check_in_time" class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.check_in_time') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order.check_in_time }}</span>
            </li>
            <li v-if="order.order_type == orderTypeEnums.dineIn && order.checkout" class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.check_out_time') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order.checkout }}</span>
            </li>

            <!-- Order Note - View Mode -->
            <li class="flex items-start gap-2">
                <span class="w-32 text-sm">{{ $t('label.order_note') }}:</span>
                <span class="text-sm text-heading flex-1">{{ order?.order_note }}</span>
            </li>
        </ul>

        <!-- Dining Tables - Edit Mode -->
        <div v-if="isEditing && editForm.order_type == orderTypeEnums.dineIn" class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('label.select_table') }}:</label>
            <TableSelectionComponent v-model="editForm.order_dinings" :order-type="editForm.order_type"
                :dine-in-type="orderTypeEnums.dineIn" :show-select-table-list="statusEnum.ACTIVE"
                :active-status="statusEnum.ACTIVE" />
        </div>

        <!-- Order Note - Edit Mode -->
        <div v-if="isEditing" class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('label.order_note') }}:</label>
            <input v-model="editForm.order_note" type="text" :placeholder="$t('label.order_note')"
                class="w-full h-10 px-3 text-sm rounded-lg border border-[#D9DBE9] text-heading" />
        </div>

        <!-- Edit Button (View Mode) -->
        <!-- <div v-if="order.payment_status == enums.paymentStatusEnum.UNPAID && showBottomEditButton && !isEditing"
            class="flex items-center justify-end gap-2 mt-3">
            <button @click="startEdit" type="button"
                class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg">
                <i class="lab lab-edit-line"></i>
                <span class="capitalize text-sm font-bold">{{ $t('button.edit') }}</span>
            </button>
        </div> -->

        <!-- Save/Cancel Buttons (Edit Mode) -->
        <div v-if="isEditing" class="flex flex-wrap justify-end gap-3 mt-5">
            <button @click="saveEdit" type="button" :disabled="loading"
                class="flex w-36 items-center justify-center gap-3 rounded-lg text-base py-2 px-3 font-medium text-white bg-[#1AB759] disabled:opacity-50 disabled:cursor-not-allowed">
                <i v-if="!loading" class="lab lab-save lab-font-size-12 text-white"></i>
                <i v-else class="lab lab-line-spinner lab-font-size-12 text-white animate-spin"></i>
                <span>{{ loading ? $t('label.saving') : $t('button.save') }}</span>
            </button>

            <button @click="cancelEdit" type="button" :disabled="loading"
                class="flex w-36 items-center justify-center gap-3 rounded-lg text-base py-2 px-3 font-medium text-white bg-[#FB4E4E] disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="lab lab-close lab-font-size-12 text-white"></i>
                <span>{{ $t('button.cancel') }}</span>
            </button>
        </div>
    </div>
</template>

<script>

import appService from '../../../services/appService';
import alertService from '../../../services/alertService';
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import posPaymentMethodEnum from '../../../enums/modules/posPaymentMethodEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import statusEnum from '../../../enums/modules/statusEnum';
import sourceEnum from '../../../enums/modules/sourceEnum';
import discountTypeEnum from '../../../enums/modules/discountTypeEnum';
import TableSelectionComponent from './TableSelectionComponent.vue';
import OrderTypeSelectorComponent from './OrderTypeSelectorComponent.vue';

export default {
    name: 'OrderBasicInfoComponent',
    components: {
        TableSelectionComponent,
        OrderTypeSelectorComponent
    },
    props: {
        order: { type: Object, required: true },
        showTopEditButton: { type: Boolean, default: true },
        showBottomEditButton: { type: Boolean, default: true }
    },
    emits: ['saveEdit', 'editClick', 'updated', 'error'],
    data() {
        return {
            isEditing: false,
            loading: false,
            editForm: {
                order_type: null,
                order_note: '',
                order_dinings: [],
                token: null,
                number_of_people: null
            },
            statusEnum: statusEnum,
            enums: {
                orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
                posPaymentMethodEnum: posPaymentMethodEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t('label.delivery'),
                    [orderTypeEnum.TAKEAWAY]: this.$t('label.takeaway'),
                    [orderTypeEnum.DINING_TABLE]: this.$t('label.dining_table'),
                    [orderTypeEnum.TOKEN]: this.$t('label.token'),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t('label.online_order'),
                    [orderTypeEnum.POS]: this.$t('label.pos'),
                },
                orderStatusEnumArray: {
                    [orderStatusEnum.PENDING]: this.$t('label.pending'),
                    [orderStatusEnum.ACCEPT]: this.$t('label.accept'),
                    [orderStatusEnum.PROCESSING]: this.$t('label.processing'),
                    [orderStatusEnum.OUT_FOR_DELIVERY]: this.$t('label.out_for_delivery'),
                    [orderStatusEnum.DELIVERED]: this.$t('label.delivered'),
                    [orderStatusEnum.CANCELED]: this.$t('label.canceled'),
                    [orderStatusEnum.REJECTED]: this.$t('label.rejected'),
                    [orderStatusEnum.RETURNED]: this.$t('label.returned'),
                    [orderStatusEnum.PENDING_PAYMENT]: this.$t('label.pending_payment'),
                },
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t('label.paid'),
                    [paymentStatusEnum.UNPAID]: this.$t('label.unpaid'),
                },
                paymentStatusObject: [
                    {
                        name: this.$t('label.paid'),
                        value: paymentStatusEnum.PAID,
                    },
                    {
                        name: this.$t('label.unpaid'),
                        value: paymentStatusEnum.UNPAID,
                    },
                ],
                orderStatusObject: [
                    {
                        name: this.$t('label.accept'),
                        value: orderStatusEnum.ACCEPT,
                    },
                    {
                        name: this.$t('label.processing'),
                        value: orderStatusEnum.PROCESSING,
                    },
                    {
                        name: this.$t('label.delivered'),
                        value: orderStatusEnum.DELIVERED,
                    },
                ],
                posPaymentMethodEnumArray: {
                    [posPaymentMethodEnum.CASH]: this.$t('label.cash'),
                    [posPaymentMethodEnum.CARD]: this.$t('label.card'),
                    [posPaymentMethodEnum.ABA]: this.$t('label.aba'),
                    [posPaymentMethodEnum.ACLEDA]: this.$t('label.acleda'),
                    [posPaymentMethodEnum.HUIONE]: this.$t('label.huione'),
                },
                sourceEnumArray: {
                    [sourceEnum.WEB]: this.$t('label.web'),
                    [sourceEnum.APP]: this.$t('label.app'),
                    [sourceEnum.POS]: this.$t('label.pos'),
                    [sourceEnum.TABLE]: this.$t('label.table'),
                    [sourceEnum.ONLINE_ORDER]: this.$t('label.online_order'),
                },
            },
            orderTypeEnums: {
                dineIn: orderTypeEnum.DINING_TABLE,
                takeAway: orderTypeEnum.TAKEAWAY,
                pos: orderTypeEnum.POS,
                token: orderTypeEnum.TOKEN,
            },
            discountTypeEnum: discountTypeEnum,
        };
    },
    mounted() {
        // Ensure dining tables are loaded
        this.$store.dispatch('diningTable/lists', {
            order_column: 'id',
            order_type: 'asc',
        });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        editClick() {
            this.$emit('editClick');
        },
        startEdit() {
            this.isEditing = true;

            // Copy all order fields to editForm
            this.editForm.order_type = this.order.order_type;
            this.editForm.order_note = this.order.order_note || '';
            this.editForm.token = this.order.token || null;
            this.editForm.number_of_people = this.order.number_of_people || null;

            // Map dining_tables to order_dinings format
            if (this.order.dining_tables && this.order.dining_tables.length > 0) {
                this.editForm.order_dinings = this.order.dining_tables.map(table => ({
                    id: table.id
                }));
            } else {
                this.editForm.order_dinings = [];
            }
        },
        cancelEdit() {
            this.isEditing = false;
            this.editForm.order_type = null;
            this.editForm.order_note = '';
            this.editForm.order_dinings = [];
            this.editForm.token = null;
            this.editForm.number_of_people = null;
        },
        saveEdit() {
            // Prepare update payload
            const updateData = {
                order_type: this.editForm.order_type,
                order_note: this.editForm.order_note,
                order_dinings: this.editForm.order_dinings,
                token: this.editForm.token,
                number_of_people: this.editForm.number_of_people
            };

            // Call confirmation dialog first
            appService
                .updateOrderInfo()
                .then((res) => {
                    this.loading = true;

                    // Prepare payload in the format expected by the Vuex store
                    const payload = {
                        id: this.order.id,
                        order: updateData
                    };

                    this.$store
                        .dispatch('posOrder/updateOrderInfo', payload)
                        .then((res) => {
                            this.loading = false;
                            this.isEditing = false;
                            alertService.successFlip(1, this.$t('message.update_success'));

                            // Emit updated event so parent can reload data
                            this.$emit('updated', res.data);
                        })
                        .catch((err) => {
                            this.loading = false;
                            const errorMessage = err.response?.data?.message || this.$t('message.update_failed');
                            alertService.error(errorMessage);

                            // Emit error event
                            this.$emit('error', err);
                        });
                })
                .catch((err) => {
                    this.loading = false;
                });
        }
    },
};
</script>
