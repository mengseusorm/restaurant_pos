<template>
    <LoadingComponent :props="loading" />

    <div class="db-card db-tab-div active mb-5">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.front_desk') }}</h3>
            <div class="db-card-filter flex items-center gap-2">
                <button
                    v-if="permissionChecker('massage_sessions_create')"
                    class="db-btn text-white bg-indigo-600"
                    @click="openNewGroup"
                    :disabled="newGroupLoading"
                >
                    <i class="lab lab-add-line"></i>
                    <span>{{ $t('button.new_group_session') }}</span>
                </button>
                <button  v-if="permissionChecker('session_queue_create')" class="db-btn text-white bg-yellow-500 relative" @click="openQueueAddModal">
                    <i class="lab lab-add-line"></i>
                    <span>{{ $t('button.add_to_queue') }}</span>
                    <span v-if="waitingCount > 0" class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center px-1 leading-none">
                        {{ waitingCount }}
                    </span>
                </button>
                <button class="db-btn text-white bg-gray-600" @click="refreshBoard">
                    <i class="lab lab-refresh-line"></i>
                    <span>{{ $t('button.refresh') }}</span>
                </button>
            </div>
        </div>
        <div class="p-4 space-y-6">

            <!-- ===== 1. Top Summary Bar ===== -->
            <div class="grid grid-cols-2 xl:grid-cols-5 gap-4">
                <!-- Rooms in Use -->
                <div class="rounded-xl p-4 bg-blue-50 border border-blue-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-500 flex items-center justify-center text-white flex-shrink-0">
                        <i class="lab lab-rooms text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">{{ $t('label.rooms_in_use') }}</p>
                        <p class="text-2xl font-bold text-blue-700 leading-tight">{{ summary.rooms_in_use ?? 0 }}</p>
                        <p class="text-xs text-blue-400">/ {{ rooms.length }} {{ $t('label.total') }}</p>
                    </div>
                </div>

                <!-- Available Therapists -->
                <div class="rounded-xl p-4 bg-green-50 border border-green-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-green-500 flex items-center justify-center text-white flex-shrink-0">
                        <i class="lab lab-therapist text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-green-600 font-medium uppercase tracking-wide">{{ $t('label.available_therapists') }}</p>
                        <p class="text-2xl font-bold text-green-700 leading-tight">{{ summary.available_therapists ?? 0 }}</p>
                        <p class="text-xs text-green-400">/ {{ therapists.length }} {{ $t('label.total') }}</p>
                    </div>
                </div>

                <!-- Waiting Customers -->
                <div class="rounded-xl p-4 bg-yellow-50 border border-yellow-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-yellow-500 flex items-center justify-center text-white flex-shrink-0">
                        <i class="lab lab-queue text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-yellow-600 font-medium uppercase tracking-wide">{{ $t('label.waiting_customers') }}</p>
                        <p class="text-2xl font-bold text-yellow-700 leading-tight">{{ waitingCount }}</p>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="rounded-xl p-4 bg-purple-50 border border-purple-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-500 flex items-center justify-center text-white flex-shrink-0">
                        <i class="lab lab-session text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 font-medium uppercase tracking-wide">{{ $t('label.active_sessions') }}</p>
                        <p class="text-2xl font-bold text-purple-700 leading-tight">{{ summary.total_active_sessions ?? 0 }}</p>
                    </div>
                </div>

                <!-- Open Group Sessions -->
                <div
                    class="rounded-xl p-4 bg-indigo-50 border border-indigo-100 flex items-center gap-4 cursor-pointer hover:bg-indigo-100 transition-colors"
                    @click="$router.push({ name: 'admin.group-session.list' })"
                >
                    <div class="w-12 h-12 rounded-lg bg-indigo-500 flex items-center justify-center text-white flex-shrink-0">
                        <i class="lab lab-user-line text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-indigo-600 font-medium uppercase tracking-wide">{{ $t('label.open_group_sessions') }}</p>
                        <p class="text-2xl font-bold text-indigo-700 leading-tight">{{ summary.total_group_sessions ?? 0 }}</p>
                        <p class="text-xs text-indigo-400">{{ $t('label.groups') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="db-card db-tab-div active mb-5">
        <div class="p-4 space-y-6">
            <!-- ===== Section Divider ===== -->
            <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                    {{ $t('menu.rooms') }} &amp; {{ $t('menu.therapist_profiles') }}
                </span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- <h3 class="db-card-title border-b-2">{{ $t('menu.rooms') }} &amp; {{ $t('menu.therapist_profiles') }}</h3> -->

            <!-- ===== 2. Room Grid + Therapist Panel ===== -->
            <div class="flex flex-col xl:flex-row gap-6">

                <!-- Room Grid -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <i class="lab lab-rooms text-gray-400"></i>
                            {{ $t('menu.rooms') }}
                            <span class="text-xs font-normal text-gray-400">({{ filteredRooms.length }}/{{ rooms.length }})</span>
                        </h4>
                        <!-- Room Status Filter -->
                        <div class="flex items-center gap-1 flex-wrap">
                            <button
                                v-for="f in roomFilters" :key="f.value"
                                type="button"
                                class="px-2.5 py-1 rounded-full text-xs font-medium border transition-colors"
                                :class="roomStatusFilter === f.value
                                    ? 'bg-primary text-white border-primary'
                                    : 'bg-white text-gray-500 border-gray-200 hover:border-primary hover:text-primary'"
                                @click="roomStatusFilter = f.value"
                            >
                                {{ f.label }}
                                <span v-if="f.count !== null" class="ml-1 opacity-70">{{ f.count }}</span>
                            </button>
                        </div>
                    </div>
                    <div v-if="filteredRooms.length === 0" class="p-8 text-center text-gray-400">
                        {{ $t('label.no_rooms_found') }}
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
                        <RoomCardComponent
                            v-for="room in filteredRooms"
                            :key="room.id"
                            :room="room"
                            :active-items="room.active_items || []"
                            :can-open-session="permissionChecker('massage_sessions_create')"
                            :can-edit-status="permissionChecker('rooms_edit')"
                            @open-session="handleOpenSession"
                            @session-updated="refreshBoard"
                            @view-detail="viewSessionDetail"
                        />
                    </div>
                </div>

                <!-- Therapist Status Panel -->
                <div class="xl:w-72 flex-shrink-0">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="lab lab-therapist text-gray-400"></i>
                        {{ $t('menu.therapist_profiles') }}
                        <span class="text-xs font-normal text-gray-400">({{ therapists.length }})</span>
                    </h4>
                    <div v-if="therapists.length === 0" class="p-4 text-center text-gray-400 text-sm border rounded-lg">
                        {{ $t('label.no_therapists_found') }}
                    </div>
                    <div v-else class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                        <div
                            v-for="t in therapists"
                            :key="t.id"
                            class="rounded-lg p-3 border flex items-center gap-3 transition-colors"
                            :class="therapistCardClass(t.status)"
                        >
                            <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold"
                                :class="therapistAvatarClass(t.status)">
                                {{ (t.name || '?')[0].toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ t.name }}</p>
                                <p v-if="t.room_name" class="text-xs text-gray-500 truncate">
                                    {{ t.room_name }}<span v-if="t.service_name"> · {{ t.service_name }}</span>
                                </p>
                            </div>
                            <span class="text-xs rounded-full px-2 py-0.5 capitalize flex-shrink-0" :class="therapistBadgeClass(t.status)">
                                {{ t.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="db-card db-tab-div active mb-5">
        <div class="p-4 space-y-6">

            <!-- ===== Room Time Schedule ===== -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                        {{ $t('label.room_schedule') || 'Room Schedule' }}
                    </span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3">
                    <div v-for="room in rooms" :key="'sched-' + room.id"
                        class="rounded-xl border overflow-hidden"
                        :class="roomScheduleCardClass(room)">
                        <!-- Room header -->
                        <div class="flex items-center justify-between px-3 py-2 border-b"
                            :class="roomScheduleHeaderClass(room)">
                            <span class="font-semibold text-sm text-gray-800">{{ room.name }}</span>
                            <span class="text-xs rounded-full px-2 py-0.5"
                                :class="roomStatusBadgeClass(room)">
                                {{ roomStatusLabel(room) }}
                            </span>
                        </div>
                        <!-- Item slots -->
                        <div class="px-3 py-2">
                            <div v-if="activeRoomItems(room).length > 0"
                                class="space-y-1.5">
                                <div v-for="si in activeRoomItems(room)"
                                    :key="'rsi-' + si.session_item_id"
                                    class="rounded-lg bg-white border border-blue-100 px-2.5 py-1.5 space-y-0.5">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-xs font-medium text-gray-800 truncate">
                                            {{ si.item_name || '—' }}
                                        </span>
                                        <span class="text-[10px] rounded px-1.5 py-0.5 capitalize flex-shrink-0"
                                            :class="itemStatusBadgeInline(si.status)">
                                            {{ si.status == 'in_progress' ? $t('label.in_progress') : si.status }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 space-y-0.5">
                                        <div v-if="si.start_time" class="flex items-center gap-1">
                                            <i class="lab lab-calendar-line text-gray-400"></i>
                                            <span class="text-gray-400">{{ $t('label.start') || 'Start' }}:</span>
                                            <span>{{ si.start_time }}</span>
                                        </div>
                                        <div v-if="si.end_time" class="flex items-center gap-1">
                                            <i class="lab lab-calendar-line text-orange-400"></i>
                                            <span class="text-gray-400">{{ $t('label.end') || 'End' }}:</span>
                                            <span class="text-orange-600 font-medium">{{ si.end_time }}</span>
                                        </div>
                                        <div v-if="si.started_time" class="flex items-center gap-1">
                                            <i class="lab lab-play-line text-blue-400"></i>
                                            <span class="text-gray-400">{{ $t('label.started') || 'Started' }}:</span>
                                            <span class="text-blue-600">{{ si.started_time }}</span>
                                        </div>
                                        <div v-if="si.therapist_name" class="flex items-center gap-1 text-gray-400">
                                            <i class="lab lab-therapist"></i>
                                            <span>{{ si.therapist_name }}</span>
                                        </div>
                                        <div v-if="si.bed_name" class="flex items-center gap-1 text-gray-400">
                                            <i class="lab lab-bed"></i>
                                            <span>{{ si.bed_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-2 text-center text-xs font-medium" :class="roomEmptyTextClass(room)">
                                <i :class="roomEmptyIcon(room)" class="mr-1"></i>{{ roomStatusLabel(room) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="db-card db-tab-div active mb-5">
        <div class="p-4 space-y-6">

            <!-- ===== Section Divider ===== -->
            <div v-if="groupSessions.length > 0" class="flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                    {{ $t('menu.group_sessions') }}
                </span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- ===== 3. Group Sessions Panel ===== -->
            <div v-if="groupSessions.length > 0">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="lab lab-user-line text-gray-400"></i>
                        {{ $t('label.open_group_sessions') }}
                        <span class="text-xs font-normal text-gray-400">({{ groupSessions.length }})</span>
                    </h4>
                    <button
                        v-if="permissionChecker('massage_sessions_create')"
                        type="button"
                        class="db-btn py-1.5 px-3 text-sm text-white bg-indigo-600"
                        @click="openNewGroup"
                        :disabled="newGroupLoading"
                    >
                        <i class="lab lab-add-line mr-1"></i>
                        {{ $t('button.new_group_session') }}
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <div
                        v-for="group in groupSessions"
                        :key="group.id"
                        class="border border-indigo-200 rounded-xl overflow-hidden bg-white hover:shadow-md transition-shadow cursor-pointer"

                    >
                        <!-- Group Card Header -->
                        <div @click="viewGroupDetail(group.id)" class="bg-indigo-50 px-4 py-3 flex items-center justify-between border-b border-indigo-100">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold">
                                    {{ (group.sub_sessions || []).length }}
                                </div>
                                <span class="font-semibold text-gray-800 text-sm">
                                    {{ group.code || ($t('label.group_session') + ' #' + group.id) }}
                                </span>
                            </div>
                            <span class="text-xs rounded-full px-2 py-0.5" :class="group.status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700'">{{ group.status === 'in_progress' ? $t('label.in_progress') : $t('label.open') }}</span>
                        </div>
                        <!-- Group Card Body -->
                        <div class="px-4 py-3 space-y-2">
                            <p v-if="group.notes" class="text-sm text-gray-600 truncate">{{ group.notes }}</p>
                            <!-- Member list -->
                            <div class="space-y-2">
                                <div v-for="(sub, i) in (group.sub_sessions || [])" :key="sub.id">
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold flex-shrink-0">{{ i + 1 }}</span>
                                        <span class="truncate flex-1">{{ sub.guest_name || $t('label.person') + ' ' + (i + 1) }}</span>
                                    </div>
                                    <!-- Per-item start buttons for service items -->
                                    <div v-if="sub.session_items && sub.session_items.length > 0" class="mt-1 ml-7 space-y-0.5">
                                        <div v-for="si in sub.session_items" :key="'si-' + si.id" class="flex items-center gap-1.5 text-[10px]">


                                            <button
                                                v-if="isServiceItem(si.item_id) && si.status === 'pending' && sub.status !== 'done'"
                                                type="button"
                                                class="px-1.5 py-0.5 rounded bg-green-600 text-white text-[9px] leading-tight"
                                                :disabled="!!startItemLoading[si.id]"
                                                @click.stop="startItemInline(sub.id, si.id)">
                                                <i class="lab lab-play-line"></i> {{ $t('button.start') }}
                                            </button>

                                            <span class="rounded px-1 py-0.5" :class="itemStatusBadgeInline(si.status)">{{ si.status == 'in_progress' ? $t('label.in_progress') : si.status }}</span>

                                            <span :class="isServiceItem(si.item_id) ? 'text-blue-500' : 'text-orange-500'">{{ si.item_name || '—' }}</span>
                                            <span v-if="si.room_name" class="text-gray-400 text-[9px]">· {{ si.room_name }}</span>
                                            <span v-if="si.bed_name" class="text-gray-400 text-[9px]">· {{ si.bed_name }}</span>
                                        </div>
                                    </div>
                                    <div v-if="sub.status !== 'done' && permissionChecker('massage_sessions_edit')" class="flex gap-1 mt-1 ml-7">
                                        <button type="button"
                                            class="px-1.5 py-0.5 text-[10px] rounded border text-blue-600 border-blue-400 hover:bg-blue-50"
                                            @click.stop="toggleAddItemFormInline(sub.id, 'service')">
                                            <i class="lab lab-add-line"></i>
                                            {{ showAddItemSubId === sub.id && showAddItemMode === 'service' ? $t('button.cancel') : $t('button.add_service') }}
                                        </button>
                                        <button type="button"
                                            class="px-1.5 py-0.5 text-[10px] rounded border text-orange-600 border-orange-400 hover:bg-orange-50"
                                            @click.stop="toggleAddItemFormInline(sub.id, 'product')">
                                            <i class="lab lab-add-line"></i>
                                            {{ showAddItemSubId === sub.id && showAddItemMode === 'product' ? $t('button.cancel') : $t('button.add_product') }}
                                        </button>
                                    </div>
                                    <div v-if="showAddItemSubId === sub.id && showAddItemMode !== null"
                                        class="mt-2 ml-7 border border-indigo-200 rounded-lg p-2 bg-indigo-50 space-y-1.5"
                                        @click.stop>
                                        <p class="text-[10px] font-semibold text-indigo-700 uppercase tracking-wide">
                                            {{ showAddItemMode === 'service' ? $t('label.add_service') : $t('label.add_product') }}
                                        </p>
                                        <div>
                                            <select v-model="addItemForm.item_id" class="db-field-control text-xs py-0.5"
                                                :class="addItemErrors.item_id ? 'invalid' : ''">
                                                <option value="">-- {{ $t('label.select_item') }} --</option>
                                                <option v-for="item in (showAddItemMode === 'service' ? serviceItems : productItems)"
                                                    :key="item.id" :value="item.id">{{ itemName(item) }}</option>
                                            </select>
                                            <small v-if="addItemErrors.item_id" class="db-field-alert text-[10px]">{{ addItemErrors.item_id[0] }}</small>
                                        </div>
                                        <select v-model="addItemForm.room_id" class="db-field-control text-xs py-0.5">
                                            <option value="">-- {{ $t('label.room') }} --</option>
                                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                                        </select>
                                        <select v-model="addItemForm.bed_id" class="db-field-control text-xs py-0.5">
                                            <option value="">-- {{ $t('label.bed') }} --</option>
                                            <option v-for="bed in bedsForRoom" :key="bed.id" :value="bed.id">{{ bed.name }}</option>
                                        </select>
                                        <select v-if="showAddItemMode === 'service'" v-model="addItemForm.therapist_id" class="db-field-control text-xs py-0.5">
                                            <option value="">-- {{ $t('label.therapist') }} --</option>
                                            <option v-for="t in therapistProfilesList" :key="t.id" :value="t.user_id">
                                                {{ t.user ? t.user.name : t.user_id }}
                                            </option>
                                        </select>
                                        <div>
                                            <input type="number" v-model="addItemForm.price"
                                                class="db-field-control text-xs py-0.5" min="0" step="0.01"
                                                :placeholder="$t('label.price') + ' *'"
                                                :class="addItemErrors.price ? 'invalid' : ''" />
                                            <small v-if="addItemErrors.price" class="db-field-alert text-[10px]">{{ addItemErrors.price[0] }}</small>
                                        </div>
                                        <input type="number" v-model="addItemForm.quantity"
                                            class="db-field-control text-xs py-0.5" min="1" step="1"
                                            :placeholder="$t('label.quantity')" />
                                        <input type="number" v-model="addItemForm.discount"
                                            class="db-field-control text-xs py-0.5" min="0" step="0.01"
                                            :placeholder="$t('label.discount')" />
                                        <template v-if="showAddItemMode === 'service'">
                                            <input type="number" v-model="addItemForm.duration"
                                                class="db-field-control text-xs py-0.5" min="1"
                                                :placeholder="$t('label.duration_minutes')" />
                                            <input type="datetime-local" v-model="addItemForm.start_time"
                                                class="db-field-control text-xs py-0.5" />
                                        </template>
                                        <input type="text" v-model="addItemForm.notes"
                                            class="db-field-control text-xs py-0.5" :placeholder="$t('label.notes')" />
                                        <div class="flex gap-1 pt-0.5">
                                            <button type="button"
                                                class="px-2 py-1 text-[10px] rounded bg-indigo-600 text-white"
                                                :disabled="addItemLoading"
                                                @click.stop="submitAddItemInline(sub.id)">
                                                {{ addItemLoading ? '...' : $t('button.save') }}
                                            </button>
                                            <button type="button"
                                                class="px-2 py-1 text-[10px] rounded bg-gray-400 text-white"
                                                @click.stop="showAddItemSubId = null; showAddItemMode = null; resetAddItemFormInline()">
                                                {{ $t('button.cancel') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                                <span class="text-xs text-gray-400">{{ $t('label.total') }}</span>
                                <span class="font-bold text-indigo-700">{{ formatPrice(group.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="db-card db-tab-div active mb-5"  v-if="permissionChecker('session_queue')">
        <div class="p-4 space-y-6">

            <!-- ===== Section Divider ===== -->
            <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                    {{ $t('label.waiting_queue') }}
                </span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- ===== 5. Waiting Queue Panel ===== -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="lab lab-queue text-gray-400"></i>
                    {{ $t('label.waiting_queue') }}
                    <span v-if="waitingCount > 0" class="text-xs bg-yellow-100 text-yellow-800 rounded-full px-2 py-0.5">
                        {{ waitingCount }} {{ $t('label.waiting') }}
                    </span>
                </h4>
                <WaitingQueueComponent
                    ref="queueComponent"
                    :branch-id="currentBranchId"
                    @queue-updated="refreshBoard"
                />
            </div>

        </div>
    </div>

    <!-- Open Session Modal -->
    <OpenSessionModalComponent
        :room="selectedRoom"
        @session-opened="handleSessionOpened"
    />

    <!-- Queue Add Modal -->
    <QueueAddModalComponent :branch-id="currentBranchId" @queue-added="handleQueueAdded" />
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import RoomCardComponent from "./RoomCardComponent";
import WaitingQueueComponent from "./WaitingQueueComponent";
import OpenSessionModalComponent from "./OpenSessionModalComponent";
import QueueAddModalComponent from "./QueueAddModalComponent";
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";

export default {
    name: "FrontDeskBoardComponent",
    components: {
        LoadingComponent,
        RoomCardComponent,
        WaitingQueueComponent,
        OpenSessionModalComponent,
        QueueAddModalComponent,
    },
    data() {
        return {
            loading:              { isActive: false },
            selectedRoom:         null,

            autoRefreshInterval:  null,
            clockInterval:        null,
            now:                  Date.now(),
            newGroupLoading:      false,
            roomStatusFilter:     '',
            showAddItemSubId:     null,
            showAddItemMode:      null,
            addItemLoading:       false,
            startItemLoading:     {},
            addItemErrors:        {},
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
        rooms()           { return this.$store.getters['frontDesk/rooms'] ?? []; },
        filteredRooms() {
            if (!this.roomStatusFilter) return this.rooms;
            if (this.roomStatusFilter === 'occupied') {
                return this.rooms.filter(r => this.activeRoomItems(r).length > 0);
            }
            return this.rooms.filter(r => this.roomDisplayStatus(r) === this.roomStatusFilter);
        },
        roomFilters() {
            const all       = this.rooms.length;
            const occupied  = this.rooms.filter(r => this.activeRoomItems(r).length > 0).length;
            const available = this.rooms.filter(r => this.roomDisplayStatus(r) === 'available').length;
            const cleaning  = this.rooms.filter(r => this.roomDisplayStatus(r) === 'cleaning').length;
            return [
                { value: '',          label: this.$t('label.all'),       count: all },
                { value: 'available', label: this.$t('label.available'), count: available },
                { value: 'occupied',  label: this.$t('label.occupied'),  count: occupied },
                { value: 'cleaning',  label: this.$t('label.cleaning'),  count: cleaning },
            ];
        },
        therapists()      { return this.$store.getters['frontDesk/therapists'] ?? []; },
        groupSessions()   { return this.$store.getters['frontDesk/groupSessions'] ?? []; },
        summary()         { return this.$store.getters['frontDesk/summary'] ?? {}; },
        branch()          { return this.$store.getters['backendGlobalState/branchShow']; },
        currentBranchId() { return this.branch?.id || this.$store.getters.authBranchId || 0; },
        waitingCount() {
            return (this.$store.getters['sessionQueue/lists'] || [])
                .filter(q => q.status === 'waiting').length;
        },
        allItems()              { return this.$store.getters['item/lists'] ?? []; },
        serviceItems()          { return this.allItems.filter(i => i.item_kind === 2); },
        productItems()          { return this.allItems.filter(i => i.item_kind !== 2); },
        lang()                  { return this.$store.getters['frontendLanguage/show']?.code ?? 'en'; },
        therapistProfilesList() { return this.$store.getters['therapistProfile/lists'] ?? []; },
        allBeds()               { return this.$store.getters['bed/lists'] ?? []; },
        bedsForRoom()           {
            if (!this.addItemForm.room_id) return this.allBeds;
            return this.allBeds.filter(b => b.room_id === parseInt(this.addItemForm.room_id));
        },
        setting()               { return this.$store.getters['frontendSetting/lists']; },
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
        this.loadBoard();
        this.autoRefreshInterval = setInterval(this.loadBoard, 30000);
        this.clockInterval       = setInterval(() => { this.now = Date.now(); }, 1000);
        this.$store.dispatch('item/lists', { paginate: 0 });
        this.$store.dispatch('bed/lists', { paginate: 0 });
        this.$store.dispatch('therapistProfile/lists', { paginate: 0 });
    },
    unmounted() {
        if (this.autoRefreshInterval) clearInterval(this.autoRefreshInterval);
        if (this.clockInterval)       clearInterval(this.clockInterval);
    },
    methods: {
        permissionChecker(permission) {
            return appService.permissionChecker(permission);
        },
        loadBoard() {
            this.loading.isActive = true;
            this.$store.dispatch('frontDesk/loadBoard', this.currentBranchId)
                .catch((err) => { alertService.error(err.response?.data?.message); })
                .finally(() => { this.loading.isActive = false; });
        },
        refreshBoard() {
            this.loadBoard();
            if (this.$refs.queueComponent) {
                this.$refs.queueComponent.loadQueue();
            }
        },
        handleOpenSession({ room }) {
            this.selectedRoom = room;
            this.$nextTick(() => {
                appService.modalShow('#openSessionModal');
            });
        },
        handleSessionOpened(session) {
            this.selectedRoom = null;
            if (session && session.group_session_id) {
                this.$router.push({ name: 'admin.group-session.detail', params: { id: session.group_session_id } });
            } else {
                this.refreshBoard();
            }
        },
        openQueueAddModal() {
            appService.modalShow('#queueAddModal');
        },
        handleQueueAdded() {
            if (this.$refs.queueComponent) {
                this.$refs.queueComponent.loadQueue();
            }
        },
        handleCheckout(session) {
            // Navigate to the detail page instead of direct checkout
            this.viewSessionDetail(session.id ?? session);
        },
        viewSessionDetail(id) {
            this.$router.push({ name: 'admin.sub-session.detail', params: { id } });
        },
        viewGroupDetail(id) {
            this.$router.push({ name: 'admin.group-session.detail', params: { id } });
        },
        activeRoomItems(room) {
            return (room.active_items || []).filter(si => si.status !== 'completed');
        },
        roomDisplayStatus(room) {
            if (this.activeRoomItems(room).length > 0) return 'occupied';
            return room.status || (room.is_occupied ? 'occupied' : 'available');
        },
        roomStatusLabel(room) {
            const status = this.roomDisplayStatus(room);
            const map = {
                available: this.$t('label.available'),
                occupied:  this.$t('label.occupied'),
                cleaning:  this.$t('label.cleaning'),
            };
            return map[status] || status;
        },
        roomScheduleCardClass(room) {
            const map = {
                available: 'border-green-200 bg-green-50',
                occupied:  'border-blue-200 bg-blue-50',
                cleaning:  'border-cyan-200 bg-cyan-50',
            };
            return map[this.roomDisplayStatus(room)] || 'border-gray-200 bg-gray-50';
        },
        roomScheduleHeaderClass(room) {
            const map = {
                available: 'bg-green-100 border-green-200',
                occupied:  'bg-blue-100 border-blue-200',
                cleaning:  'bg-cyan-100 border-cyan-200',
            };
            return map[this.roomDisplayStatus(room)] || 'bg-gray-100 border-gray-200';
        },
        roomStatusBadgeClass(room) {
            const map = {
                available: 'bg-green-200 text-green-800',
                occupied:  'bg-blue-200 text-blue-800',
                cleaning:  'bg-cyan-200 text-cyan-800',
            };
            return map[this.roomDisplayStatus(room)] || 'bg-gray-200 text-gray-700';
        },
        roomEmptyTextClass(room) {
            const map = {
                available: 'text-green-600',
                cleaning:  'text-cyan-700',
            };
            return map[this.roomDisplayStatus(room)] || 'text-gray-500';
        },
        roomEmptyIcon(room) {
            return this.roomDisplayStatus(room) === 'available' ? 'lab lab-check' : 'lab lab-door-2';
        },
        openNewGroup() {
            this.newGroupLoading = true;
            this.$store.dispatch('frontDesk/createGroup', { branch_id: this.currentBranchId })
                .then((res) => {
                    const group = res.data.data;
                    this.viewGroupDetail(group.id);
                })
                .catch((err) => { alertService.error(err.response?.data?.message); })
                .finally(() => { this.newGroupLoading = false; });
        },
        elapsedTime(startedAt) {
            const diffSec = Math.max(0, Math.floor((this.now - new Date(startedAt).getTime()) / 1000));
            const h = Math.floor(diffSec / 3600);
            const m = Math.floor((diffSec % 3600) / 60);
            const s = diffSec % 60;
            if (h > 0) {
                return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            }
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        therapistCardClass(status) {
            const map = {
                available: 'bg-green-50 border-green-200',
                busy:      'bg-red-50 border-red-200',
                away:      'bg-yellow-50 border-yellow-200',
            };
            return map[status] ?? 'bg-gray-50 border-gray-200';
        },
        therapistAvatarClass(status) {
            const map = {
                available: 'bg-green-100 text-green-700',
                busy:      'bg-red-100 text-red-700',
                away:      'bg-yellow-100 text-yellow-700',
            };
            return map[status] ?? 'bg-gray-200 text-gray-600';
        },
        therapistBadgeClass(status) {
            const map = {
                available: 'bg-green-100 text-green-700',
                busy:      'bg-red-100 text-red-700',
                away:      'bg-yellow-100 text-yellow-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },
        sessionStatusBadge(status) {
            const map = {
                pending:     'bg-yellow-100 text-yellow-700',
                in_progress: 'bg-blue-100 text-blue-700',
                completed:   'bg-green-100 text-green-700',
            };
            return map[status] ?? 'bg-gray-100 text-gray-600';
        },

        toggleAddItemFormInline(subId, mode) {
            if (this.showAddItemSubId === subId && this.showAddItemMode === mode) {
                this.showAddItemSubId = null;
                this.showAddItemMode  = null;
            } else {
                this.showAddItemSubId = subId;
                this.showAddItemMode  = mode;
            }
            this.resetAddItemFormInline();
        },

        resetAddItemFormInline() {
            this.addItemErrors = {};
            this.addItemForm = { item_id: '', quantity: 1, room_id: '', bed_id: '', therapist_id: '', price: '', discount: '', duration: '', start_time: '', notes: '' };
        },

        submitAddItemInline(subId) {
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
                id:   subId,
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
                this.showAddItemSubId = null;
                this.showAddItemMode  = null;
                this.resetAddItemFormInline();
                this.refreshBoard();
            }).catch((err) => {
                this.addItemErrors = err.response?.data?.errors ?? {};
                alertService.error(err.response?.data?.message);
            }).finally(() => { this.addItemLoading = false; });
        },

        startItemInline(subSessionId, itemId) {
            this.startItemLoading = { ...this.startItemLoading, [itemId]: true };
            this.$store.dispatch('subSession/startItem', { sessionId: subSessionId, itemId })
                .then(() => {
                    alertService.success(this.$t('message.session_started') || 'Service started');
                    this.refreshBoard();
                }).catch((err) => {
                    alertService.error(err.response?.data?.message || err.message || 'Failed to start service');
                }).finally(() => {
                    this.startItemLoading = { ...this.startItemLoading, [itemId]: false };
                });
        },

        itemStatusBadgeInline(status) {
            const map = { pending: 'bg-gray-100 text-gray-500', in_progress: 'bg-blue-100 text-blue-600', completed: 'bg-green-100 text-green-600' };
            return map[status] ?? 'bg-gray-100 text-gray-500';
        },

        itemName(item) {
            if (!item) return '—';
            return (this.lang !== 'en' && item['name_' + this.lang]) ? item['name_' + this.lang] : item.name;
        },
        formatPrice(val) {
            return appService.currencyFormat(
                parseFloat(val) || 0,
                this.setting?.site_digit_after_decimal_point,
                this.branch?.currency_id?.symbol,
                this.setting?.site_currency_position
            );
        },
        isServiceItem(itemId) {
            const item = this.allItems.find(i => i.id === parseInt(itemId));
            return item ? item.item_kind === 2 : false;
        },
    },
};
</script>
