<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16 min-h-[800px]">
        <div class="container max-w-[965px]">
            <router-link :to="{ name: 'table.menu.table', params: { slug: this.$route.params.slug } }" class="text-xs font-medium inline-flex mb-3 items-center gap-2 text-primary">
                <i class="lab lab-undo lab-font-size-16"></i>
                <span>{{ $t('label.back_to_home') }}</span>
            </router-link>

            <div class="row">
                <div class="col-12 md:col-7">
                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.table') }}</h3>
                        <p class="capitalize p-4 text-heading">{{ $t('label.inside') }} - {{ table.name }}</p>
                    </div>

                    <!-- <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.payment') }}</h3>
                        <ul class="p-4 flex flex-col gap-5">
                            <li class="flex items-center gap-1.5">
                                <div class="custom-radio">
                                    <input type="radio" id="cash" v-model="paymentMethodType" value="cash"
                                           class="custom-radio-field">
                                    <span class="custom-radio-span border-gray-400"></span>
                                </div>
                                <label for="cash" class="db-field-label text-heading">{{ $t('label.cash') }}</label>
                            </li>
                            <li class="flex items-center gap-1.5">
                                <div class="custom-radio">
                                    <input type="radio" id="digital" v-model="paymentMethodType" value="digitalPayment"
                                           class="custom-radio-field">
                                    <span class="custom-radio-span border-gray-400"></span>
                                </div>
                                <label for="digital" class="db-field-label text-heading">{{ $t('label.digital_payment') }}</label>
                            </li>
                        </ul>
                    </div> -->

                    <div class="mb-6 rounded-2xl shadow-xs bg-white">
                        <!-- <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.payment') }}</h3>
                        <ul class="p-4 flex flex-col gap-5">
                            <li class="flex items-center gap-1.5" v-for="item in paymentMethods" :key="item.id"> 
                                <div class="custom-radio">
                                    <input type="radio" id="cash" v-model="paymentMethod" :value="item.id"
                                           class="custom-radio-field">
                                    <span class="custom-radio-span border-gray-400"></span>
                                </div>
                                <label for="cash" class="db-field-label text-heading">{{ item.name }}</label>
                            </li>    
                        </ul>  -->

                        <div class="p-4 flex flex-col gap-2">
                            <label class="capitalize font-medium">
                                {{ $t('label.payment') || 'Payment Method' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-3 mt-2">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    type="button"
                                    :class="['px-4 py-2 rounded-lg border font-medium transition', paymentMethod === method.name ? 'bg-primary text-white border-primary' : 'bg-white text-heading border-gray-300 hover:border-primary']"
                                    @click="
                                        paymentMethod = method.name;
                                        checkoutProps.form.payment_method = method.id;
                                        checkoutProps.form.payment_method_name = method.name;
                                        checkoutProps.form.payment_method_id = method.id;
                                    "
                                >
                                    {{ method.name }}
                                </button>
                            </div>

                            <!-- <label class="capitalize font-medium">
                                {{ $t('label.phone_number') || 'Phone Number' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="tel"
                                class="w-full p-3 border border-gray-300 rounded-xl"
                                :placeholder="$t('placeholder.phone_number') || 'Enter your phone number'"
                                v-model="checkoutProps.form.phone_number"
                                pattern="[0-9]*"
                                inputmode="numeric"
                                maxlength="11"
                                @input="checkoutProps.form.phone_number = $event.target.value.replace(/\D/g, '').slice(0, 11)"
                            />

                            <label class="capitalize font-medium mt-2">
                                {{ $t('label.address_or_location') || 'Address or Location' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" class="w-full p-3 border border-gray-300 rounded-xl" :placeholder="$t('placeholder.address_or_location') || 'Enter your address or location'" v-model="checkoutProps.form.address_or_location" /> -->

                            <!-- <h3 class="capitalize font-medium p-4 border-b border-gray-100">{{ $t('label.table_order_note') }}</h3> -->
                            <label class="capitalize font-medium mt-2">{{ $t('label.note') || 'Note' }}</label>
                            <textarea class="w-full p-3 border border-gray-300 rounded-xl" rows="5" :placeholder="$t('placeholder.note') || 'Leave your message here...'" v-model="checkoutProps.form.order_note"></textarea>
                        </div>
                    </div>

                    <button type="button" class="hidden md:block w-full rounded-lg capitalize font-medium leading-6 py-3 text-white bg-primary" @click="orderSubmit">
                        {{ $t('button.place_order') }}
                    </button>
                </div>

                <div class="col-12 md:col-5">
                    <div class="rounded-2xl shadow-xs bg-white">
                        <div class="p-4 border-b">
                            <h3 class="capitalize font-medium mb-3 text-center">
                                {{ $t('label.cart_summary') }}
                            </h3>
                            <div class="pl-3">
                                <div v-for="cart in carts" class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2">
                                    <div class="flex items-center gap-3 relative">
                                        <h3 class="absolute top-5 ltr:-left-3 rtl:-right-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                            {{ cart.quantity }}
                                        </h3>
                                        <img :src="cart.image" alt="thumbnail" class="w-16 h-16 rounded-lg flex-shrink-0" />
                                        <div class="w-full">
                                            <span class="text-sm font-medium capitalize transition text-heading">
                                                {{ cart.name }}
                                            </span>
                                            <p v-if="Object.keys(cart.item_variations.variations).length !== 0" class="capitalize text-xs mb-1.5">
                                                <span v-for="(variation, variationName) in cart.item_variations.names"> {{ variationName }}: {{ variation }}, &nbsp; </span>
                                            </p>
                                            <h4 class="text-xs font-semibold">
                                                {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                            </h4>
                                        </div>
                                    </div>
                                    <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''" class="flex flex-col gap-1.5 mt-2">
                                        <li v-if="cart.item_extras.extras.length > 0" class="flex gap-1">
                                            <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{ $t('label.extras') }}:</h3>
                                            <p class="text-xs">
                                                <span v-for="extra in cart.item_extras.names"> {{ extra }}, &nbsp; </span>
                                            </p>
                                        </li>
                                        <li v-if="cart.instruction !== ''" class="flex gap-1">
                                            <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{ $t('label.instruction') }}:</h3>
                                            <p class="text-xs">{{ cart.instruction }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="rounded-xl mb-6 border border-[#EFF0F6]">
                                <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                                    <li class="flex items-center justify-between text-heading">
                                        <span class="text-sm leading-6 capitalize">
                                            {{ $t('label.subtotal') }}
                                        </span>
                                        <span class="text-sm leading-6 capitalize">
                                            {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                        </span>
                                    </li>
                                </ul>
                                <div class="flex items-center justify-between p-3">
                                    <h4 class="text-sm leading-6 font-semibold capitalize">
                                        {{ $t('label.total') }}
                                    </h4>
                                    <h5 class="text-sm leading-6 font-semibold capitalize">
                                        {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                                    </h5>
                                </div>
                            </div>
                            <button type="button" class="block md:hidden w-full rounded-lg capitalize font-medium leading-6 py-3 text-white bg-primary" @click="orderSubmit">
                                {{ $t('button.place_order') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import LoadingComponent from '../../table/components/LoadingComponent.vue';
import appService from '../../../services/appService';
import sourceEnum from '../../../enums/modules/sourceEnum';
import _ from 'lodash';
import OrderTypeEnum from '../../../enums/modules/orderTypeEnum';
import IsAdvanceOrderEnum from '../../../enums/modules/isAdvanceOrderEnum';
import router from '../../../router';
import alertService from '../../../services/alertService';

export default {
    name: 'CheckoutComponent',
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            placeOrderShow: false,
            // paymentMethodType: 'cash',
            paymentMethod: null,
            checkoutProps: {
                form: {
                    dining_table_id: null,
                    customer_id: 2,
                    branch_id: null,
                    subtotal: 0,
                    discount: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: OrderTypeEnum.DINING_TABLE,
                    is_advance_order: IsAdvanceOrderEnum.NO,
                    source: sourceEnum.TABLE,
                    address_id: null,
                    items: [],
                    order_note: '',
                    // paymentMethod: null,
                    payment_method: null, // This is the payment method name that will be used to save in the order
                    payment_method_name: null, // This is the payment method name that will be used to display in the order
                    payment_method_id: null, // This is the payment method id that will be used to save in the order

                    phone_number: '',
                    address_or_location: '',
                },
            },
        };
    },
    mounted() {
        if (this.$store.getters['tableCart/lists'].length === 0) {
            this.$router.push({ name: 'table.menu.table', params: { slug: this.$route.params.slug } });
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        carts: function () {
            return this.$store.getters['tableCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['tableCart/subtotal'];
        },
        table: function () {
            return this.$store.getters['tableCart/table'];
        },
        paymentMethods: function () {
            return this.$store.getters['paymentMethod/lists'];
        },
    },
    mounted() {
        this.$store
            .dispatch('paymentMethod/listTablePayment', {
                order_column: 'id',
                order_type: 'asc',
            })
            .then()
            .catch();
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

            // if (this.checkoutProps.form.phone_number === '') {
            //     alertService.error(this.$t('message.phone_number_required'));
            //     return;
            // }

            // if (this.checkoutProps.form.address_or_location === '') {
            //     alertService.error(this.$t('message.address_or_location_required'));
            //     return;
            // }

            // console.table(this.table);

            // alert("dining_table_id: " + this.table.id);
            // alert("branch_id: " + this.table.branch_id);

            this.loading.isActive = true;
            this.checkoutProps.form.dining_table_id = this.table.id;
            this.checkoutProps.form.branch_id = this.table.branch_id;
            this.checkoutProps.form.subtotal = this.subtotal;
            this.checkoutProps.form.total = parseFloat(this.subtotal).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.items = [];

            // this.checkoutProps.form.order_note = 'Phone: ' + this.checkoutProps.form.phone_number + '\n' + 'Address: ' + this.checkoutProps.form.address_or_location + '\n' + 'Note: ' + this.checkoutProps.form.note;


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
                    item_price: item.convert_price,
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras,
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            this.$store
                .dispatch('tableDiningOrder/save', this.checkoutProps.form)
                .then((orderResponse) => {
                    this.checkoutProps.form.subtotal = 0;
                    this.checkoutProps.form.discount = 0;
                    this.checkoutProps.form.delivery_charge = 0;
                    this.checkoutProps.form.delivery_time = null;
                    this.checkoutProps.form.total = 0;
                    this.checkoutProps.form.items = [];
                    this.checkoutProps.form.order_note = '';
                    this.checkoutProps.form.source = sourceEnum.TABLE; // Reset source to table

                    //TODO: Should send by payment method

                    this.$store
                        .dispatch('tableCart/resetCart')
                        .then((res) => {
                            this.loading.isActive = false;
                            this.$store.dispatch('tableCart/paymentMethod', this.paymentMethod).then().catch();

                            if (this.paymentMethod === 'HUIONE') {
                                router.push({ name: 'table.make.payment', params: { slug: this.table.slug, id: orderResponse.data.data.id } });
                            } else {
                                router.push({ name: 'table.menu.table', params: { slug: this.table.slug }, query: { id: orderResponse.data.data.id } });
                            }
                            // router.push({name: "table.menu.table", params: {slug : this.table.slug}, query: {id: orderResponse.data.data.id}});
                            // router.push({name: "table.make.payment", params: {slug : this.table.slug, id: orderResponse.data.data.id}});
                        })
                        .catch();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    if (typeof err.response.data.errors === 'object') {
                        _.forEach(err.response.data.errors, (error) => {
                            alertService.error(error[0]);
                        });
                    }
                });
        },
    },
};
</script>
