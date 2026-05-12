<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <!-- Header -->
            <div class="db-card-header border-none">
                <h3 class="db-card-title">
                    {{ $t('label.group_session') }} #{{ groupId }}
                    <span v-if="group" class="ml-2 text-sm font-normal rounded-full px-2 py-0.5 capitalize"
                        :class="statusBadge(group.status)">
                        {{ group.status }}
                    </span>
                </h3>
                <div class="db-card-filter flex items-center gap-2">
                    <button class="db-btn py-2 text-white bg-gray-600" @click="$router.push({ name: 'admin.front-desk.board' })">
                        <i class="lab lab-arrow-left"></i>
                        <span>{{ $t('button.back') }}</span>
                    </button>
                </div>
            </div>

            <div v-if="group" class="p-4 sm:p-6 space-y-6">

                <!-- ===== Group Summary ===== -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-xl p-4 bg-gray-50 border border-gray-200 space-y-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{
                            $t('label.group_session') }}</p>
                        <p class="text-lg font-bold text-gray-800">#{{ group.id }}</p>
                        <p v-if="group.notes" class="text-sm text-gray-500">{{ group.notes }}</p>
                        <p class="text-xs text-gray-400">{{ $t('label.arrival_time') }}: {{
                            formatDateTime(group.arrival_time) }}</p>
                    </div>
                    <div class="rounded-xl p-4 bg-blue-50 border border-blue-100 space-y-1">
                        <p class="text-xs font-medium text-blue-500 uppercase tracking-wide">{{ $t('label.members') }}
                        </p>
                        <p class="text-3xl font-bold text-blue-700">{{ (group.sub_sessions || []).length }}</p>
                        <p class="text-xs text-blue-400">{{ $t('label.persons') }}</p>
                    </div>
                    <div class="rounded-xl p-4 bg-green-50 border border-green-100 space-y-1">
                        <p class="text-xs font-medium text-green-500 uppercase tracking-wide">{{ $t('label.total') }}
                        </p>
                        <p class="text-3xl font-bold text-green-700">{{ formatPrice(group.total_amount) }}</p>
                        <p class="text-xs text-green-400">{{ $t('label.group_total') }}</p>
                    </div>
                </div>

                <!-- ===== Members (Sub-Sessions) ===== -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="lab lab-user-line text-gray-400"></i>
                            {{ $t('label.members') }}
                            <span class="text-xs font-normal text-gray-400">({{ (group.sub_sessions || []).length
                            }})</span>
                        </h4>
                        <button v-if="group.status === 'open' && permissionChecker('massage_sessions_create')"
                            type="button" class="db-btn py-1.5 px-3 text-sm text-white bg-primary"
                            @click="toggleAddMemberForm">
                            <i class="lab lab-add-line mr-1"></i>
                            {{ $t('button.add_guest') }}
                        </button>
                    </div>

                    <!-- Add Guest Form -->
                    <div v-if="showAddMemberForm"
                        class="mb-4 border border-blue-200 rounded-xl p-4 bg-blue-50 space-y-3">
                        <h5 class="text-sm font-semibold text-blue-700">{{ $t('label.add_guest') }}</h5>
                        <form @submit.prevent="submitAddMember">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="db-field-title required">{{ $t('label.guest_name') }}</label>
                                    <input type="text" v-model="addMemberForm.guest_name" class="db-field-control"
                                        :class="addMemberErrors.guest_name ? 'invalid' : ''" />
                                    <small v-if="addMemberErrors.guest_name" class="db-field-alert">
                                        {{ addMemberErrors.guest_name[0] }}
                                    </small>
                                </div>
                                <div>
                                    <label class="db-field-title after:hidden">{{ $t('label.phone') }}</label>
                                    <input type="text" v-model="addMemberForm.phone" class="db-field-control" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="db-field-title after:hidden">{{ $t('label.notes') }}</label>
                                    <textarea v-model="addMemberForm.notes" class="db-field-control" rows="2"></textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="addMemberForm.share_group_bill"
                                            class="w-4 h-4 rounded border-gray-300" />
                                        <span class="text-sm text-gray-700">{{ $t('label.share_group_bill') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button type="submit" class="db-btn py-2 text-white bg-primary text-sm"
                                    :disabled="addMemberLoading">
                                    <i class="lab lab-save mr-1"></i>
                                    {{ $t('button.save') }}
                                </button>
                                <button type="button" class="db-btn py-2 text-white bg-gray-500 text-sm"
                                    @click="cancelAddMember">
                                    {{ $t('button.cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Per-person cards -->
                    <div v-if="group.sub_sessions && group.sub_sessions.length > 0" class="space-y-4">
                        <div v-for="(sub, index) in group.sub_sessions" :key="sub.id"
                            class="border rounded-xl overflow-hidden" :class="memberCardClass(sub.status)">
                            <!-- Person Header -->
                            <div class="flex items-center justify-between px-4 py-3 bg-white border-b">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                        :class="personAvatarClass(index)">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ sub.guest_name || `${$t('label.person')} ${index + 1}` }}
                                        </p>
                                        <p v-if="sub.phone" class="text-xs text-gray-400">{{ sub.phone }}</p>
                                        <p class="text-xs text-gray-500">
                                            <span v-if="sub.session_items && sub.session_items[0] && sub.session_items[0].room">
                                                <i class="lab lab-rooms mr-0.5"></i> {{ sub.session_items[0].room.name }}
                                            </span>
                                            <span v-if="sub.session_items && sub.session_items[0] && sub.session_items[0].room && sub.session_items[0].therapist"> · </span>
                                            <span v-if="sub.session_items && sub.session_items[0] && sub.session_items[0].therapist">
                                                <i class="lab lab-therapist mr-0.5"></i> {{ sub.session_items[0].therapist.name }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap justify-end">
                                    <span class="text-xs rounded-full px-2 py-0.5 capitalize"
                                        :class="subStatusBadge(sub.status)">
                                        {{ sub.status}}
                                    </span>
                                    <span class="font-semibold text-gray-700 text-sm">{{ formatPrice(sub.subtotal) }}</span>
                                    <!-- Start button -->
                                    <!-- <button
                                        v-if="sub.status === 'waiting' && permissionChecker('massage_sessions_edit')"
                                        type="button"
                                        class="db-btn-outline py-1 px-2 text-xs text-green-600 border-green-600"
                                        :disabled="!!actionLoading[sub.id]"
                                        @click="startSubSession(sub.id)">
                                        <i class="lab lab-play-line mr-0.5"></i>
                                        {{ $t('button.start') }}
                                    </button> -->
                                    <!-- Complete button -->
                                    <button
                                        v-if="sub.status === 'in_service' && permissionChecker('massage_sessions_edit')"
                                        type="button"
                                        class="db-btn-outline py-1 px-2 text-xs text-orange-600 border-orange-400"
                                        :disabled="!!actionLoading[sub.id]"
                                        @click="completeSubSession(sub.id)">
                                        <i class="lab lab-check mr-0.5"></i>
                                        {{ $t('button.complete') }}
                                    </button>
                                    <!-- Checkout Guest button -->
                                    <button
                                        v-if="sub.status === 'done' && !sub.is_checked_out && permissionChecker('massage_sessions_edit')"
                                        type="button"
                                        class="db-btn-outline py-1 px-2 text-xs text-purple-600 border-purple-400"
                                        :disabled="!!actionLoading[sub.id]"
                                        @click="checkoutGuest(sub.id)">
                                        <i class="lab lab-price-tag mr-0.5"></i>
                                        {{ $t('button.checkout') }}
                                    </button>
                                    <!-- Pay unpaid checkout order -->
                                    <button
                                        v-if="hasUnpaidOrder(sub)"
                                        type="button"
                                        class="db-btn-outline py-1 px-2 text-xs text-orange-600 border-orange-400"
                                        @click="goToOrder(sub.resolved_order_id)">
                                        <i class="lab lab-price-tag mr-0.5"></i>
                                        {{ $t('button.checkout') }}
                                    </button>
                                    <!-- <button type="button"
                                        class="db-btn-outline py-1 px-2 text-xs text-blue-600 border-blue-600"
                                        @click="viewSubSession(sub.id)">
                                        <i class="lab lab-edit-line mr-0.5"></i>
                                        {{ $t('button.detail') }}
                                    </button> -->
                                    <SmDeleteComponent
                                        v-if="group.status === 'open' && permissionChecker('massage_sessions_delete')"
                                        @click="removeMember(sub.id)" />
                                </div>
                            </div>

                            <!-- Compact session items list -->
                            <div class="px-4 py-3 bg-gray-50">
                                <div v-if="sub.session_items && sub.session_items.length > 0" class="space-y-2">
                                    <div v-for="item in sub.session_items" :key="item.id"
                                        class="rounded-lg border bg-white p-2 space-y-1.5">
                                        <!-- Row 1: type badge + name + therapist/room + price + status -->
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-xs rounded px-1.5 py-0.5 flex-shrink-0"
                                                    :class="item.item && item.item.item_kind === 2 ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600'">
                                                    {{ item.item && item.item.item_kind === 2 ? $t('label.service') : $t('label.product') }}
                                                </span>
                                                <span class="text-sm font-medium text-gray-800">
                                                    {{ item.item ? itemName(item.item) : '—' }}
                                                </span>
                                                <span v-if="item.therapist" class="text-xs text-gray-400">({{ item.therapist.name }})</span>
                                                <span v-if="item.room" class="text-xs text-gray-400">· {{ item.room.name }}</span>
                                                <span v-if="item.bed" class="text-xs text-gray-400">· {{ item.bed.name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                <span v-if="item.quantity && item.quantity > 1" class="text-xs text-gray-500">×{{ item.quantity }}</span>
                                                <span class="text-sm font-medium text-gray-700">{{ formatPrice(item.final_price) }}</span>
                                                <span class="text-xs rounded-full px-1.5 py-0.5 capitalize" :class="itemStatusBadge(item.status)">
                                                    {{ item.status == 'in_progress' ? $t('label.in_progress') : item.status }}
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Row 2: time info -->
                                       <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xl text-gray-400">
                                            <span v-if="itemScheduledStart(item)" class="text-base">
                                                <i class="lab lab-calendar-line"></i> {{ $t('label.scheduled') }}: {{ itemScheduledStart(item) }}
                                            </span>
                                            <span v-if="itemScheduledEnd(item)" class="text-base">
                                                <i class="lab lab-calendar-line"></i> {{ $t('label.end') }}: {{ itemScheduledEnd(item) }}
                                            </span>
                                            <span v-if="item.started_time" class="text-base text-blue-500">
                                                <i class="lab lab-play-line"></i> {{ $t('label.started') }}: {{ item.started_time }}
                                            </span>
                                            <span v-if="item.ended_time" class="text-base text-green-500">
                                                <i class="lab lab-check"></i> {{ $t('label.ended') }}: {{ item.ended_time }}
                                            </span>
                                            <span v-if="showItemCountdown(item)" class="text-base text-blue-600 font-semibold">
                                                <i class="lab lab-clock"></i> {{ $t('label.remaining') }}: {{ itemRemainingLabel(item) }}
                                            </span>
                                        </div>
                                        <!-- Row 3: action buttons -->
                                        <div v-if="sub.status !== 'done' && item.status !== 'completed'" class="flex items-center gap-1.5 flex-wrap">
                                            <!-- Start: service + pending -->
                                           <button
                                                v-if="item.item && item.item.item_kind === 2 && item.status === 'pending'"
                                                type="button"
                                                class="db-btn py-1.5 px-4 text-sm text-white bg-green-600"
                                                :disabled="!!actionLoading['item-' + item.id]"
                                                @click="startSessionItem(sub.id, item)">
                                                <i class="lab lab-play-line mr-1"></i>{{ $t('button.start') }}
                                            </button>
                                            <!-- Complete: service + in_progress -->
                                            <button
                                                v-if="item.item && item.item.item_kind === 2 && item.status === 'in_progress' && permissionChecker('massage_sessions_edit')"
                                                type="button"
                                                class="db-btn py-1.5 px-4 text-sm text-white bg-indigo-600"
                                                :disabled="!!actionLoading['item-' + item.id]"
                                                @click="completeSessionItem(sub.id, item)">
                                                <i class="lab lab-check mr-0.5"></i>{{ $t('button.complete') }}
                                            </button>
                                            <!-- Delivered: product + not completed -->
                                            <button
                                                v-if="item.item && item.item.item_kind !== 2 && permissionChecker('massage_sessions_edit')"
                                                type="button"
                                                class="db-btn py-1.5 px-4 text-sm text-white bg-orange-500"
                                                :disabled="!!actionLoading['item-' + item.id]"
                                                @click="completeSessionItem(sub.id, item)">
                                                <i class="lab lab-check mr-0.5"></i>{{ $t('button.delivered') }}
                                            </button>
                                            <!-- Edit -->
                                            <button
                                                v-if="permissionChecker('massage_sessions_edit')"
                                                type="button"
                                                class="w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center"
                                                :disabled="!!actionLoading['item-' + item.id]"
                                                @click="openEditItem(item, sub.id)"
                                                :title="$t('button.edit')">
                                                <i class="lab lab-edit-line text-xs"></i>
                                            </button> 
                                            <!-- Delete -->
                                            <button
                                                v-if="permissionChecker('massage_sessions_edit')"
                                                type="button"
                                                class="text-red-400 hover:text-red-600 transition-colors ml-auto"
                                                :disabled="!!actionLoading['item-' + item.id]"
                                                @click="removeItem(sub.id, item.id)"
                                                :title="$t('button.remove')">
                                                <i class="lab lab-delete-line text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-gray-400">{{ $t('label.no_items_yet') }}</p>
                            </div>

                            <!-- Add Item Section -->
                            <div class="px-4 py-2 bg-white border-t border-gray-100">
                                <div v-if="sub.status !== 'done' && permissionChecker('massage_sessions_edit')" class="flex gap-1.5">
                                    <button
                                        type="button"
                                        class="db-btn-outline py-1 px-2.5 text-xs text-blue-600 border-blue-400"
                                        @click="toggleAddItemForm(sub.id, 'service')"
                                    >
                                        <i class="lab lab-add-line mr-0.5"></i>
                                        {{ showAddItemFormId === sub.id && showAddItemFormMode === 'service' ? $t('button.cancel') : $t('button.add_service') }}
                                    </button>
                                    <button
                                        type="button"
                                        class="db-btn-outline py-1 px-2.5 text-xs text-orange-600 border-orange-400"
                                        @click="toggleAddItemForm(sub.id, 'product')"
                                    >
                                        <i class="lab lab-add-line mr-0.5"></i>
                                        {{ showAddItemFormId === sub.id && showAddItemFormMode === 'product' ? $t('button.cancel') : $t('button.add_product') }}
                                    </button>
                                </div>

                                <!-- Inline Add Item Form -->
                                <div v-if="showAddItemFormId === sub.id && showAddItemFormMode !== null"
                                    class="mt-3 border border-indigo-200 rounded-lg p-3 bg-indigo-50 space-y-2">
                                    <h6 class="text-xs font-semibold text-indigo-700">
                                        {{ showAddItemFormMode === 'service' ? $t('label.add_service') : $t('label.add_product') }}
                                    </h6>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div class="sm:col-span-2">
                                            <label class="db-field-title text-xs required">
                                                {{ showAddItemFormMode === 'service' ? $t('label.service') : $t('label.product') }}
                                            </label>
                                            <select v-model="addItemForm.item_id" class="db-field-control text-sm py-1"
                                                :class="addItemErrors.item_id ? 'invalid' : ''">
                                                <option value="">-- {{ $t('label.select_item') }} --</option>
                                                <option v-for="item in (showAddItemFormMode === 'service' ? serviceItems : productItems)"
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
                                        <div v-if="showAddItemFormMode === 'service'">
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
                                        <!-- qty -->
                                        <div>
                                            <label class="db-field-title text-xs after:hidden">{{ $t('label.quantity') }}</label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="fa-solid fa-minus w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center" @click="decreaseQuantity"></button>
                                                <input type="number" v-model="editItemForm.quantity" disabled min="1" max="10" class="text-center w-7 text-xl font-semibold text-heading indec-value"/>   
                                                <button type="button" class="fa-solid fa-plus w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center" @click="increaseQuantity"></button>
                                            </div> 
                                        </div>
                                        <div>
                                            <label class="db-field-title text-xs after:hidden">{{ $t('label.discount') }}</label>
                                            <input type="number" v-model="addItemForm.discount"
                                                class="db-field-control text-sm py-1" min="0" step="0.01" placeholder="0.00" />
                                        </div>
                                        <div v-if="showAddItemFormMode === 'service'">
                                            <label class="db-field-title text-xs after:hidden">{{ $t('label.duration_minutes') }}</label>
                                            <input type="number" v-model="addItemForm.duration"
                                                class="db-field-control text-sm py-1" min="1" placeholder="60" />
                                        </div>
                                        <div v-if="showAddItemFormMode === 'service'">
                                            <label class="db-field-title text-xs after:hidden">{{ $t('label.start_time') }}</label>
                                            <input type="datetime-local" v-model="addItemForm.start_time"
                                                class="db-field-control text-sm py-1" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="db-field-title text-xs after:hidden">{{ $t('label.notes') }}</label>
                                            <input type="text" v-model="addItemForm.notes" class="db-field-control text-sm py-1" />
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <button type="button"
                                            class="db-btn py-1.5 px-3 text-white bg-indigo-600 text-xs"
                                            :disabled="addItemLoading"
                                            @click="submitAddItem(sub.id)">
                                            <i class="lab lab-save mr-0.5"></i>
                                            {{ $t('button.save') }}
                                        </button>
                                        <button type="button"
                                            class="db-btn py-1.5 px-3 text-white bg-gray-500 text-xs"
                                            @click="showAddItemFormId = null; showAddItemFormMode = null; resetAddItemForm()">
                                            {{ $t('button.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="border border-dashed border-gray-200 rounded-xl p-8 text-center text-gray-400">
                        <i class="lab lab-user-line text-4xl block mb-2"></i>
                        <p>{{ $t('label.no_members_yet') }}</p>
                        <p class="text-xs mt-1">{{ $t('label.add_member_hint') }}</p>
                    </div>
                </div>

                <!-- ===== Checkout Section ===== -->
                <template v-if="group.status === 'open' || group.status === 'in_progress'">
                    <div v-if="activeSubSessions.length > 0" class="border-t pt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                            <i class="lab lab-price-tag text-gray-400"></i>
                            {{ $t('label.checkout_options') }}
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Option A: Single Bill -->
                            <div v-if="group.sub_sessions && group.sub_sessions.length > 1" class="border-2 border-blue-200 rounded-xl p-5 flex flex-col">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">A</span>
                                    <h5 class="font-semibold text-gray-800">{{ $t('label.checkout_single_bill') }}</h5>
                                </div>
                                <p class="text-sm text-gray-500 mb-3">{{ $t('label.checkout_single_bill_desc') }}</p>
                                <p class="text-3xl font-bold text-blue-600 mb-1">{{ formatPrice(group.total_amount) }}</p>
                                <p class="text-xs text-gray-400 mb-4">{{ $t('label.combined_total') }}</p>
                                <button type="button" class="db-btn text-white bg-blue-600 w-full mt-auto"
                                    :disabled="checkoutLoading" @click="checkoutGroup">
                                    <i class="lab lab-price-tag mr-1"></i>
                                    {{ $t('button.checkout_as_group') }}
                                </button>
                            </div>

                            <!-- Option B: Split Bill -->
                            <!-- <div class="border-2 border-green-200 rounded-xl p-5 flex flex-col">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">B</span>
                                    <h5 class="font-semibold text-gray-800">{{ $t('label.checkout_split_bill') }}</h5>
                                </div>
                                <p class="text-sm text-gray-500 mb-3">{{ $t('label.checkout_split_bill_desc') }}</p>
                                <div class="space-y-1 mb-4 flex-1">
                                    <div v-for="(sub, i) in activeSubSessions" :key="sub.id"
                                        class="flex justify-between text-sm">
                                        <span class="text-gray-600">
                                            {{ sub.guest_name || `${$t('label.person')} ${i + 1}` }}
                                        </span>
                                        <span class="font-semibold text-green-700">{{ formatPrice(sub.subtotal) }}</span>
                                    </div>
                                </div>
                                <button type="button" class="db-btn text-white bg-green-600 w-full mt-auto"
                                    :disabled="splitCheckoutLoading" @click="checkoutSplit">
                                    <i class="lab lab-price-tag mr-1"></i>
                                    {{ $t('button.checkout_split') }}
                                </button>
                            </div> -->
                        </div>
                    </div>
                    <div v-else-if="group.orders && group.orders.length > 0" class="border-t pt-6 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="lab lab-price-tag text-gray-400"></i>
                            {{ $t('label.payment_pending') }}
                        </h4>
                        <div v-for="order in group.orders" :key="order.id"
                            class="border rounded-xl bg-orange-50 border-orange-200 p-4 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-800 text-sm">
                                        #{{ order.order_serial_no || order.id }}
                                    </span>
                                    <span class="text-xs rounded-full px-2 py-0.5 font-medium"
                                        :class="Number(order.payment_status) === 5 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'">
                                        {{ Number(order.payment_status) === 5 ? $t('label.paid') : $t('label.unpaid') }}
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-gray-700 mt-1">{{ formatPrice(order.total) }}</p>
                            </div>
                            <button type="button"
                                class="db-btn py-1.5 px-3 text-sm text-white flex-shrink-0"
                                :class="Number(order.payment_status) === 5 ? 'bg-gray-500' : 'bg-blue-600'"
                                @click="$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } })">
                                <i class="lab lab-price-tag mr-1"></i>
                                {{ Number(order.payment_status) === 5 ? $t('button.view') : $t('button.pay_now') }}
                            </button>
                        </div>
                    </div>
                    <div v-else class="border-t pt-4 text-center text-gray-400 text-sm py-6">
                        {{ $t('label.add_members_to_checkout') }}
                    </div>
                </template>

                <!-- Completed State -->
                <div v-else-if="group.status === 'completed'" class="border-t pt-6 space-y-4">
                    <!-- Summary banner -->
                    <div class="rounded-xl bg-green-50 border border-green-200 p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center text-xl flex-shrink-0">
                            <i class="lab lab-check"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-green-800">{{ $t('label.group_checked_out') }}</h4>
                            <p class="text-xs text-green-500 mt-0.5">{{ formatDateTime(group.end_time) }}</p>
                        </div>
                        <div class="ml-auto text-right">
                            <p class="text-2xl font-bold text-green-700">{{ formatPrice(group.total_amount) }}</p>
                            <p class="text-xs text-green-400">{{ $t('label.total') }}</p>
                        </div>
                    </div>

                    <!-- Order cards -->
                    <div v-if="group.orders && group.orders.length > 0">
                        <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="lab lab-pos-order text-gray-400"></i>
                            {{ $t('label.orders') }}
                        </h5>
                        <div class="space-y-2">
                            <div v-for="order in group.orders" :key="order.id"
                                class="border rounded-xl bg-white p-4 flex items-center gap-3">
                                <!-- Order info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-gray-800 text-sm">
                                            #{{ order.order_serial_no || order.id }}
                                        </span>
                                        <span v-if="order.customer_name" class="text-xs text-gray-500">
                                            · {{ order.customer_name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span class="text-sm font-bold text-gray-700">{{ formatPrice(order.total) }}</span>
                                        <span v-if="order.payment_status === 10"
                                            class="text-xs rounded-full px-2 py-0.5 bg-red-100 text-red-600 font-medium">
                                            {{ $t('label.unpaid') }}
                                        </span>
                                        <span v-else-if="order.payment_status === 15"
                                            class="text-xs rounded-full px-2 py-0.5 bg-yellow-100 text-yellow-700 font-medium">
                                            {{ $t('label.partial') }}
                                            <span class="ml-1 opacity-75">{{ formatPrice(order.balance_due) }} {{ $t('label.due') }}</span>
                                        </span>
                                        <span v-else
                                            class="text-xs rounded-full px-2 py-0.5 bg-green-100 text-green-700 font-medium">
                                            {{ $t('label.paid') }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Action button -->
                                <button type="button"
                                    class="db-btn py-1.5 px-3 text-sm text-white flex-shrink-0"
                                    :class="order.payment_status === 5 ? 'bg-gray-500' : 'bg-blue-600'"
                                    @click="$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } })">
                                    <i class="lab lab-eye mr-1"></i>
                                    {{ order.payment_status === 5 ? $t('button.view') : $t('button.pay_now') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled State -->
                <div v-else-if="group.status === 'cancelled'" class="border-t pt-6">
                    <div class="rounded-xl bg-red-50 border border-red-200 p-6 text-center">
                        <h4 class="text-lg font-bold text-red-700">{{ $t('label.group_cancelled') }}</h4>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===== Edit Session Item Modal ===== -->
    <div v-if="showEditItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-800">{{ $t('label.update') }} {{ $t('label.item') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="db-field-title text-xs required">{{ editItemForm.type === 'service' ? $t('label.service') : $t('label.name') }}</label>
                    <select v-model="editItemForm.item_id" @change="onEditItemChange" class="db-field-control text-sm py-1">
                        <option value="">-- {{ $t('label.name') }} --</option>
                        <option v-for="item in (editItemForm.type === 'service' ? serviceItems : productItems)" :key="item.id" :value="item.id">{{ itemName(item) }}</option>
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
                    <div class="flex items-center gap-2">
                        <button type="button" class="fa-solid fa-minus w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center" @click="decreaseQuantity"></button>
                        <input type="number" v-model="editItemForm.quantity" disabled min="1" max="10" class="text-center w-7 text-xl font-semibold text-heading indec-value"/>   
                        <button type="button" class="fa-solid fa-plus w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-500 hover:text-primary hover:border-primary flex items-center justify-center" @click="increaseQuantity"></button>
                    </div>
                    <!-- <input type="number" v-model="editItemForm.quantity" min="1" max="10" class="db-field-control text-sm py-1"/> -->
                </div> 
                <div v-if="editItemForm.type === 'service'">
                    <label class="db-field-title text-xs">{{ $t('label.started_at') }}</label>
                    <input type="datetime-local" v-model="editItemForm.started_at" @change="syncEditItemEndedAt" class="db-field-control text-sm py-1" />
                </div>
                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600">
                    <div>
                        <span class="block font-medium text-gray-500">{{ $t('label.unit_price') }}</span>
                        <span class="font-semibold text-gray-800">{{ editItemForm.unit_price || '0.00' }}</span>
                    </div>
                    <div v-if="editItemForm.type === 'service'">
                        <span class="block font-medium text-gray-500">{{ $t('label.duration_minutes') }}</span>
                        <span class="font-semibold text-gray-800">{{ editItemForm.duration_minutes || 0 }}</span>
                    </div>
                    <div v-if="editItemForm.type === 'service'">
                        <span class="block font-medium text-gray-500">{{ $t('label.ended_at') }}</span>
                        <span class="font-semibold text-gray-800">{{ editItemForm.ended_at || '—' }}</span>
                    </div>
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
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmIconEditComponent from "../components/buttons/SmIconModalEditComponent.vue";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";

export default {
    name: "GroupSessionDetailComponent",
    components: { LoadingComponent, SmDeleteComponent, SmIconEditComponent },
    data() {
        return {
            loading: { isActive: false },
            addMemberLoading: false,
            checkoutLoading: false,
            splitCheckoutLoading: false,
            actionLoading: {},
            now: Date.now(),
            timerInterval: null,
            group: null,
            showAddMemberForm: false,
            addMemberErrors: {},
            addMemberForm: {
                guest_name: '',
                phone: '',
                notes: '',
                share_group_bill: false,
            },
            showAddItemFormId: null,
            showAddItemFormMode: null,
            addItemLoading: false,
            addItemErrors: {},
            addItemForm: {
                item_id: '',
                quantity: 1,
                room_id: '',
                bed_id: '',
                therapist_id: '',
                price: '',
                discount: '',
                duration: '',
                start_time: '',
                notes: '',
            },
            showEditItemModal: false,
            addLoading: false,
            editingSubSessionId: null,
            editItemForm: { id: null, item_id: null, therapist_id: '', quantity: 1, duration_minutes: '', unit_price: '', started_at: '', ended_at: '', notes: '', type: 'service' },
        };
    },
    computed: {
        groupId() { return this.$route.params.id; },
        lang() { return this.$store.getters['frontendLanguage/show']?.code ?? 'en'; },
        activeSubSessions() {
            if (!this.group?.sub_sessions) return [];
            return this.group.sub_sessions.filter(s => !s.is_checked_out);
        },
        rooms()        { return this.$store.getters['room/lists'] ?? []; },
        beds()         { return this.$store.getters['bed/lists'] ?? []; },
        bedsForRoom()  {
            if (!this.addItemForm.room_id) return this.beds;
            return this.beds.filter(b => b.room_id === parseInt(this.addItemForm.room_id));
        },
        therapists()   { return this.$store.getters['therapistProfile/lists'] ?? []; },
        allItems()     { return this.$store.getters['item/lists'] ?? []; },
        setting()      { return this.$store.getters['frontendSetting/lists']; },
        branch()       { return this.$store.getters['backendGlobalState/branchShow']; },
        serviceItems() { return this.allItems.filter(i => i.item_kind === 2); },
        productItems() { return this.allItems.filter(i => i.item_kind !== 2); },
    },
    watch: {
        'addItemForm.item_id'(newId) {
            if (!newId) { this.addItemForm.price = ''; this.addItemForm.duration = ''; return; }
            const item = this.allItems.find(i => i.id === newId || i.id === parseInt(newId));
            if (item && item.price != null) {
                this.addItemForm.price = item.price;
            }
            if (item && item.item_kind === 2 && item.duration) {
                this.addItemForm.duration = item.duration;
            }
        },
    },
    mounted() {
        this.loadGroup();
        this.$store.dispatch('item/lists', { paginate: 0 });
        this.$store.dispatch('room/lists', { paginate: 0 });
        this.$store.dispatch('bed/lists', { paginate: 0 });
        this.$store.dispatch('therapistProfile/lists', { paginate: 0 });
        this.timerInterval = setInterval(() => {
            this.now = Date.now();
        }, 1000);
    },
    beforeUnmount() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
    },
    methods: {
        permissionChecker(permission) { return appService.permissionChecker(permission); },
        loadGroup() {
            this.loading.isActive = true;
            this.$store.dispatch('groupSession/show', this.groupId)
                .then((res) => { this.group = res.data.data; })
                .catch((err) => { alertService.error(err.response?.data?.message || 'Failed to load group session'); })
                .finally(() => { this.loading.isActive = false; });
        },
        toggleAddMemberForm() {
            this.showAddMemberForm = !this.showAddMemberForm;
            if (!this.showAddMemberForm) { this.resetAddMemberForm(); }
        },
        cancelAddMember() {
            this.showAddMemberForm = false;
            this.resetAddMemberForm();
        },
        resetAddMemberForm() {
            this.addMemberErrors = {};
            this.addMemberForm = { guest_name: '', phone: '', notes: '', share_group_bill: false };
        },
        toggleAddItemForm(subSessionId, mode) {
            if (this.showAddItemFormId === subSessionId && this.showAddItemFormMode === mode) {
                this.showAddItemFormId = null;
                this.showAddItemFormMode = null;
            } else {
                this.showAddItemFormId = subSessionId;
                this.showAddItemFormMode = mode;
            }
            this.resetAddItemForm();
        },
        resetAddItemForm() {
            this.addItemErrors = {};
            this.addItemForm = { item_id: '', quantity: 1, room_id: '', bed_id: '', therapist_id: '', price: '', discount: '', duration: '', start_time: '', notes: '' };
        },
        submitAddItem(subSessionId) {
            this.addItemErrors = {};
            if (!this.addItemForm.item_id) {
                this.addItemErrors.item_id = [this.$t('label.select_item') + ' required'];
                return;
            }
            if (!this.addItemForm.price && this.addItemForm.price !== 0) {
                this.addItemErrors.price = [this.$t('label.price') + ' required'];
                return;
            }
            this.addItemLoading = true;
            this.$store.dispatch('subSession/addItem', {
                id: subSessionId,
                form: {
                    item_id:      this.addItemForm.item_id,
                    quantity:     parseInt(this.addItemForm.quantity) || 1,
                    room_id:      this.addItemForm.room_id || null,
                    bed_id:       this.addItemForm.bed_id || null,
                    therapist_id: this.addItemForm.therapist_id || null,
                    price:        parseFloat(this.addItemForm.price) || 0,
                    discount:     parseFloat(this.addItemForm.discount) || 0,
                    duration:     this.addItemForm.duration ? parseInt(this.addItemForm.duration) : null,
                    start_time:   this.addItemForm.start_time || null,
                    notes:        this.addItemForm.notes || null,
                },
            }).then(() => {
                alertService.success(this.$t('message.item_added'));
                this.showAddItemFormId = null;
                this.showAddItemFormMode = null;
                this.resetAddItemForm();
                this.loadGroup();
            }).catch((err) => {
                this.addItemErrors = err.response?.data?.errors ?? {};
                alertService.error(err.response?.data?.message);
            }).finally(() => { this.addItemLoading = false; });
        },
        startSessionItem(subSessionId, item) {
            appService.confirmDialog(this.$t('message.start_item_question') || 'Start this service?', '', 'question').then(() => {
                this.actionLoading = { ...this.actionLoading, ['item-' + item.id]: true };
                this.$store.dispatch('subSession/startItem', { sessionId: subSessionId, itemId: item.id })
                    .then(() => {
                        alertService.success(this.$t('message.session_started') || 'Service started');
                        this.loadGroup();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message || err.message || 'Failed to start service');
                    }).finally(() => {
                        this.actionLoading = { ...this.actionLoading, ['item-' + item.id]: false };
                    });
            }).catch(() => {});
        },
        completeSessionItem(subSessionId, item) {
            const isProduct = item.item && item.item.item_kind !== 2;
            const msg = isProduct
                ? (this.$t('message.deliver_item_question') || 'Mark this item as delivered?')
                : (this.$t('message.complete_item_question') || 'Complete this service?');
            appService.confirmDialog(msg, '', 'question').then(() => {
                this.actionLoading = { ...this.actionLoading, ['item-' + item.id]: true };
                this.$store.dispatch('subSession/completeItem', { sessionId: subSessionId, itemId: item.id })
                    .then(() => {
                        alertService.success(isProduct
                            ? (this.$t('message.item_delivered') || 'Item delivered')
                            : (this.$t('message.session_completed') || 'Service completed'));
                        this.loadGroup();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message || err.message || 'Failed to update item');
                    }).finally(() => {
                        this.actionLoading = { ...this.actionLoading, ['item-' + item.id]: false };
                    });
            }).catch(() => {});
        },
        startSubSession(subSessionId) {
            appService.confirmDialog(this.$t('message.start_session_question') || 'Start this session?', '', 'question').then(() => {
                this.$set ? this.$set(this.actionLoading, subSessionId, true) : (this.actionLoading = { ...this.actionLoading, [subSessionId]: true });
                this.$store.dispatch('subSession/start', { id: subSessionId })
                    .then(() => {
                        alertService.success(this.$t('message.session_started') || 'Session started');
                        this.loadGroup();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => {
                        this.actionLoading = { ...this.actionLoading, [subSessionId]: false };
                    });
            }).catch(() => {});
        },
        completeSubSession(subSessionId) { 
            appService.confirmDialog(this.$t('message.complete_session_question') || 'Complete this session?', '', 'question').then(() => {
                this.actionLoading = { ...this.actionLoading, [subSessionId]: true };
                this.$store.dispatch('subSession/complete', { id: subSessionId })
                    .then(() => {
                        alertService.success(this.$t('message.session_completed') || 'Session completed');
                        this.loadGroup();
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => {
                        this.actionLoading = { ...this.actionLoading, [subSessionId]: false };
                    });
            }).catch(() => {});
        },
        checkoutGuest(subSessionId) {
            appService.confirmDialog(this.$t('message.checkout_guest_question') || 'Checkout this guest?', '', 'question').then(() => {
                this.actionLoading = { ...this.actionLoading, [subSessionId]: true };
                this.$store.dispatch('subSession/checkout', { id: subSessionId })
                    .then((res) => {
                        alertService.success(this.$t('message.checkout_success'));
                        const order = res.data?.data?.order;
                        if (order?.id) {
                            this.$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } });
                        } else {
                            this.loadGroup();
                        }
                    }).catch((err) => {
                        alertService.error(err.response?.data?.message);
                    }).finally(() => {
                        this.actionLoading = { ...this.actionLoading, [subSessionId]: false };
                    });
            }).catch(() => {});
        },
        removeItem(subSessionId, sessionItemId) {
            appService.confirmDialog(this.$t('message.remove_item_question') || 'Remove this item?', '', 'question').then(() => {
                const key = 'item-' + sessionItemId;
                this.actionLoading = { ...this.actionLoading, [key]: true };
                this.$store.dispatch('subSession/removeItem', {
                    id: subSessionId,
                    sessionServiceItemId: sessionItemId,
                }).then(() => {
                    alertService.success(this.$t('message.item_removed') || 'Item removed');
                    this.loadGroup();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => {
                    this.actionLoading = { ...this.actionLoading, [key]: false };
                });
            });
        },
        submitAddMember() {
            this.addMemberErrors = {};
            if (!this.addMemberForm.guest_name) {
                this.addMemberErrors.guest_name = [this.$t('label.customer_name') + ' required'];
                return;
            }
            this.addMemberLoading = true;
            this.$store.dispatch('groupSession/addSubSession', { groupId: this.groupId, form: this.addMemberForm })
                .then(() => {
                    alertService.success(this.$t('message.member_added'));
                    this.showAddMemberForm = false;
                    this.resetAddMemberForm();
                    this.loadGroup();
                })
                .catch((err) => {
                    this.addMemberErrors = err.response?.data?.errors ?? {};
                    alertService.error(err.response?.data?.message);
                })
                .finally(() => { this.addMemberLoading = false; });
        },
        removeMember(subSessionId) {
            appService.confirmDialog(this.$t('message.remove_member_question'), '', 'question').then(() => {
                this.loading.isActive = true;
                this.$store.dispatch('groupSession/removeSubSession', {
                    groupId: this.groupId,
                    subSessionId: subSessionId,
                }).then(() => {
                    alertService.success(this.$t('message.member_removed'));
                    this.loadGroup();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => { this.loading.isActive = false; });
            }).catch(() => {});
        },
        viewSubSession(id) {
            this.$router.push({ name: 'admin.sub-session.detail', params: { id } });
        },
        checkoutGroup() {
            const subSessions = this.group?.sub_sessions || [];
            const allCompleted = this.checkAllSessionItemsCompleted(subSessions);

            if (!allCompleted) {
                alertService.error(
                    this.$t('message.checkout_group_not_completed') ||
                    'All services must be completed before group checkout.'
                );
                return;
            }

            appService.confirmDialog(this.$t('message.checkout_group_question'), '', 'question').then(() => {
                this.checkoutLoading = true;
                this.$store.dispatch('groupSession/checkout', {
                    id: this.groupId,
                    form: {},
                }).then((res) => {
                    alertService.success(this.$t('message.checkout_success'));
                    const order = res.data?.data?.order;
                    if (order?.id) {
                        this.$router.push({ name: 'admin.pos.orders.show', params: { id: order.id } });
                    } else {
                        this.loadGroup();
                    }
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => { this.checkoutLoading = false; });
            }).catch(() => {});
        },
        checkoutSplit() {
            appService.confirmDialog(this.$t('message.checkout_split_question'), '', 'question').then(() => {
                this.splitCheckoutLoading = true;
                this.$store.dispatch('groupSession/checkoutSplit', {
                    id: this.groupId,
                    form: {},
                }).then(() => {
                    alertService.success(this.$t('message.checkout_success'));
                    this.loadGroup();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message);
                }).finally(() => { this.splitCheckoutLoading = false; });
            }).catch(() => {});
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
            if (!item) return '—';
            return (this.lang !== 'en' && item['name_' + this.lang]) ? item['name_' + this.lang] : item.name;
        },
        itemScheduledStart(item) {
            return item?.started_at || item?.start_time || null;
        },
        itemScheduledEnd(item) {
            return item?.ended_at || item?.end_time || null;
        },
        showItemCountdown(item) {
            return item?.item?.item_kind === 2 && item?.status === 'in_progress';
        },
        sessionItemDuration(item) {
            return Number(item?.duration || item?.duration_minutes || item?.item?.duration || 0);
        },
        itemRemainingLabel(item) {
            return this.formatSeconds(this.itemRemainingSeconds(item));
        },
        itemRemainingSeconds(item) {
            const duration = this.sessionItemDuration(item);
            const startedAt = this.timestampValue(item?.started_time_raw || item?.started_time || item?.started_at_raw || item?.started_at);

            if (!duration || !startedAt) {
                return 0;
            }

            return Math.max(0, Math.floor(((startedAt + duration * 60 * 1000) - this.now) / 1000));
        },
        formatSeconds(seconds) {
            const safeSeconds = Math.max(0, Number(seconds) || 0);
            const hours = Math.floor(safeSeconds / 3600);
            const minutes = Math.floor((safeSeconds % 3600) / 60);
            const secs = safeSeconds % 60;
            const pad = (value) => String(value).padStart(2, '0');

            if (hours > 0) {
                return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
            }

            return `${pad(minutes)}:${pad(secs)}`;
        },
        timestampValue(value) {
            if (!value) return null;

            const text = String(value).trim();
            const displayMatch = text.match(/^(\d{1,2})-(\d{1,2})-(\d{4}),\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/i);

            if (displayMatch) {
                const [, day, month, year, hourRaw, minute, meridiem] = displayMatch;
                let hour = parseInt(hourRaw, 10);
                if (meridiem.toUpperCase() === 'PM' && hour !== 12) hour += 12;
                if (meridiem.toUpperCase() === 'AM' && hour === 12) hour = 0;

                return new Date(Number(year), Number(month) - 1, Number(day), hour, Number(minute)).getTime();
            }

            const parsed = new Date(text).getTime();
            return Number.isNaN(parsed) ? null : parsed;
        },
        hasUnpaidOrder(subSession) {
            return !!subSession?.is_checked_out
                && !!subSession?.resolved_order_id
                && Number(subSession?.order_payment_status) !== 5;
        },
        goToOrder(orderId) {
            if (!orderId) return;
            this.$router.push({ name: 'admin.pos.orders.show', params: { id: orderId } });
        },
        formatDateTime(dt) {
            if (!dt) return '—';
            return new Date(dt).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
        },
        statusBadge(status) {
            const map = {
                open:        'bg-blue-100 text-blue-700',
                in_progress: 'bg-indigo-100 text-indigo-700',
                completed:   'bg-green-100 text-green-700',
                cancelled:   'bg-red-100 text-red-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
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
        memberCardClass(status) {
            const map = {
                waiting:    'border-yellow-200',
                in_service: 'border-blue-200',
                done:       'border-purple-200',
            };
            return map[status] ?? 'border-gray-200';
        },
        personAvatarClass(index) {
            const colors = [
                'bg-blue-100 text-blue-700',
                'bg-purple-100 text-purple-700',
                'bg-orange-100 text-orange-700',
                'bg-pink-100 text-pink-700',
                'bg-teal-100 text-teal-700',
                'bg-indigo-100 text-indigo-700',
            ];
            return colors[index % colors.length];
        },
        toLocalDatetime(dt) {
            if (!dt) return '';
            const fmtMatch = String(dt).match(/^(\d{1,2})-(\d{1,2})-(\d{4}),\s*(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (fmtMatch) {
                const [, dd, mm, yyyy, hRaw, min, ampm] = fmtMatch;
                let h = parseInt(hRaw, 10);
                if (ampm.toUpperCase() === 'PM' && h !== 12) h += 12;
                if (ampm.toUpperCase() === 'AM' && h === 12) h = 0;
                const pad = (n) => String(n).padStart(2, '0');
                return `${yyyy}-${pad(mm)}-${pad(dd)}T${pad(h)}:${min}`;
            }
            const d = new Date(dt);
            if (isNaN(d.getTime())) return '';
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
        findItem(itemId) {
            return this.allItems.find(i => i.id == itemId);
        },
        itemPrice(item) {
            if (!item) return '';
            return item.price ?? item.total_amount_price ?? '';
        },
        itemDuration(item) {
            if (!item || item.item_kind !== 2) return '';
            return item.duration || '';
        },
        calculateEndedAt(startedAt, durationMinutes) {
            if (!startedAt || !durationMinutes) return '';
            const start = new Date(startedAt);
            if (isNaN(start.getTime())) return '';
            start.setMinutes(start.getMinutes() + parseInt(durationMinutes, 10));
            const pad = (n) => String(n).padStart(2, '0');
            return `${start.getFullYear()}-${pad(start.getMonth()+1)}-${pad(start.getDate())}T${pad(start.getHours())}:${pad(start.getMinutes())}`;
        },
        syncEditItemEndedAt() {
            this.editItemForm.ended_at = this.calculateEndedAt(
                this.editItemForm.started_at,
                this.editItemForm.duration_minutes
            );
        },
        applyEditItemDefaults(fallback = {}) {
            const item = this.findItem(this.editItemForm.item_id) || fallback.item;
            const price = this.itemPrice(item);
            const duration = this.itemDuration(item);
            this.editItemForm.unit_price = price !== '' && price != null ? price : (fallback.unit_price ?? '');
            this.editItemForm.duration_minutes = duration !== '' && duration != null ? duration : (fallback.duration_minutes ?? '');
            this.syncEditItemEndedAt();
        },
        openEditItem(si, subSessionId) {
            const isService = si.item?.item_kind === 2;
            this.editingSubSessionId = subSessionId;
            this.editItemForm = {
                id:               si.id,
                item_id:          si.item_id,
                therapist_id:     si.therapist_id || '',
                quantity:         si.quantity || 1,
                duration_minutes: si.duration ?? si.duration_minutes ?? '',
                unit_price:       si.price ?? si.unit_price ?? '',
                started_at:       this.toLocalDatetime(si.started_at || si.start_time),
                ended_at:         this.toLocalDatetime(si.ended_at || si.end_time),
                notes:            si.notes || '',
                type:             isService ? 'service' : 'product',
            };
            this.applyEditItemDefaults({
                item: si.item,
                unit_price: si.price ?? si.unit_price ?? '',
                duration_minutes: si.duration ?? si.duration_minutes ?? '',
            });
            this.showEditItemModal = true;
        },
        onEditItemChange() {
            this.applyEditItemDefaults();
        },
        submitEditItem() {
            if (!this.editItemForm.quantity || this.editItemForm.quantity < 1) {
                alertService.error(this.$t('label.qty') + ' required');
                return;
            }
            this.applyEditItemDefaults();
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
            };
            this.$store.dispatch('frontDesk/updateItem', {
                id:            this.editingSubSessionId,
                sessionItemId: this.editItemForm.id,
                form,
            }).then(() => {
                alertService.success(this.$t('message.update'));
                this.showEditItemModal = false;
                this.editingSubSessionId = null;
                this.loadGroup();
            }).catch((err) => { alertService.error(err.response?.data?.message); })
              .finally(() => { this.addLoading = false; });
        },
        decreaseQuantity() {
            if (this.editItemForm.quantity > 1) {
                this.editItemForm.quantity--; 
            }
        },
        increaseQuantity() {
            this.editItemForm.quantity++; 
        },
        checkAllSessionItemsCompleted(subSessions) {
            if (!subSessions || subSessions.length === 0) return false;

            for (const sub of subSessions) {
                const items = sub.session_items || [];

                // If a sub-session has no items, treat as not ready
                if (items.length === 0) return false;

                const allCompleted = items.every(item => item.status === 'completed');
                if (!allCompleted) return false;
            }

            return true;
        },
    },
};
</script>
