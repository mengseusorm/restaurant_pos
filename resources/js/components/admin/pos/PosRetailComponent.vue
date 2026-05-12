<template>
    <LoadingComponent :props="loading" />
    <PosCustomerComponent v-on:onCustomerCreate="onCustomerCreate" />
    <MemberSelectComponent v-on:onMemberSelect="onMemberSelect" v-on:onMemberCreate="onMemberCreate" />

    <!-- Main Container with Equal Split -->

    <div class="flex flex-col md:flex-row gap-2 md:gap-4 h-[calc(100vh-110px)] max-w-full overflow-hidden pt-3">
        <!-- Start of Left Side Content -->
        <div id="cart-section" class="w-full md:w-1/2 db-pos-cartDiv bg-white rounded-lg overflow-hidden flex flex-col shadow-lg min-h-0 border">
            <!-- Cart Header -->
            <div class="w-full px-4 pt-4 pb-2 flex-shrink-0 border-b border-gray-100">
                <h3 class="mb-3">{{ $t('label.order') }}</h3>
            </div>
            
            <!-- Cart Items Section with Fixed Header and Scrollable Body -->
            <div id="cart-items-section" class="flex-1 min-h-0 flex flex-col overflow-hidden">
                <!-- Table Container with Fixed Header -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full">
                        <!-- Sticky Table Header -->
                        <thead class="bg-gray-50 sticky top-0 z-10 border-b border-gray-200">
                            <tr class="h-9">
                                <th class="capitalize text-xs font-normal font-rubik text-left pl-4 pr-3 text-heading w-10"></th>
                                <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                                    {{ $t('label.item') }}
                                </th>
                                <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading w-20">
                                    {{ $t('label.qty') }}
                                </th>
                                <th class="capitalize text-xs font-normal font-rubik text-left px-3 pr-4 text-heading w-24">
                                    {{ $t('label.price') }}
                                </th>
                            </tr>
                        </thead>
                        
                        <!-- Table Body -->
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(cart, index) in carts" :key="index" class="hover:bg-gray-50 transition-colors">
                                <td class="pl-3 py-3 align-top border-b border-[#EFF0F6] w-10">
                                    <button @click.prevent="deleteCartItem(index)" class="text-red-500 hover:text-red-700 transition-colors">
                                        <i class="lab lab-trash-line-2 font-fill-danger"></i>
                                    </button>
                                </td>
                                <td class="px-3 py-3 align-top border-b border-[#EFF0F6]">
                                    <h3 class="capitalize text-xs font-rubik text-[#2E2F38] font-medium mb-1">
                                        {{ cart.name }}
                                    </h3>
                                    <p v-if="Object.keys(cart.item_variations.variations).length !== 0" class="mb-1">
                                        <span v-for="(variation, variationName) in cart.item_variations.names" :key="variationName">
                                            <span class="capitalize text-[10px] leading-4 font-rubik text-heading">{{ variationName }}: &nbsp;</span>
                                            <span class="capitalize text-[10px] leading-4 font-rubik">{{ variation }}, &nbsp;</span>
                                        </span>
                                    </p>
                                    <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''" class="text-[10px] space-y-1">
                                        <li v-if="cart.item_extras.extras.length > 0" class="leading-4">
                                            <span class="capitalize font-rubik text-heading"> {{ $t('label.extras') }}: </span>
                                            <span class="capitalize font-rubik">
                                                <span v-for="extra in cart.item_extras.names" :key="extra"> {{ extra }}, &nbsp; </span>
                                            </span>
                                        </li>
                                        <li v-if="cart.instruction !== ''" class="leading-4">
                                            <span class="capitalize font-rubik text-heading"> {{ $t('label.instruction') }}: </span>
                                            <span class="capitalize font-rubik">
                                                {{ cart.instruction }}
                                            </span>
                                        </li>
                                    </ul>
                                </td>
                                <td class="px-3 py-3 align-top border-b border-[#EFF0F6] w-20">
                                    <div class="flex items-center indec-group">
                                        <button
                                            @click.prevent="cartQuantityDecrement(index)"
                                            :class="cart.quantity === 1 ? 'fa-trash-can' : 'fa-minus'"
                                            class="fa-solid text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"
                                        ></button>
                                        <input v-on:keypress="onlyNumber($event)" v-on:keyup="cartQuantityUp(index, $event)" type="number" :value="cart.quantity" class="text-center w-7 text-xs font-semibold text-heading indec-value mx-1" />
                                        <button @click.prevent="cartQuantityIncrement(index)" class="fa-solid fa-plus text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top border-b border-[#EFF0F6] text-xs font-rubik text-heading font-medium w-24">
                                    {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Empty Cart State -->
                    <div v-if="!carts || carts.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fa-solid fa-shopping-cart text-2xl text-gray-400"></i>
                        </div>
                        <!-- <h3 class="text-sm font-medium text-gray-900 mb-1">{{ $t('message.empty_cart') || 'Your cart is empty' }}</h3> -->
                        <p class="text-xs text-gray-500">{{ $t('message.add_items_to_cart') || 'Add items from the menu to get started' }}</p>
                    </div>
                </div>
            </div>

            <!-- Fixed Summary Section at Bottom -->
            <div id="summary-section" class="flex-shrink-0 p-4 bg-gray-50 border-t border-gray-200">
                <ul class="flex flex-col gap-2 mb-2 mt-2">
                        <li class="flex items-center justify-between">
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                                {{ $t('label.sub_total') }}
                            </span>
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                                {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                            </span>
                        </li>

                        <li class="flex items-center justify-between">
                            <span class="text-sm font-rubik capitalize leading-6">{{ $t('label.discount') }} {{ posDiscount && posDiscount > 0 ? '(' + posDiscountPercentage + '%)' : '' }}</span>
                            <span class="text-sm font-rubik capitalize leading-6">
                                {{ currencyFormat(posDiscount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                            </span>
                        </li>

                        <li class="flex items-center justify-between">
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]"> {{ $t('label.sub_total') }} {{ posDiscount && posDiscount > 0 ? '(' + $t('label.after_discount') + ')' : '' }} </span>
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                                {{ currencyFormat(subtotal - posDiscount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                            </span>
                        </li>

                        <li class="flex items-center justify-between">
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                                {{ $t('label.vat') }}
                            </span>
                            <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                                {{ currencyFormat(totalTax, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                            </span>
                        </li>

                        <li class="flex items-center justify-between py-2 px-3 bg-primary/10 rounded-lg border border-primary/20">
                            <span class="text-sm font-bold font-rubik capitalize leading-6 text-primary">
                                {{ $t('label.total') }}
                            </span>
                            <span class="text-lg font-bold font-rubik capitalize leading-6 text-primary">
                                {{ currencyFormat(subtotal + totalTax - posDiscount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                            </span>
                        </li>
                    </ul>
                </div>
        </div>
        <!-- End of Left Side Content --> 
        <!-- Start of Right Side Content -->
        <div id="item-section" class="w-full md:w-1/2 db-pos-cartDiv flex flex-col min-h-0 overflow-hidden">
            <div v-if="carts.length > 0 " class="w-full shadow-lg z-50 rounded-lg bg-white border pt-4 px-4 mb-5">
                <!-- Two Column Button Grid -->
                <div class="grid grid-cols-3 gap-2" v-if="carts.length > 0 && checkoutProps.form.id == null">
                    <button @click.prevent="resetCart" class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#FB4E4E] hover:bg-red-600 transition-colors">
                        {{ $t('button.cancel') }}
                    </button>
                    <button v-if="branch.show_suspense_button == statusEnum.ACTIVE" @click.prevent="saveDraft" class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-orange-500 hover:bg-orange-600 transition-colors">
                        {{ $t('button.suspend') }}
                    </button>
                    <button v-if="branch.show_paid_order_button == statusEnum.ACTIVE" @click.prevent="orderSubmit" class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#1AB759] hover:bg-green-600 transition-colors">
                        {{ $t('button.order') }}
                    </button>
                    <button
                        v-if="branch.show_select_customer == statusEnum.ACTIVE"
                        @click.prevent="toggleCustomerSection"
                        class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white transition-colors"
                        :class="showCustomerSection ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600'"
                    >
                        {{ $t('button.agent') }}
                    </button>
                    <button
                        v-if="branch.show_select_member == statusEnum.ACTIVE"
                        @click.prevent="toggleMemberSection"
                        class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white transition-colors"
                        :class="showMemberSection ? 'bg-purple-600' : 'bg-purple-500 hover:bg-purple-600'"
                    >
                        {{ $t('button.member') }}
                    </button>
                    <button
                        @click.prevent="toggleDiscountSection"
                        class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white transition-colors"
                        :style="showDiscountSection ? 'background-color: #4F46E5;' : 'background-color: #6366F1;'"
                        :class="showDiscountSection ? '' : 'hover:bg-indigo-600'"
                    >
                        {{ $t('button.discount') }}
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2" v-if="carts.length > 0 && checkoutProps.form.id > 0">
                    <button @click.prevent="resetCart" class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#FB4E4E]">
                        {{ $t('button.cancel') }}
                    </button>
                    <button @click.prevent="orderUnpaid" v-if="branch.show_unpaid_button == statusEnum.ACTIVE" class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 px-2 text-white bg-primary">
                        {{ $t('button.unpaid') }}
                    </button>
                </div>

                <div id="customer-section" class="mt-5">
                    <!-- Customer Selection with Transition -->
                    <transition name="slide-fade" mode="out-in">
                        <div v-if="showCustomerSection && branch.show_select_customer == statusEnum.ACTIVE" class="flex gap-2 mb-5 p-3 bg-blue-50 rounded-lg border border-blue-200" key="customer-section">
                            <vue-select
                                class="db-field-control w-full flex-auto text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                                id="customer"
                                v-model="checkoutProps.form.customer_id"
                                :options="customers"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                :placeholder="$t('label.select_customer')"
                                :search-placeholder="$t('label.search_customer')"
                            />
                            <button @click="addCustomer" type="button" class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                                <i class="lab lab-add-circle-line"></i>
                                <span class="capitalize text-sm font-bold">{{ $t('button.add') }}</span>
                            </button>
                        </div>
                    </transition>

                    <!-- Member Selection with Transition -->
                    <transition name="slide-fade" mode="out-in">
                        <div v-if="showMemberSection && branch.show_select_member == statusEnum.ACTIVE" class="flex gap-2 mb-5 p-3 bg-purple-50 rounded-lg border border-purple-200" key="member-section">
                            <div class="db-field-control w-full flex-auto text-sm rounded-lg appearance-none text-heading border-[#D9DBE9] min-h-[40px] flex items-center px-3">
                                <span v-if="selectedMember" class="text-sm text-heading">
                                    {{ selectedMember.name }}
                                    <span class="text-xs text-gray-500"> ({{ selectedMember.phone }}{{ selectedMember.card_number ? ' - ' + selectedMember.card_number : '' }}) </span>
                                </span>
                                <span v-else class="text-sm text-gray-400">{{ $t('label.select_member') }}</span>
                            </div>

                            <button v-if="!selectedMember" @click="searchAndSelectMember" type="button" class="flex items-center justify-center gap-1.5 px-3 h-10 w-[120px] rounded-lg text-white bg-primary">
                                <i class="lab lab-search-normal"></i>
                                <span class="capitalize text-sm font-bold">{{ $t('button.search') }}</span>
                            </button>
                            <button v-if="selectedMember" @click="clearSelectedMember" type="button" class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                                <i class="lab lab-close"></i>
                                <span class="capitalize text-sm font-bold">{{ $t('button.clear') }}</span>
                            </button>
                        </div>
                    </transition>

                    <div v-if="saveDrafts.length > 0 && branch.show_suspense_button == statusEnum.ACTIVE" class="p-3 mt-2 rounded-lg border border-[#D9DBE9]">
                        <h4 class="text-sm font-medium mb-3">{{ $t('label.suspend_order') }}</h4>
                        <div v-if="saveDrafts.length > 0">
                            <div class="flex flex-wrap items-center justify-start gap-3">
                                <button class="db-btn-outline success modal-btn m-0.5" v-for="(item, index) in saveDrafts" :key="index" @click="unSaveDraft(item, index)">
                                    <i class="lab lab-edit-line"></i>
                                    <span>{{ item.length }}</span>
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-600">{{ $t('label.no_suspend_order') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Discount Section with Transition -->
                <transition name="slide-fade" mode="out-in">
                    <div v-if="showDiscountSection" class="mb-5 pt-5 p-3 bg-indigo-50 rounded-lg border border-indigo-200" key="discount-section">
                        <div class="flex h-[38px]" v-if="carts.length > 0">
                            <div class="db-field-down-arrow">
                                <select v-model="discountType" class="w-[120px] h-full text-sm font-rubik rounded-tl rounded-bl appearance-none border pl-3 text-heading border-[#EFF0F6] rtl:pr-2">
                                    <option :value="discountTypeEnum.PERCENTAGE">
                                        {{ $t('label.percentage') }}
                                    </option>
                                    <option :value="discountTypeEnum.FIXED">
                                        {{ $t('label.fixed') }}
                                    </option>
                                </select>
                            </div>
                            <input v-on:keypress="floatNumber($event)" v-model="discount" type="text" :placeholder="$t('label.add_discount')" class="w-full h-full border-t border-b px-3 border-[#EFF0F6]" />
                            <button @click.prevent="applyDiscount" type="submit" class="flex-shrink-0 w-16 h-full text-sm font-medium font-rubik capitalize ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg text-white bg-[#008BBA]">
                                {{ $t('button.apply') }}
                            </button>
                        </div>
                        <div class="flex h-[38px] gap-2 items-center mt-3" v-if="carts.length > 0 && discountType == discountTypeEnum.PERCENTAGE">
                            <span
                                v-for="percent in [10, 15, 20, 30, 50, 70]"
                                :key="percent"
                                @click="discount = percent"
                                class="cursor-pointer px-2 py-1 rounded-full text-xs font-semibold border border-primary text-primary bg-primary/10 hover:bg-primary hover:text-white transition"
                                :class="{ 'bg-primary text-white': discount == percent }"
                            >
                                {{ percent }}%
                            </span>
                        </div>

                        <small class="db-field-alert" v-if="discountErrorMessage">{{ discountErrorMessage }}</small>
                    </div>
                </transition>
            </div>

            <div class="w-full shadow-lg z-50 rounded-lg bg-white border flex flex-col flex-1 min-h-0">
                <div class="p-4 border-b border-gray-200">
                    <form @submit.prevent="performSearch" class="flex items-center w-full h-[38px] leading-[38px] rounded-lg bg-white">
                        <input 
                            ref="searchInput"
                            type="text" 
                            v-model="props.search.name" 
                            :placeholder="$t('label.search_by_barcode_or_name') || 'Search by barcode or item name'"
                            class="w-full px-5 rounded-tl-lg rounded-bl-lg border placeholder:text-xs placeholder:font-rubik placeholder:text-[#A0A3BD] border-[#EFF0F6]" 
                            @keypress="onSearchKeypress"
                        />
                        <button type="submit" class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg bg-primary">
                            <i class="lab lab-search-normal text-white"></i>
                        </button>
                    </form>
                    
                    <!-- Search results indicator -->
                    <div v-if="props.search.name && props.search.name.trim() !== ''" class="mt-2 text-xs text-gray-600">
                        <span v-if="filteredItems.length === 0" class="text-red-600">
                            <i class="fa-solid fa-times-circle mr-1"></i>
                            {{ $t('message.no_items_match_search') || 'No items match your search' }}
                        </span>
                        <span v-else-if="filteredItems.length === 1" class="text-green-600">
                            <i class="fa-solid fa-check-circle mr-1"></i>
                            {{ $t('message.one_item_ready') || 'Press Enter to add' }} "{{ filteredItems[0].name }}" {{ $t('message.to_cart') || 'to cart' }}
                        </span>
                        <span v-else class="text-blue-600">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            {{ $t('message.multiple_items_found') || 'Found' }} {{ filteredItems.length }} {{ $t('message.items_press_enter') || 'items. Press Enter to see options or refine search.' }}
                        </span>
                    </div>
                </div>

                <div class="flex-1 min-h-0 p-4 pb-0">
                    <div class="h-full">
                        <ItemListViewComponent ref="itemListView" :items="filteredItems" :loading="itemsLoading" />
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Right Side Content -->
    </div>
    <!-- End of Main Container -->

    <!-- Mobile cart button hidden as cart is now always visible -->
    <!-- <button class="db-pos-cartBtn fixed md:hidden bottom-0 z-10 left-0 w-full h-14 py-4 text-center flex items-center justify-center shadow-xl-top gap-3 bg-primary">
        <i class="lab lab-bag-2 lab-font-size-13 text-white"></i>
        <span class="text-base font-medium font-rubik text-white">
            {{ totalItems() }} {{ $t('label.items') }} -
            {{ currencyFormat(subtotal - posDiscount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
        </span>
    </button> -->

    <!-- <ReceiptComponent v-if="lastOrder" :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :modalId="'unpaidReceiptModal'" /> -->

    <!-- <ReceiptComponent :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLastOrderOnly="true" :modalId="'lastOrderReceiptModal'" /> -->
    <!-- <ReceiptComponent v-if="lastOrder" :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :isPrintLastOrderOnly="true" :modalId="'lastOrderReceiptModal'" /> -->

    <!--====================================
      PAYMENT MODAL PART START
    =====================================-->
    <PaymentComponent :props="checkoutProps" />
    <!--====================================
          PAYMENT MODAL PART END
    =====================================-->

    <DraftModalComponent :itemDraft="itemDraft" :itemIndex="itemIndex" />
</template>
<script>
import SmIconSidebarModalEditComponent from '../components/buttons/SmIconSidebarModalEditComponent';
import LoadingComponent from '../components/LoadingComponent';
import ItemListViewComponent from './ItemListViewComponent';
import sourceEnum from '../../../enums/modules/sourceEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import isAdvanceOrderEnum from '../../../enums/modules/isAdvanceOrderEnum';
import statusEnum from '../../../enums/modules/statusEnum';
import roleEnum from '../../../enums/modules/roleEnum';
import appService from '../../../services/appService';
import discountTypeEnum from '../../../enums/modules/discountTypeEnum';
import alertService from '../../../services/alertService';
import posCartSyncService from '../../../services/posCartSyncService';
import ReceiptComponent from './ReceiptComponent';
import PosCustomerComponent from './PosCustomerComponent';
import posPaymentMethodEnum from '../../../enums/modules/posPaymentMethodEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import { Swiper, SwiperSlide } from 'swiper/vue';
import DraftModalComponent from './DraftModalComponent';
import SelectTableModalComponent from './SelectTableModalComponent.vue';
import SelectTableComponent from './SelectTableComponent.vue';
import OrderTypeSelectorComponent from '../components/OrderTypeSelectorComponent.vue';

import 'swiper/css';
import { useRoute } from 'vue-router';
import MemberSelectComponent from '../components/MemberSelectComponent.vue';
import OrderBasicInfoComponent from '../components/OrderBasicInfoComponent.vue';
import PaymentComponent from './PaymentComponent.vue';
export default {
    name: 'PosRetailComponent',
    components: {
        ReceiptComponent,
        LoadingComponent,
        ItemListViewComponent,
        PosCustomerComponent,
        MemberSelectComponent,
        Swiper,
        SwiperSlide,
        SmIconSidebarModalEditComponent,
        DraftModalComponent,
        PaymentComponent,
        SelectTableModalComponent,
        SelectTableComponent,
        OrderTypeSelectorComponent,
        OrderBasicInfoComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            itemsLoading: false,
            itemsLoaded: false, // Track if items have been loaded
            // Add loading flags for other data
            customersLoaded: false,
            branchesLoaded: false,
            companyLoaded: false,
            defaultAccessLoaded: false,
            order: {},

            showCustomerSection: false,
            showMemberSection: false,
            showDiscountSection: false,

            customPeople: null,

            discount: null,
            statusEnum: statusEnum,
            item: null,
            itemInfo: null,
            addons: {},
            addonQuantity: {},
            itemArrays: [],
            itemDraft: {},
            itemIndex: 0,

            lastOrder: {},

            selectedMember: null, // Add selectedMember to store the selected member data

            checkoutProps: {
                form: {
                    id: this.$route.params.id,
                    branch_id: null,
                    subtotal: 0,
                    total_tax: 0,
                    token: '',
                    customer_id: null,
                    member_id: null,
                    discount: 0,
                    discount_percentage: 0,
                    delivery_charge: 0,
                    delivery_time: null,
                    total: 0,
                    order_type: orderTypeEnum.TAKEAWAY,
                    is_advance_order: isAdvanceOrderEnum.NO,
                    pos_payment_method: posPaymentMethodEnum.CASH,
                    pos_payment_note: '',
                    payment_method: posPaymentMethodEnum.CASH,
                    source: sourceEnum.RETAIL_POS,
                    address_id: null,
                    items: [],
                    payment_status: paymentStatusEnum.PAID,
                    business_date: null,
                    order_dinings: [],
                    pos_received_amount: null,
                    currency: null,
                    number_of_people: 1,
                    order_note: '',
                    customer_name: '',
                    customer_phone_number: '',
                    customer_address: '',
                },
            },
            props: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc',
                    name: '',
                    item_category_id: '',
                    status: statusEnum.ACTIVE,
                },
            },
            settings: {
                itemsToShow: 6.2,
                wrapAround: false,
                snapAlign: 'start',
            },
            breakpoints: {
                200: {
                    itemsToShow: 1.4,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                250: {
                    itemsToShow: 1.9,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                300: {
                    itemsToShow: 2.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                375: {
                    itemsToShow: 3,
                    wrapAround: true,
                    snapAlign: 'start',
                },
                540: {
                    itemsToShow: 4.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                700: {
                    itemsToShow: 5.2,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                768: {
                    itemsToShow: 3.2,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                830: {
                    itemsToShow: 3.6,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                900: {
                    itemsToShow: 4.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                960: {
                    itemsToShow: 5.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                1024: {
                    snapAlign: 'start',
                    itemsToShow: 3.5,
                    wrapAround: false,
                },
                1100: {
                    snapAlign: 'start',
                    itemsToShow: 4.1,
                    wrapAround: false,
                },
                1180: {
                    snapAlign: 'start',
                    itemsToShow: 4.8,
                    wrapAround: false,
                },
                1280: {
                    snapAlign: 'start',
                    itemsToShow: 5.2,
                    wrapAround: false,
                },
                1400: {
                    snapAlign: 'start',
                    itemsToShow: 5.8,
                    wrapAround: false,
                },
                1600: {
                    snapAlign: 'start',
                    itemsToShow: 6.8,
                    wrapAround: false,
                },
                1700: {
                    snapAlign: 'start',
                    itemsToShow: 7.8,
                    wrapAround: false,
                },
                1800: {
                    snapAlign: 'start',
                    itemsToShow: 8.8,
                    wrapAround: false,
                },
                1920: {
                    snapAlign: 'start',
                    itemsToShow: 9.8,
                    wrapAround: false,
                },
                2000: {
                    snapAlign: 'start',
                    itemsToShow: 10.8,
                    wrapAround: false,
                },
                2100: {
                    snapAlign: 'start',
                    itemsToShow: 11.8,
                    wrapAround: false,
                },
            },
            statusEnum: statusEnum,
            discountTypeEnum: discountTypeEnum,
            posPaymentMethodEnum: posPaymentMethodEnum,
            discountType: discountTypeEnum.PERCENTAGE,
            discountErrorMessage: '',
            route: useRoute(),
            isPosShow: /^\/admin\/pos\/show\/\d+$/,
        };
    },
    computed: {
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        
        items: function () {
            return this.$store.getters['item/lists'];
        },
        filteredItems: function () {
            if (!this.items || !Array.isArray(this.items)) {
                return [];
            }
            
            let filtered = this.items;
            
            // Filter by category if selected
            if (this.props.search.item_category_id) {
                filtered = filtered.filter(item => {
                    return item.item_category_id == this.props.search.item_category_id;
                });
            }
            
            // Filter by search term (name or barcode)
            if (this.props.search.name && this.props.search.name.trim() !== '') {
                const searchTerm = this.props.search.name.toLowerCase().trim();
                
                filtered = filtered.filter(item => {
                    // Search by name
                    const nameMatch = item.name && item.name.toLowerCase().includes(searchTerm);
                    // Search by barcode
                    const barcodeMatch = item.barcode && item.barcode.toLowerCase().includes(searchTerm);
                    
                    return nameMatch || barcodeMatch;
                });
            }
            
            return filtered;
        },

        customers: function () {
            return this.$store.getters['user/lists'];
        },

        carts: function () {
            return this.$store.getters['posCart/lists'];
        },
        cartOrder: function () {
            return this.$store.getters['posCart/order'];
        },
        saveDrafts: function () {
            return this.$store.getters['posCartSaveDraft/lists'];
        },
        subtotal: function () {
            return this.$store.getters['posCart/subtotal'];
        },
        totalTax: function () {
            return this.$store.getters['posCart/totalTax'];
        },
        posDiscount: function () {
            return this.$store.getters['posCart/discount'];
        },
        posDiscountPercentage: function () {
            return this.$store.getters['posCart/discountPercentage'];
        },
        authInfo: function () {
            return this.$store.getters['authInfo'];
        },
    },
    watch: {
        'checkoutProps.form.order_type': {
            handler(newVal) {
                // Store order type in localStorage for customer view
                localStorage.setItem('posOrderType', newVal);
            },
            immediate: true,
        },
        $route: {
            handler(newRoute) {
                // Sync current route for CustomerView display mode detection
                posCartSyncService.syncCurrentRoute(newRoute.path);
            },
            immediate: true,
        },
    },
    mounted() {
        const headerNavBtn = document.querySelector('.db-header-nav');
        if (headerNavBtn) {
            headerNavBtn.click();
        }

        this.$route.params.id;

        // Initialize cart synchronization service
        posCartSyncService.initCartSync(this.$store);

        // Sync initial route for CustomerView
        posCartSyncService.syncCurrentRoute(this.$route.path);

        this.loadItemList(); // Load items only once on mount
        this?.posOrderShow();

        this.loadBranches();
        this.loadDefaultAccess();
        this.loadCustomers();
        this.loadCompany();
        
        // Focus search input for better UX
        this.$nextTick(() => {
            if (this.$refs.searchInput) {
                this.$refs.searchInput.focus();
            }
        });
    },
    methods: {
        toggleCustomerSection: function () {
            // If customer section is already open, close it
            if (this.showCustomerSection) {
                this.showCustomerSection = false;
            } else {
                // Open customer section and close others
                this.showCustomerSection = true;
                // this.showMemberSection = false;
                // this.showDiscountSection = false;
            }
        },
        toggleMemberSection: function () {
            // If member section is already open, close it
            if (this.showMemberSection) {
                this.showMemberSection = false;
            } else {
                // Open member section and close others
                this.showMemberSection = true;
                // this.showCustomerSection = false;
                // this.showDiscountSection = false;
            }
        },
        toggleDiscountSection: function () {
            // If discount section is already open, close it
            if (this.showDiscountSection) {
                this.showDiscountSection = false;
            } else {
                // Open discount section and close others
                this.showDiscountSection = true;
                // this.showCustomerSection = false;
                // this.showMemberSection = false;
            }
        },
        closeAllSections: function () {
            this.showCustomerSection = false;
            this.showMemberSection = false;
            this.showDiscountSection = false;
        },
        goToOrderDetail: function (orderId) {
            this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
        },
        onCustomPeopleChange: function (value) {
            this.checkoutProps.form.number_of_people = this.customPeople;
        },
        posOrderShow: function () {
            const testRoute = this.isPosShow.test(this.route.path);
            if (testRoute) {
                return true;
            }
        },
        updateTableSelected: function (selectedTables) {
            this.checkoutProps.form.order_dinings = selectedTables;
        },
        resetDinIngTable: function () {
            this.$refs?.selectTable?.clearSelectedTables();
        },
        selectPaymentMethod: function (paymentMethodId) {
            this.checkoutProps.form.pos_payment_method = paymentMethodId;
            this.checkoutProps.form.payment_method = paymentMethodId;
        },
        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        floatNumber: function (e) {
            return appService.floatNumber(e);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        search: function () {
            // Don't auto-search on every input change - only filter for display
            // The actual search with auto-add will happen on Enter key press
        },
        performSearch: function() {
            // Smart search: filter and auto-add if only one match when Enter is pressed
            const searchTerm = this.props.search.name.trim();
            
            if (searchTerm === '') {
                return;
            }
            
            // Get filtered items
            const matches = this.filteredItems;
            
            if (matches.length === 1) {
                // Only one match - automatically add to cart
                const item = matches[0];
                this.quickAddToCart(item);
                // Clear search after adding
                this.props.search.name = '';
            } else if (matches.length === 0) {
                // No matches found
                alertService.warning(this.$t('message.no_items_match_search') || 'No items match your search');
            } else {
                // Multiple matches - show info message
                alertService.info(this.$t('message.multiple_items_found') || `Found ${matches.length} items. Please refine your search.`);
            }
        },
        onSearchKeypress: function(event) {
            // Only trigger search and auto-add when Enter key is pressed
            if (event.key === 'Enter') {
                event.preventDefault();
                this.performSearch();
            }
        },
        quickAddToCart: async function(item) {
            // Check if item has variations or extras that require customization
            if (item.itemAttributes && item.itemAttributes.length > 0) {
                // Item has variations - show modal for customization
                this.$refs.itemListView.variationModalShow(item);
                return;
            }
            
            if (item.extras && item.extras.length > 0) {
                // Item has extras - show modal for customization
                this.$refs.itemListView.variationModalShow(item);
                return;
            }
            
            // Simple item - add directly to cart
            await this.$refs.itemListView.order(item);
            
            // Show success message
            alertService.success(this.$t('message.add_to_cart') + ': ' + item.name);
            
            // Re-focus search input for continuous scanning
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },
        // Optimized data loading methods - only load if data doesn't exist in store
        
        loadBranches: function() {
            if (this.branchesLoaded || (this.branches && this.branches.length > 0)) {
                return;
            }
            
            this.$store.dispatch('branch/lists', {
                order_column: 'id',
                order_type: 'asc',
            }).then(() => {
                this.branchesLoaded = true;
            }).catch((err) => {
                console.error('Error loading branches:', err);
            });
        },
        
        loadDefaultAccess: function() {
            if (this.defaultAccessLoaded) {
                return;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('defaultAccess/show')
                .then((res) => {
                    this.checkoutProps.form.branch_id = res.data.data.branch_id;
                    // Wait for branches to be loaded before accessing them
                    this.$nextTick(() => {
                        if (this.branches && this.branches.length > 0) {
                            const getBusinessDate = this.branches.filter((item) => item.id === this.checkoutProps.form.branch_id);
                            if (getBusinessDate.length > 0) {
                                this.checkoutProps.form.business_date = getBusinessDate[0].current_business_day;
                            }
                        }
                    });
                    this.defaultAccessLoaded = true;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    console.error('Error loading default access:', err);
                });
        },
        
        loadCustomers: function() {
            if (this.customersLoaded || (this.customers && this.customers.length > 0)) {
                return;
            }
            
            this.customerList();
        },
        
        loadCompany: function() {
            if (this.companyLoaded) {
                return;
            }
            
            this.loading.isActive = true;
            this.$store.dispatch('company/lists')
                .then((res) => {
                    // Note: company data might be used elsewhere in the component
                    // You might want to store this in a data property or store
                    this.companyLoaded = true;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    console.error('Error loading company:', err);
                });
        },
        
        
        
        loadItemList: function() {
            // Use existing optimized itemList method
            this.itemList();
        },
        
        customerList: function (id = null) {
            if (this.customersLoaded && id === null) {
                return;
            }
            
            this.loading.isActive = true;
            this.$store
                .dispatch('user/lists', {
                    order_column: 'id',
                    order_type: 'asc',
                    status: statusEnum.ACTIVE,
                    role_id: roleEnum.CUSTOMER,
                })
                .then((res) => {
                    this.checkoutProps.form.customer_id = id === null ? res.data.data[0].id : id;
                    this.customersLoaded = true;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    console.error('Error loading customers:', err);
                });
        },
        
        // Method to reset loading flags when data needs to be refreshed
        resetLoadingFlags: function() {
            this.itemsLoaded = false;
            this.customersLoaded = false;
            this.branchesLoaded = false;
            this.companyLoaded = false;
            this.defaultAccessLoaded = false;
        },
        
        // Method to force reload all data
        forceReloadAllData: function() {
            this.resetLoadingFlags();
            this.loadItemList();
            this.loadBranches();
            this.loadDefaultAccess();
            this.loadCustomers();
            this.loadCompany();
        },
        itemList: function (page = 1) {
            // Only load items if not already loaded or if forced
            if (this.itemsLoaded && page === 1) {
                return;
            }
            
            this.itemsLoading = true;
            this.props.search.page = page;

            this.$store
                .dispatch('item/lists', this.props.search)
                .then((res) => {
                    this.itemsLoading = false;
                    this.itemsLoaded = true; // Mark items as loaded
                })
                .catch((err) => {
                    this.itemsLoading = false;
                });
        },
        cartQuantityUp: function (id, e) {
            if (e.target.value > 0) {
                this.$store
                    .dispatch('posCart/quantity', {
                        id: id,
                        status: e.target.value,
                    })
                    .then(() => {
                        // Sync cart data to localStorage and dispatch events
                        posCartSyncService.syncCartData(this.$store);
                    })
                    .catch();
            }
        },
        cartQuantityIncrement: function (id) {
            this.$store
                .dispatch('posCart/quantity', { id: id, status: 'increment' })
                .then(() => {
                    // Sync cart data to localStorage and dispatch events
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch();
        },
        cartQuantityDecrement: function (id) {
            this.$store
                .dispatch('posCart/quantity', { id: id, status: 'decrement' })
                .then(() => {
                    // Sync cart data to localStorage and dispatch events
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch();
        },
        deleteCartItem: function (id) {
            this.$store
                .dispatch('posCart/deleteCartItem', {
                    id: id,
                    status: 'decrement',
                })
                .then(() => {
                    // Sync cart data to localStorage and dispatch events
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch();
        },
        applyDiscount: function () {
            this.discountErrorMessage = '';
            // let totalWithTax = this.subtotal + this.totalTax;
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.subtotal < this.discount) {
                    this.discountErrorMessage = this.$t('message.discount_fixed_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat(+this.discount).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store
                        .dispatch('posCart/discount', this.checkoutProps.form.discount)
                        .then(() => {
                            // Sync cart data to localStorage and dispatch events
                            posCartSyncService.syncCartData(this.$store);
                        })
                        .catch();
                }
            } else {
                if (this.discount > 100) {
                    this.discountErrorMessage = this.$t('message.discount_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat((this.subtotal * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    // this.checkoutProps.form.discount = parseFloat((totalWithTax * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store
                        .dispatch('posCart/discount', this.checkoutProps.form.discount)
                        .then(() => {
                            // Sync cart data to localStorage and dispatch events
                            posCartSyncService.syncCartData(this.$store);
                        })
                        .catch();
                }
            }
        },
        resetCart: function () {
            appService
                .confirmDialog(this.$t('message.reset_cart_title'), this.$t('message.reset_cart_confirm_message'), 'warning', this.$t('label.yes'), this.$t('label.no'))
                .then(() => {
                    this.loading.isActive = true;
                    this.$store
                        .dispatch('posCart/resetCart')
                        .then((res) => {
                            this.loading.isActive = false;
                            this.checkoutProps.form.id = null;
                            this.checkoutProps.form.order_dinings = [];
                            this.order.dining_tables = [];
                            // Clear selected member when cart is reset
                            this.selectedMember = null;
                            this.checkoutProps.form.member_id = null;
                            this.checkoutProps.form.pos_payment_method = posPaymentMethodEnum.CASH;
                            this.checkoutProps.form.pos_payment_note = '';
                            
                            // Clear search
                            this.props.search.name = '';
                            this.props.search.item_category_id = '';

                            // Close all toggle sections when cart is reset
                            this.closeAllSections();

                            // Clear cart data from localStorage and notify other components
                            posCartSyncService.clearCartData();

                            this.$router.push({ name: 'admin.pos.retail' });
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                        });
                })
                .catch(() => {
                    // User clicked cancel, do nothing
                });

        },
        orderSubmit: function () {
            // appService.modalShow('#paymentMethod');

            this.loading.isActive = true;
            this.checkoutProps.form.subtotal = this.subtotal;
            this.checkoutProps.form.total_tax = this.totalTax;
            this.checkoutProps.form.discount_percentage = this.posDiscountPercentage;
            // this.checkoutProps.form.total = parseFloat(this.subtotal - this.checkoutProps.form.discount).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.total = parseFloat(this.subtotal - this.posDiscount).toFixed(this.setting.site_digit_after_decimal_point); //Total with discount is not saving in system

            this.checkoutProps.form.items = [];
            this.checkoutProps.form.pos_payment_note = this.checkoutProps.form.pos_payment_method === posPaymentMethodEnum.CASH ? null : this.checkoutProps.form.pos_payment_note;

            /**
             * get business from branch
             *
             */

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

            this.loading.isActive = false;
            
            // Ensure DOM is updated before showing modal
            this.$nextTick(() => {
                appService.modalShow('#orderpayment');
            });
        },
        
        totalItems: function () {
            if (this.carts.length > 0) {
                let totalItem = 0;
                this.carts.forEach((cart) => {
                    totalItem += cart.quantity;
                });
                return totalItem;
            }
        },
        addCustomer: function () {
            appService.modalShow('#customerModal');
        },
        onCustomerCreate: function (customerId) {
            appService.modalHide('#customerModal');
            this.customerList(customerId);
        },
        searchAndSelectMember: function () {
            appService.modalShow('#memberSearchModal');
        },
        onMemberSelect: function (memberData) {
            // Store the selected member data for display
            this.selectedMember = memberData;
            // Set the selected member in the checkout form
            this.checkoutProps.form.member_id = memberData.id;
            // Update the store with selected member data
            this.$store.dispatch('posCart/setSelectedMember', memberData);
            // Optionally update UI to show selected member
            alertService.success(this.$t('message.member_selected') + ': ' + memberData.name);
        },
        onMemberCreate: function (memberData) {
            // Store the newly created member data for display
            this.selectedMember = memberData;
            // Set the newly created member in the checkout form
            this.checkoutProps.form.member_id = memberData.id;
            // Update the store with selected member data
            this.$store.dispatch('posCart/setSelectedMember', memberData);
            alertService.success(this.$t('message.member_created') + ': ' + memberData.name);
        },
        clearSelectedMember: function () {
            this.selectedMember = null;
            this.checkoutProps.form.member_id = null;
            // Clear member from store
            this.$store.dispatch('posCart/setSelectedMember', null);
            alertService.success(this.$t('message.member_cleared'));
        },
        closeModal: function () {
            appService.modalHide('#paymentMethod');
        },
        
        getBranches: function () {
            // Use optimized loading method
            this.loadBranches();
            return this.branches;
        },
        saveDraft: function () {
            appService
                .saveDraftConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        if (this.carts.length > 0) {
                            this.loading.isActive = false;
                            this.$store
                                .dispatch('posCartSaveDraft/lists', this.carts)
                                .then((res) => {
                                    alertService.success(this.$t('message.suspend'));
                                    this.carts = [];
                                })
                                .catch((err) => {
                                    this.loading.isActive = false;
                                    alertService.error(err.response.data.message);
                                });
                        }
                        this.$store
                            .dispatch('posCart/resetCart')
                            .then((res) => {
                                // Clear cart data from localStorage and notify other components
                                posCartSyncService.clearCartData();
                            })
                            .catch();
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        unSaveDraft: function (item, index) {
            this.itemDraft = item;
            this.itemIndex = index;
            appService.modalShow('#draftModal');
        },
        itemsCount: function (items) {
            return items.reduce((sum, item) => sum + (item.quantity || 0), 0);
        },
    },
};
</script>

<style scoped>
/* Smooth slide-fade transition for toggle sections */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease-in-out;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.slide-fade-enter-to,
.slide-fade-leave-from {
    opacity: 1;
    transform: translateY(0);
}

/* Enhanced button active states */
.bg-blue-600,
.bg-purple-600,
.bg-indigo-600 {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

/* Section styling improvements */
.bg-blue-50,
.bg-purple-50,
.bg-indigo-50 {
    backdrop-filter: blur(10px);
}

/* Cart table styling */
#cart-items-section .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

#cart-items-section .overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

#cart-items-section .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

#cart-items-section .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

#cart-items-section .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Fixed summary section styling */
#summary-section {
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .slide-fade-enter-from,
    .slide-fade-leave-to {
        transform: translateX(-20px);
    }
}
</style>
