<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 mt-2">
        <div class="db-card p-3">
            <div class="flex flex-wrap gap-3 justify-end">

                <button v-if="order && order.group_session_id"
                    type="button"
                    class="db-btn h-[37px] m-0.5 text-white bg-indigo-600"
                    @click="$router.push({ name: 'admin.group-session.detail', params: { id: order.group_session_id } })">
                    <i class="lab lab-arrow-left lab-font-size-16"></i>
                    {{ $t('button.back_to_group_session') }}
                </button>

                <div v-if="order && order.payment_status !== enums.paymentStatusEnum.PAID">
                    <button v-if="permissionChecker('make_payment')" type="button" class="db-btn h-[37px] m-0.5 text-white bg-[#1AB759]" @click="makeOrderPayment">
                        <i class="lab lab-currencies lab-font-size-16"></i>
                        {{ $t('button.make_payment') }}
                    </button>
                </div>
                <SmButtonComponent v-if="order.payment_status == enums.paymentStatusEnum.UNPAID && !order?.group_session_id" @click="goToAddOrder" :span="$t('button.add_order')" icon="lab lab-plus lab-font-size-16 text-white" />

                <SmButtonDefaultComponent  @click="printBill()" data-modal="modal" icon="lab lab-printer-line lab-font-size-16" :span="$t('button.print_bill')" />
                <SmButtonDefaultComponent  @click="printInvoice()" data-modal="modal" icon="lab lab-printer-line lab-font-size-16" :span="$t('button.print_invoice')" />
                <SmButtonDefaultComponent  v-if="branch.show_print_label_button == statusEnum.ACTIVE && !order?.group_session_id" @click="printLabel()" data-modal="modal" icon="lab lab-printer-line lab-font-size-16" :span="$t('button.print_label')" />

                <SmButtonComponent v-if="order.payment_status === enums.paymentStatusEnum.UNPAID && order.source === sourceEnum.ONLINE_ORDER"  @click="checkPaymentOrderStatus" :span="$t('button.check_payment')" icon="lab lab-check text-white" />
                <SmButtonDefaultComponent v-if="branch.show_select_member == statusEnum.ACTIVE && !this.order.member_id && order.payment_status !== enums.paymentStatusEnum.PAID && !order?.group_session_id"  @click="searchAndSelectMember" :span="$t('button.search_member')" icon="lab lab-search-normal" />

                <SmButtonComponent v-if="branch.show_select_member == statusEnum.ACTIVE && this.order.member_id && order.payment_status !== enums.paymentStatusEnum.PAID"  @click="removeMember" class="flex items-center justify-center gap-1.5 px-3 h-10 rounded-lg text-red-500 bg-[#FDEDED]" :span="$t('button.remove_member')" icon="lab lab-close" />
                <div class="relative" v-if="branch.change_status_paid_to_unpaid == statusEnum.ACTIVE && order.payment_status !== enums.paymentStatusEnum.UNPAID && permissionChecker('make_payment')">
                    <select v-model="payment_status" @change="changePaymentStatus($event)" class="db-btn h-[37px] text-white bg-primary m-0.5">
                        <option v-for="paymentStatus in enums.paymentStatusObject" :key="paymentStatus.value" :value="paymentStatus.value">
                            {{ paymentStatus.name }}
                        </option>
                    </select>
                    <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                </div>
               <div class="relative" v-if="branch.change_status_paid_to_unpaid == statusEnum.ACTIVE && !order?.group_session_id">
                    <select v-model="order_status" @change.prevent="orderStatus($event)" class="text-sm capitalize appearance-none gap-2 px-4 h-[38px] rounded border border-primary bg-white text-primary">
                        <option v-for="orderStatus in orderStatusOptions" :value="orderStatus.status_code" :key="orderStatus.id">
                            {{ orderStatus.name }}
                        </option>
                    </select>
                    <i class="lab lab-arrow-down-2 lab-font-size-16 absolute top-1/2 right-3.5 -translate-y-1/2 text-primary"></i>
                </div>

                <SmButtonDefaultComponent v-if="((branch.show_delete_order_button == statusEnum.ACTIVE && permissionChecker('pos-orders') && !order?.group_session_id)) && branch.id" @click="destroyOrder(order.id)" data-modal="modal" icon="lab lab-delete text-danger" :span="$t('button.void_order')" /> 
                <SmButtonDefaultComponent v-if="order.payment_status === enums.paymentStatusEnum.UNPAID && !order?.group_session_id" @click="openTransferModal" data-modal="modal" icon="lab lab-send-2 lab-font-size-16" :span="$t('button.transfer_items')" /> 
            </div>
        </div>

    </div>

    <div class="col-12 sm:col-7">
        <div class="row">
            <div v-if="false" class="col-12">
                <div class="db-card">
                    <div class="db-card-header">
                        <h3 class="db-card-title">{{ $t('label.delivery_information') }}</h3>
                    </div>
                    <div class="db-card-body">
                        <div class="flex items-center gap-3 mb-4">
                            <img class="w-8 rounded-full" :src="orderUser.image" alt="avatar" />
                            <h4 class="font-semibold text-sm capitalize text-[#374151]">
                                {{ textShortener(orderUser.name, 20) }}
                            </h4>
                        </div>
                        <ul class="flex flex-col gap-3 py-4 mb-4 border-y border-[#EFF0F6]">
                            <li class="flex items-center gap-2.5">
                                <i class="lab lab-mail lab-font-size-14"></i>
                                <span class="text-xs">{{ orderUser.email }}</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <i class="lab lab-call-calling-linear lab-font-size-14"></i>
                                <span class="text-xs">{{ orderUser.country_code + '' + orderUser.phone }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- order items -->
            <div class="col-12">
                <div class="mb-3" v-for="order_items in groupByOrderTime">
                    <div class="db-card" v-if="groupByOrderTime">
                        <div class="db-card-header py-3">
                            <h3 class="db-card-title">
                                {{ $t('label.order_items') }} <span v-if="groupByOrderTime.length > 1">( {{ $t('label.sub_order') }} #{{ order_items.order_times }} )</span>
                            </h3>
                        </div>
                        <div class="db-card-body p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.number_sequence') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.photo') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.item_name') }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.qty') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.unit_price') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.amount') }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-heading uppercase tracking-wider" v-if="order.payment_status === enums.paymentStatusEnum.UNPAID">{{ $t('label.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(items, index) in order_items.items" :key="items.item.id" class="hover:bg-gray-50">

                                            <td class="px-4 py-3 text-sm text-heading">{{ index + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <img class="w-12 h-12 rounded-lg object-cover" :src="items.item.item_image" alt="thumbnail" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="space-y-1">
                                                    <p class="text-sm font-medium text-heading">{{ items.item.item_name }}</p>
                                                    <p v-if="items.item.item_variations.length !== 0" class="text-xs text-gray-600">
                                                        <span v-for="(variation, index) in items.item.item_variations" :key="index">
                                                            {{ variation.variation_name }}: {{ variation.name }}<span v-if="index + 1 < items.item.item_variations.length">, </span>
                                                        </span>
                                                    </p>
                                                    <p v-if="items.item.item_extras.length > 0" class="text-xs text-gray-600">
                                                        <span class="font-medium">{{ $t('label.extras') }}:</span>
                                                        <span v-for="(extra, index) in items.item.item_extras" :key="index">
                                                            {{ extra.name }}<span v-if="index + 1 < items.item.item_extras.length">, </span>
                                                        </span>
                                                    </p>
                                                    <p v-if="items.item.instruction !== ''" class="text-xs text-gray-600">
                                                        <span class="font-medium">{{ $t('label.instruction') }}:</span> {{ items.item.instruction }}
                                                    </p>
                                                    <p v-if="items.item.discount > 0" class="text-xs text-red-500">
                                                        {{ $t('label.discount') }}: {{ currencyFormat(items.item.discount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                                                        <span v-if="items.item.discount_percentage > 0">({{ items.item.discount_percentage }}%)</span>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white text-sm font-semibold">
                                                    {{ items.item.quantity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-heading">
                                                {{ currencyFormat(items.item.price, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-semibold text-heading">
                                                {{ items.item.total_currency_price }}
                                            </td>
                                            <td class="px-4 py-3" v-if="order.payment_status === enums.paymentStatusEnum.UNPAID">
                                                <div class="flex items-center justify-center gap-2">
                                                    <SmIconModalEditComponent @click="viewOrderItem(items.item)" v-if="orderItems.length >= 1"/>
                                                    <SmIconDeleteComponent v-if="orderItems.length > 1 && permissionChecker('delete_order')" @click="destroy(items.item.id, items)" />
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 mt-5">
                    <SmButtonComponent v-if="order.payment_status == enums.paymentStatusEnum.UNPAID" @click="goToAddOrder" :span="$t('button.add_order')" icon="lab lab-plus lab-font-size-16 text-white" />
                </div>
            </div>
            <!-- end -->
            <!-- order items deleted-->
            <!-- Show checkbox only if there are more than 1 deleted items -->
            <div v-if="order?.order_item_deleted && order.order_item_deleted.length > 0" class="col-12 mb-3">
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="showDeletedItems"
                        v-model="showItemDeleted"
                        class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"
                    >
                    <label for="showDeletedItems" class="text-sm font-medium text-heading">
                        {{ $t('label.show_deleted_items') }} ({{ order.order_item_deleted.length }})
                    </label>
                </div>
            </div>
            <div v-if="showItemDeleted == true" class="col-12">
                <div class="mb-3">
                    <div class="db-card">
                        <div class="db-card-header py-3">
                            <h3 class="db-card-title">
                                {{ $t('label.item_order_deleted') }}
                            </h3>
                        </div>
                        <div class="db-card-body p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.number_sequence') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.photo') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.item_name') }}</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.qty') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.unit_price') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-heading uppercase tracking-wider">{{ $t('label.amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(item, index) in order?.order_item_deleted" :key="item" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-heading">{{ index + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <img class="w-12 h-12 rounded-lg object-cover" :src="item.item_image" alt="thumbnail" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="space-y-1">
                                                    <p class="text-sm font-medium text-heading">{{ item.item_name }}</p>
                                                    <p v-if="item.item_variations && item.item_variations !== '[]' && item.item_variations.length > 0" class="text-xs text-gray-600">
                                                        <span v-for="(variation, index) in item.item_variations" :key="index">
                                                            {{ variation.variation_name }}: {{ variation.name }}<span v-if="index + 1 < item.item_variations.length">, </span>
                                                        </span>
                                                    </p>
                                                    <p v-if="item.item_extras && item.item_extras !== '[]' && item.item_extras.length > 0" class="text-xs text-gray-600">
                                                        <span class="font-medium">{{ $t('label.extras') }}:</span>
                                                        <span v-for="(extra, index) in item.item_extras" :key="index">
                                                            {{ extra.name }}<span v-if="index + 1 < item.item_extras.length">, </span>
                                                        </span>
                                                    </p>
                                                    <p v-if="item.instruction && item.instruction !== ''" class="text-xs text-gray-600">
                                                        <span class="font-medium">{{ $t('label.instruction') }}:</span> {{ item.instruction }}
                                                    </p>
                                                    <p v-if="item.discount > 0" class="text-xs text-red-500">
                                                        {{ $t('label.discount') }}: {{ currencyFormat(item.discount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                                                        <span v-if="item.discount_percentage > 0">({{ item.discount_percentage }}%)</span>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white text-sm font-semibold">
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-heading">
                                                {{ currencyFormat(item.total_price, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-semibold text-heading">
                                                {{ item.total_currency_price }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end -->
        </div>
    </div>
    <MemberSelectComponent v-on:onMemberSelect="onMemberSelect" v-on:onMemberCreate="onMemberCreate" />

    <div class="col-12 sm:col-5">
        <div class="row">
            <div class="col-12">
                <div class="db-card p-5">
                    <OrderBasicInfoComponent
                        :order="order"
                        @updated="getOrderDetails"
                    />
                </div>
            </div>

            <div class="col-12" v-if="order && order.member">
                <div class="db-card p-5">
                    <MemberInformationComponent :member="order.member" size="normal" />
                </div>
            </div>

            <!-- Customer Information Section -->
            <div class="col-12" v-if="order && (order.customer_name || order.customer_phone_number || order.customer_address)">
                <div class="db-card p-5">
                    <h3 class="db-card-title mb-3">{{ $t('label.customer_information') }}</h3>
                    <div class="space-y-3">
                        <div v-if="order.customer_name" class="flex items-center gap-3">
                            <i class="lab lab-profile lab-font-size-16 text-gray-500"></i>
                            <div>
                                <span class="text-sm text-gray-500">{{ $t('label.customer_name') }}:</span>
                                <span class="text-sm font-medium text-heading ml-2">{{ order.customer_name }}</span>
                            </div>
                        </div>
                        <div v-if="order.customer_phone_number" class="flex items-center gap-3">
                            <i class="lab lab-call-calling-linear lab-font-size-16 text-gray-500"></i>
                            <div>
                                <span class="text-sm text-gray-500">{{ $t('label.customer_phone_number') }}:</span>
                                <span class="text-sm font-medium text-heading ml-2">{{ order.customer_phone_number }}</span>
                            </div>
                        </div>
                        <div v-if="order.customer_address" class="flex items-start gap-3">
                            <i class="lab lab-location lab-font-size-16 text-gray-500 mt-0.5"></i>
                            <div>
                                <span class="text-sm text-gray-500">{{ $t('label.customer_address') }}:</span>
                                <span class="text-sm font-medium text-heading ml-2">{{ order.customer_address }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12" v-if="order && order.transactions && order.transactions.length > 0">
                <div class="db-card p-5">
                    <h3 class="db-card-title mb-3">{{ $t('label.payment_transactions') }}</h3>
                    <div class="space-y-3">
                        <div v-for="transaction in order.transactions" :key="transaction.id" class="p-3 border border-gray-200 rounded-lg">

                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">

                                    <span class="text-sm text-gray-600">{{ transaction.payment_method }}</span>
                                    <span class="text-xs text-gray-500">{{ transaction.transaction_no }}</span>
                                    <div class="text-xs text-gray-500 ">
                                        <span v-if="transaction.user_name">
                                            {{ $t('label.by') }}: {{ transaction.user_name }}
                                        </span>
                                    </div>


                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-semibold" :class="transaction.sign === '+' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.sign }}{{ transaction.amount }}
                                        </span>
                                        <div class="text-xs text-gray-500 ">
                                            <span class="capitalize">{{ transaction.type }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500 ">{{ transaction?.date }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button
                                    v-if="canRefund(transaction)"
                                    @click="initiateRefund(transaction)"
                                    :disabled="refundLoading"
                                    class="flex items-center gap-1 px-3 py-1 text-xs mt-3 font-medium text-white bg-red-600 rounded hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                >
                                    <i class="lab lab-undo lab-font-size-14"></i>
                                    <span>{{ $t('button.refund') }}</span>
                                </button>
                                <button
                                    v-if="canVoid(transaction)"
                                    @click="initiateVoid(transaction)"
                                    :disabled="voidLoading"
                                    class="flex items-center gap-1 px-3 py-1 text-xs mt-3 font-medium text-white bg-orange-600 rounded hover:bg-orange-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                >
                                    <i class="lab lab-close lab-font-size-14"></i>
                                    <span>{{ $t('button.void') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- start discount -->
            <div class="col-12" v-if="permissionChecker('discount') && order.payment_status === enums.paymentStatusEnum.UNPAID">
                <div class="db-card p-3">
                    <div class="flex h-[38px]">
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
                    <div class="flex h-[38px] gap-2 items-center mt-3" v-if="discountType == discountTypeEnum.PERCENTAGE">
                        <span v-for="percent in [10, 15, 20, 30, 50, 70]" :key="percent"  @click="discount = percent" class="cursor-pointer px-2 py-1 rounded-full text-xs font-semibold border border-primary text-primary bg-primary/10 hover:bg-primary hover:text-white transition"  :class="{ 'bg-primary ': discount == percent }">
                            {{ percent }}%
                        </span>
                    </div>
                </div>
            </div>
            <!--end discount -->
            <div class="col-12">
                <div class="db-card p-1">
                    <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.subtotal') }}</span>
                            <!-- <span class="text-sm leading-6 capitalize">{{ order.subtotal_currency_price }}</span> -->
                            <span class="text-sm leading-6 capitalize">{{ order.subtotal_without_tax_currency_price }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.discount') }} ({{ order.discount_percentage }} %)</span>
                            <span class="text-sm leading-6 capitalize">{{ order.discount_currency_price }}</span>
                        </li>
                        <li class="flex items-center justify-between text-heading">
                            <span class="text-sm leading-6 capitalize">{{ $t('label.vat') }}</span>
                            <span class="text-sm leading-6 capitalize">{{ order.total_tax_currency_price }}</span>
                        </li>
                    </ul>
                    <div class="flex items-center justify-between p-3">
                        <h4 class="text-sm leading-6 font-bold capitalize">{{ $t('label.total') }}</h4>
                        <h5 class="text-sm leading-6 font-bold capitalize">
                            {{ order.total_currency_price }}
                        </h5>
                    </div>
                    <div v-if="order.points_earned > 0" class="flex items-center justify-between p-3">
                        <h4 class="text-sm leading-6 font-bold capitalize">{{ $t('label.points_earned') }}</h4>
                        <h5 class="text-sm leading-6 font-bold capitalize">
                            {{ order.points_earned }}
                        </h5>
                    </div>
                    <div v-if="order.points_redeemed > 0" class="flex items-center justify-between p-3">
                        <h4 class="text-sm leading-6 font-bold capitalize">{{ $t('label.points_redeemed') }}</h4>
                        <h5 class="text-sm leading-6 font-bold capitalize">
                            {{ order.points_redeemed }}
                        </h5>
                    </div>
                </div>
            </div>


            <div class="col-12" v-if="(false && order.payment_status === enums.paymentStatusEnum.UNPAID) || show_payment_method">
                <div class="db-card">
                    <div class="db-card-header">
                        <div class="flex flex-row items-center justify-between w-full">
                            <h3 class="db-card-title">{{ $t('label.payment_method') }}</h3>
                        </div>
                    </div>
                    <div class="db-card-body">
                        <div class="flex flex-col gap-3 p-1 border-[#EFF0F6]">
                            <div class="flex mt-1">
                                <div class="swiper size-swiper">
                                    <div class="size-tabs">
                                        <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16">
                                            <SwiperSlide class="!w-fit" v-for="(item, index) in paymentMethods" :key="index">
                                                <label :class="['variation-margin-right w-full h-[52px] cursor-pointer py-2 px-5 rounded-md gap-2 flex items-center border transition border-[#F7F7FC] bg-[#F7F7FC]', { active: checkoutProps.form.pos_payment_method == item.id }]">
                                                    <input type="radio" class="mr-2 accent-primary" :value="item.id" v-model="checkoutProps.form.pos_payment_method" @change="selectPaymentMethod(item)" />
                                                    <div>
                                                        <h3 class="text-xs">
                                                            {{ item.name }}
                                                        </h3>
                                                    </div>
                                                </label>
                                            </SwiperSlide>
                                        </Swiper>
                                    </div>
                                </div>
                            </div>
                            <div class="flex h-[38px] mt-2 flex-col gap-3 py-4 mb-4 border-y border-[#EFF0F6]">
                                <button type="button" class="flex items-center justify-center gap-3 rounded-lg text-base py-3 px-3 font-medium text-white bg-[#1AB759]" @click="makeOrderPayment">
                                    <span>{{ $t('button.make_payment') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div v-if="order.payment_status === enums.paymentStatusEnum.UNPAID && permissionChecker('make_payment')" class="col-12"> -->
            <!-- <div v-if="order.payment_status === enums.paymentStatusEnum.UNPAID" class="col-12">
                <div class="flex h-[38px] flex-col gap-3 border-y border-[#EFF0F6] mb-10">
                    <button type="button" class="flex items-center justify-center gap-3 rounded-lg text-base py-3 px-3 font-medium text-white bg-[#1AB759]" @click="makeOrderPayment">
                        <span>{{ $t('button.make_payment') }}</span>
                    </button>
                </div>
            </div> -->
        </div>
    </div>
    <!-- <ReceiptPosOrderDetailComponent :order="orderPrinter" :isPrintMenu="false" /> -->
    <CustomItemModal
        :show="showCustomItemModal"
        :cartItem="editOrderItem"
        :cartIndex="editOrderItemIndex"
        @close="closeCustomItemModal"
        @save="handleCustomItemSave"
    />
    <OrderItemTransferModal
        :show="showTransferModal"
        :sourceOrder="order"
        @close="closeTransferModal"
        @confirm="handleTransferConfirm"
    />
    <PaymentComponent :props="checkoutProps" @orderPaid="handleOrderPaid" />

    <ReceiptComponent v-if="order" :order="order" :isPrintMenu="false" :isPrintLabel="false" :isPrintBill="true" :isAutoPrint="false" :autoCloseModalTime="-1" :modalId="'posOrderShow-printBillModal'" />
    <ReceiptComponent v-if="order" :order="order" :isPrintMenu="false" :isPrintLabel="false" :isPrintBill="false" :isAutoPrint="false" :autoCloseModalTime="-1"  :modalId="'posOrderShow-printInvoiceModal'" />
    <ReceiptComponent v-if="order" :order="order" :isPrintLabel="false" :isPrintMenu="false" :isPrintInvoice="false" :isPrintBill="false" :isAutoPrint="false" :autoCloseModalTime="-1" :modalId="'posOrderShow-printLabelModal'" />
    <ReceiptComponent ref="updatePrintComponent" v-if="updatePrintOrder" :order="updatePrintOrder" :isPrintMenu="true" :isPrintLabel="false" :isPrintBill="true" :isPrintUpdate="true" :isAutoPrint="true" :autoCloseModalTime="-1" :modalId="'posOrderShow-updatePrintModal'" />

</template>
<script>

import SmButtonComponent from '../components/buttons/SmButtonComponent';
import SmButtonDefaultComponent from '../components/buttons/SmButtonDefaultComponent';
import LoadingComponent from '../components/LoadingComponent';
import alertService from '../../../services/alertService';
import posCartSyncService from '../../../services/posCartSyncService';
import PaginationTextComponent from '../components/pagination/PaginationTextComponent';
import PaginationBox from '../components/pagination/PaginationBox';
import PaginationSMBox from '../components/pagination/PaginationSMBox';
import appService from '../../../services/appService';
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import sourceEnum from '../../../enums/modules/sourceEnum';
import TableLimitComponent from '../components/TableLimitComponent';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import print from 'vue3-print-nb';
import PosOrderReceiptComponent from './PosOrderReceiptComponent';
import posPaymentMethodEnum from '../../../enums/modules/posPaymentMethodEnum';
import { Swiper, SwiperSlide } from 'swiper/vue';
import ReceiptPosOrderDetailComponent from '../pos/ReceiptPosOrderDetailComponent.vue';
import 'swiper/css';
import SmIconDeleteComponent from '../components/buttons/SmIconDeleteComponent';
import SmIconModalEditComponent from '../components/buttons/SmIconModalEditComponent';
import SmModalEditComponent from '../components/buttons/SmModalEditComponent.vue';
import VueSimpleAlert from 'vue3-simple-alert';
import Collage from 'vue-material-design-icons/Collage.vue';
import CollapseAll from 'vue-material-design-icons/CollapseAll.vue';
import statusEnum from '../../../enums/modules/statusEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
// import SelectTableModalComponent from '../pos/SelectTableModalComponent.vue';
import discountTypeEnum from '../../../enums/modules/discountTypeEnum';
import SelectTableComponent from '../pos/SelectTableComponent.vue';
import CustomItemModal from '../pos/CustomItemModal.vue';
import OrderItemTransferModal from '../pos/OrderItemTransferModal.vue';
import PaymentComponent from '../pos/PaymentComponent.vue';
import MemberInformationComponent from '../components/MemberInformationComponent.vue';
import MemberSelectComponent from '../components/MemberSelectComponent.vue';
import ReceiptComponent from '../pos/ReceiptComponent.vue';
import OrderBasicInfoComponent from '../components/OrderBasicInfoComponent.vue';

export default {
    name: 'PosOrderShowComponent',
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        PosOrderReceiptComponent,
        posPaymentMethodEnum,
        Swiper,
        SwiperSlide,
        SmModalEditComponent,
        ReceiptPosOrderDetailComponent,
        SmIconDeleteComponent,
        VueSimpleAlert,
        Collage,
        CollapseAll,
        // SelectTableModalComponent,
        SelectTableComponent,
        CustomItemModal,
        OrderItemTransferModal,
        PaymentComponent,
        MemberInformationComponent,
        MemberSelectComponent,
        ReceiptComponent,
        OrderBasicInfoComponent,
        SmButtonComponent,
        SmButtonDefaultComponent,
        SmIconModalEditComponent
    },
    directives: {
        print,
    },
    data() {
        return {
            showItemDeleted: false,
            discountType: discountTypeEnum.PERCENTAGE,
            discountErrorMessage: '',
            discount: null,
            orderPrinter: {},
            errors: {},
            loading: {
                isActive: false,
            },
            printLoading: true,
            printObj: {
                id: 'print',
                popTitle: this.$t('menu.order_receipt'),
            },
            checkoutProps: {
                form: {
                    order_id: this.$route.params.id,

                    total: 0,
                    total_tax: 0,

                    pos_payment_method: 1,
                    payment_method: 1,
                    pos_received_amount: 0,

                    payment_status: paymentStatusEnum.UNPAID,

                    pos_payment_note: '',
                    // search: "",
                    order_dinings: [],
                    // discount: 0,
                },
            },
            editOrderItem: {},
            editOrderItemIndex: null,
            showCustomItemModal: false,
            showTransferModal: false,
            statusEnum: statusEnum,
            sourceEnum: sourceEnum,
            enums: {
                // orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
                // posPaymentMethodEnum: posPaymentMethodEnum,
                // orderTypeEnumArray: {
                //     [orderTypeEnum.DELIVERY]: this.$t('label.delivery'),
                //     [orderTypeEnum.TAKEAWAY]: this.$t('label.takeaway'),
                //     [orderTypeEnum.DINING_TABLE]: this.$t('label.dining_table'),
                //     [orderTypeEnum.TOKEN]: this.$t('label.token'),
                //     [orderTypeEnum.ONLINE_ORDER]: this.$t('label.online_order'),
                //     [orderTypeEnum.POS]: this.$t('label.pos'),
                // },
                // orderStatusEnumArray: {
                //     [orderStatusEnum.ACCEPT]: this.$t('label.accept'),
                //     [orderStatusEnum.PROCESSING]: this.$t('label.processing'),
                //     [orderStatusEnum.DELIVERED]: this.$t('label.delivered'),
                // },
                // paymentStatusEnumArray: {
                //     [paymentStatusEnum.PAID]: this.$t('label.paid'),
                //     [paymentStatusEnum.UNPAID]: this.$t('label.unpaid'),
                // },
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
                        name: this.$t('label.pending'),
                        value: orderStatusEnum.PENDING, // 1
                    },
                    {
                        name: this.$t('label.accept'),
                        value: orderStatusEnum.ACCEPT, // 4
                    },
                    {
                        name: this.$t('label.processing'),
                        value: orderStatusEnum.PROCESSING, // 7
                    },
                    {
                        name: this.$t('label.out_for_delivery'),
                        value: orderStatusEnum.OUT_FOR_DELIVERY, // 10
                    },
                    {
                        name: this.$t('label.delivered'),
                        value: orderStatusEnum.DELIVERED, // 13
                    },
                    {
                        name: this.$t('label.canceled'),
                        value: orderStatusEnum.CANCELED, // 16
                    },
                    {
                        name: this.$t('label.rejected'),
                        value: orderStatusEnum.REJECTED, // 19
                    },
                    {
                        name: this.$t('label.returned'),
                        value: orderStatusEnum.RETURNED, // 22
                    },
                    {
                        name: this.$t('label.pending_payment'),
                        value: orderStatusEnum.PENDING_PAYMENT, // 25
                    },

                ],
            },
            payment_status: null,
            order_status: null,
            pos_payment_note: null,
            show_payment_method: false,
            groups: {},
            showCollectionItem: false,
            orderTypeEnums: {
                dineIn: orderTypeEnum.DINING_TABLE,
                takeAway: orderTypeEnum.TAKEAWAY,
                pos: orderTypeEnum.POS,
                token: orderTypeEnum.TOKEN,
            },
            discountTypeEnum: discountTypeEnum,
            updatePrintOrder: null,
            refundLoading: false,
            voidLoading: false,
            search: {
                paginate: 1,
                page: 1,
                per_page: 10,
                order_column: 'id',
                order_by: "desc",
                order_serial_no: "",
                source: sourceEnum.POS,
                user_id: null,
                status: null,
                from_date: null,
                to_date: null,
                payment_status: null
            }
        };
    },
    mounted() {
        this.getOrderDetails();
        this.$store.dispatch('paymentMethod/lists');
        this.diningTable();
        this.orderStatusOption();
        // Sync initial route for CustomerView
        posCartSyncService.syncCurrentRoute(this.$route.path);

        // Save initial order data if available
        if (this.order && Object.keys(this.order).length > 0) {
            const orderData = {
                id: this.order.id,
                order_serial_no: this.order.order_serial_no,
                total: this.order.total,
                subtotal: this.order.subtotal,
                tax: this.order.tax,
                discount: this.order.discount,
                payment_status: this.order.payment_status,
                status: this.order.status,
                order_type: this.order.order_type,
                dining_tables: this.order.dining_tables,
                order_items: this.orderItems,
                created_at: this.order.created_at,
                updated_at: this.order.updated_at
            };

            localStorage.setItem('currentOrderData', JSON.stringify(orderData));
        }
    },
    beforeUnmount() {
        // Clean up order data when leaving the component
        localStorage.removeItem('currentOrderData');
    },
    computed: {
        orderStatusOptions: function () {
            return this.$store.getters['orderStatus/lists'];
        },
        diningtables: function () {
            return this.$store.getters['diningTable/lists'];
        },
        order: function () {
            return this.$store.getters['posOrder/show'];
        },
        orderItems: function () {
            return this.$store.getters['posOrder/orderItems'];
        },
        orderUser: function () {
            return this.$store.getters['posOrder/orderUser'];
        },
        paymentMethods: function () {
            return this.$store.getters['paymentMethod/lists'];
        },
        groupByOrderTime() {
            const items_ = this.orderItems;

            const groupedItems = Array.isArray(items_)
                ? items_.reduce((acc, item) => {
                      const key = item.order_times;
                      if (!acc[key]) {
                          acc[key] = {
                              order_times: item.order_times,
                              items: [],
                          };
                      }
                      acc[key].items.push({
                          item,
                      });
                      return acc;
                  }, {})
                : {};

            const data = Object.values(groupedItems).sort((a, b) => {
                const timeA = parseInt(a.order_times);
                const timeB = parseInt(b.order_times);
                return timeA - timeB;
            });
            return data;
        },

        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        posDiscount: function () {
            return this.$store.getters['posCart/discount'];
        },
        stockRecords: function () {
            return this.$store.getters['stockRecord/lists'];
        },
        subtotal: function () {
            return this.$store.getters['posCart/subtotal'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    methods: {
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },

        handleOrderPaid: function () {
            console.log('Order paid successfully');

            this.$store
                .dispatch('posOrder/show', this.$route.params.id)
                .then((res) => {
                    this.payment_status = res.data.data.payment_status;
                    this.order_status = res.data.data.status;
                    this.loading.isActive = false;

                    this.printInvoice();
                })
                .catch((error) => {
                    this.loading.isActive = false;
                });


        },

        getOrderDetails: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('posOrder/show', this.$route.params.id)
                .then((res) => {
                    this.payment_status = res.data.data.payment_status;
                    this.order_status = res.data.data.status;
                    this.loading.isActive = false;
                })
                .catch((error) => {
                    this.loading.isActive = false;
                });
        },

        goToAddOrder: async function () {
            this.loading.isActive = true;
            await this.$store
                .dispatch('posCart/setOrder', this.order)
                .then((res) => {
                    this.loading.isActive = false;
                    this.$router.push('/admin/pos/show/' + this.$route.params.id);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    // alertService.error();
                    console.log(err)
                });

        },

        releaseDiningTable: function (dining_tables) {
            appService
                .tableReleaseSuccess()
                .then((res) => {
                    try {
                        this.loading.isActive = true;

                        if (dining_tables && dining_tables.length > 0) {
                            dining_tables.forEach((table) => {
                                this.$store
                                    .dispatch('posOrder/releaseDiningTable_', {
                                        id: table.current_order_id,
                                        dining_table_id: table.id,
                                    })
                                    .then((res) => {
                                        this.loading.isActive = false;
                                        alertService.success(this.$t('message.table_release'));
                                    })
                                    .catch((err) => {
                                        this.loading.isActive = false;
                                        alertService.error(err.response.data.message);
                                    });
                            });
                        }
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },

        viewOrderItem: function (item) {
            // console.log("Item: ", item);
            this.editOrderItem = item;
            this.editOrderItemIndex = null; // Not used for order items, but required by component
            this.showCustomItemModal = true;
        },
        closeCustomItemModal: function () {
            this.showCustomItemModal = false;
            this.editOrderItem = {};
            this.editOrderItemIndex = null;
        },
        openTransferModal: function () {
            this.showTransferModal = true;
        },
        closeTransferModal: function () {
            this.showTransferModal = false;
        },
        handleTransferConfirm: async function (transferData) {
            try {
                this.loading.isActive = true;

                console.log("Transfer Data: ", transferData);

                // Calculate total quantity being transferred
                const totalTransferQty = transferData.items.reduce((sum, item) => sum + item.quantity, 0);

                // Calculate total quantity in source order
                const totalSourceQty = this.order.order_items
                    ? this.order.order_items.reduce((sum, item) => sum + item.quantity, 0)
                    : 0;

                // Check if all items are being transferred
                const isTransferringAll = totalTransferQty >= totalSourceQty;

                // Call API to transfer items
                const response = await this.$store.dispatch('posOrder/transferItems', transferData);

                // Success response returns OrderDetailsResource (response.data.data exists)
                // Error response returns { status: false, message: '...' }
                console.log("Response: ", response);
                if (response.data.data || response.status === 200) {
                    alertService.success(this.$t('message.items_transferred_successfully'));
                    this.closeTransferModal();

                    // If all items transferred, redirect to target order
                    if (isTransferringAll) {
                        // Force navigation by using router.replace then push, or reload the page
                        const targetOrderId = transferData.targetOrderId;
                        if (targetOrderId == this.$route.params.id) {
                            // Same order (edge case), just reload
                            await this.getOrderDetails();
                        } else {
                            // Navigate to target order and force reload
                            await this.$router.push('/admin/pos-orders/show/' + targetOrderId);
                            // Force page reload to ensure data is fresh
                            this.$router.go(0);
                        }
                    } else {
                        // Otherwise, refresh current order details
                        await this.getOrderDetails();
                    }
                } else {
                    alertService.error(response.data.message || this.$t('message.transfer_failed'));
                }
            } catch (error) {
                console.error('Transfer failed:', error);
                const errorMessage = error.response?.data?.message || this.$t('message.transfer_failed');
                alertService.error(errorMessage);
            } finally {
                this.loading.isActive = false;
            }
        },
        handleCustomItemSave: function (data) {
            console.log('Custom item data from modal: ', data);

            // Validate required fields
            if (!this.editOrderItem || !this.editOrderItem.id) {
                alertService.error(this.$t('message.invalid_order_item'));
                return;
            }

            // For PosOrderShowComponent, we update the order item through API
            const orderItemToUpdate = {
                id: this.editOrderItem.id,
                order_id: this.editOrderItem.order_id,
                item_id: this.editOrderItem.item_id,
                quantity: parseFloat(data.quantity) || 1,
                price: parseFloat(data.unitPrice) || 0,
                convert_price: parseFloat(data.unitPrice) || 0,
                discount: parseFloat(data.discount) || 0,
                discount_percentage: parseFloat(data.discountPercentage) || 0,
                total_price: parseFloat(data.total) || 0,

                order_item_custom_name: data.customName || ''
            };

            // Update data to editOrderItem for immediate UI reflection
            // this.editOrderItem.quantity = updateValue.quantity;
            // this.editOrderItem.price = updateValue.price;
            // this.editOrderItem.convert_price = updateValue.convert_price;
            // this.editOrderItem.discount = updateValue.discount;
            // this.editOrderItem.discount_percentage = updateValue.discount_percentage;
            // this.editOrderItem.order_item_custom_name = updateValue.order_item_custom_name;

            // this.loading.isActive = true;
            // this.$store
            //     .dispatch('posOrder/updateOrderItem', {
            //         orderItem: updateValue
            //     })
            //     .then((res) => {
            //         this.loading.isActive = false;
            //         alertService.success(res.data.message);
            //         this.getOrderDetails();
            //         this.closeCustomItemModal();
            //     })
            //     .catch((err) => {
            //         this.loading.isActive = false;
            //         if (err.response && err.response.data && err.response.data.message) {
            //             alertService.error(err.response.data.message);
            //         } else {
            //             alertService.error(this.$t('message.update_failed'));
            //         }
            //     });

            const oldQty = this.editOrderItem.quantity;
            console.log('oldQty:', oldQty, 'newQty:', data.quantity);

            const diff = data.quantity - oldQty;
            console.log('diff:', diff);

            this.loading.isActive = true;
            this.$store
            .dispatch('posOrder/updateOrderItem', { orderItem: orderItemToUpdate })
            .then((res) => {
                this.loading.isActive = false;
                alertService.successFlip(1, this.$t('label.update'));

                console.log("Updated Order Item: ");
                const updatedOrderItem = res.data.data;
                console.log(res.data.data);

                // Reload order data first and wait for it to complete
                return this.$store.dispatch('posOrder/show', this.$route.params.id).then(() => {
                    this.closeCustomItemModal();

                    // Print if quantity changed - now this.order is updated
                    if (diff !== 0) {
                        console.log('Printing update with diff:', diff);

                        console.log("Order: ");
                        console.log(this.order);
                        // Create temp order for printing
                        this.updatePrintOrder = {
                            ...this.order,
                            order_items_unique: [{
                                ...updatedOrderItem,
                                quantity: diff,
                            }]
                        };
                        console.log('Update Print Order:');
                        console.log(this.updatePrintOrder);
                        this.printUpdate();
                    } else {
                        console.log('No diff, not printing');
                    }
                });
            })
            .catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },

        handleUpdatedQty: function (updateData) {
            // console.log('Received updateData: ', updateData);

            const updateValue = updateData.item;
            const oldQty = updateData.oldQuantity;
            console.log('updateValue: ', updateValue);
            console.log('oldQty:', oldQty, 'newQty:', updateValue.quantity);

            const diff = updateValue.quantity - oldQty;
            console.log('diff:', diff);

            this.loading.isActive = true;
            this.$store
                .dispatch('posOrder/updateOrderItem', { orderItem: updateValue })
                .then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(1, this.$t('label.update'));

                    // Reload order data first
                    return this.$store.dispatch('posOrder/show', this.$route.params.id);
                })
                .then(() => {
                    appService.modalHide("#editOrderItemModal");

                    // Print if quantity changed
                    if (diff !== 0) {
                        console.log('Printing update with diff:', diff);

                        console.log("Order: ");
                        console.log(this.order);
                        // Create temp order for printing
                        this.updatePrintOrder = {
                            ...this.order,
                            order_items_unique: [{
                                ...updateValue,
                                quantity: diff,
                                item_name: updateValue.item_name,
                                total_currency_price: updateValue.total_currency_price,
                                item_variations: updateValue.item_variations || [],
                                item_extras: updateValue.item_extras || [],
                                instruction: updateValue.instruction || '',
                            }]
                        };
                        console.log('Update Print Order:');
                        console.log(this.updatePrintOrder);
                        this.printUpdate();
                    } else {
                        console.log('No diff, not printing');
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        orderStatusOption: function () {
            this.$store.dispatch('orderStatus/lists', {
                order_column: 'id',
                order_type: 'asc',
            });
        },

        diningTable: function () {
            this.$store.dispatch('diningTable/lists', {
                order_column: 'id',
                order_type: 'asc',
            });
        },
        formatPrice: function (price) {
            return parseFloat(parseFloat(price).toFixed(2));
        },
        applyDiscount: function () {
            const subtotal = this.formatPrice(this.order.subtotal_price);
            this.discountErrorMessage = '';
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.formatPrice(this.order.total_currency_price) < this.discount) {
                    this.discountErrorMessage = this.$t('message.discount_fixed_error_message');
                } else if (this.discount != null && this.discount > 0) {
                    // const discountAmount = parseFloat(this.discount) + this.formatPrice(this.order.discount_currency);
                    const discountAmount = parseFloat(this.discount);
                    this.discountOrder(discountAmount, subtotal);
                }
            } else {
                if (this.discount > 100) {
                    this.discountErrorMessage = this.$t('message.discount_error_message');
                } else if (this.discount != null && this.discount >= 0) {
                    // const discountAmount = parseFloat((subtotal * this.discount) / 100) + this.formatPrice(this.order.discount_currency);
                    const discountAmount = parseFloat((subtotal * this.discount) / 100);
                    this.discountOrder(discountAmount, subtotal);
                } else if (this.discount != null && this.discount >= 0) {
                    this.discountOrder(0, subtotal);
                }
            }
        },
        discountOrder: function (discount, subtotal) {
            appService
                .discountOrder()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch('posOrder/discount', {
                                id: this.$route.params.id,
                                discount: discount,
                                total: subtotal - discount,
                                discount_percentage: this.discountType == discountTypeEnum.PERCENTAGE ? this.discount : ((discount / subtotal) * 100).toFixed(2),
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(1, this.$t('message.get_discount'));
                                this.discount = null;
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        floatNumber: function (e) {
            return appService.floatNumber(e);
        },
        toggleSelectTable(table) {
            const index = this.selectedTables.findIndex((t) => t.id === table.id);
            if (index !== -1) {
                this.selectedTables.splice(index, 1);
            } else {
                this.selectedTables.push(table);
            }
        },
        isSelected(table) {
            return this.selectedTables.some((t) => t.id === table.id);
        },
        orderDiningTable: function (itemDining) {
            itemDining.forEach((id) => {
                return id;
            });
        },
        selectPaymentMethod: function (paymentMethod) {
            this.checkoutProps.form.pos_payment_method = paymentMethod.id;
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        orderStatus: function (e) {
            console.log("Status e to: ", e.target.value);
            try {
                if (!e.target.value) return;

                this.loading.isActive = true;

                this.$store
                    .dispatch('posOrder/changeStatus', {
                        id: this.$route.params.id,
                        status: e.target.value,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t('label.status'));
                        this.getOrderDetails();
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        const errorMessage = err.response?.data?.message || this.$t('message.update_failed');
                        alertService.error(errorMessage);
                        // Reset to previous value - find the matching status object
                        this.order_status = this.orderStatusOptions?.find(s => s.status_code === this.order?.status) || null;
                    });
            } catch (err) {
                this.loading.isActive = false;
                console.error('Status change error:', err);
                alertService.error(this.$t('message.update_failed'));
            }
        },
        changePaymentStatus: function (e) {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch('posOrder/changePaymentStatus', {
                        id: this.$route.params.id,
                        payment_status: e.target.value,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t('label.payment_status'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        makeOrderPayment: function () {
            if (this.order.payment_status == paymentStatusEnum.PAID) {
                alertService.info(this.$t('message.already_paid'));
                return;
            }

            this.checkoutProps.form.pos_payment_method = posPaymentMethodEnum.CASH;
            this.checkoutProps.form.pos_received_amount = this.order.total_amount_price;
            this.checkoutProps.form.pos_payment_note = '';

            this.checkoutProps.form.total = this.order.total_price;
            this.checkoutProps.form.total_tax = this.order.total_tax_price;

            appService.modalShow('#orderpayment');
        },
        checkPaymentOrderStatus: function () {
            if (this.order.payment_status == paymentStatusEnum.UNPAID) {
                this.$store
                    .dispatch('posOrder/checkPaymentOrderStatus', {
                        id: this.$route.params.id,
                        time_to_check: 300
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        // Check if any of the results match the current order ID
                        const currentOrderId = parseInt(this.$route.params.id);
                        const hasMatchingOrder = res.data.data.results.some(result =>
                            result.order_id === currentOrderId && result.status == 'DONE_PAYMENT'
                        );

                        if (hasMatchingOrder) {
                            // Reload order data if this order was updated
                            this.getOrderDetails();
                        }

                        alertService.successFlip(1, this.$t('message.update_success'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            }
        },
        changePaymentMethod: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch('posOrder/changePaymentMethod', {
                        id: this.$route.params.id,
                        pos_payment_method: this.checkoutProps.form.pos_payment_method,
                        payment_method_id: this.checkoutProps.form.pos_payment_method,
                    })
                    .then((res) => {
                        this.loading.isActive = false;

                        if (this.order.dining_tables && this.order.dining_tables.length > 0) {
                            this.releaseDiningTable(this.order.dining_tables).then(() => {
                                this.$store.dispatch('diningTable/lists', {
                                    order_column: 'id',
                                    order_type: 'asc',
                                });

                                this.$router.go(0);
                            });
                        } else {
                            this.$router.go(0);
                        }

                        alertService.successFlip(1, this.$t('label.payment_method'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }

            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch('posOrder/changePaymentStatus', {
                        id: this.$route.params.id,
                        payment_status: paymentStatusEnum.PAID,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t('label.payment_status'));
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        printInvoice: function () {
            // if (this.payment_status == paymentStatusEnum.PAID) {
            //     this.orderPrinter = this.order;
            //     appService.modalShow('#posOrderShow-printInvoiceModal');
            // } else {
            //     alertService.info(this.$t('message.change_payment_status'));
            // }

            appService.modalShow('#posOrderShow-printInvoiceModal');
        },
        printBill: function () {
            appService.modalShow('#posOrderShow-printBillModal');
        },
        printLabel: function () {
            appService.modalShow('#posOrderShow-printLabelModal');
        },
        destroy: function (id, items) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;

                    let orderItemBeforeDelete = JSON.parse(JSON.stringify(items.item));

                    this.$store.dispatch('posOrder/destroyPosItemOrder', { orderItemId: id, reason: 'Item deleted' }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t('label.delete'));
                        this.$store.dispatch('posOrder/show', this.$route.params.id);

                        this.updatePrintOrder = {
                            ...this.order,
                            order_items_unique: [{
                                ...orderItemBeforeDelete,
                                quantity: -orderItemBeforeDelete.quantity,
                            }]
                        };

                        this.printUpdate();

                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        printUpdate: function () {
            this.$nextTick(() => {
                appService.modalShow('#posOrderShow-updatePrintModal');
            });
        },

        print: function (item_) {
            const data = this.orderItems;
            const items = [];
            if (Array.isArray(data) && data.length > 0) {
                data.map((item) => parseInt(item.order_times)).filter((num) => !isNaN(num));
                data.forEach((item) => {
                    if (item.order_times == item_) {
                        items.push({
                            id: item.id,
                            branch_id: item.branch_id,
                            branch: item.branch,
                            order_id: item.order_id,
                            item_id: item.item_id,
                            item_name: item.item_name,
                            item_image: item.item_image,
                            item_extras: item.item_extras,
                            item_extra_currency_total: item.item_extra_currency_total,
                            item_variations: item.item_variations,
                            item_variation_currency_total: item.item_variation_currency_total,
                            quantity: item.quantity,
                            price: item.price,
                            discount: item.discount,
                            tax_currency_amount: item.tax_currency_amount,
                            tax_currency_rate: item.tax_currency_rate,
                            tax_rate: item.tax_rate,
                            tax_type: item.tax_type,
                            tax_name: item.tax_name,
                            total_convert_price: item.total_convert_price,
                            total_currency_price: item.total_currency_price,
                            total_without_tax_currency_price: item.total_without_tax_currency_price,
                            order_times: item.order_times,
                            instruction: item.instruction,
                            created_at: item.created_at,
                            printers: null,
                        });
                    }
                });
            }
            this.order.order_items = items;
            this.orderPrinter = this.order;
            appService.modalShow('#receiptModal');
        },
        tokenPos: function () {
            this.$refs.token.classList.add('active');
            this.$refs.takeAway.classList.remove('active');
            this.$refs.dineIn.classList.remove('active');
        },
        dineInOrder: function () {
            this.$refs.dineIn.classList.add('active');
            this.$refs.takeAway.classList.remove('active');
            this.$refs.token.classList.remove('active');
        },
        takeAwayOrder: function () {
            this.checkoutProps.form.dining_table_id = null;
            this.$refs.takeAway.classList.add('active');
            this.$refs.token.classList.remove('active');
            this.$refs.dineIn.classList.remove('active');
        },
        removeDiningTable: function (diningTables) {
            appService
                .tableReleaseSuccess()
                .then((res) => {
                    try {
                        this.loading.isActive = true;

                        diningTables;

                        this.$store
                            .dispatch('posOrder/releaseDiningTable_', {
                                id: this.$route.params.id,
                                dining_table_id: dining_table_id,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.success(this.$t('message.table_release'));
                                this.$store.dispatch('diningTable/lists', {
                                    order_column: 'id',
                                    order_type: 'asc',
                                });
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        resetDinIngTable: function () {
            this.$refs.selectTable.clearSelectedTables();
        },
        updateTableSelected: function (data) {
            this.checkoutProps.form.order_dinings = data;
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch('posOrder/addDiningTable', {
                        id: this.$route.params.id,
                        diningTable: data,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(1, this.$t('label.select_table'));
                        this.diningTable();
                        this.resetDinIngTable();
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
        showOrderDiningTable: function (orderId) {
            if (orderId) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
                this.reset();
            }
        },

        onMemberSelect: function (memberData) {
            this.setMember(memberData);
        },
        onMemberCreate: function (memberData) {
            this.setMember(memberData);
        },

        setMember: function (memberData) {
            this.order.member = memberData;
            this.order.member_id = memberData.id;

            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch('posOrder/setMember', {
                        id: this.$route.params.id,
                        member_id: this.order.member_id,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.member_selected') + ': ' + memberData.name);
                        this.getOrderDetails(); //Reload order data
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },

        removeMember: function () {
            appService
                .confirmDialog('' + this.$t('button.remove_member') + '?', this.$t('message.remove_member_confirm'))
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch('posOrder/removeMember', {
                                id: this.$route.params.id,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.success(this.$t('message.member_removed'));
                                this.order.member = null;
                                this.order.member_id = null;

                                this.getOrderDetails(); //Reload order data
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },

        searchAndSelectMember: function () {
            appService.modalShow('#memberSearchModal');
        },
        destroyOrder: function (id) {
            // Check if order has transactions and calculate the sum
            if (this.order && this.order.transactions && this.order.transactions.length > 0) {
                // Calculate the sum of transaction amounts considering signs
                const transactionSum = this.order.transactions.reduce((sum, transaction) => {
                    const amount = parseFloat(transaction.amount) || 0;
                    if (transaction.sign === '-') {
                        return sum - amount;
                    } else if (transaction.sign === '+') {
                        return sum + amount;
                    }
                    return sum;
                }, 0);

                // If sum is not 0, prevent deletion
                if (Math.abs(transactionSum) > 0.01) { // Using 0.01 tolerance for floating point comparison
                    alertService.error(this.$t('message.cannot_delete_order_with_outstanding_balance'));
                    return;
                }
            }

            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('posOrder/destroy', { id: id ,search: this.search  }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.pos_orders'));
                        this.$router.push('/admin/pos-order-deleted');
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },

        canRefund: function(transaction) {
            // Check if transaction has transaction_no, type is not refund, and sign is positive
            if (!transaction.transaction_no || transaction.type === 'refund' || transaction.sign === '-') {
                return false;
            }

            // Check if this transaction already has a refund
            if (this.order && this.order.transactions) {
                const hasRefund = this.order.transactions.some(t =>
                    t.type === 'refund' && parseInt(t.reference_transaction) === parseInt(transaction.id)
                );
                if (hasRefund) {
                    return false;
                }
            }

            // Check if payment method provider is payway
            const paymentMethod = this.paymentMethods.find(pm => pm.id === transaction.pos_payment_method);
            if (paymentMethod && paymentMethod.provider && paymentMethod.provider.toLowerCase() === 'payway') {
                return true;
            }

            return false;
        },

        canVoid: function(transaction) {
            // Check if transaction has transaction_no, type is payment, and sign is positive
            if (!transaction.transaction_no || transaction.type !== 'payment' || transaction.sign === '-') {
                return false;
            }

            // Check if this transaction already has a void transaction
            if (this.order && this.order.transactions) {
                const hasVoid = this.order.transactions.some(t =>
                    t.type === 'void' && parseInt(t.reference_transaction) === parseInt(transaction.id)
                );
                if (hasVoid) {
                    return false;
                }
            }

            // Can only void if payment method provider is NOT payway
            const paymentMethod = this.paymentMethods.find(pm => pm.id === transaction.pos_payment_method);
            if (paymentMethod && paymentMethod.provider && paymentMethod.provider.toLowerCase() === 'payway') {
                return false;
            }

            return true;
        },

        initiateRefund: async function(transaction) {
            try {
                // Confirm refund action
                const result = await appService.confirmDialog(
                    this.$t('message.refund_confirmation_message', { amount: transaction.amount }),
                    this.$t('message.refund_confirmation_title')
                );

                if (!result) {
                    return;
                }

                this.refundLoading = true;
                this.loading.isActive = true;

                // Call refund API
                const response = await this.$store.dispatch('posOrder/refundTransaction', {
                    transaction_id: transaction.id,
                    refund_amount: transaction.amount
                });

                this.refundLoading = false;
                this.loading.isActive = false;

                if (response.data.status.code === '00') {
                    alertService.success(this.$t('message.refund_successful'));
                    // Reload order details to show updated transactions
                    this.getOrderDetails();
                } else {
                    alertService.error(response.data.status.message || this.$t('message.refund_failed'));
                }

            } catch (error) {
                this.refundLoading = false;
                this.loading.isActive = false;
                console.error('Refund error:', error);
                const errorMessage = error.response?.data?.status?.message ||
                                   error.response?.data?.message ||
                                   this.$t('message.refund_failed');
                alertService.error(errorMessage);
            }
        },

        initiateVoid: async function(transaction) {
            try {
                // Confirm void action
                const result = await appService.confirmDialog(
                    this.$t('message.void_confirmation_title') || 'Void Transaction',
                    this.$t('message.void_confirmation_message', { amount: transaction.amount }) || `Are you sure you want to void this transaction of ${transaction.amount}?`
                );

                if (!result) {
                    return;
                }

                this.voidLoading = true;
                this.loading.isActive = true;

                // Call void API
                const response = await this.$store.dispatch('posOrder/voidTransaction', {
                    transaction_id: transaction.id,
                    void_amount: transaction.amount
                });

                this.voidLoading = false;
                this.loading.isActive = false;

                if (response.data && response.data.data) {
                    alertService.success(this.$t('message.void_successful') || 'Transaction voided successfully');
                    // Reload order details to show updated transactions
                    this.getOrderDetails();
                } else {
                    alertService.error(response.data?.message || this.$t('message.void_failed') || 'Failed to void transaction');
                }

            } catch (error) {
                this.voidLoading = false;
                this.loading.isActive = false;
                console.error('Void error:', error);
                const errorMessage = error.response?.data?.message ||
                                   this.$t('message.void_failed') ||
                                   'Failed to void transaction';
                alertService.error(errorMessage);
            }
        },
    },
    watch: {
        '$route': {
            handler(newRoute) {
                // Sync current route for CustomerView display mode detection
                posCartSyncService.syncCurrentRoute(newRoute.path);
            },
            immediate: true
        },
        order: {
            handler(newOrder) {
                if (newOrder && Object.keys(newOrder).length > 0) {
                    // Save order data to localStorage for CustomerView
                    const orderData = {
                        id: newOrder.id,
                        order_serial_no: newOrder.order_serial_no,
                        total: newOrder.total,
                        subtotal: newOrder.subtotal,
                        tax: newOrder.tax,
                        discount: newOrder.discount,
                        payment_status: newOrder.payment_status,
                        status: newOrder.status,
                        order_type: newOrder.order_type,
                        dining_tables: newOrder.dining_tables,
                        order_items: this.orderItems,
                        created_at: newOrder.created_at,
                        updated_at: newOrder.updated_at
                    };

                    localStorage.setItem('currentOrderData', JSON.stringify(orderData));

                    // Dispatch storage event for cross-tab communication
                    window.dispatchEvent(new StorageEvent('storage', {
                        key: 'currentOrderData',
                        newValue: JSON.stringify(orderData),
                        url: window.location.href
                    }));
                }
            },
            deep: true,
            immediate: true
        },
        orderItems: {
            handler(newItems) {
                if (newItems && this.order && Object.keys(this.order).length > 0) {
                    // Update order data when items change
                    const orderData = {
                        id: this.order.id,
                        order_serial_no: this.order.order_serial_no,
                        total: this.order.total,
                        subtotal: this.order.subtotal,
                        tax: this.order.tax,
                        discount: this.order.discount,
                        payment_status: this.order.payment_status,
                        status: this.order.status,
                        order_type: this.order.order_type,
                        dining_tables: this.order.dining_tables,
                        order_items: newItems,
                        created_at: this.order.created_at,
                        updated_at: this.order.updated_at
                    };

                    localStorage.setItem('currentOrderData', JSON.stringify(orderData));

                    // Dispatch storage event for cross-tab communication
                    window.dispatchEvent(new StorageEvent('storage', {
                        key: 'currentOrderData',
                        newValue: JSON.stringify(orderData),
                        url: window.location.href
                    }));
                }
            },
            deep: true
        },

    },
};
</script>
