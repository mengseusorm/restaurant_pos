<template>
    <LoadingComponent :props="loading" />
    <PosCustomerComponent v-if="branch.show_select_member == statusEnum.ACTIVE"
        v-on:onCustomerCreate="onCustomerCreate" />
    <MemberSelectComponent v-if="branch.show_select_member == statusEnum.ACTIVE" v-on:onMemberSelect="onMemberSelect"
        v-on:onMemberCreate="onMemberCreate" />

    <div class="md:w-[calc(100%-440px)] lg:w-[calc(100%-420px)] xl:w-[calc(100%-477px)]">

        <form @submit.prevent="search" class="flex items-center w-full h-[38px] leading-[38px] mb-4 rounded-lg bg-white">
            <button type="button" @click="showKeyboardShortcuts = !showKeyboardShortcuts"
                class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tl-lg ltr:rounded-bl-lg rtl:rounded-tr-lg rtl:rounded-br-lg border border-r-0 border-gray-200 hover:bg-gray-50 transition p-1"
                :title="showKeyboardShortcuts ? $t('label.hide_shortcuts') : $t('label.show_shortcuts')">
                <Keyboard :fillColor="showKeyboardShortcuts ? '#1AB759' : '#9CA3AF'" :size="30" />
            </button>
            <input id="pos-search-input" type="text" v-model="props.search.name"
                :placeholder="$t('label.search_by_menu_item')"
                class="w-full h-full px-5 border placeholder:text-xs placeholder:font-rubik placeholder:text-[#A0A3BD] border-gray-200" />
            <button type="submit"
                class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg bg-primary">
                <i class="lab lab-search-normal text-white"></i>
            </button>
        </form>
        <!-- Keyboard Shortcuts -->
        <KeyboardShortcutsComponent v-if="showKeyboardShortcuts" v-model="props.search.name"
            input-id="pos-search-input" />

        <div class="swiper pos-menu-swiper mb-6" v-if="categories.length > 1">
            <Swiper dir="ltr" :speed="1000" slidesPerView="auto" :spaceBetween="16" class="menu-slides">
                <SwiperSlide class="!w-fit" v-for="(category, index) in categories" :key="category"
                    :class="category.id === props.search.item_category_id || (category.id === 0 && props.search.item_category_id === '') ? 'pos-group' : ''">
                    <router-link v-if="index === 0" to="#" @click.prevent="allCategory"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category" />
                        <h3 class="text-xs leading-[16px] font-medium font-rubik h-8 flex items-center justify-center"
                            style="min-height: 2rem">
                            {{ category.name }}
                        </h3>
                    </router-link>
                    <router-link v-else to="#" @click.prevent="setCategory(category.id)"
                        class="w-28 flex flex-col items-center text-center gap-4 py-4 px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white">
                        <img class="h-7 drop-shadow-category" :src="category.thumb" alt="category" />
                        <h3 class="text-xs leading-[16px] font-medium font-rubik h-8 flex items-center justify-center"
                            style="min-height: 2rem">
                            {{ category.name }}
                        </h3>
                    </router-link>
                </SwiperSlide>
            </Swiper>
        </div>
        <div class="overflow-y-auto thin-scrolling">
            <ItemComponent :items="items" />
        </div>
    </div>
    <div
        class="db-pos-cartDiv fixed top-0 w-full h-screen rounded-none z-50 md:z-10 md:top-[90px] ltr:md:right-5 rtl:md:left-5 md:w-[452px] lg:w-[405px] xl:w-[460px] md:h-[calc(100vh-90px)] md:rounded-lg overflow-hidden bg-white flex flex-col md:border md:border-gray-200">
        <div class="overflow-y-auto thin-scrolling flex-1">
            <div class="p-3">
                <div class="md:hidden text-right mb-3">
                    <button class="db-pos-cartCls">
                        <i class="lab-close-circle-line font-fill-danger lab-font-size-24"></i>
                    </button>
                </div>

                <div v-if="checkoutProps.form.id" class="border-[#D9DBE9] border rounded-lg p-3 mb-3">
                    <h3 class="mb-3">{{ $t('label.add_more_order') }}</h3>
                    <OrderBasicInfoComponent :order="cartOrder" @editClick="goToOrderDetail(checkoutProps.form.id)"
                        :show-bottom-edit-button="false" />
                </div>
                <div v-else>
                    <h3 class="mb-3">{{ $t('label.create_new_order') }}</h3>
                    <div v-if="branch.show_select_member == statusEnum.ACTIVE" class="flex gap-2 mb-2">
                        <vue-select
                            class="db-field-control w-full flex-auto text-sm rounded-lg appearance-none text-heading border-[#D9DBE9]"
                            id="customer" v-model="checkoutProps.form.customer_id" :options="customers" label-by="name"
                            value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                            :placeholder="$t('label.select_customer')"
                            :search-placeholder="$t('label.search_customer')" />
                        <button @click="addCustomer" type="button"
                            class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                            <i class="lab lab-add-circle-line"></i>
                            <span class="capitalize text-sm font-bold">{{ $t('button.add') }}</span>
                        </button>
                    </div>
                    <div v-if="branch.show_select_member == statusEnum.ACTIVE" class="flex gap-2 mb-2">
                        <div
                            class="db-field-control w-full flex-auto text-sm rounded-lg appearance-none text-heading border-[#D9DBE9] min-h-[40px] flex items-center px-3">
                            <span v-if="selectedMember" class="text-sm text-heading">
                                {{ selectedMember.name }}
                                <span class="text-xs text-gray-500"> ({{ selectedMember.phone }}{{
                                    selectedMember.card_number ? ' - ' + selectedMember.card_number : '' }}) </span>
                            </span>
                            <span v-else class="text-sm text-gray-400">{{ $t('label.select_member') }}</span>
                        </div>

                        <button v-if="!selectedMember" @click="searchAndSelectMember" type="button"
                            class="flex items-center justify-center gap-1.5 px-3 h-10 w-[120px] rounded-lg text-white bg-primary">
                            <i class="lab lab-search-normal"></i>
                            <span class="capitalize text-sm font-bold">{{ $t('button.search') }}</span>
                        </button>
                        <button v-if="selectedMember" @click="clearSelectedMember" type="button"
                            class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-white bg-primary">
                            <i class="lab lab-close"></i>
                            <span class="capitalize text-sm font-bold">{{ $t('button.clear') }}</span>
                        </button>
                    </div>

                    <!-- Order Type Selection Component -->
                    <OrderTypeSelectorComponent v-model="checkoutProps.form" />

                    <!-- Table Selection Component -->
                    <TableSelectionComponent v-model="checkoutProps.form.order_dinings"
                        :order-type="checkoutProps.form.order_type" :dine-in-type="orderTypeEnums.dineIn"
                        :show-select-table-list="branch.show_select_table_list" :active-status="statusEnum.ACTIVE" />

                    <div class="flex gap-2 h-[38px]">
                        <input v-model.number="checkoutProps.form.order_note" type="text"
                            :placeholder="$t('label.order_note')"
                            class="w-full h-full px-3 rounded-lg border border-[#D9DBE9]" />
                    </div>

                    <!-- Customer Name -->
                    <div v-if="branch.show_customer_name == statusEnum.ACTIVE" class="mb-2 mt-2">
                        <input v-model="checkoutProps.form.customer_name" type="text"
                            :placeholder="$t('label.customer_name')"
                            class="w-full h-10 px-3 text-sm rounded-lg border border-[#D9DBE9] text-heading" />
                    </div>

                    <!-- Customer Phone Number -->
                    <div v-if="branch.show_customer_phone_number == statusEnum.ACTIVE" class="mb-2">
                        <input v-model="checkoutProps.form.customer_phone_number" type="text"
                            :placeholder="$t('label.customer_phone_number')"
                            class="w-full h-10 px-3 text-sm rounded-lg border border-[#D9DBE9] text-heading" />
                    </div>

                    <!-- Customer Address -->
                    <div v-if="branch.show_customer_address == statusEnum.ACTIVE" class="mb-2">
                        <input v-model="checkoutProps.form.customer_address" type="text"
                            :placeholder="$t('label.customer_address')"
                            class="w-full h-10 px-3 text-sm rounded-lg border border-[#D9DBE9] text-heading" />
                    </div>
                </div>

                <div v-if="saveDrafts.length > 0 && branch.show_suspense_button == statusEnum.ACTIVE"
                    class="p-3 mt-2 rounded-lg border border-[#D9DBE9]">
                    <h4 class="text-sm font-medium mb-3">{{ $t('label.suspend_order') }}</h4>
                    <div v-if="saveDrafts.length > 0">
                        <div class="flex flex-wrap items-center justify-start gap-3">
                            <button class="db-btn-outline success modal-btn m-0.5" v-for="(item, index) in saveDrafts"
                                :key="index" @click="unSaveDraft(item, index)">
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
            <table class="w-full">
                <thead class="bg-primary-light">
                    <tr class="h-9">
                        <th class="capitalize text-xs font-normal font-rubik text-left pl-3 text-heading"></th>
                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                            {{ $t('label.item') }}
                        </th>
                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                            {{ $t('label.qty') }}
                        </th>
                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                            {{ $t('label.unit_price') }}
                        </th>
                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                            {{ $t('label.amount') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(cart, index) in carts">
                        <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] rtl:pr-3">
                            <button @click.prevent="deleteCartItem(index)">
                                <i class="lab lab-trash-line-2 font-fill-danger"></i>
                            </button>
                        </td>
                        <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                            <h3 class="capitalize text-xs font-rubik text-[#2E2F38]">
                                {{ cart.name }}
                                <span v-if="cart.order_item_custom_name" class="text-gray-600"><br> {{
                                    cart.order_item_custom_name }}</span>
                                <button v-if="cart.can_input_custom_name == statusEnum.ACTIVE"
                                    @click.prevent="openCustomNameModal(index, cart)"
                                    class="ml-2 text-primary hover:text-primary-dark">
                                    <i class="fa-solid fa-pen-to-square text-[12px]"></i>
                                </button>
                            </h3>
                            <p v-if="Object.keys(cart.item_variations.variations).length !== 0">
                                <span v-for="(variation, variationName) in cart.item_variations.names">
                                    <span class="capitalize text-[10px] leading-4 font-rubik text-heading">{{
                                        variationName }}: &nbsp;</span>
                                    <span class="capitalize text-[10px] leading-4 font-rubik">{{ variation }}, &nbsp;
                                    </span>
                                </span>
                            </p>
                            <ul v-if="cart.item_extras.extras.length > 0 || cart.instruction !== ''">
                                <li v-if="cart.item_extras.extras.length > 0" class="leading-4">
                                    <span class="capitalize text-[10px] leading-4 font-rubik text-heading"> {{
                                        $t('label.extras') }}: </span>
                                    <p class="capitalize text-[10px] leading-4 font-rubik">
                                        <span v-for="extra in cart.item_extras.names"> {{ extra }}, &nbsp; </span>
                                    </p>
                                </li>
                                <li v-if="cart.instruction !== ''" class="leading-4">
                                    <span class="capitalize text-[10px] leading-4 font-rubik text-heading"> {{
                                        $t('label.instruction') }}: </span>
                                    <span class="capitalize text-[10px] leading-4 font-rubik">
                                        {{ cart.instruction }}
                                    </span>
                                </li>
                            </ul>
                        </td>
                        <td class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6]">
                            <div class="flex items-center indec-group">
                                <button @click.prevent="cartQuantityDecrement(index)"
                                    :class="cart.quantity === 1 ? 'fa-trash-can' : 'fa-minus'"
                                    class="fa-solid text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"></button>
                                <input v-on:keypress="onlyNumber($event)" v-on:keyup="cartQuantityUp(index, $event)"
                                    type="number" :value="cart.quantity"
                                    class="text-center w-7 text-xs font-semibold text-heading indec-value" />
                                <button @click.prevent="cartQuantityIncrement(index)"
                                    class="fa-solid fa-plus text-[10px] w-[18px] h-[18px] leading4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                            </div>
                        </td>
                        <td
                            class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] text-xs font-rubik text-heading">
                            {{ currencyFormat(cart.convert_price, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </td>
                        <td
                            class="pl-3 py-3 last:pr-3 align-top border-b border-[#EFF0F6] text-xs font-rubik text-heading">
                            <div v-if="cart.discount > 0" class="space-y-1">
                                <div class="text-[10px] text-gray-500 line-through">
                                    {{ currencyFormat(cart.quantity * cart.convert_price,
                                    setting.site_digit_after_decimal_point,
                                    branch.currency_id?.symbol, setting.site_currency_position) }}
                                </div>
                                <div class="text-[10px] text-red-600">
                                    -{{ currencyFormat(cart.discount, setting.site_digit_after_decimal_point,
                                    branch.currency_id?.symbol, setting.site_currency_position) }}
                                    <span v-if="cart.discount_percentage > 0">({{ cart.discount_percentage }}%)</span>
                                </div>
                                <div class="font-semibold">
                                    {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point,
                                    branch.currency_id?.symbol, setting.site_currency_position) }}
                                </div>
                            </div>
                            <div v-else>
                                {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point,
                                branch.currency_id?.symbol, setting.site_currency_position) }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="p-4">
                <div v-if="permissionChecker('discount')">
                    <!-- <div v-if="branch.show_discount_button == statusEnum.ACTIVE"> -->
                    <div class="flex h-[38px]" v-if="carts.length > 0">
                        <div class="db-field-down-arrow">
                            <select v-model="discountType"
                                class="w-[120px] h-full text-sm font-rubik rounded-tl rounded-bl appearance-none border pl-3 text-heading border-[#EFF0F6] rtl:pr-2">
                                <option :value="discountTypeEnum.PERCENTAGE">
                                    {{ $t('label.percentage') }}
                                </option>
                                <option :value="discountTypeEnum.FIXED">
                                    {{ $t('label.fixed') }}
                                </option>
                            </select>
                        </div>
                        <input v-on:keypress="floatNumber($event)" v-model="discount" type="text"
                            :placeholder="$t('label.add_discount')"
                            class="w-full h-full border-t border-b px-3 border-[#EFF0F6]" />
                        <button @click.prevent="applyDiscount" type="submit"
                            class="flex-shrink-0 w-16 h-full text-sm font-medium font-rubik capitalize ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg text-white bg-[#008BBA]">
                            {{ $t('button.apply') }}
                        </button>
                    </div>
                    <div class="flex h-[38px] gap-2 items-center mt-3"
                        v-if="carts.length > 0 && discountType == discountTypeEnum.PERCENTAGE">
                        <span v-for="percent in [10, 15, 20, 30, 50, 70]" :key="percent" @click="discount = percent"
                            class="cursor-pointer px-2 py-1 rounded-full text-xs font-semibold border border-primary text-primary bg-primary/10 hover:bg-primary hover:text-white transition"
                            :class="{ 'bg-primary ': discount == percent }">
                            {{ percent }}%
                        </span>
                    </div>

                    <small class="db-field-alert" v-if="discountErrorMessage">{{ discountErrorMessage }}</small>
                </div>

                <ul class="flex flex-col gap-1.5 mb-4 mt-4">
                    <li class="flex items-center justify-between">
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ $t('label.sub_total') }}
                        </span>
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ currencyFormat(subtotal, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </span>
                    </li>

                    <li class="flex items-center justify-between">
                        <span class="text-sm font-rubik capitalize leading-6">{{ $t('label.discount') }} {{ posDiscount
                            && posDiscount > 0 ? '(' + posDiscountPercentage + '%)' : '' }}</span>
                        <span class="text-sm font-rubik capitalize leading-6">
                            {{ currencyFormat(posDiscount, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </span>
                    </li>

                    <li class="flex items-center justify-between">
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]"> {{ $t('label.sub_total')
                            }} {{ posDiscount && posDiscount > 0 ? '(' + $t('label.after_discount') + ')' : '' }}
                        </span>
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ currencyFormat(subtotal - posDiscount, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </span>
                    </li>

                    <li class="flex items-center justify-between">
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ $t('label.vat') }}
                        </span>
                        <span class="text-sm font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ currencyFormat(totalTax, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </span>
                    </li>

                    <li class="flex items-center justify-between">
                        <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ $t('label.total') }}
                        </span>
                        <span class="text-sm font-medium font-rubik capitalize leading-6 text-[#2E2F38]">
                            {{ currencyFormat(subtotal + totalTax - posDiscount, setting.site_digit_after_decimal_point,
                            branch.currency_id?.symbol, setting.site_currency_position) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="w-full p-2 shadow-md z-50 border-t">
            <!-- Two Column Button Grid -->
            <div class="grid grid-cols-2 gap-2" v-if="carts.length > 0 && checkoutProps.form.id == null">
                <button @click.prevent="resetCart"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#FB4E4E]">
                    {{ $t('button.cancel') }}
                </button>
                <button v-if="branch.show_paid_order_button == statusEnum.ACTIVE" @click.prevent="orderSubmit"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#1AB759]">
                    {{ $t('button.order') }}
                </button>
                <button v-if="branch.show_suspense_button == statusEnum.ACTIVE" @click.prevent="saveDraft"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-orange-500">
                    {{ $t('button.suspend') }}
                </button>
                <button @click.prevent="orderUnpaid" v-if="branch.show_unpaid_button == statusEnum.ACTIVE"
                    :disabled="isOrderUnpaidProcessing || loading.isActive"
                    :class="{ 'opacity-50 cursor-not-allowed': isOrderUnpaidProcessing || loading.isActive }"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 px-2 text-white bg-primary">
                    <span v-if="isOrderUnpaidProcessing">{{ $t('label.processing') }}...</span>
                    <span v-else>{{ $t('button.unpaid') }}</span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2" v-if="carts.length > 0 && checkoutProps.form.id > 0">
                <button @click.prevent="resetCart"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 text-white bg-[#FB4E4E]">
                    {{ $t('button.cancel') }}
                </button>
                <button @click.prevent="orderUnpaid" v-if="branch.show_unpaid_button == statusEnum.ACTIVE"
                    :disabled="isOrderUnpaidProcessing || loading.isActive"
                    :class="{ 'opacity-50 cursor-not-allowed': isOrderUnpaidProcessing || loading.isActive }"
                    class="capitalize text-sm font-medium leading-2 font-rubik w-full text-center rounded-md py-2 px-2 text-white bg-primary">
                    <span v-if="isOrderUnpaidProcessing">{{ $t('label.processing') }}...</span>
                    <span v-else>{{ $t('button.unpaid') }}</span>
                </button>
            </div>
        </div>
    </div>
    <button
        class="db-pos-cartBtn fixed md:hidden bottom-0 z-10 left-0 w-full h-14 py-4 text-center flex items-center justify-center shadow-xl-top gap-3 bg-primary">
        <i class="lab lab-bag-2 lab-font-size-13 text-white"></i>
        <span class="text-base font-medium font-rubik text-white">
            {{ totalItems() }} {{ $t('label.items') }} -
            {{ currencyFormat(subtotal - posDiscount, setting.site_digit_after_decimal_point,
            branch.currency_id?.symbol, setting.site_currency_position) }}
        </span>
    </button>
    <ReceiptComponent v-if="showUnpaidReceiptModal && lastOrder && Object.keys(lastOrder).length > 0" :order="lastOrder"
        :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :isNewOrder="true" :modalId="'unpaidReceiptModal'"
        :isAutoPrint="true" @modal-closed="hideReceiptModals" />
    <ReceiptComponent
        v-if="checkoutProps.form.id > 0 && showLastOrderReceiptModal && lastOrder && Object.keys(lastOrder).length > 0"
        :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :isPrintLastOrderOnly="true"
        :isNewOrder="true" :modalId="'lastOrderReceiptModal'" :isAutoPrint="true" @modal-closed="hideReceiptModals" />
    <ReceiptComponent
        v-if="branch.show_paid_order_button == statusEnum.ACTIVE && showPaidReceiptModal && lastOrder && Object.keys(lastOrder).length > 0"
        :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :isNewOrder="true"
        :modalId="'paidReceiptModal'" :isAutoPrint="true" @modal-closed="hideReceiptModals" />
    <!--====================================
      PAYMENT MODAL PART START
  =====================================-->
    <PaymentComponent :props="checkoutProps" :isNewOrder="true" @orderPaid="onOrderPaid" />
    <!--====================================
          PAYMENT MODAL PART END
      =====================================-->
    <DraftModalComponent :itemDraft="itemDraft" :itemIndex="itemIndex" />
    <CustomItemModal :show="showCustomItemModal" :cartItem="editingCartItem" :cartIndex="editingCartIndex"
        @close="closeCustomItemModal" @save="handleCustomItemSave" />
</template>
<script>
import SmIconSidebarModalEditComponent from '../components/buttons/SmIconSidebarModalEditComponent';
import LoadingComponent from '../components/LoadingComponent';
import ItemComponent from './ItemComponent';
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
// import SelectTableComponent from './SelectTableComponent.vue';
import PaymentComponent from './PaymentComponent';
import CustomItemModal from './CustomItemModal.vue';
import OrderTypeSelectorComponent from '../components/OrderTypeSelectorComponent.vue';
import TableSelectionComponent from '../components/TableSelectionComponent.vue';
import KeyboardShortcutsComponent from '../components/KeyboardShortcutsComponent.vue';
import Keyboard from 'vue-material-design-icons/Keyboard.vue';

import 'swiper/css';
import { useRoute } from 'vue-router';
import MemberSelectComponent from '../components/MemberSelectComponent.vue';
import OrderBasicInfoComponent from '../components/OrderBasicInfoComponent.vue';
export default {
    name: 'PosComponent',
    components: {
        ReceiptComponent,
        LoadingComponent,
        ItemComponent,
        PosCustomerComponent,
        MemberSelectComponent,
        Swiper,
        SwiperSlide,
        SmIconSidebarModalEditComponent,
        DraftModalComponent,
        PaymentComponent,
        // SelectTableComponent,
        OrderTypeSelectorComponent,
        TableSelectionComponent,
        OrderBasicInfoComponent,
        KeyboardShortcutsComponent,
        CustomItemModal,
        Keyboard,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            isOrderUnpaidProcessing: false,
            order: {},

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
            showUnpaidReceiptModal: false,
            showLastOrderReceiptModal: false,
            showPaidReceiptModal: false,
            selectedMember: null,
            showCustomItemModal: false,
            editingCartIndex: null,
            editingCartItem: null,
            showKeyboardShortcuts: false,
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
                    order_type: orderTypeEnum.DINING_TABLE,
                    is_advance_order: isAdvanceOrderEnum.NO,
                    pos_payment_method: posPaymentMethodEnum.CASH,
                    pos_payment_note: '',
                    payment_method: posPaymentMethodEnum.CASH,
                    source: sourceEnum.POS,
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
                    check_in_time: null,
                    check_out_time: null, 
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
            categoryProps: {
                paginate: 0,
                order_column: 'sort',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
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
            orderTypeEnums: {
                dineIn: orderTypeEnum.DINING_TABLE,
                takeAway: orderTypeEnum.TAKEAWAY,
                pos: orderTypeEnum.POS,
                token: orderTypeEnum.TOKEN,
            },
            route: useRoute(),
            isPosShow: /^\/admin\/pos\/show\/\d+$/,
        };
    },
    computed: {
        // diningTable:function(){
        //     return this.$store.getters['diningTable/lists'];
        // },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        categories: function () {
            return this.$store.getters['posCategory/lists'];
        },
        items: function () {
            // Filter items from store based on search criteria
            let allItems = this.$store.getters['item/lists'];
            let filteredItems = Array.isArray(allItems) ? allItems : [];

            // Filter by name, item_code, or barcode (search query)
            if (this.props.search.name && this.props.search.name.trim()) {
                const searchTerm = this.props.search.name.toLowerCase().trim();
                filteredItems = filteredItems.filter(item => {
                    const name = item.name ? item.name.toLowerCase() : '';
                    const itemCode = item.item_code ? item.item_code.toLowerCase() : '';
                    const barcode = item.barcode ? item.barcode.toLowerCase() : '';
                    return name.includes(searchTerm) || itemCode.includes(searchTerm) || barcode.includes(searchTerm);
                });
            }

            // Filter by category
            if (this.props.search.item_category_id && this.props.search.item_category_id !== '') {
                filteredItems = filteredItems.filter(item =>
                    item.item_category_id == this.props.search.item_category_id
                );
            }

            return filteredItems;
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
        stockRecords: function () {
            return this.$store.getters['stockRecord/lists'];
        },
        paymentMethods: function () {
            return this.$store.getters['backendGlobalState/paymentMethods'];
        },
        // diningtables: function () {
        //     return this.$store.getters['diningTable/lists'];
        // },
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
            immediate: true
        },
        '$route': {
            handler(newRoute) {
                // Sync current route for CustomerView display mode detection
                posCartSyncService.syncCurrentRoute(newRoute.path);
            },
            immediate: true
        }
    },
    async mounted() {

        this.$nextTick(() => {
            if (!document?.querySelector(".db-sidebar")?.classList?.contains("active")) {
            document?.querySelector(".db-sidebar")?.classList?.add("active");
            document?.querySelector(".db-main")?.classList?.add("expand");

            const headerNav = document?.querySelector(".db-header-nav");
            if (headerNav) {
                headerNav.classList.remove("fa-align-left");
                headerNav.classList.add("fa-bars");
            }
            }
        });
        

        // const headerNavBtn = document.querySelector('.db-header-nav.active');
        // if (!headerNavBtn) {
        //     document.querySelector('.db-header-nav').click();
        // }

        // Set check_in_time when page mounts (for new orders)
        if (!this.$route.params.id) {
            this.checkoutProps.form.check_in_time = new Date().toISOString();
        }
        // Check for table_id parameter to pre-select table for dine-in order
        if (this.$route.query.table_id) {
            this.checkoutProps.form.order_type = orderTypeEnum.DINING_TABLE;
            this.checkoutProps.form.order_dinings = [{ id: parseInt(this.$route.query.table_id) }];
        }

        posCartSyncService.initCartSync(this.$store);
        posCartSyncService.syncCurrentRoute(this.$route.path);

        // Parallelize independent API calls to reduce load time
        this.loading.isActive = true;
        try {
            const promises = [
                this.$store.dispatch('backendGlobalState/paymentMethods').catch(() => {}),
                this.$store.dispatch('branch/lists', {
                    order_column: 'id',
                    order_type: 'asc',
                }).catch(() => {}),
                this.$store.dispatch('diningTable/lists', {
                    order_column: 'id',
                    order_type: 'asc',
                    status: statusEnum.ACTIVE,
                }).catch(() => {}),
                this.itemCategories(),
                this.itemList(),
                this.stockRecordLists(),
            ];
            await Promise.all(promises);

            // Fetch default access to get branch_id
            const defaultAccessRes = await this.$store.dispatch('defaultAccess/show').catch(() => {
                this.loading.isActive = false;
                return null;
            });
            if (defaultAccessRes) {
                this.checkoutProps.form.branch_id = defaultAccessRes.data.data.branch_id;
                const getBusinessDate = this.branches.filter((item) => item.id === this.checkoutProps.form.branch_id);
                this.checkoutProps.form.business_date = getBusinessDate[0]?.current_business_day;
            }

            // Parallelize customer and company data (less critical)
            const secondaryPromises = [
                this.customerList(),
                this.$store.dispatch('company/lists').then((res) => {
                    this.company.name = res.data.data.company_name;
                    this.company.email = res.data.data.company_email;
                    this.company.phone = res.data.data.company_phone;
                    this.company.address = res.data.data.company_address;
                }).catch(() => {}),
            ];
            await Promise.all(secondaryPromises);

            this.loading.isActive = false;
        } catch (err) {
            this.loading.isActive = false;
        }

        // Load existing order into cart (e.g., navigated from sub-session checkout)
        if (this.$route.params.id) {
            this.loadOrderIntoCart(this.$route.params.id);
        }
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        // updateDiningTables() {
        //     // Fetch latest table status without loading indicator
        //     this.$store.dispatch('diningTable/lists', {
        //         order_column: "id",
        //         order_type: "asc",
        //         status: statusEnum.ACTIVE,
        //     });
        // },

        goToOrderDetail: function (orderId) {
            this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
        },
        loadOrderIntoCart: function (orderId) {
            this.loading.isActive = true;
            // Reset cart before loading order to prevent item quantity accumulation
            // when the user navigates back and triggers checkout again.
            this.$store.dispatch('posCart/resetCart');
            this.$store.dispatch('posOrder/show', orderId)
                .then((res) => {
                    const order = res.data.data;
                    // Set the order metadata for display (OrderBasicInfoComponent)
                    this.$store.dispatch('posCart/setOrder', order);

                    // Resolve item details from local store
                    const storeItems = this.$store.getters['item/lists'] || [];

                    // Map order_items to posCart format
                    // item_variations / item_extras come as JSON strings "[]" from the API — parse safely
                    const parseJsonField = (val) => {
                        if (!val || val === '[]' || val === '{}') return null;
                        try { return JSON.parse(val); } catch { return null; }
                    };

                    const cartItems = (order.order_items || []).map(oi => {
                        const storeItem = storeItems.find(i => i.id === oi.item_id);

                        // Build variation / extra objects from parsed JSON
                        const parsedVariations = parseJsonField(oi.item_variations);
                        const parsedExtras     = parseJsonField(oi.item_extras);

                        const itemVariations = parsedVariations?.length
                            ? { variations: Object.fromEntries(parsedVariations.map(v => [v.item_attribute_id, v.id])), names: Object.fromEntries(parsedVariations.map(v => [v.variation_name, v.name])) }
                            : { variations: {}, names: {} };

                        const itemExtras = parsedExtras?.length
                            ? { extras: parsedExtras.map(e => e.id), names: parsedExtras.map(e => e.name) }
                            : { extras: [], names: [] };

                        return {
                            item_id:               oi.item_id,
                            convert_price:         parseFloat(oi.price) || 0,
                            currency_price:        parseFloat(oi.price) || 0,
                            quantity:              oi.quantity,
                            discount:              parseFloat(oi.discount) || 0,
                            discount_percentage:   parseFloat(oi.discount_percentage) || 0,
                            item_variation_total:  parseFloat(oi.item_variation_total) || 0,
                            item_extra_total:      parseFloat(oi.item_extra_total) || 0,
                            item_variations:       itemVariations,
                            item_extras:           itemExtras,
                            name:                  storeItem?.name || oi.order_item_custom_name || '',
                            image:                 storeItem?.thumb || '',
                            instruction:           oi.instruction || '',
                            price_with_tax:        parseFloat(storeItem?.price_with_tax || oi.price) || 0,
                            tax_amount:            parseFloat(storeItem?.tax_amount || oi.tax_amount) || 0,
                            order_item_custom_name: oi.order_item_custom_name || null,
                            can_input_custom_name:  storeItem?.can_input_custom_name || null,
                        };
                    });

                    this.$store.dispatch('posCart/lists', cartItems);
                    posCartSyncService.syncCartData(this.$store);

                    // Pre-populate checkout form from the saved order
                    this.checkoutProps.form.subtotal              = parseFloat(order.subtotal)  || 0;
                    this.checkoutProps.form.total_tax             = parseFloat(order.total_tax) || 0;
                    this.checkoutProps.form.total                 = parseFloat(order.total)     || 0;
                    this.checkoutProps.form.customer_name         = order.customer_name         || '';
                    this.checkoutProps.form.customer_phone_number = order.customer_phone_number || '';
                    this.checkoutProps.form.order_type            = order.order_type            ?? this.checkoutProps.form.order_type;
                    this.checkoutProps.form.source                = order.source                ?? this.checkoutProps.form.source;
                    this.checkoutProps.form.currency              = order.currency              || null;
                    this.checkoutProps.form.currency_id           = order.currency_id           || null;
                    this.checkoutProps.form.business_date         = order.business_date         || null;
                    this.checkoutProps.form.check_in_time         = order.check_in_time         || null;
                    this.checkoutProps.form.payment_status        = order.payment_status        ?? this.checkoutProps.form.payment_status;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message);
                });
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
        // resetDinIngTable: function () {
        //     this.$refs?.selectTable?.clearSelectedTables();
        // },
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
            // No API call needed - filtering is done locally in computed property
        },
        customerList: function (id = null) {
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
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        allCategory: function () {
            this.props.search.name = '';
            this.props.search.item_category_id = '';
            // No API call needed - filtering is done locally in computed property
        },
        itemCategories: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch('posCategory/lists', this.categoryProps)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        itemList: function (page = 1) {
            // Load all items without search criteria for local filtering
            this.loading.isActive = true;
            const searchParams = {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
                name: '',
                item_category_id: '',
                status: statusEnum.ACTIVE,
            };

            this.$store
                .dispatch('item/lists', searchParams)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        setCategory: function (id) {
            this.props.search.item_category_id = id;
            // No API call needed - filtering is done locally in computed property
        },
        cartQuantityUp: function (id, e) {
            if (e.target.value > 0) {
                this.$store
                    .dispatch('posCart/quantity', {
                        id: id,
                        status: e.target.value,
                    })
                    .then(() => {
                        posCartSyncService.syncCartData(this.$store);
                    })
                    .catch();
            }
        },
        cartQuantityIncrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: 'increment' }).then(() => {
                // Sync cart data to localStorage and dispatch events
                posCartSyncService.syncCartData(this.$store);
            }).catch();
        },
        cartQuantityDecrement: function (id) {
            this.$store.dispatch('posCart/quantity', { id: id, status: 'decrement' }).then(() => {
                // Sync cart data to localStorage and dispatch events
                posCartSyncService.syncCartData(this.$store);
            }).catch();
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
        openCustomNameModal: function (index, cart) {
            this.editingCartIndex = index;
            this.editingCartItem = cart;
            this.showCustomItemModal = true;
        },
        closeCustomItemModal: function () {
            this.showCustomItemModal = false;
            this.editingCartIndex = null;
            this.editingCartItem = null;
        },
        handleCustomItemSave: function (data) {
            this.$store.dispatch('posCart/updateCustomItem', {
                index: data.index,
                customName: data.customName,
                quantity: data.quantity,
                unitPrice: data.unitPrice,
                discount: data.discount,
                discountPercentage: data.discountPercentage
            }).then(() => {
                posCartSyncService.syncCartData(this.$store);
            }).catch((err) => {
                alertService.error(err.response.data.message);
            });
        },
        applyDiscount: function () {
            this.discountErrorMessage = '';
            // let totalWithTax = this.subtotal + this.totalTax;
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.subtotal < this.discount) {
                    this.discountErrorMessage = this.$t('message.discount_fixed_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat(+this.discount).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then(() => {
                        // Sync cart data to localStorage and dispatch events
                        posCartSyncService.syncCartData(this.$store);
                    }).catch();
                }
            } else {
                if (this.discount > 100) {
                    this.discountErrorMessage = this.$t('message.discount_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat((this.subtotal * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    // this.checkoutProps.form.discount = parseFloat((totalWithTax * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store.dispatch('posCart/discount', this.checkoutProps.form.discount).then(() => {
                        // Sync cart data to localStorage and dispatch events
                        posCartSyncService.syncCartData(this.$store);
                    }).catch();
                }
            }
        },
        resetCart: function () {

            appService.confirmDialog(
                this.$t('message.reset_cart_title'),
                this.$t('message.reset_cart_confirm_message'),
                "warning",
                this.$t('label.yes'),
                this.$t('label.no')
            ).then(() => {
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
                        // Hide receipt modals
                        this.showUnpaidReceiptModal = false;
                        this.showLastOrderReceiptModal = false;
                        this.showPaidReceiptModal = false;
                        this.lastOrder = {}; 
                        // Clear cart data from localStorage and notify other components
                        posCartSyncService.clearCartData();

                        this.$router.push({ name: 'admin.pos' });
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                    });
            }).catch(() => {
                // User clicked cancel, do nothing
            });
        },
        orderSubmit: function () {
            if (this.branch.create_paid_order_confirm == this.statusEnum.ACTIVE) {
                appService.confirmDialog(
                    this.$t('message.create_paid_order_title'),
                    this.$t('message.create_paid_order_confirm_message'),
                    "warning",
                    this.$t('label.yes'),
                    this.$t('label.no')
                ).then(() => {
                    this.processOrderSubmit();
                }).catch(() => {
                    // User cancelled, do nothing
                });
            } else {
                this.processOrderSubmit();
            }
        },
        processOrderSubmit: function () {
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
                    discount_percentage: item.discount_percentage || 0,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras,
                    order_item_custom_name: item.order_item_custom_name || null,
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            this.loading.isActive = false;
            appService.modalShow('#orderpayment');
        },
        
        orderUnpaid: function () {
            // Prevent duplicate clicks
            if (this.isOrderUnpaidProcessing || this.loading.isActive) {
                return;
            }

            if (this.branch.create_unpaid_order_confirm == this.statusEnum.ACTIVE) {
                appService.confirmDialog(
                    this.$t('message.create_unpaid_order_title'),
                    this.$t('message.create_unpaid_order_confirm_message'),
                    "warning",
                    this.$t('label.yes'),
                    this.$t('label.no')
                ).then(() => {
                    this.processOrderUnpaid();
                }).catch(() => {
                    // User cancelled, do nothing
                });
            } else {
                this.processOrderUnpaid();
            }
        },
        processOrderUnpaid: function () {
            // Set processing flag to prevent duplicate submissions
            this.isOrderUnpaidProcessing = true;
            this.loading.isActive = true;

            this.checkoutProps.form.subtotal = this.subtotal;
            // this.checkoutProps.form.total = parseFloat(this.subtotal - this.checkoutProps.form.discount).toFixed(this.setting.site_digit_after_decimal_point);

            this.checkoutProps.form.total_tax = this.totalTax;
            this.checkoutProps.form.discount_percentage = this.posDiscountPercentage;
            // this.checkoutProps.form.total = parseFloat(this.subtotal - this.checkoutProps.form.discount).toFixed(this.setting.site_digit_after_decimal_point);
            this.checkoutProps.form.total = parseFloat(this.subtotal - this.posDiscount).toFixed(this.setting.site_digit_after_decimal_point); //Total with discount is not saving in system

            this.checkoutProps.form.items = [];
            this.checkoutProps.form.pos_payment_note = this.checkoutProps.form.pos_payment_method === posPaymentMethodEnum.CASH ? null : this.checkoutProps.form.pos_payment_note;
            this.checkoutProps.form.pos_payment_method = this.posPaymentMethodEnum.CASH;

            this.checkoutProps.form.payment_status = paymentStatusEnum.UNPAID;

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
                    discount_percentage: item.discount_percentage || 0,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras,
                    order_item_custom_name: item.order_item_custom_name || null,
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);
            this.checkoutProps.form.payment_status = paymentStatusEnum.UNPAID;
            this.checkoutProps.form.currency = this.branch.currency_id?.code;
            this.checkoutProps.form.currency_id = this.branch.currency_id?.id;
            // Default receive_payment_currency to same as order currency for unpaid orders
            this.checkoutProps.form.receive_payment_currency = this.branch.currency_id?.code;
            this.checkoutProps.form.receive_payment_currency_id = this.branch.currency_id?.id;

            this.$store
                .dispatch('defaultAccess/show')
                .then((res) => {
                    this.checkoutProps.form.branch_id = res.data.data.branch_id;
                    this.$store
                        .dispatch('posOrder/save', this.checkoutProps.form)
                        .then((orderResponse) => { 
                            this.lastOrder = orderResponse.data.data; 
                            this.checkoutProps.form.token = '';
                            this.checkoutProps.form.subtotal = null;
                            this.checkoutProps.form.discount = 0;
                            this.checkoutProps.form.delivery_time = null;
                            this.checkoutProps.form.total = 0;
                            this.checkoutProps.form.order_type = orderTypeEnum.POS;
                            this.checkoutProps.form.is_advance_order = isAdvanceOrderEnum.NO;
                            this.checkoutProps.form.source = sourceEnum.POS;
                            this.checkoutProps.form.address_id = null;
                            this.checkoutProps.form.items = [];
                            this.discount = null;
                            this.discountType = discountTypeEnum.PERCENTAGE;
                            this.checkoutProps.form.pos_payment_method = this.posPaymentMethodEnum.CASH;
                            this.checkoutProps.form.pos_payment_note = '';
                            this.checkoutProps.form.order_type = orderTypeEnum.DINING_TABLE;
                            this.checkoutProps.form.order_dinings = null;
                            this.checkoutProps.form.currency = null;
                            this.checkoutProps.form.currency_id = null;
                            this.checkoutProps.form.receive_payment_currency = null;
                            this.checkoutProps.form.receive_payment_currency_id = null;
                            this.checkoutProps.form.number_of_people = 1;
                            this.checkoutProps.form.order_note = '';

                            // this.resetDinIngTable();
                            this.$store
                                .dispatch('posCart/resetCart')
                                .then((res) => {
                                    posCartSyncService.clearCartData();

                                    // Update tables without refresh
                                    // this.updateDiningTables();

                                    this.loading.isActive = false;
                                    this.isOrderUnpaidProcessing = false;

                                    // Show receipt modal after tables are updated
                                    if (this.$route.params.id) {
                                        this.showLastOrderReceiptModal = true;
                                        this.$nextTick(() => {
                                            appService.modalShow('#lastOrderReceiptModal');
                                        });
                                    } else {
                                        this.showUnpaidReceiptModal = true;
                                        this.$nextTick(() => {
                                            appService.modalShow('#unpaidReceiptModal');
                                        });
                                    }
                                })
                        })
                        .catch((err) => {
                            console.log('ERROR:', err);
                            this.loading.isActive = false;
                            this.isOrderUnpaidProcessing = false;
                            if (typeof err.response.data.errors === 'object') {
                                _.forEach(err.response.data.errors, (error) => {
                                    alertService.error(error[0]);
                                });
                            }
                        });
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.isOrderUnpaidProcessing = false;
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
        onOrderPaid: function (orderResponse) {
            // Set check_out_time when order is paid
            this.checkoutProps.form.check_out_time = new Date().toISOString();

            // Handle successful payment - show receipt with auto-print enabled
            this.lastOrder = orderResponse.data.data;
            this.showPaidReceiptModal = true;
            this.$nextTick(() => {
                appService.modalShow('#paidReceiptModal');
            });
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
        hideReceiptModals: function () {
            this.showUnpaidReceiptModal = false;
            this.showLastOrderReceiptModal = false;
            this.showPaidReceiptModal = false;
        },
        stockRecordLists: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;

            this.$store
                .dispatch('stockRecord/lists', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        getBranches: function () {
            return this.$store.dispatch('branch/lists');
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
        showTable: function () {
            appService.modalShow('#diningtableModal');
        },
    },
};
</script>

<style scoped>
/* Number shortcut buttons */
.number-shortcut-btn {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    user-select: none;
}

.number-shortcut-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.number-shortcut-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.number-shortcut-btn:focus {
    outline: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.3);
}
</style>
