<template>
    <LoadingComponent :props="loading"/>
    <section class="pt-4 pb-24 px-4 min-h-screen bg-gray-50">
        <div class="max-w-lg mx-auto">

            <div class="space-y-4">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <!-- Header -->
                        <div class="p-5 border-b border-gray-100">
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <i class="lab lab-edit text-primary"></i>
                                {{ $t('label.payment') || 'Payment Method' }}
                            </h3>
                        </div>

                        <div class="p-5">
                            <label class="block text-base font-semibold mb-3 text-gray-900">
                                {{ $t('label.payment') || 'Payment Method' }}
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    type="button"
                                    :class="['w-full p-4 rounded-xl border-2 font-semibold transition-all text-left', paymentMethod === method.name ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-primary hover:bg-primary/5']"
                                    @click="
                                        paymentMethod = method.name;
                                        checkoutProps.form.payment_method = method.id;
                                        checkoutProps.form.payment_method_name = method.name;
                                        checkoutProps.form.payment_method_id = method.id;
                                    "
                                >
                                    <div class="flex items-center justify-between">
                                        <span>{{ method.name }}</span>
                                        <i v-if="paymentMethod === method.name" class="lab lab-check text-lg"></i>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Form Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-lg font-semibold mb-6 flex items-center gap-2">
                            <i class="lab lab-edit text-primary"></i>
                            {{ $t('label.customer_info') || 'Customer Information' }}
                        </h2>

                        <!-- Customer Information Section -->
                        <div class="space-y-4">
                            <!-- Customer Name Field -->
                            <div v-if="branch.show_customer_name == statusEnum.ACTIVE">
                                <label class="block text-sm font-semibold mb-2 text-gray-900">
                                    <i class="lab lab-user text-primary mr-2"></i>
                                    {{ $t('label.customer_name') || 'Customer Name' }}
                                </label>
                                <input type="text" class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-base" :placeholder="$t('label.customer_name') || 'Enter customer name'" v-model="checkoutProps.form.customer_name" />
                            </div>

                            <!-- Customer Phone Number Field -->
                            <div v-if="branch.show_customer_phone_number == statusEnum.ACTIVE">
                                <label class="block text-sm font-semibold mb-2 text-gray-900">
                                    <i class="lab lab-call text-primary mr-2"></i>
                                    {{ $t('label.customer_phone_number') || 'Customer Phone Number' }}
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input
                                    type="tel"
                                    class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-base"
                                    :placeholder="$t('label.customer_phone_number') || 'Enter customer phone number'"
                                    v-model="checkoutProps.form.customer_phone_number"
                                    pattern="[0-9]*"
                                    inputmode="numeric"
                                />
                            </div>

                            <!-- Customer Address Field -->
                            <div v-if="branch.show_customer_address == statusEnum.ACTIVE">
                                <label class="block text-sm font-semibold mb-2 text-gray-900">
                                    <i class="lab lab-location text-primary mr-2"></i>
                                    {{ $t('label.customer_address') || 'Customer Address' }}
                                </label>
                                <input type="text" class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-base" :placeholder="$t('label.customer_address') || 'Enter customer address'" v-model="checkoutProps.form.customer_address" />
                            </div>

                            <!-- Order Note Field -->
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-gray-900">
                                    <i class="lab lab-edit text-primary mr-2"></i>
                                    {{ $t('label.note') || 'Note' }}
                                </label>
                                <textarea class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-0 text-base resize-none" rows="4" :placeholder="$t('placeholder.note') || 'Leave your message here...'" v-model="checkoutProps.form.note"> </textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <!-- Header -->
                        <div class="p-5 border-b border-gray-100">
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                <i class="lab lab-shopping-cart text-primary"></i>
                                {{ $t('label.cart_summary') }}
                            </h3>
                        </div>

                        <!-- Cart Items -->
                        <div class="p-5">
                            <div class="space-y-4">
                                <div v-for="cart in carts" :key="cart.item_id" class="border border-gray-100 rounded-lg p-4 relative">
                                    <!-- Quantity Badge -->
                                    <div class="absolute -top-2 -left-2 w-7 h-7 bg-primary text-white text-sm font-bold rounded-full flex items-center justify-center shadow-md">
                                        {{ cart.quantity }}
                                    </div>

                                    <!-- Item Content -->
                                    <div class="flex gap-4">
                                        <img :src="cart.image" alt="thumbnail" class="w-20 h-20 rounded-lg flex-shrink-0 object-cover border border-gray-200" />
                                        <div class="flex-1 min-w-0">
                                            <!-- Item Name -->
                                            <h4 class="font-semibold text-base text-gray-900 mb-2 leading-tight">
                                                {{ cart.name }}
                                            </h4>

                                            <!-- Variations -->
                                            <div v-if="Object.keys(cart.item_variations.variations).length !== 0" class="mb-2">
                                                <div v-for="(variation, variationName) in cart.item_variations.names" :key="variationName" class="text-sm text-gray-600 mb-1">
                                                    <span class="font-medium">{{ variationName }}:</span>
                                                    <span class="ml-1">{{ variation }}</span>
                                                </div>
                                            </div>

                                            <!-- Extras -->
                                            <div v-if="cart.item_extras.extras.length > 0" class="mb-2">
                                                <span class="text-sm font-medium text-gray-600">{{ $t('label.extras') }}:</span>
                                                <span class="text-sm text-gray-700 ml-1">
                                                    <span v-for="(extra, index) in cart.item_extras.names" :key="index">
                                                        {{ extra }}<span v-if="index + 1 < cart.item_extras.names.length">, </span>
                                                    </span>
                                                </span>
                                            </div>

                                            <!-- Instructions -->
                                            <div v-if="cart.instruction !== ''" class="mb-3">
                                                <span class="text-sm font-medium text-gray-600">{{ $t('label.instruction') }}:</span>
                                                <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-2 rounded">{{ cart.instruction }}</p>
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right">
                                                <span class="text-lg font-bold text-primary">
                                                    {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
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
                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">{{ $t('label.subtotal') }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                    </span>
                                </div>

                                <!-- Total -->
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-gray-900">{{ $t('label.total') }}</span>
                                    <span class="text-xl font-bold text-primary">
                                        {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fixed bottom-4 left-4 right-4 z-50 flex justify-center">
                        <div class="flex items-center gap-3 bg-white/30 backdrop-blur-sm rounded-full shadow-lg border border-gray-200 px-4 py-2">
                            <!-- Home Button -->
                            <router-link :to="{ name: 'online.order.menu', params: { slug: this.$route.params.slug } }" class="flex items-center gap-2 px-4 py-3 rounded-full bg-gray-100 hover:bg-gray-200 transition-all duration-200 shadow-md">
                                <i class="fa-solid fa-home text-gray-700"></i>
                                <span class="text-sm font-semibold text-gray-700">{{ $t('label.home') || 'Home' }}</span>
                            </router-link>

                            <!-- Place Order Button -->
                            <button type="button" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-primary hover:bg-primary-dark transition-all duration-200 shadow-md" @click="orderSubmit">
                                <i class="fa-solid fa-bag-shopping text-sm text-white"></i>
                                <span class="text-sm font-semibold text-white">{{ $t('button.place_order') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</template>
<script>

import LoadingComponent from "../../table/components/LoadingComponent.vue";
import appService from "../../../services/appService";
import sourceEnum from "../../../enums/modules/sourceEnum";
import _ from "lodash";
import OrderTypeEnum from "../../../enums/modules/orderTypeEnum";
import IsAdvanceOrderEnum from "../../../enums/modules/isAdvanceOrderEnum";
import router from "../../../router";
import alertService from "../../../services/alertService";
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name: "OnlineOrderCheckoutComponent",
    components: {LoadingComponent},
    data() {
        return {
            loading: {
                isActive: false,
            },
            placeOrderShow: false,
            paymentMethod: null,
            statusEnum: statusEnum,
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
                    source: sourceEnum.ONLINE_ORDER,
                    address_id: null,
                    items: [],
                    order_note: "",
                    payment_method: null, // This is the payment method name that will be used to save in the order
                    payment_method_name: null, // This is the payment method name that will be used to display in the order
                    payment_method_id: null, // This is the payment method id that will be used to save in the order

                    phone_number: "",
                    address_or_location: "",
                    customer_name: "",
                    customer_phone_number: "",
                    customer_address: "",
                }
            },
        }
    },
    mounted() {
        if (this.$store.getters['onlineOrderCart/lists'].length === 0) {
            this.$router.push({name: 'online.order.menu', params: {slug: this.$route.params.slug}});
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        carts: function () {
            return this.$store.getters['onlineOrderCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['onlineOrderCart/subtotal'];
        },
        // table: function () {
        //     return this.$store.getters['onlineOrderCart/table'];
        // },
        branch: function () {
            return this.$store.getters['onlineOrderBranch/show'];
        },
        paymentMethods: function () {
            return this.$store.getters['paymentMethod/lists'];
        }
    },
    mounted() {
        this.$store.dispatch('paymentMethod/listOnlinePayment',{
            order_column: 'id',
            order_type: 'asc',
        }).then().catch()
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        orderSubmit: function () {


            if (this.paymentMethod === null) {
                alertService.error(this.$t('message.payment_method_required'));
                return;
            }

            if (this.branch.show_customer_phone_number == this.statusEnum.ACTIVE && (this.checkoutProps.form.customer_phone_number === '' || this.checkoutProps.form.customer_phone_number === null)) {
                alertService.error(this.$t('message.phone_number_required'));
                return;
            }

            // if (this.checkoutProps.form.address_or_location === '') {
            //     alertService.error(this.$t('message.address_or_location_required'));
            //     return;
            // }

            this.loading.isActive = true;
            // this.checkoutProps.form.dining_table_id = this.table.id;
            this.checkoutProps.form.branch_id = this.branch.branch_id;
            this.checkoutProps.form.subtotal  = this.subtotal;
            this.checkoutProps.form.total     = parseFloat(this.subtotal).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.items     = [];

            // console.log("Branch: ", this.branch);

            // Build order note with available information

            // let orderNoteInfo = [];
            // orderNoteInfo.push('Phone: ' + this.checkoutProps.form.phone_number);
            // orderNoteInfo.push('Address: ' + this.checkoutProps.form.address_or_location);

            // // Add customer fields to order note if they are enabled and have values
            // if (this.branch.show_customer_name == 1 && this.checkoutProps.form.customer_name) {
            //     orderNoteInfo.push('Customer Name: ' + this.checkoutProps.form.customer_name);
            // }
            // if (this.branch.show_customer_phone_number == 1 && this.checkoutProps.form.customer_phone_number) {
            //     orderNoteInfo.push('Customer Phone: ' + this.checkoutProps.form.customer_phone_number);
            // }
            // if (this.branch.show_customer_address == 1 && this.checkoutProps.form.customer_address) {
            //     orderNoteInfo.push('Customer Address: ' + this.checkoutProps.form.customer_address);
            // }

            // if (this.checkoutProps.form.note) {
            //     orderNoteInfo.push('Note: ' + this.checkoutProps.form.note);
            // }

            // this.checkoutProps.form.order_note = orderNoteInfo.join("\n");

            _.forEach(this.carts, (item, index) => {
                let item_variations = [];
                if (Object.keys(item.item_variations.variations).length > 0) {
                    _.forEach(item.item_variations.variations, (value, index) => {
                        item_variations.push({
                            "id": value,
                            "item_id": item.item_id,
                            "item_attribute_id": index,
                        });
                    });
                }

                if (Object.keys(item.item_variations.names).length > 0) {
                    let i = 0;
                    _.forEach(item.item_variations.names, (value, index) => {
                        item_variations[i].variation_name = index;
                        item_variations[i].name           = value;
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
                    item_price: item.convert_price,
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);


            this.$store.dispatch('onlineOrderOrder/save', this.checkoutProps.form).then(orderResponse => {
                this.checkoutProps.form.subtotal = 0;
                this.checkoutProps.form.discount = 0;
                this.checkoutProps.form.delivery_charge = 0;
                this.checkoutProps.form.delivery_time = null;
                this.checkoutProps.form.total = 0;
                this.checkoutProps.form.items = [];
                this.checkoutProps.form.order_note = "";
                this.checkoutProps.form.source = sourceEnum.ONLINE_ORDER; // Reset source to online order

                //TODO: Should send by payment method

                this.$store.dispatch('onlineOrderCart/resetCart').then(res => {
                    this.loading.isActive = false;
                    this.$store.dispatch('onlineOrderCart/paymentMethod', this.paymentMethod).then().catch();

                    if( this.paymentMethod === 'HUIONE') {
                        router.push({name: "online.order.make.payment", params: {slug : this.branch.online_order_slug, id: orderResponse.data.data.id}});
                    }else{
                       router.push({name: "online.order.menu", params: {slug : this.branch.online_order_slug}, query: {id: orderResponse.data.data.id}});
                    }
                    // router.push({name: "table.menu.table", params: {slug : this.table.slug}, query: {id: orderResponse.data.data.id}});
                    // router.push({name: "table.make.payment", params: {slug : this.table.slug, id: orderResponse.data.data.id}});
                }).catch();
            }).catch((err) => {
                this.loading.isActive = false;
                if (typeof err.response.data.errors === 'object') {
                    _.forEach(err.response.data.errors, (error) => {
                        alertService.error(error[0]);
                    });
                }
            })
        }
    }

}
</script>
