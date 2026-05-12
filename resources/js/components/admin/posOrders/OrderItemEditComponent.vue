<template>
    <div :id="modalId" class="modal">
        <div class="modal-dialog max-w-[500px] rounded-none" :id="modelId + '-edit'" :dir="direction">
            <div class="modal-header hidden-print">
                <h5 class="modal-title">{{ $t('button.edit') }}</h5>
            </div>
            <div class="modal-body">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="py-1 font-normal text-xs capitalize text-left text-heading">{{ $t('label.item_description') }}</th>
                            <th class="py-1 font-normal text-xs capitalize text-center text-heading">{{ $t('label.qty') }}</th>
                            <!-- <th class="py-1 font-normal text-xs capitalize text-right text-heading">{{ $t('label.price') }}</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left font-normal align-top py-1">
                                <h4 class="text-sm font-normal capitalize">{{ orderItem.item_name }}</h4>
                            </td>
                            <td class="text-center font-normal align-top py-1">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="decreaseQty()" class="px-2 py-1 bg-gray-200 rounded" :disabled="newQuantity <= 1">-</button>
                                    <span class="text-xs">{{ newQuantity }}</span>
                                    <button @click="increaseQty()" class="px-2 py-1 bg-gray-200 rounded">+</button>
                                </div>
                            </td>
                            <!-- <td class="text-right font-normal align-top py-1">
                                <p class="text-xs leading-5 text-heading">{{ item.total_without_tax_currency_price }}</p>
                            </td> -->
                        </tr>
                    </tbody>
                </table>

                <div class="flex items-center gap-2 mt-8">
                    <button type="button" @click="reset" class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                        <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                        <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                    </button>

                    <button @click="confirmEdit" type="button"
                    class="rounded text-base py-2 px-3 font-medium w-full text-white bg-primary">{{
                        $t("button.confirm") }}</button>
                </div>
                
            </div>
        </div>
    </div>
</template>

<script>
import print from 'vue3-print-nb';
import appService from '../../../services/appService';
import displayModeEnum from '../../../enums/modules/displayModeEnum'; 
import statusEnum from '../../../enums/modules/statusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';

export default {
    name: 'OrderItemEditComponent',
    props: {
        orderItem: {
            type: Object,
            required: true,
        },
        modalId: {
            type: String,
            default: 'orderItemEditModal',
        },
    },
    data() {
        return {
            statusEnum,
            paymentStatusEnum,
            enums: {
                orderTypeEnum,
            },
            modelId: this.$props.modalId || 'orderItemEditModal',
            newQuantity: Number(this.orderItem.quantity) || 0,
            originalQuantity: Number(this.orderItem.quantity) || 0,
        };
    },
    watch: {
        orderItem: {
            handler(newVal) {
                this.newQuantity = Number(newVal.quantity) || 0;
                this.originalQuantity = Number(newVal.quantity) || 0;
            },
            deep: true,
            immediate: true,
        },
    },
    computed: {
        direction() {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    
    methods: {
        reset() {
            this.$emit('close');
            appService.modalHide('#' + this.modelId);
        },
        increaseQty() {
            this.newQuantity++;
            // this.$emit('update-qty', { item, quantity: item.quantity });
            
            // console.log("Order Item: ", this.orderItem);
            // console.log('Updated quantity:', this.newQuantity);
        },
        decreaseQty() {
            if (this.newQuantity > 1) {
                this.newQuantity--;
                // this.$emit('update-qty', { item: this.orderItem, quantity: this.newQuantity });
            }
        },
        confirmEdit() {
            // Emit the updated quantity to the parent component
            // this.$emit('updatedQty', { orderItemId: this.orderItem.id, quantity: this.newQuantity });
            this.$props.orderItem.quantity = this.newQuantity;

            const itemTaxAmount = this.$props.orderItem.item.tax_amount || 0;
            this.$props.orderItem.tax_amount = itemTaxAmount * this.newQuantity;

            //TODO:
            this.$props.orderItem.price = this.$props.orderItem.item.price;
            // this.$props.orderItem.price = (
            //     (this.orderItem.convert_price + this.orderItem.item_variation_total + this.orderItem.item_extra_total) * this.newQuantity
            // );

            this.$props.orderItem.total_price = (
                this.$props.orderItem.price * this.newQuantity
            );

            // console.log("orderitem: ", this.$props.orderItem);

            // console.log('Emitting updatedQty with:', { newQty: this.$props.orderItem.quantity, oldQuantity: this.originalQuantity });

            this.$emit('updatedQty', { item: this.$props.orderItem, oldQuantity: this.originalQuantity });
            appService.modalHide('#' + this.modelId);
        },
    },
    directives: {
        print,
    },
};
</script>

<style scoped>
/* Keep your styles as needed */
</style>
