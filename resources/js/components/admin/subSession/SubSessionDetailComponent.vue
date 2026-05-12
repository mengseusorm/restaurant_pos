<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">

            <!-- Header -->
            <div class="db-card-header border-none">
                <h3 class="db-card-title flex items-center gap-2">
                    {{ $t('label.session_detail') }}
                    <span v-if="session" class="text-sm font-normal rounded-full px-2 py-0.5 capitalize"
                        :class="subStatusBadge(session.status)">
                        {{ statusLabel(session.status) }}
                    </span>
                </h3>
                <div class="db-card-filter flex items-center gap-2">
                    <button
                        v-if="session && session.group_session_id"
                        class="db-btn py-2 text-white bg-indigo-600"
                        @click="$router.push({ name: 'admin.group-session.detail', params: { id: session.group_session_id } })"
                    >
                        <i class="lab lab-arrow-left"></i>
                        <span>{{ $t('button.back_to_group') }}</span>
                    </button>
                    <button class="db-btn py-2 text-white bg-gray-600" @click="$router.back()">
                        <i class="lab lab-arrow-left"></i>
                        <span>{{ $t('button.back') }}</span>
                    </button>
                </div>
            </div>

            <div v-if="session" class="p-4 sm:p-6 space-y-4">

                <!-- ===== Info Strip ===== -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <!-- <div class="rounded-xl p-3 bg-gray-50 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium uppercase mb-0.5">{{ $t('label.customer') }}</p>
                        <p class="font-semibold text-gray-800 truncate text-sm">{{ session.customer_name || '—' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ session.customer_phone || '' }}</p>
                    </div> -->
                    <div class="rounded-xl p-3 bg-gray-50 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium uppercase mb-0.5">{{ $t('label.guest') }}</p>
                        <p class="font-semibold text-gray-800 truncate text-sm">{{ session.guest_name || '—' }}</p>
                        <p v-if="session.phone" class="text-xs text-gray-500 truncate">{{ session.phone }}</p>
                    </div>
                    <!-- <div class="rounded-xl p-3 bg-blue-50 border border-blue-100">
                        <p class="text-xs text-blue-500 font-medium uppercase mb-0.5">{{ $t('label.room') }}</p>
                        <p class="font-semibold text-blue-800 truncate text-sm">{{ session.room?.name || '—' }}</p>
                    </div> -->
                    <div class="rounded-xl p-3 bg-blue-50 border border-blue-100">
                        <p class="text-xs text-blue-500 font-medium uppercase mb-0.5">{{ $t('label.session_time') }}</p>
                        <p class="text-xs font-semibold text-blue-800 leading-snug">
                            {{ session.start_time ? formatDateTime(session.start_time) : $t('label.not_started') }}
                        </p>
                        <p v-if="session.end_time" class="text-xs text-blue-400 leading-snug">
                            {{ $t('label.ended') }}: {{ formatDateTime(session.end_time) }}
                        </p>
                    </div>
                    <div class="rounded-xl p-3 border"
                        :class="{
                            'bg-gray-50 border-gray-200 text-gray-600':    session.status === 'pending',
                            'bg-blue-50 border-blue-200 text-blue-700':    session.status === 'in_progress',
                            'bg-green-50 border-green-200 text-green-700': session.status === 'completed',
                            'bg-slate-50 border-slate-200 text-slate-500': session.status === 'checked_out',
                        }">
                        <p class="text-xs font-medium uppercase mb-0.5 opacity-70">{{ $t('label.status') }}</p>
                        <p class="font-semibold text-sm">{{ statusLabel(session.status) }}</p>
                        <p v-if="session.is_checked_out" class="text-xs text-green-600 font-medium mt-0.5">
                            <i class="lab lab-check mr-0.5"></i> {{ $t('label.checked_out') }}
                        </p>
                    </div>
                    <div class="rounded-xl p-3 bg-green-50 border border-green-100">
                        <p class="text-xs font-medium text-green-500 uppercase mb-0.5">{{ $t('label.subtotal') }}</p>
                        <p class="text-xl font-bold text-green-700 leading-tight">{{ formatPrice(session.subtotal) }}</p>
                        <p class="text-xs text-green-400">{{ (session.session_items || []).length }} {{ $t('label.items') }}</p>
                    </div>
                </div>
                <p v-if="session.notes" class="text-xs text-gray-400 italic px-1">"{{ session.notes }}"</p>

                <!-- ===== Action Buttons ===== -->
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="!session.is_checked_out">
                        <button
                            v-if="session.status === 'waiting' && permissionChecker('massage_sessions_edit')"
                            class="db-btn py-2 px-5 text-white bg-green-600"
                            :disabled="actionLoading"
                            @click="startSession"
                        >
                            <i class="lab lab-play-line mr-1"></i>
                            {{ $t('button.start') }}
                        </button>
                        <button
                            v-if="session.status === 'in_service' && permissionChecker('massage_sessions_edit')"
                            class="db-btn py-2 px-5 text-white bg-orange-500"
                            :disabled="actionLoading"
                            @click="completeSession"
                        >
                            <i class="lab lab-check mr-1"></i>
                            {{ $t('button.complete') }}
                        </button>
                        <button
                            v-if="session.status === 'done' && !session.is_checked_out && permissionChecker('massage_sessions_edit')"
                            class="db-btn py-2 px-5 text-white bg-primary"
                            :disabled="checkoutLoading"
                            @click="handleCheckout"
                        >
                            <i class="lab lab-price-tag mr-1"></i>
                            {{ checkoutLoading ? $t('button.loading') : $t('button.checkout') }}
                        </button>
                    </template>
                    <div v-else class="flex items-center flex-wrap gap-2 rounded-xl bg-green-50 border border-green-200 px-4 py-2 text-green-700">
                        <i class="lab lab-check-circle text-lg"></i>
                        <span class="font-semibold text-sm">{{ $t('label.checked_out') }}</span>
                        <!-- Payment status badge -->
                        <span v-if="session.order_payment_status != null"
                            :class="session.order_payment_status === 5 ? 'bg-green-100 text-green-700 border-green-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300'"
                            class="ml-1 text-xs font-semibold px-2 py-0.5 rounded-full border"
                        >
                            {{ session.order_payment_status === 5 ? $t('label.paid') : $t('label.unpaid') }}
                        </span>
                        <!-- Pay Now button for unpaid orders -->
                        <button
                            v-if="session.order_payment_status != null && session.order_payment_status !== 5"
                            class="ml-2 db-btn py-1.5 px-4 text-white bg-orange-500 hover:bg-orange-600 text-sm"
                            @click="$router.push({ name: 'admin.pos.orders.show', params: { id: session.resolved_order_id } })"
                        >
                            <i class="lab lab-price-tag mr-1"></i>
                            {{ $t('button.pay_now') }}
                        </button>
                        <!-- View Order button -->
                        <button
                            v-if="session.resolved_order_id"
                            class="db-btn py-1.5 px-4 text-white bg-primary text-sm"
                            @click="$router.push({ name: 'admin.pos.orders.show', params: { id: session.resolved_order_id } })"
                        >
                            <i class="lab lab-eye mr-1"></i>
                            {{ $t('button.view_order') }}
                        </button>
                    </div>
                </div>

                <!-- ===== Session Items ===== -->
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="lab lab-pos text-gray-400"></i>
                            {{ $t('label.session_items') }}
                            <span class="text-xs font-normal bg-gray-200 text-gray-500 rounded-full px-2 py-0.5">{{ (session.session_items || []).length }}</span>
                        </h4>
                        <div v-if="canModify && permissionChecker('massage_sessions_edit')" class="flex gap-1.5">
                            <button
                                class="db-btn py-1.5 px-3 text-sm text-white bg-blue-600"
                                @click="toggleAddItemForm('service')"
                            >
                                <i class="lab lab-add-line mr-1"></i>
                                {{ addItemMode === 'service' ? $t('button.cancel') : $t('button.add_service') }}
                            </button>
                            <button
                                class="db-btn py-1.5 px-3 text-sm text-white bg-orange-500"
                                @click="toggleAddItemForm('product')"
                            >
                                <i class="lab lab-add-line mr-1"></i>
                                {{ addItemMode === 'product' ? $t('button.cancel') : $t('button.add_product') }}
                            </button>
                        </div>
                    </div>

                    <!-- Add Item Form -->
                    <div v-if="addItemMode !== null" class="border-b border-indigo-200 p-4 bg-indigo-50 space-y-3">
                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">
                            {{ addItemMode === 'service' ? $t('label.add_service') : $t('label.add_product') }}
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="db-field-title text-xs required">
                                    {{ addItemMode === 'service' ? $t('label.service') : $t('label.product') }}
                                </label>
                                <select v-model="addItemForm.item_id" class="db-field-control text-sm py-1"
                                    :class="addItemErrors.item_id ? 'invalid' : ''">
                                    <option value="">-- {{ $t('label.select_item') }} --</option>
                                    <option v-for="item in (addItemMode === 'service' ? serviceItemsList : productItemsList)"
                                        :key="item.id" :value="item.id">{{ itemName(item) }}</option>
                                </select>
                                <small v-if="addItemErrors.item_id" class="db-field-alert">{{ addItemErrors.item_id[0] }}</small>
                            </div>
                            <div>
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.room') }}</label>
                                <select v-model="addItemForm.room_id" class="db-field-control text-sm py-1">
                                    <option value="">-- {{ $t('label.room') }} --</option>
                                    <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.bed') }}</label>
                                <select v-model="addItemForm.bed_id" class="db-field-control text-sm py-1">
                                    <option value="">-- {{ $t('label.bed') }} --</option>
                                    <option v-for="bed in bedsForRoom" :key="bed.id" :value="bed.id">{{ bed.name }}</option>
                                </select>
                            </div>
                            <div v-if="addItemMode === 'service'">
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.therapist') }}</label>
                                <select v-model="addItemForm.therapist_id" class="db-field-control text-sm py-1">
                                    <option value="">-- {{ $t('label.therapist') }} --</option>
                                    <option v-for="t in therapists" :key="t.id" :value="t.user_id">
                                        {{ t.user ? t.user.name : t.user_id }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="db-field-title text-xs required">{{ $t('label.price') }}</label>
                                <input type="number" v-model="addItemForm.price"
                                    class="db-field-control text-sm py-1" min="0" step="0.01" placeholder="0.00"
                                    :class="addItemErrors.price ? 'invalid' : ''" />
                                <small v-if="addItemErrors.price" class="db-field-alert">{{ addItemErrors.price[0] }}</small>
                            </div>
                            <div>
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.quantity') }}</label>
                                <input type="number" v-model="addItemForm.quantity"
                                    class="db-field-control text-sm py-1" min="1" step="1" placeholder="1" />
                            </div>
                            <div>
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.discount') }}</label>
                                <input type="number" v-model="addItemForm.discount"
                                    class="db-field-control text-sm py-1" min="0" step="0.01" placeholder="0.00" />
                            </div>
                            <div v-if="addItemMode === 'service'">
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.duration_minutes') }}</label>
                                <input type="number" v-model="addItemForm.duration"
                                    class="db-field-control text-sm py-1" min="1" placeholder="60" />
                            </div>
                            <div v-if="addItemMode === 'service'">
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.start_time') }}</label>
                                <input type="datetime-local" v-model="addItemForm.start_time"
                                    class="db-field-control text-sm py-1" />
                            </div>
                            <div class="col-span-2">
                                <label class="db-field-title text-xs after:hidden">{{ $t('label.notes') }}</label>
                                <input type="text" v-model="addItemForm.notes" class="db-field-control text-sm py-1" />
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="db-btn py-1.5 px-4 text-white bg-indigo-600 text-sm"
                                :disabled="addItemLoading" @click="submitAddItem">
                                <i class="lab lab-save mr-1"></i>
                                {{ addItemLoading ? $t('button.loading') : $t('button.save') }}
                            </button>
                            <button class="db-btn py-1.5 px-4 text-white bg-gray-500 text-sm"
                                @click="toggleAddItemForm(addItemMode)">
                                {{ $t('button.cancel') }}
                            </button>
                        </div>
                    </div>

                    <!-- Session Items Table -->
                    <div v-if="(session.session_items || []).length > 0" class="overflow-x-auto w-full">
                        <table class="db-table stripe w-full">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th w-8">#</th>
                                    <th class="db-table-head-th">{{ $t('label.type') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.name') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.room') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.bed') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.therapist') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.duration') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.start_time') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.price') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.qty') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.discount') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.final_price') }}</th>
                                    <th class="db-table-head-th">{{ $t('label.status') }}</th>
                                    <th v-if="canModify" class="db-table-head-th w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="(si, index) in session.session_items" :key="si.id">
                                    <td class="db-table-body-td text-gray-400 text-xs">{{ index + 1 }}</td>
                                    <td class="db-table-body-td">
                                        <span class="text-xs rounded-full px-2 py-0.5 whitespace-nowrap"
                                            :class="si.item && si.item.item_kind === 2 ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600'">
                                            {{ si.item && si.item.item_kind === 2 ? $t('label.service') : $t('label.product') }}
                                        </span>
                                    </td>
                                    <td class="db-table-body-td font-medium whitespace-nowrap">{{ itemName(si.item) }}</td>
                                    <td class="db-table-body-td text-gray-600 whitespace-nowrap">{{ si.room ? si.room.name : '—' }}</td>
                                    <td class="db-table-body-td text-gray-600 whitespace-nowrap">{{ si.bed ? si.bed.name : '—' }}</td>
                                    <td class="db-table-body-td text-gray-600 whitespace-nowrap">{{ si.therapist ? si.therapist.name : '—' }}</td>
                                    <td class="db-table-body-td text-right text-gray-600 whitespace-nowrap">
                                        {{ si.duration ? si.duration + ' ' + $t('label.min') : '—' }}
                                    </td>
                                    <td class="db-table-body-td text-xs text-gray-500 whitespace-nowrap">
                                        {{ si.start_time ? formatDateTime(si.start_time) : '—' }}
                                    </td>
                                    <td class="db-table-body-td text-right whitespace-nowrap">{{ formatPrice(si.price) }}</td>
                                    <td class="db-table-body-td text-right text-gray-600">{{ si.quantity || 1 }}</td>
                                    <td class="db-table-body-td text-right text-red-500 whitespace-nowrap">
                                        {{ parseFloat(si.discount || 0) > 0 ? '-' + formatPrice(si.discount) : '—' }}
                                    </td>
                                    <td class="db-table-body-td text-right font-semibold text-gray-800 whitespace-nowrap">
                                        {{ formatPrice(si.final_price) }}
                                    </td>
                                    <td class="db-table-body-td">
                                        <span class="text-xs rounded-full px-2 py-0.5 capitalize whitespace-nowrap"
                                            :class="itemStatusBadge(si.status)">
                                            {{ si.status == 'in_progress' ? $t('label.in_progress') : si.status }}
                                        </span>
                                    </td>
                                    <td v-if="canModify" class="db-table-body-td text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <SmIconEditComponent @click="openEditItem(si)" :title="$t('button.edit')"/>
                                            <SmIconDeleteComponent @click="removeItem(si.id)"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td :colspan="canModify ? 11 : 10" class="db-table-body-td text-right font-semibold text-gray-600 text-xs uppercase">{{ $t('label.subtotal') }}</td>
                                    <td class="db-table-body-td text-right font-bold text-blue-700 whitespace-nowrap">{{ formatPrice(serviceSubtotal) }}</td>
                                    <td v-if="canModify" colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div v-else class="px-4 py-6 text-center text-sm text-gray-400">{{ $t('label.no_data') }}</div>
                </div>

                <!-- ===== Products Group (item_kind = 1) ===== -->
                <div class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 text-green-600 text-xs font-bold">P</span>
                            {{ $t('label.products_consumed') }}
                            <span class="text-xs font-normal bg-gray-200 text-gray-500 rounded-full px-2 py-0.5">{{ productItems.length }}</span>
                        </h4>
                        <div v-if="canModify" class="flex gap-2">
                            <!-- <button
                                class="db-btn text-white bg-green-600 text-xs py-1.5 px-3"
                                @click="openProductPicker"
                            >
                                <i class="lab lab-list mr-1"></i>
                                <span>{{ $t('button.add_from_catalog') }}</span>
                            </button> -->
                            <button
                                class="db-btn-outline text-green-600 border-green-400 text-xs py-1.5 px-3"
                                @click="toggleAddProduct"
                            >
                                <i class="lab lab-add-line mr-1"></i>
                                <span>{{ $t('label.add_product') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Add Product Form -->
                    <div v-if="showAddProductForm" class="border-b border-green-200 p-4 bg-green-50">
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
                            <div class="col-span-2">
                                <label class="db-field-title text-xs required">{{ $t('label.name') }}</label>
                                <select v-model="addProductForm.item_id" @change="onProductItemChange" class="db-field-control text-sm py-1">
                                    <option value="">-- {{ $t('label.name') }} --</option>
                                    <option v-for="item in productItemsList" :key="item.id" :value="item.id">{{ itemName(item) }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="db-field-title text-xs required">{{ $t('label.qty') }}</label>
                                <input type="number" v-model="addProductForm.quantity" min="1" class="db-field-control text-sm py-1" />
                            </div>
                            <div>
                                <label class="db-field-title text-xs">{{ $t('label.unit_price') }}</label>
                                <input type="number" v-model="addProductForm.unit_price" step="0.01" min="0" class="db-field-control text-sm py-1" />
                            </div>
                            <div class="flex gap-2">
                                <button class="db-btn text-white bg-green-600 text-sm py-1.5 px-4 flex-1" :disabled="addLoading" @click="submitAddProduct">
                                    {{ addLoading ? $t('button.loading') : $t('button.confirm') }}
                                </button>
                                <button class="db-btn-outline text-sm py-1.5 px-3" @click="showAddProductForm = false">✕</button>
                            </div>
                        </div>
                    </div>

                    <div v-if="productItems.length > 0" class="overflow-x-auto w-full">
                        <table class="db-table stripe w-full">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th w-8">#</th>
                                    <th class="db-table-head-th">{{ $t('label.name') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.qty') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.unit_price') }}</th>
                                    <th class="db-table-head-th text-right">{{ $t('label.amount') }}</th>
                                    <th v-if="canModify" class="db-table-head-th w-20"></th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="(si, index) in productItems" :key="si.id">
                                    <td class="db-table-body-td text-gray-400 text-xs">{{ index + 1 }}</td>
                                    <td class="db-table-body-td font-medium">{{ itemName(si.item) }}</td>
                                    <td class="db-table-body-td text-right">{{ si.quantity }}</td>
                                    <td class="db-table-body-td text-right">{{ formatPrice(si.unit_price ?? si.price) }}</td>
                                    <td class="db-table-body-td text-right font-semibold">{{ formatPrice(si.final_price ?? si.total_price) }}</td>
                                    <td v-if="canModify" class="db-table-body-td text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <SmIconEditComponent @click="openEditItem(si)" />
                                            <SmIconDeleteComponent @click="removeItem(si.id)"/>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td :colspan="canModify ? 4 : 3" class="db-table-body-td text-right font-semibold text-gray-600 text-xs uppercase">
                                        {{ $t('label.total') }}
                                    </td>
                                    <td class="db-table-body-td text-right font-bold text-primary whitespace-nowrap">
                                        {{ formatPrice(session.subtotal) }}
                                    </td>
                                    <td v-if="canModify"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div v-else class="py-10 text-center text-gray-400">
                        <i class="lab lab-pos text-3xl block mb-2 opacity-40"></i>
                        <p class="text-sm">{{ $t('label.no_items_yet') }}</p>
                    </div>
                </div>

                <!-- ===== Checkout Banner ===== -->
                <div v-if="session.status === 'done' && !session.is_checked_out && (session.session_items || []).length > 0"
                    class="rounded-xl bg-primary/5 border border-primary/20 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">{{ $t('label.ready_to_checkout') }}</p>
                        <p class="text-2xl font-bold text-primary leading-tight">{{ formatPrice(session.subtotal) }}</p>
                    </div>
                    <button
                        v-if="permissionChecker('massage_sessions_edit')"
                        class="db-btn py-2.5 px-8 text-white bg-primary text-base whitespace-nowrap"
                        :disabled="checkoutLoading"
                        @click="handleCheckout"
                    >
                        <i class="lab lab-price-tag mr-2"></i>
                        {{ checkoutLoading ? $t('button.loading') : $t('button.checkout') }}
                    </button>
                </div>

                <!-- ===== Payment Banner (checked out but unpaid) ===== -->
                <div v-if="session.is_checked_out && session.order_payment_status != null && session.order_payment_status !== 5"
                    class="rounded-xl bg-orange-50 border border-orange-200 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-orange-500 uppercase font-medium mb-0.5">{{ $t('label.payment_pending') }}</p>
                        <p class="text-2xl font-bold text-orange-600 leading-tight">{{ formatPrice(session.subtotal) }}</p>
                    </div>
                    <button
                        class="db-btn py-2.5 px-8 text-white bg-orange-500 hover:bg-orange-600 text-base whitespace-nowrap"
                        @click="$router.push({ name: 'admin.pos.orders.show', params: { id: session.resolved_order_id } })"
                    >
                        <i class="lab lab-price-tag mr-2"></i>
                        {{ $t('button.pay_now') }}
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ===== Edit Session Item Modal ===== -->
    <div v-if="showEditItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-800">{{ $t('label.update') }} {{ $t('label.item') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Change Service / Product -->
                <div class="sm:col-span-2">
                    <label class="db-field-title text-xs required">{{ editItemForm.type === 'service' ? $t('label.service') : $t('label.name') }}</label>
                    <select v-model="editItemForm.item_id" @change="onEditItemChange" class="db-field-control text-sm py-1">
                        <option value="">-- {{ $t('label.name') }} --</option>
                        <option v-for="item in (editItemForm.type === 'service' ? serviceItemsList : productItemsList)" :key="item.id" :value="item.id">{{ itemName(item) }}</option>
                    </select>
                </div>
                <div v-if="editItemForm.type === 'service'">
                    <label class="db-field-title text-xs">{{ $t('label.therapist') }}</label>
                    <select v-model="editItemForm.therapist_id" class="db-field-control text-sm py-1">
                        <option value="">-- {{ $t('label.therapist') }} --</option>
                        <option v-for="t in therapists" :key="t.id" :value="t.user_id">
                            {{ t.user ? t.user.name : t.user_id }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="db-field-title text-xs required">{{ $t('label.qty') }}</label>
                    <input type="number" v-model="editItemForm.quantity" min="1" class="db-field-control text-sm py-1" />
                </div>
                <div>
                    <label class="db-field-title text-xs">{{ $t('label.unit_price') }}</label>
                    <input type="number" v-model="editItemForm.unit_price" step="0.0001" min="0" class="db-field-control text-sm py-1" />
                </div>
                <div v-if="editItemForm.type === 'service'">
                    <label class="db-field-title text-xs">{{ $t('label.duration_minutes') }}</label>
                    <input type="number" v-model="editItemForm.duration_minutes" min="1" class="db-field-control text-sm py-1" />
                </div>
                <div>
                    <label class="db-field-title text-xs">{{ $t('label.started_at') }}</label>
                    <input type="datetime-local" v-model="editItemForm.started_at" class="db-field-control text-sm py-1" />
                </div>
                <div>
                    <label class="db-field-title text-xs">{{ $t('label.ended_at') }}</label>
                    <input type="datetime-local" v-model="editItemForm.ended_at" :min="editItemForm.started_at || undefined" class="db-field-control text-sm py-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="db-field-title text-xs">{{ $t('label.notes') }}</label>
                    <input type="text" v-model="editItemForm.notes" class="db-field-control text-sm py-1" />
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button class="db-btn-outline text-sm py-1.5 px-4" @click="showEditItemModal = false">{{ $t('button.close') }}</button>
                <button class="db-btn text-white bg-indigo-600 text-sm py-1.5 px-4" :disabled="addLoading" @click="submitEditItem">{{ addLoading ? $t('button.loading') : $t('button.confirm') }}</button>
            </div>
        </div>
    </div>

</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent.vue";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import SmIconEditComponent from "../components/buttons/SmIconModalEditComponent.vue";

const ITEM_KIND_SERVICE = 2;

export default {
    name: "SubSessionDetailComponent",
    components: { LoadingComponent, SmIconDeleteComponent, SmIconEditComponent },
    data() {
        return {
            loading:         { isActive: false },
            actionLoading:   false,
            checkoutLoading: false,
            addItemLoading:  false,
            addLoading:      false,
            session:         null,
            showAddServiceForm:  false,
            showAddProductForm:  false,
            addServiceForm: {
                item_id:          '',
                therapist_id:     '',
                quantity:         1,
                duration_minutes: 60,
                unit_price:       '',
                started_at:       '',
                ended_at:         '',
            },
            addProductForm: {
                item_id:    '',
                quantity:   1,
                unit_price: '',
            },
            showProductPickerModal: false,
            pickerSearch:        '',
            pickerCategoryId:    '',
            pickerCart:          {},
            showEditItemModal: false,
            editItemForm: { id: null, item_id: null, therapist_id: '', quantity: 1, duration_minutes: '', unit_price: '', started_at: '', ended_at: '', notes: '', type: 'service' },
            addItemMode: null,
            addItemErrors:   {},
            addItemForm: {
                item_id:      '',
                quantity:     1,
                room_id:      '',
                bed_id:       '',
                therapist_id: '',
                price:        '',
                discount:     '',
                duration:     '',
                start_time:   '',
                notes:        '',
            },
        };
    },
    computed: {
        sessionId()       { return this.$route.params.id; },
        lang()            { return this.$store.getters['frontendLanguage/show']?.code ?? 'en'; },
        setting()         { return this.$store.getters['frontendSetting/lists']; },
        branch()          { return this.$store.getters['backendGlobalState/branchShow']; },
        allItems()        { return this.$store.getters['item/lists'] || []; },
        rooms()           { return this.$store.getters['room/lists'] || []; },
        beds()            { return this.$store.getters['bed/lists'] || []; },
        bedsForRoom()     {
            if (!this.addItemForm.room_id) return this.beds;
            return this.beds.filter(b => b.room_id === parseInt(this.addItemForm.room_id));
        },
        therapists()      { return this.$store.getters['therapistProfile/lists'] || []; },
        serviceItemsList(){ return this.allItems.filter(i => i.item_kind === ITEM_KIND_SERVICE); },
        productItemsList(){ return this.allItems.filter(i => i.item_kind !== ITEM_KIND_SERVICE); },
        productItems() {
            if (!this.session || !this.session.session_items) return [];
            return this.session.session_items.filter(si => si.item && si.item.item_kind !== ITEM_KIND_SERVICE);
        },
        serviceSubtotal() {
            if (!this.session || !this.session.session_items) return 0;
            return this.session.session_items
                .filter(si => si.item && si.item.item_kind === ITEM_KIND_SERVICE)
                .reduce((sum, si) => sum + parseFloat(si.final_price || 0), 0);
        },
        canModify() {
            return this.session && this.session.status !== 'done' && !this.session.is_checked_out;
        },
    },
    watch: {
        'addItemForm.item_id'(newId) {
            if (!newId) { this.addItemForm.price = ''; this.addItemForm.duration = ''; return; }
            const item = this.allItems.find(i => i.id === newId || i.id === parseInt(newId));
            if (item && item.total_amount_price != null) {
                this.addItemForm.price = item.total_amount_price;
            }
            if (item && item.item_kind === 2 && item.duration) {
                this.addItemForm.duration = item.duration;
            }
        },
    },
    mounted() {
        this.loadSession();
        this.$store.dispatch('item/lists', { paginate: 0 });
        this.$store.dispatch('room/lists', { paginate: 0 });
        this.$store.dispatch('bed/lists', { paginate: 0 });
        this.$store.dispatch('therapistProfile/lists', { paginate: 0 });
    },
    methods: {
        permissionChecker(permission) { return appService.permissionChecker(permission); },

        loadSession() {
            this.loading.isActive = true;
            this.$store.dispatch('subSession/show', this.sessionId)
                .then((res) => { this.session = res.data.data; })
                .catch((err) => { alertService.error(err.response?.data?.message || 'Failed to load session'); })
                .finally(() => { this.loading.isActive = false; });
        },

        toggleAddItemForm(mode) {
            if (this.addItemMode === mode) {
                this.addItemMode = null;
            } else {
                this.addItemMode = mode;
            }
            this.resetAddItemForm();
        },

        resetAddItemForm() {
            this.addItemErrors = {};
            this.addItemForm = { item_id: '', quantity: 1, room_id: '', bed_id: '', therapist_id: '', price: '', discount: '', duration: '', start_time: '', notes: '' };
        },

        submitAddItem() {
            this.addItemErrors = {};
            if (!this.addItemForm.item_id) {
                this.addItemErrors.item_id = [this.$t('label.select_item') + ' required'];
                return;
            }
            if (this.addItemForm.price === '' || this.addItemForm.price === null) {
                this.addItemErrors.price = [this.$t('label.price') + ' required'];
                return;
            }
            this.addItemLoading = true;
            this.$store.dispatch('subSession/addItem', {
                id:   this.sessionId,
                form: {
                    item_id:      this.addItemForm.item_id,
                    quantity:     parseInt(this.addItemForm.quantity) || 1,
                    room_id:      this.addItemForm.room_id      || null,
                    bed_id:       this.addItemForm.bed_id        || null,
                    therapist_id: this.addItemForm.therapist_id || null,
                    price:        parseFloat(this.addItemForm.price) || 0,
                    discount:     parseFloat(this.addItemForm.discount) || 0,
                    duration:     this.addItemForm.duration   ? parseInt(this.addItemForm.duration)   : null,
                    start_time:   this.addItemForm.start_time || null,
                    notes:        this.addItemForm.notes      || null,
                },
            }).then(() => {
                alertService.success(this.$t('message.item_added'));
                this.addItemMode = null;
                this.resetAddItemForm();
                this.loadSession();
            }).catch((err) => {
                this.addItemErrors = err.response?.data?.errors ?? {};
                alertService.error(err.response?.data?.message);
            }).finally(() => { this.addItemLoading = false; });
        },

        removeItem(sessionItemId) {
            appService.destroyConfirmation().then((res) => {
                if (!res.isConfirmed) return;
                this.loading.isActive = true;
                this.$store.dispatch('subSession/removeItem', {
                    id: this.sessionId,
                    sessionServiceItemId: sessionItemId,
                }).then(() => {
                    alertService.success(this.$t('label.item') + ' ' + this.$t('message.deleted'));
                    this.loadSession();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => { this.loading.isActive = false; });
            }).catch(() => {});
        },

        startItem(si) {
            appService.confirmDialog(this.$t('message.start_item_question') || 'Start this service?', '', 'question').then(() => {
                this.actionLoading = true;
                this.$store.dispatch('subSession/startItem', { sessionId: this.sessionId, itemId: si.id })
                    .then(() => {
                        alertService.success(this.$t('message.session_started') || 'Service started');
                        this.loadSession();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message || err.message || 'Failed to start service');
                    }).finally(() => { this.actionLoading = false; });
            }).catch(() => {});
        },

        startSession() {
            appService.confirmDialog(this.$t('message.start_session_question') || 'Start this session?', '', 'question').then(() => {
                this.actionLoading = true;
                this.$store.dispatch('subSession/start', { id: this.sessionId })
                    .then(() => {
                        alertService.success(this.$t('message.session_started') || 'Session started');
                        this.loadSession();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => { this.actionLoading = false; });
            }).catch(() => {});
        },

        completeSession() {
            appService.confirmDialog(this.$t('message.complete_session_question') || 'Complete this session?', '', 'question').then(() => {
                this.actionLoading = true;
                this.$store.dispatch('subSession/complete', { id: this.sessionId })
                    .then(() => {
                        alertService.success(this.$t('message.session_completed') || 'Session completed');
                        this.loadSession();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => { this.actionLoading = false; });
            }).catch(() => {});
        },

        handleCheckout() {
            appService.confirmDialog(this.$t('message.checkout_guest_question') || 'Checkout this guest?', '', 'question').then(() => {
                this.checkoutLoading = true;
                this.$store.dispatch('subSession/checkout', { id: this.sessionId })
                    .then((res) => {
                        alertService.success(this.$t('message.checkout_success'));
                        const order = res.data?.data?.order;
                        if (order?.id) {
                            this.$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } });
                        } else {
                            this.loadSession();
                        }
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => { this.checkoutLoading = false; });
            }).catch(() => {});
        },

        formatDateTime(dt) {
            if (!dt) return '\u2014';
            return new Date(dt).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
        },

        formatPrice(val) {
            return appService.currencyFormat(
                parseFloat(val) || 0,
                this.setting?.site_digit_after_decimal_point,
                this.branch?.currency_id?.symbol,
                this.setting?.site_currency_position
            );
        },

        itemName(item) {
            if (!item) return '\u2014';
            return (this.lang !== 'en' && item['name_' + this.lang]) ? item['name_' + this.lang] : item.name;
        },

        statusLabel(status) {
            const map = {
                waiting:    this.$t('label.waiting'),
                in_service: this.$t('label.in_progress'),
                done:       this.$t('label.done'),
            };
            return map[status] ?? status;
        },

        subStatusBadge(status) {
            const map = {
                waiting:    'bg-yellow-100 text-yellow-700',
                in_service: 'bg-blue-100 text-blue-700',
                done:       'bg-purple-100 text-purple-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },

        itemStatusBadge(status) {
            const map = {
                pending:     'bg-gray-100 text-gray-600',
                in_progress: 'bg-blue-100 text-blue-600',
                completed:   'bg-green-100 text-green-600',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },
        toLocalDatetime(dt) {
            if (!dt) return '';
            // Handle "DD-MM-YYYY, HH:MM AM/PM" format returned by the API
            const fmtMatch = String(dt).match(/^(\d{2})-(\d{2})-(\d{4}),\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (fmtMatch) {
                const [, dd, mm, yyyy, hRaw, min, ampm] = fmtMatch;
                let h = parseInt(hRaw, 10);
                if (ampm.toUpperCase() === 'PM' && h !== 12) h += 12;
                if (ampm.toUpperCase() === 'AM' && h === 12) h = 0;
                const pad = (n) => String(n).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}T${pad(h)}:${min}`;
            }
            // Fallback: ISO or any format parseable by Date
            const d = new Date(dt);
            if (isNaN(d.getTime())) return '';
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
        openEditItem(si) {
            const isService = si.item?.item_kind === ITEM_KIND_SERVICE;
            this.editItemForm = {
                id:               si.id,
                item_id:          si.item_id,
                therapist_id:     si.therapist_id || '',
                quantity:         si.quantity || 1,
                duration_minutes: si.duration_minutes || '',
                unit_price:       si.unit_price || '',
                started_at:       this.toLocalDatetime(si.started_at),
                ended_at:         this.toLocalDatetime(si.ended_at),
                notes:            si.notes || '',
                type:             isService ? 'service' : 'product',
            };
            this.showEditItemModal = true;
        },
        onEditItemChange() {
            const item = this.allItems.find(i => i.id == this.editItemForm.item_id);
            if (item) { this.editItemForm.unit_price = item.total_amount_price ?? ''; }
        },
        submitEditItem() {
            if (!this.editItemForm.quantity || this.editItemForm.quantity < 1) {
                alertService.error(this.$t('label.qty') + ' required');
                return;
            }
            this.addLoading = true;
            const form = {
                item_id:          this.editItemForm.item_id,
                therapist_id:     this.editItemForm.therapist_id || null,
                quantity:         this.editItemForm.quantity,
                duration_minutes: this.editItemForm.duration_minutes || null,
                unit_price:       this.editItemForm.unit_price || null,
                started_at:       this.editItemForm.started_at || null,
                ended_at:         this.editItemForm.ended_at || null,
                notes:            this.editItemForm.notes || null,
                branch_id:        this.currentBranchId,
            };
            this.$store.dispatch('frontDesk/updateItem', {
                id:            this.sessionId,
                sessionItemId: this.editItemForm.id,
                form,
            })
                .then(() => {
                    alertService.success(this.$t('message.update'));
                    this.showEditItemModal = false;
                    this.loadSession();
                })
                .catch((err) => { alertService.error(err.response?.data?.message); })
                .finally(() => { this.addLoading = false; });
        },
        toggleAddProduct() {
            this.showAddProductForm = !this.showAddProductForm;
            if (!this.showAddProductForm) {
                this.addProductForm = { item_id: '', quantity: 1, unit_price: '' };
            }
        },
        onProductItemChange() {
            const item = this.allItems.find(i => i.id == this.addProductForm.item_id);
            if (item) {
                const price = parseFloat(item.total_amount_price) || 0;
                this.addProductForm.unit_price = parseFloat(price.toFixed(4));
            }
        },
        submitAddProduct() {
            if (!this.addProductForm.item_id) {
                alertService.error(this.$t('label.select_item') + ' required');
                return;
            }
            if (!this.addProductForm.quantity || this.addProductForm.quantity < 1) {
                alertService.error(this.$t('label.qty') + ' required');
                return;
            }
            this.addLoading = true;
            this.$store.dispatch('subSession/addItem', {
                id:   this.sessionId,
                form: {
                    item_id:  this.addProductForm.item_id,
                    quantity: parseInt(this.addProductForm.quantity) || 1,
                    unit_price: parseFloat(this.addProductForm.unit_price) || 0,
                },
            }).then(() => {
                alertService.success(this.$t('message.item_added'));
                this.showAddProductForm = false;
                this.addProductForm = { item_id: '', quantity: 1, unit_price: '' };
                this.loadSession();
            }).catch((err) => {
                alertService.error(err.response?.data?.message);
            }).finally(() => { this.addLoading = false; });
        },
    },
};
</script>
