<template>
    <div>
        <div v-if="branch.show_select_table == statusEnum.ACTIVE || branch.show_token == statusEnum.ACTIVE || branch.show_delivery == statusEnum.ACTIVE" class="p-3 mb-2 rounded-lg border border-[#D9DBE9]">
            <h4 class="text-sm font-medium mb-2">{{ $t('label.select_order_type') }}</h4>
            <div class="db-field-radio-group gap-1 active-group">
                <label v-if="branch.show_select_table == statusEnum.ACTIVE" ref="dineIn" @click="dineInOrder" for="dinein" class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC] active">
                    <div class="custom-radio sm">
                        <input ref="dineInInput" type="radio" id="dinein" name="orderType" :value="orderTypeEnums.dineIn" v-model="modelValue.order_type" class="custom-radio-field" />
                        <span class="custom-radio-span"></span>
                    </div>
                    <h3 class="db-field-label text-sm text-heading">{{ $t('label.dine_in') }}</h3>
                </label>
                <label v-if="branch.show_token == statusEnum.ACTIVE" ref="token" @click="tokenPos" for="token" class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]">
                    <div class="custom-radio sm">
                        <input ref="tokenInput" type="radio" id="token" name="orderType" :value="orderTypeEnums.token" v-model="modelValue.order_type" class="custom-radio-field" />
                        <span class="custom-radio-span"></span>
                    </div>
                    <h3 class="db-field-label text-sm text-heading">{{ $t('label.token') }}</h3>
                </label>
                <label v-if="branch.show_delivery == statusEnum.ACTIVE" ref="takeAway" @click="takeAwayOrder" for="takeway" class="!w-fit db-field-radio px-2.5 py-2 rounded-lg border border-[#F7F7FC] bg-[#F7F7FC]">
                    <div class="custom-radio sm">
                        <input ref="takeAwayInput" type="radio" id="takeway" name="orderType" :value="orderTypeEnums.takeAway" v-model="modelValue.order_type" class="custom-radio-field" />
                        <span class="custom-radio-span"></span>
                    </div>
                    <h3 class="db-field-label text-sm text-heading">{{ $t('label.takeaway') }}</h3>
            </label>
            </div>
        </div>
        <div v-if="modelValue.order_type == orderTypeEnums.token" class="mt-2 mb-2">
            <input class="db-field-control text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]" id="token" v-model="modelValue.token" :placeholder="$t('label.token_no')" />
        </div>

        <div v-if="branch.show_input_number_of_people == statusEnum.ACTIVE && modelValue.order_type == orderTypeEnums.dineIn" class="flex gap-3 mb-2 h-[38px]">
            <div class="db-field-down-arrow">
                <select v-model="modelValue.number_of_people" class="w-[120px] h-full text-sm font-rubik appearance-none rounded-lg border border-[#D9DBE9] pl-3 text-heading rtl:pr-2">
                    <option v-for="n in 12" :key="n" :value="n" :selected="n === modelValue.number_of_people">{{ n }} {{ $t('label.people') }}</option>
                    <option :value="customPeople" v-if="customPeople && customPeople > 12" :selected="customPeople === modelValue.number_of_people">{{ customPeople }} {{ $t('label.people') }}</option>
                    <option :value="null" disabled>---</option>
                    <!-- <option :value="'custom'">{{ $t('label.custom') }}</option> -->
                </select>
            </div>
            <input v-model.number="customPeople" type="number" min="1" :placeholder="$t('label.enter_people_number')" class="w-full h-full rounded-lg border border-[#D9DBE9] text-center" @change="onCustomPeopleChange" />
        </div>

        <!-- <div class="flex gap-2 h-[38px]">
            <input v-model.number="modelValue.order_note" type="text" :placeholder="$t('label.order_note')" class="w-full h-full px-3 rounded-lg border border-[#D9DBE9]" />
        </div> -->
    </div>
</template>

<script>
import statusEnum from '../../../enums/modules/statusEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';

export default {
    name: 'OrderTypeSelectorComponent',
    props: {
        modelValue: {
            type: Object,
            required: true,
        },
    },
    emits: ['update:modelValue', 'dine-in-selected', 'token-selected', 'takeaway-selected'],
    data() {
        return {
            statusEnum: statusEnum,
            orderTypeEnums: {
                dineIn: orderTypeEnum.DINING_TABLE,
                takeAway: orderTypeEnum.TAKEAWAY,
                pos: orderTypeEnum.POS,
                token: orderTypeEnum.TOKEN,
            },
            customPeople: null, // For custom number of people input
        };
    },
    computed: {
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
    },
    methods: {
        dineInOrder() {
            this.updateOrderType(this.orderTypeEnums.dineIn);
            this.$emit('dine-in-selected');
        },
        tokenPos() {
            this.updateOrderType(this.orderTypeEnums.token);
            this.$emit('token-selected');
        },
        takeAwayOrder() {
            this.updateOrderType(this.orderTypeEnums.takeAway);
            this.$emit('takeaway-selected');
        },
        updateOrderType(orderType) {
            const updatedModel = { ...this.modelValue, order_type: orderType };
            this.$emit('update:modelValue', updatedModel);
        },

        onCustomPeopleChange: function (value) {
            this.modelValue.number_of_people = this.customPeople;
        },
    },
};
</script>
