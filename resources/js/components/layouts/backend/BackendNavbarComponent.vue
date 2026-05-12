<template>
    <div class="backdrop"></div>
    <header class="db-header border-b border-gray-200 py-2" v-show="isNavbarReady">
        <div class="flex items-center gap-2 float-left">
            <button class="fa-solid fa-align-left db-header-nav w-9 h-9 rounded-lg text-primary bg-primary/5"></button>
            <router-link class="w-24 flex-shrink-0" :to="{ name: 'admin.dashboard' }">
                <img class="w-full" :src="setting.theme_logo" alt="logo" />
            </router-link>
        </div>
        <div class="flex items-center justify-end w-full gap-2">
            <div v-if="hasSecondaryButtons" class="dropdown-group relative hidden sm:block xl:hidden">
                <div class="flex items-center justify-between md:justify-center gap-4">
                    <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="dropdown-group relative">
                        <button class="dropdown-btn px-2 h-16 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-1 min-w-20">
                            <i class="fas fa-ellipsis-h text-sm"></i>
                            <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-sm">{{ $t('label.quick_menu') || 'Menu' }}</span>
                        </button>
                        <ul v-if="languages.length > 0" class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-[72px] right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                            <div v-if="branch.show_retail_pos_button == statusEnum.ACTIVE" class="mb-1">
                                <router-link v-if="pos.permission" class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-blue-600 hover:bg-blue-50 transition-colors duration-200" :to="{ name: 'admin.pos.retail' }">
                                    <i class="lab lab-pos-bold lab-font-size-16"></i>
                                    <span class="text-sm">{{ $t('label.retail') || 'Retail' }}</span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_quick_pos_button == statusEnum.ACTIVE" class="mb-1">
                                <router-link v-if="pos.permission" class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-green-600 hover:bg-green-50 transition-colors duration-200" :to="{ name: 'admin.quickpos' }">
                                    <i class="lab lab-pos-bold lab-font-size-16"></i>
                                    <span class="text-sm">{{ $t('label.quick_pos') || 'Quick POS' }}</span>
                                </router-link>
                            </div>

                            <div v-if="showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-blue-600 hover:bg-blue-200 transition-colors duration-200" :to="{ name: 'admin.orders', query: { show: 'pos-orders' } }">
                                    <i class="text-sm lab lab-pos-orders"></i>
                                    <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="ms-1 text-xs">{{ $t('label.orders') }}</span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_unpaid_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-red-600 hover:bg-red-50 transition-colors duration-200" :to="{ name: 'admin.orders', query: { show: 'unpaid-orders' } }">
                                    <i class="text-sm lab lab-fill-moneys"></i>
                                    <span class="text-sm">{{ $t('label.unpaid') }}</span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_online_order_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-blue-600 hover:bg-blue-50 transition-colors duration-200 relative" :to="'/admin/pending-orders'">
                                    <i class="lab lab-site"></i>
                                    <span class="text-sm">{{ $t('label.online') }}</span>
                                    <span v-if="pendingOnlineOrdersCount > 0" class="ml-auto bg-red-500 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                                        {{ pendingOnlineOrdersCount }}
                                    </span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_pending_order_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-orange-600 hover:bg-orange-50 transition-colors duration-200 relative" :to="{ name: 'admin.orders', query: { show: 'pending-orders' } }">
                                    <i class="lab lab-pos-orders"></i>
                                    <span class="text-sm">{{ $t('label.pending') }}</span>
                                    <span v-if="pendingOrdersCount > 0" class="ml-auto bg-red-500 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                                        {{ pendingOrdersCount }}
                                    </span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_table_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-blue-600 hover:bg-blue-50 transition-colors duration-200" :to="'/admin/pos-orders/list-dining-table'">
                                    <i class="lab lab-dining-table"></i>
                                    <span class="text-sm">{{ $t('label.table') }}</span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_floor_plan == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <router-link class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-purple-600 hover:bg-purple-50 transition-colors duration-200" :to="{ name: 'admin.floorPlan' }">
                                    <i class="lab lab-shop"></i>
                                    <span class="text-sm">{{ $t('label.floor_plan') }}</span>
                                </router-link>
                            </div>
                            <div v-if="branch.show_customer_view_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="mb-1">
                                <button @click="openCustomerView()" :class="customerViewOpen ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-blue-600 hover:bg-blue-50'" class="w-full px-3 py-2 rounded-lg flex items-center gap-3 transition-colors duration-200">
                                    <i class="lab lab-view text-sm"></i>
                                    <span class="text-sm">{{ $t('label.customer_view') }}</span>
                                    <i v-if="customerViewOpen" class="fas fa-circle text-[6px] text-blue-500 ml-auto"></i>
                                </button>
                            </div>
                            <div v-if="showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                                <div class="w-full px-3 py-2 rounded-lg flex items-center gap-3" :class="internetStatus.isOnline ? 'text-green-600' : 'text-red-600'" :title="internetStatus.isOnline ? 'Connected to Internet' : 'No Internet Connection'">
                                    <i :class="internetStatus.isOnline ? 'fas fa-wifi' : 'fas fa-wifi-slash'" class="text-sm"></i>
                                    <span class="text-sm">{{ internetStatus.isOnline ? $t('label.online') : $t('label.offline') }}</span>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 my-2"></div>

                            <div class="">
                                <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="dropdown-group relative">
                                    <button class="dropdown-btn w-full px-3 py-2 rounded-lg flex items-center gap-3">
                                        <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full" />
                                        <span class="whitespace-nowrap text-xs font-medium capitalize text-heading">
                                            {{ language.name }}
                                        </span>
                                    </button>
                                    <ul v-if="languages.length > 0" class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-14 right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                                        <li @click="changeLanguage(lang.id, lang.code)" v-for="lang in languages" :key="lang.id" class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer hover:bg-gray-100" :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                                            <img :src="lang.image" alt="flag" class="w-4 h-4 rounded-full" />
                                            <span class="text-heading capitalize text-sm">{{ lang.name }}</span>
                                            <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs ml-auto"></i>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div v-if="authBranch === 0" class="relative dropdown-group">
                                <button class="dropdown-btn w-full px-3 py-2 rounded-lg flex items-center gap-3 dropdown-btn">
                                    <i class="lab lab-shop font-fill-primary"></i>
                                    <span class="block">{{ $t('label.branch') }}</span>
                                    <b class="whitespace-nowrap">{{ branch.name }}</b>
                                    <i class="lab lab-arrow-down text-xs ml-1.5 lab-font-size-14"></i>
                                </button>
                                <ul v-if="branches.length > 0" class="p-2 w-fit rounded-lg shadow-xl absolute top-14 left-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                                    <li v-for="branch in branches" class="flex items-center gap-2 w-full px-2.5 rounded-md transition hover:bg-gray-100">
                                        <input @click="changeBranch(branch.id)" v-model="defaultBranch" type="radio" :id="'branch_id_' + branch.id" :value="branch.id" name="branch" class="w-3 cursor-pointer mb-[1px] accent-primary" />
                                        <label :for="'branch_id_' + branch.id" class="capitalize leading-8 text-sm min-w-[150px] cursor-pointer text-heading">
                                            {{ branch.name }}
                                        </label>
                                    </li>
                                </ul>
                            </div>
                            <div class="border-t border-gray-200 my-2"></div>
                            <div class="mb-1">
                                <button @click="fullPage()" class="w-full px-3 py-2 rounded-lg flex items-center gap-3 text-orange-600 hover:bg-orange-50 transition-colors duration-200">
                                    <i class="lab lab-element-3"></i>
                                    <span class="text-sm">{{ $t('label.fullscreen') }}</span>
                                </button>
                            </div>
                        </ul>
                    </div>
                </div>

            </div>


            <div class="hidden sm:flex sm:gap-3">
                <div v-if="branch.show_pos_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link v-if="pos.permission" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ path: '/admin/' + pos.url }">
                        <i class="text-sm lab lab-pos-bold"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">POS</span>
                    </router-link>
                </div>

                <div v-if="branch.show_retail_pos_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link v-if="pos.permission" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ name: 'admin.pos.retail' }">
                        <i class="text-sm lab lab-pos-bold"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.retail') }}</span>
                    </router-link>
                </div>

                <div v-if="branch.show_quick_pos_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link v-if="pos.permission" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-green-100 text-green-600 hover:bg-green-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ name: 'admin.quickpos' }">
                        <i class="text-sm lab lab-pos-bold"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.quick_pos') }}</span>
                    </router-link>
                </div>

                <div v-if="branch.show_table_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link class="db-table-badge bg-blue-100 text-blue-600 px-1 h-14 rounded-lg flex flex-col items-center justify-center gap-0.5 min-w-16" :to="'/admin/pos-orders/list-dining-table'">
                        <i class="text-sm lab lab-dining-table"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.table') }}</span>
                    </router-link>
                </div>
            </div>
            <div class="hidden xl:contents">
                <div v-if="showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ name: 'admin.orders', query: { show: 'pos-orders' } }">
                        <i class="text-sm lab lab-pos-orders"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.orders') }}</span>
                    </router-link>
                </div>
                <div v-if="branch.show_unpaid_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-red-100 text-red-600 hover:bg-red-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ name: 'admin.orders', query: { show: 'unpaid-orders' } }">
                        <i class="text-sm lab lab-fill-moneys"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.unpaid') }}</span>
                    </router-link>
                </div>
                <div v-if="branch.show_online_order_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link
                        class="db-table-badge bg-blue-100 text-blue-600 px-1 h-14 rounded-lg flex flex-col items-center justify-center relative gap-0.5 min-w-16"
                        :to="{ name: 'admin.online.order.list', query: { status: orderStatusEnum.PENDING, payment_status: paymentStatusEnum.PAID, from_date: '', to_date: '' } }"
                    >
                        <i class="text-sm lab lab-site"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.online') }}</span>
                        <span v-if="pendingOnlineOrdersCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full min-w-[20px] h-5 flex items-center justify-center px-1">
                            {{ pendingOnlineOrdersCount }}
                        </span>
                    </router-link>
                </div>
                <div v-if="branch.show_pending_order_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link class="db-table-badge bg-orange-100 text-orange-600 px-1 h-14 rounded-lg flex flex-col items-center justify-center relative gap-0.5 min-w-16" :to="{ name: 'admin.orders', query: { show: 'pending-orders' } }">
                        <i class="text-sm lab lab-pos-orders"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.pending') }}</span>
                        <span v-if="pendingOrdersCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full min-w-[20px] h-5 flex items-center justify-center px-1">
                            {{ pendingOrdersCount }}
                        </span>
                    </router-link>
                </div>

                <div v-if="branch.show_floor_plan == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <router-link class="db-table-badge bg-purple-100 text-purple-600 px-1 h-14 rounded-lg flex flex-col items-center justify-center gap-0.5 min-w-16" :to="{ name: 'admin.floorPlan' }">
                        <i class="text-sm lab lab-shop"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.floor_plan') }}</span>
                    </router-link>
                </div>
                <div v-if="branch.show_customer_view_button == statusEnum.ACTIVE && showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <button @click="openCustomerView()" :class="customerViewOpen ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600'" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center gap-0.5 min-w-16 transition-colors duration-200">
                        <i class="text-sm lab lab-view"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.customer_view') }}</span>
                    </button>
                </div>
                <div v-if="showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <button @click="fullPage()" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-[#FFEBD8] text-red-700 gap-0.5 min-w-16">
                        <i class="text-sm lab lab-element-3"></i>
                        <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.fullscreen') }}</span>
                    </button>
                </div>
                <div v-if="showMenuType === menuTypeEnum.POS" class="flex items-center justify-between md:justify-center">
                    <div class="h-14 rounded-lg flex flex-col items-center justify-center gap-0.5 min-w-16" :class="internetStatus.isOnline ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" :title="internetStatus.isOnline ? 'Connected to Internet' : 'No Internet Connection'">
                        <i :class="internetStatus.isOnline ? 'fas fa-wifi' : 'fas fa-wifi-slash'" class="text-sm"></i>
                        <span class="text-[10px]">{{ internetStatus.isOnline ? $t('label.online') : $t('label.offline') }}</span>
                    </div>
                </div>

                <div v-if="false" class="flex items-center justify-between md:justify-center gap-4">
                    <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="dropdown-group relative">
                        <button class="dropdown-btn px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-0.5 min-w-16">
                            <img :src="language.image" alt="flag" class="w-4 h-4 rounded-full" />
                            <span class="whitespace-nowrap text-[10px] font-medium capitalize text-heading mt-3">
                                {{ language.name }}
                            </span>
                        </button>
                        <ul v-if="languages.length > 0" class="p-2 min-w-[180px] rounded-lg shadow-xl absolute top-[72px] right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                            <li @click="changeLanguage(lang.id, lang.code)" v-for="lang in languages" :key="lang.id" class="flex items-center gap-2 py-1.5 px-2.5 rounded-md cursor-pointer hover:bg-gray-100" :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                                <img :src="lang.image" alt="flag" class="w-4 h-4 rounded-full" />
                                <span class="text-heading capitalize text-sm">{{ lang.name }}</span>
                                <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs ml-auto"></i>
                            </li>
                        </ul>
                    </div>
                </div>
                <div v-if="false" class="flex items-center justify-between md:justify-center gap-4">
                    <div v-if="authBranch === 0" class="relative dropdown-group">
                        <button class="dropdown-btn px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-0.5 min-w-16">
                            <i class="lab lab-shop text-sm font-fill-primary"></i>
                            <span class="whitespace-nowrap text-[10px] font-medium capitalize text-heading mt-3">
                                {{ branch.name }}
                            </span>
                        </button>
                        <ul v-if="branches.length > 0" class="p-2 w-fit rounded-lg shadow-xl absolute top-[72px] left-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                            <li v-for="branchItem in branches" :key="branchItem.id" class="flex items-center gap-2 w-full px-2.5 rounded-md transition hover:bg-gray-100">
                                <input
                                    @click="changeBranch(branchItem.id)"
                                    v-model="defaultBranch"
                                    type="radio"
                                    :id="'branch_id_' + branchItem.id"
                                    :value="branchItem.id"
                                    name="branch"
                                    class="w-3 cursor-pointer mb-[1px] accent-primary"
                                />
                                <label
                                    :for="'branch_id_' + branchItem.id"
                                    class="capitalize leading-8 text-sm min-w-[150px] cursor-pointer text-heading"
                                >
                                    {{ branchItem.name }}
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="branch.show_quick_pos_button == statusEnum.ACTIVE" class="flex items-center justify-between md:justify-center md:hidden">
                <router-link v-if="pos.permission" class="px-1 h-14 rounded-lg flex flex-col items-center justify-center bg-green-100 text-green-600 hover:bg-green-200 transition-colors duration-200 gap-0.5 min-w-16" :to="{ name: 'admin.quickpos' }">
                    <i class="text-sm lab lab-pos-bold"></i>
                    <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ $t('label.quick_pos') }}</span>
                </router-link>
            </div>

            <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE && languages.length > 0" class="dropdown-group relative hidden md:block">
                <button class="dropdown-btn px-2 h-14 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-0.5 min-w-16">
                    <i class="fa-solid fa-language text-sm"></i>
                    <span class="whitespace-nowrap text-[10px] font-medium capitalize text-heading">
                        {{ language.code ? language.code.toUpperCase() : textShortener(language.name, 8) }}
                    </span>
                </button>
                <ul class="p-2 min-w-[220px] rounded-lg shadow-xl absolute top-[72px] right-0 z-10 border border-gray-200 bg-white hidden dropdown-list">
                    <li
                        v-for="lang in languages"
                        :key="lang.id"
                        @click="changeLanguage(lang.id, lang.code)"
                        class="flex items-center gap-2 py-2 px-2.5 rounded-md cursor-pointer hover:bg-gray-100"
                        :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }"
                    >
                        <img :src="lang.image" alt="flag" class="w-4 h-4 rounded-full" />
                        <span class="text-heading capitalize text-sm flex-1">{{ lang.name }}</span>
                        <span class="text-[10px] text-gray-500 uppercase">{{ lang.code ? lang.code.toUpperCase() : '' }}</span>
                        <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs"></i>
                    </li>
                </ul>
            </div>

            <button
                @click="toggleMenuType"
                class="px-1 h-14 rounded-lg flex flex-col items-center justify-center text-primary bg-primary/5 hover:bg-primary/10 transition-colors duration-200 gap-0.5 min-w-16"
                :title="showMenuType === menuTypeEnum.BACKEND ? 'Switch to POS Menu' : 'Switch to Back Office Menu'"
            >
                <i :class="showMenuType === menuTypeEnum.BACKEND ? 'fa-solid fa-cash-register' : 'fa-solid fa-gear'" class="text-sm"></i>
                <span v-if="branch.show_navbar_button_text == statusEnum.ACTIVE" class="text-[10px]">{{ showMenuType === menuTypeEnum.BACKEND ? 'Exit' : 'Back Office' }}</span>
            </button>

            <div class="dropdown-group relative">
                <button class="dropdown-btn px-2 h-14 rounded-lg flex flex-col items-center justify-center bg-primary/5 text-primary hover:bg-primary/10 transition-colors duration-200 gap-1 min-w-20">
                    <img class="flex-shrink-0 w-6 h-6 object-cover rounded-full" :src="authInfo.image" alt="avatar" />
                    <span class="whitespace-nowrap text-xs font-medium capitalize text-heading">
                        {{ textShortener(authInfo.name, 15) }}
                    </span>
                </button>
                <div class="dropdown-list absolute top-[72px] right-0 z-[60] rounded-xl w-[360px] p-4 shadow-paper bg-white">
                    <div class="w-fit mx-auto text-center mb-5">
                        <figure
                            class="relative z-10 w-[98px] h-[98px] border-2 border-dashed rounded-full inline-flex items-center justify-center border-white bg-gradient-to-t from-[#FF7A00] to-[#FF016C] before:absolute before:top-1/2 before:left-1/2 before:-translate-x-1/2 before:-translate-y-1/2 before:w-24 before:h-24 before:rounded-full before:-z-10 before:bg-white"
                        >
                            <img class="w-[90px] h-[90px] rounded-full shadow-avatar" :src="authInfo.image" alt="avatar" />
                        </figure>
                        <label for="imageProperty" class="block w-11 h-11 mx-auto -mt-7 mb-3 relative z-10 rounded-full border-2 cursor-pointer bg-heading border-white">
                            <input @change="saveImage" accept="image/png, image/jpeg, image/jpg" ref="imageProperty" type="file" id="imageProperty" class="w-full h-full rounded-full opacity-0 cursor-pointer" />
                            <i class="lab lab-edit-2 absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 -z-10 lab-font-size-24 lab-font-color-1"></i>
                        </label>
                        <h3 class="font-medium text-sm leading-6 capitalize mb-0.5">{{ textShortener(authInfo.name, 20) }}</h3>
                        <p class="text-xs mb-0.5">{{ authInfo.email }}</p>
                        <p dir="ltr" class="text-xs">{{ authInfo.country_code }}{{ authInfo.phone }}</p>
                        <h3 class="font-medium text-sm leading-6 capitalize mb-0.5">{{ authInfo.currency_balance }}</h3>
                        <h3 class="text-xs mb-0.5">Version 1.5.1</h3>
                    </div>
                    <nav>
                        <router-link :to="{ name: 'admin.profile.editProfile' }" class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b border-[#EFF0F6]">
                            <i class="lab lab-edit lab-font-size-17"></i>
                            <span class="text-sm leading-6 capitalize">{{ $t('button.edit_profile') }}</span>
                        </router-link>
                        <router-link :to="{ name: 'admin.profile.changePassword' }" class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b border-[#EFF0F6]">
                            <i class="lab lab-key lab-font-size-17"></i>
                            <span class="text-sm leading-6 capitalize">{{ $t('button.change_password') }}</span>
                        </router-link>

                        <!-- Language Selector -->
                        <div v-if="setting.site_language_switch === enums.activityEnum.ENABLE" class="relative dropdown-group border-b border-[#EFF0F6]">
                            <button class="dropdown-btn paper-link transition w-full flex items-center gap-3.5 py-3">
                                <img :src="language.image" alt="flag" class="w-5 h-5 rounded-full" />
                                <span class="text-sm leading-6 capitalize flex-1 text-left">{{ language.name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <ul v-if="languages.length > 0" class="p-2 min-w-[320px] rounded-lg shadow-xl absolute top-full left-0 sm:top-0 sm:right-full sm:left-auto sm:mr-2 z-10 border border-gray-200 bg-white hidden dropdown-list">
                                <li @click="changeLanguage(lang.id, lang.code)" v-for="lang in languages" :key="lang.id" class="flex items-center gap-2 py-2 px-3 rounded-md cursor-pointer hover:bg-gray-100" :class="{ 'bg-primary/10 text-primary': isCurrentLanguage(lang.id) }">
                                    <img :src="lang.image" alt="flag" class="w-5 h-5 rounded-full" />
                                    <span class="text-heading capitalize text-sm flex-1">{{ lang.name }}</span>
                                    <i v-if="isCurrentLanguage(lang.id)" class="fa-solid fa-check text-primary text-xs"></i>
                                </li>
                            </ul>
                        </div>

                        <!-- Branch Selector -->
                        <div v-if="authBranch === 0" class="relative dropdown-group border-b border-[#EFF0F6]">
                            <button class="dropdown-btn paper-link transition w-full flex items-center gap-3.5 py-3">
                                <i class="lab lab-shop lab-font-size-17"></i>
                                <span class="text-sm leading-6 capitalize flex-1 text-left">{{ branch.name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <ul v-if="branches.length > 0" class="p-2 min-w-[320px] rounded-lg shadow-xl absolute top-full left-0 sm:top-0 sm:right-full sm:left-auto sm:mr-2 z-10 border border-gray-200 bg-white hidden dropdown-list max-h-[300px] overflow-y-auto">
                                <li v-for="branchItem in branches" :key="branchItem.id" class="flex items-center gap-2 py-2 px-3 rounded-md transition hover:bg-gray-100 cursor-pointer">
                                    <input
                                        @click="changeBranch(branchItem.id)"
                                        v-model="defaultBranch"
                                        type="radio"
                                        :id="'profile_branch_' + branchItem.id"
                                        :value="branchItem.id"
                                        name="profile_branch"
                                        class="w-4 h-4 cursor-pointer accent-primary"
                                    />
                                    <label
                                        :for="'profile_branch_' + branchItem.id"
                                        class="capitalize text-sm cursor-pointer flex-1"
                                    >
                                        {{ branchItem.name }}
                                    </label>
                                </li>
                            </ul>
                        </div>

                        <button @click="clearCache()" class="paper-link transition w-full flex items-center gap-3.5 py-3 border-b border-[#EFF0F6]">
                            <i class="lab lab-reset"></i>
                            <span class="text-sm leading-6 capitalize">{{ $t('button.clear_cache') }}</span>
                        </button>
                        <button @click="logout()" class="paper-link transition w-full flex items-center gap-3.5 py-3">
                            <i class="lab lab-logout lab-font-size-17"></i>
                            <span class="text-sm leading-6 capitalize">{{ $t('button.logout') }}</span>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Loading skeleton -->
    <header class="db-header border-b border-gray-200" v-show="!isNavbarReady">
        <div class="flex items-center gap-2 float-left">
            <div class="w-24 h-12 bg-gray-200 animate-pulse rounded"></div>
        </div>
        <div class="flex items-center justify-end w-full gap-2">
            <div class="hidden sm:flex sm:gap-3">
                <div class="w-16 h-16 bg-gray-200 animate-pulse rounded-lg"></div>
                <div class="w-16 h-16 bg-gray-200 animate-pulse rounded-lg"></div>
                <div class="w-16 h-16 bg-gray-200 animate-pulse rounded-lg"></div>
            </div>
            <div class="w-12 h-12 bg-gray-200 animate-pulse rounded-lg"></div>
        </div>
    </header>
    <div id="internetAlert" v-if="!internetStatus.isOnline || internetStatus.showAlert" class="modal active ff-modal" style="z-index: 9999">
        <div class="modal-dialog max-w-[400px] p-6 text-center relative bg-red-50 border-2 border-red-200">
            <div class="mb-4">
                <i class="fas fa-wifi-slash text-red-500 text-4xl mb-3"></i>
            </div>
            <h3 class="text-[20px] font-bold leading-8 mb-4 text-red-700">
                {{ $t('message.no_internet_connection') || 'No Internet Connection' }}
            </h3>
            <p class="text-red-600 mb-6">
                {{ $t('message.internet_required') || 'This system requires an active internet connection to function properly. Please check your connection and try again.' }}
            </p>
            <div class="flex flex-col gap-3">
                <button @click="checkInternetConnection" class="db-btn h-[40px] bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': loading.internetCheck }"></i>
                    {{ $t('button.retry_connection') || 'Retry Connection' }}
                </button>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-clock mr-1"></i>
                    {{ $t('label.last_check') || 'Last check' }}: {{ formatLastCheckTime() }}
                </div>
            </div>
        </div>
    </div>


    <div id="order" v-if="orderNotificationStatus" ref="orderNotificationModal" class="modal active ff-modal">
        <div class="modal-dialog max-w-[360px] p-6 text-center relative">
            <button @click.prevent="closeOrderNotificationModal" class="modal-close absolute top-4 right-4">
                <i class="fa-regular fa-circle-xmark"></i>
            </button>
            <h3 class="text-[18px] font-semibold leading-8 mb-6">
                {{ orderNotificationMessage }}
                <span class="block">{{ $t('message.please_check_your_order_list') }}</span>
            </h3>
            <router-link @click.prevent="closeOrderNotificationModal" :to="{ path: '/admin/' + orderNotification.url }" class="db-btn h-[38px] shadow-[0px_6px_10px_rgba(23,_114,_255,_0.24)] bg-primary text-white">
                {{ $t('button.let_me_check') }}
            </router-link>
        </div>
    </div>
</template>
<script>
import activityEnum from '../../../enums/modules/activityEnum';
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import _ from 'lodash';
import alertService from '../../../services/alertService';
import appService from '../../../services/appService';
import internetConnectivityService from '../../../services/internetConnectivityService';
// import { initializeApp } from 'firebase/app';
// import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import axios from 'axios';
import i18n from '../../../i18n';
import Fullscreen from 'vue-material-design-icons/Fullscreen.vue';
import CashClockIcon from 'vue-material-design-icons/CashClock.vue';
import TableSettings from 'vue-material-design-icons/TableSettings.vue';
import statusEnum from '../../../enums/modules/statusEnum';
import menuTypeEnum from '../../../enums/modules/menuTypeEnum';
// import diningTableModalComponent from './DiningTableModalComponent';

export default {
    name: 'BackendNavbarComponent',
    components: {
        Fullscreen,
        CashClockIcon,
        TableSettings,
        diningTableModalComponent: () => import('./DiningTableModalComponent'),
    },
    data() {
        return {
            props: {
                // tableDining: [],
            },
            loading: {
                isActive: false,
                internetCheck: false,
            },
            dataLoaded: {
                defaultAccess: false,
                branches: false,
                languages: false,
                settings: false,
                // tableDinings: false,
                initialLoadComplete: false,
            },
            enums: {
                activityEnum: activityEnum,
            },
            statusEnum: statusEnum,
            menuTypeEnum: menuTypeEnum,
            orderStatusEnum: orderStatusEnum,
            paymentStatusEnum: paymentStatusEnum,
            defaultBranch: null,
            defaultLanguage: null,
            pendingOrdersInterval: null,
            previousPendingCount: 0,
            pendingOrdersCountInterval: null,
            previousPendingOrdersCount: 0,
            pos: {
                permission: false,
                url: '',
            },
            branchProps: {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
            },
            languageProps: {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
            },
            orderNotificationStatus: false,
            orderNotificationMessage: '',
            orderNotification: {
                permission: false,
                url: '',
            },
            internetStatus: {
                isOnline: internetConnectivityService.getStatus().isOnline,
                showAlert: false,
                lastCheckTime: internetConnectivityService.getStatus().lastCheckTime,
            },
            customerViewWindow: null, // Track the customer view popup reference
            customerViewOpen: false,   // Toggle state for the customer view button
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
        authBranch: function () {
            return this.$store.getters.authBranchId;
        },
        branches: function () {
            return this.$store.getters['backendGlobalState/branches'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        languages: function () {
            return this.$store.getters['frontendLanguage/lists'];
        },
        language: function () {
            const globalState = this.$store.getters['globalState/get'];
            if (globalState && globalState.language_id && this.languages && this.languages.length > 0) {
                return this.languages.find((lang) => String(lang.id) === String(globalState.language_id)) || this.languages[0];
            }
            if (globalState && globalState.language_code && this.languages && this.languages.length > 0) {
                return this.languages.find((lang) => lang.code === globalState.language_code) || this.languages[0];
            }
            return this.languages && this.languages.length > 0 ? this.languages[0] : this.$store.getters['frontendLanguage/show'] || {};
        },
        permissions: function () {
            return this.$store.getters.authPermission;
        },
        // tableDinings: function () {
        //     return this.$store.getters['tableDiningTable/lists'];
        // },
        pendingOnlineOrdersCount: function () {
            return this.$store.getters['onlineOrder/pendingCount'];
        },
        pendingOrdersCount: function () {
            return this.$store.getters['posOrder/pendingCount'] || 0;
        },
        hasSecondaryButtons: function () {
            return (
                this.branch.show_retail_pos_button == this.statusEnum.ACTIVE ||
                this.branch.show_quick_pos_button == this.statusEnum.ACTIVE ||
                this.branch.show_unpaid_button == this.statusEnum.ACTIVE ||
                this.branch.show_online_order_button == this.statusEnum.ACTIVE ||
                this.branch.show_pending_order_button == this.statusEnum.ACTIVE ||
                this.branch.show_table_button == this.statusEnum.ACTIVE ||
                this.branch.show_floor_plan == this.statusEnum.ACTIVE ||
                this.branch.show_customer_view_button == this.statusEnum.ACTIVE
            );
        },
        isNavbarReady: function () {
            // Show navbar immediately if we have basic settings, or after initial load
            return this.dataLoaded.initialLoadComplete || (this.setting && Object.keys(this.setting).length > 0);
        },
        showMenuType: function () {
            return this.$store.getters['backendGlobalState/showMenuType'];
        },
    },
    mounted() {
        // this.props.tableDining = this.tableDinings;
        appService.responsiveLoad();

        // Parallelize critical API calls for faster initial load
        Promise.all([
            this.loadDefaultAccess(),
            this.loadBranches(),
            this.loadLanguages(),
            this.loadSettings()
        ]).then(() => {
            this.dataLoaded.initialLoadComplete = true;
            // Non-critical operations after critical data is loaded
            this.orderPermissionCheck();
            this.posPermissionCheck();

            // Defer heavy operations to next tick
            this.$nextTick(() => {
                this.initInternetMonitoring();

                // Further defer Firebase initialization
                setTimeout(() => {
                    this.initFirebaseIfNeeded();
                }, 1000);
            });
        }).catch((error) => {
            console.error('Error loading navbar data:', error);
            this.dataLoaded.initialLoadComplete = true; // Still mark as complete to show navbar
            // Still initialize permissions and monitoring even if some API calls fail
            this.orderPermissionCheck();
            this.posPermissionCheck();
            this.initInternetMonitoring();
        });
    },

    methods: {
        initFirebaseIfNeeded: function () {
            if (
                this.$store.getters.authStatus &&
                this.setting.notification_fcm_api_key &&
                this.setting.notification_fcm_auth_domain &&
                this.setting.notification_fcm_project_id &&
                this.setting.notification_fcm_storage_bucket &&
                this.setting.notification_fcm_messaging_sender_id &&
                this.setting.notification_fcm_app_id &&
                this.setting.notification_fcm_measurement_id
            ) {
                // Dynamically import Firebase
                import('firebase/app').then(({ initializeApp }) => {
                    import('firebase/messaging').then(({ getMessaging, getToken, onMessage }) => {
                        initializeApp({
                            apiKey: this.setting.notification_fcm_api_key,
                            authDomain: this.setting.notification_fcm_auth_domain,
                            projectId: this.setting.notification_fcm_project_id,
                            storageBucket: this.setting.notification_fcm_storage_bucket,
                            messagingSenderId: this.setting.notification_fcm_messaging_sender_id,
                            appId: this.setting.notification_fcm_app_id,
                            measurementId: this.setting.notification_fcm_measurement_id,
                        });
                        const messaging = getMessaging();

                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                getToken(messaging, { vapidKey: this.setting.notification_fcm_public_vapid_key })
                                    .then((currentToken) => {
                                        if (currentToken) {
                                            axios
                                                .post('/frontend/device-token/web', { token: currentToken })
                                                .then()
                                                .catch((error) => {
                                                    if (error.response.data.message === 'Unauthenticated.') {
                                                        this.$store.dispatch('loginDataReset');
                                                    }
                                                });
                                        }
                                    })
                                    .catch();
                            }
                        });

                        onMessage(messaging, (payload) => {
                            const notificationTitle = payload.notification.title;
                            const notificationOptions = {
                                body: payload.notification.body,
                                icon: '/images/default/firebase-logo.png',
                            };
                            new Notification(notificationTitle, notificationOptions);

                            if (payload.data.topicName === 'new-order-found' && this.orderNotification.permission) {
                                this.orderNotificationStatus = true;
                                this.orderNotificationMessage = payload.notification.body;
                                const audio = new Audio(this.setting.notification_audio);
                                audio.play();
                            }
                        });
                    }).catch(err => {
                        console.error('Failed to load Firebase messaging:', err);
                    });
                }).catch(err => {
                    console.error('Failed to load Firebase app:', err);
                });
            }
        },
        diningTableModal: function () {
            appService.modalShow('#diningtabletopbarModal');
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        isCurrentLanguage: function (languageId) {
            const globalState = this.$store.getters['globalState/get'];
            return globalState && String(globalState.language_id) === String(languageId);
        },
        logout: function () {
            this.$store
                .dispatch('logout')
                .then((res) => {
                    this.$router.push({ name: 'auth.login' });
                })
                .catch();
        },
        changeBranch: function (id) {
            this.$store.dispatch('defaultAccess/saveOrUpdate', { branch_id: id }).then((res) => {
                this.$store
                    .dispatch('backendGlobalState/branchShow', id)
                    .then((res) => {
                        this.resetLoadingFlags();
                        location.reload();
                    })
                    .catch();
            });
        },
        changeLanguage: function (id, code) {
            this.defaultLanguage = id;
            this.setCurrentLocale(code);

            this.$store
                .dispatch('globalState/set', { language_id: id, language_code: code })
                .then((res) => {
                    this.loadCurrentLanguage(id);
                    document.querySelectorAll('.dropdown-list').forEach((dropdown) => {
                        dropdown.classList.add('hidden');
                        dropdown.classList.remove('active');
                    });
                })
                .catch((err) => {
                    console.error('Error changing language:', err);
                });
        },
        posPermissionCheck: function () {
            const permissions = this.$store.getters.authPermission;
            if (permissions.length > 0) {
                _.forEach(permissions, (permission) => {
                    if (permission.name === 'pos') {
                        if (permission.access === true) {
                            this.pos.permission = true;
                            this.pos.url = permission.url;
                        }
                    }
                });
            }
        },
        saveImage: function () {
            if (this.$refs.imageProperty.files[0]) {
                try {
                    this.loading.isActive = true;
                    const formData = new FormData();
                    formData.append('image', this.$refs.imageProperty.files[0]);
                    this.$store
                        .dispatch('frontendEditProfile/changeImage', { form: formData })
                        .then((res) => {
                            this.$store
                                .dispatch('updateAuthInfo', res.data.data)
                                .then((res) => {
                                    this.loading.isActive = false;
                                    alertService.success(this.$t('message.photo_update'));
                                    this.$refs.imageProperty.value = null;
                                })
                                .catch((err) => {
                                    this.loading.isActive = false;
                                    alertService.error(err);
                                });
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            this.imageErrors = err.response.data.errors;
                        });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }
        },
        orderPermissionCheck: function () {
            const permissions = this.$store.getters.authPermission;
            if (permissions.length > 0) {
                _.forEach(permissions, (permission) => {
                    if (permission.name === 'table-orders') {
                        if (permission.access === true) {
                            this.orderNotification.permission = true;
                            this.orderNotification.url = permission.url;
                        }
                    }
                });
            }
        },
        closeOrderNotificationModal: function () {
            const modalTarget = this.$refs.orderNotificationModal;
            modalTarget?.classList?.remove('active');
            document.body.style.overflowY = 'auto';
            this.loading.isActive = false;
            this.orderNotificationStatus = false;
        },
        fullPage: function () {
            if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        },
        openCustomerView: async function () {
            // ── Electron path ──────────────────────────────────────────────────
            // When running inside Electron the preload exposes window.electronAPI.
            // This uses IPC to open a true BrowserWindow on the second display,
            // bypassing all browser popup-blocker and multi-screen limitations.
            if (window.electronAPI && window.electronAPI.isElectron) {
                const status = await window.electronAPI.customerViewStatus();
                if (status && status.isOpen) {
                    await window.electronAPI.closeCustomerView();
                    this.customerViewOpen = false;
                } else {
                    await window.electronAPI.openCustomerView();
                    this.customerViewOpen = true;
                }
                return;
            }

            // ── Browser fallback ───────────────────────────────────────────────
            // Toggle: if already open, close it; otherwise open a new window.
            if (this.customerViewWindow && !this.customerViewWindow.closed) {
                this.customerViewWindow.close();
                this.customerViewWindow = null;
                this.customerViewOpen = false;
                return;
            }

            const customerViewUrl = window.location.origin + '/admin/pos-customer-view';

            const targetLeft   = window.screen.width;
            const targetTop    = 0;
            const targetWidth  = window.screen.width;
            const targetHeight = window.screen.height;

            const features = [
                `left=${targetLeft}`,
                `top=${targetTop}`,
                `width=${targetWidth}`,
                `height=${targetHeight}`,
                'menubar=no',
                'toolbar=no',
                'location=no',
                'status=no',
                'scrollbars=yes',
                'resizable=yes',
            ].join(',');

            this.customerViewWindow = window.open(customerViewUrl, 'CustomerView', features);

            if (!this.customerViewWindow) {
                alert('The customer view could not be opened.\nPlease allow popups for this site in your browser settings.');
                return;
            }

            this.customerViewOpen = true;
            this.customerViewWindow.focus();

            // Detect when the user manually closes the popup so the button state resets.
            const checkClosed = setInterval(() => {
                if (!this.customerViewWindow || this.customerViewWindow.closed) {
                    this.customerViewOpen = false;
                    this.customerViewWindow = null;
                    clearInterval(checkClosed);
                }
            }, 1000);
        },
        clearCache: function () {
            appService
                .clearCacheAlert()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        localStorage.clear();
                        sessionStorage.clear();
                        if (this.$store) {
                            this.$store.commit('RESET_STORE');
                        }
                        if (typeof caches !== 'undefined') {
                            caches
                                .keys()
                                .then((names) => {
                                    names.forEach((name) => caches.delete(name));
                                })
                                .finally(() => {
                                    location.reload();
                                });
                        } else {
                            console.warn('Cache API is not supported in this environment.');
                            location.reload();
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
        formatDate: function (e) {
            return e ? e.split('T')[0] : '';
        },
        suspend: function () {
            alert('suspend');
        },

        loadDefaultAccess: function () {
            if (this.dataLoaded.defaultAccess) {
                return;
            }

            this.$store
                .dispatch('defaultAccess/show')
                .then((res) => {
                    this.defaultBranch = res.data.data.branch_id;
                    this.$store
                        .dispatch('backendGlobalState/branchShow', res.data.data.branch_id)
                        .then((branchRes) => {
                            if (branchRes.data.data.show_online_order_button != this.statusEnum.ACTIVE) {
                                console.log('Online order button is not active. Skipping count pending dispatch.');
                            } else {
                                this.fetchPendingOnlineOrdersCount();
                                this.setupDynamicInterval();
                            }

                            if (branchRes.data.data.show_pending_order_button != this.statusEnum.ACTIVE) {
                            } else {
                                this.fetchPendingOrdersCount();
                                this.setupPendingOrdersInterval();
                            }
                        })
                        .catch();
                    this.dataLoaded.defaultAccess = true;
                })
                .catch((err) => {
                    console.error('Error loading default access:', err);
                });
        },

        loadBranches: function () {
            if (this.dataLoaded.branches || (this.branches && this.branches.length > 0)) {
                return;
            }

            this.$store
                .dispatch('backendGlobalState/branches', this.branchProps)
                .then(() => {
                    this.dataLoaded.branches = true;
                })
                .catch((err) => {
                    console.error('Error loading branches:', err);
                });
        },

        loadLanguages: function () {
            if (this.dataLoaded.languages || (this.languages && this.languages.length > 0)) {
                return;
            }

            this.$store
                .dispatch('frontendLanguage/lists', this.languageProps)
                .then(() => {
                    this.dataLoaded.languages = true;
                })
                .catch((err) => {
                    console.error('Error loading languages:', err);
                });
        },

        loadSettings: function () {
            if (this.dataLoaded.settings || (this.setting && Object.keys(this.setting).length > 0)) {
                return;
            }

            this.$store
                .dispatch('frontendSetting/lists')
                .then((res) => {
                    this.defaultLanguage = res.data.data.site_default_language;
                    const globalState = this.$store.getters['globalState/lists'];

                    if (globalState.language_id > 0) {
                        this.defaultLanguage = globalState.language_id;
                    }

                    this.loadCurrentLanguage();
                    this.dataLoaded.settings = true;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    console.error('Error loading settings:', err);
                });
        },

        setCurrentLocale: function (code) {
            if (!code) {
                return;
            }

            if (i18n.global.locale && typeof i18n.global.locale === 'object' && Object.prototype.hasOwnProperty.call(i18n.global.locale, 'value')) {
                i18n.global.locale.value = code;
            } else {
                i18n.global.locale = code;
            }

            if (this.$i18n) {
                if (this.$i18n.locale && typeof this.$i18n.locale === 'object' && Object.prototype.hasOwnProperty.call(this.$i18n.locale, 'value')) {
                    this.$i18n.locale.value = code;
                } else {
                    this.$i18n.locale = code;
                }
            }

            localStorage.setItem('language_code', code);
            localStorage.setItem('locale', code);
        },

        syncLanguageState: function (language) {
            if (!language || !language.id || !language.code) {
                return;
            }

            this.$store.commit('frontendLanguage/show', language);
            this.$store.dispatch('globalState/set', {
                language_id: language.id,
                language_code: language.code,
            });
            this.setCurrentLocale(language.code);
        },

        loadCurrentLanguage(languageId) {
            const globalState = this.$store.getters['globalState/get'];
            const currentLangId = languageId || (globalState && globalState.language_id);
            if (!currentLangId) return;

            const existingLanguage = this.$store.getters['frontendLanguage/get'](currentLangId);
            if (existingLanguage && existingLanguage.code) {
                this.syncLanguageState(existingLanguage);
                return;
            }
            this.$store
                .dispatch('frontendLanguage/show', currentLangId)
                .then((res) => {
                    this.syncLanguageState(res.data.data);
                })
                .catch((err) => {
                    console.error('Error loading current language:', err);
                });
        },
        resetLoadingFlags: function () {
            this.dataLoaded.defaultAccess = false;
            this.dataLoaded.branches = false;
            this.dataLoaded.languages = false;
            this.dataLoaded.settings = false;
            // this.dataLoaded.tableDinings = false;
        },


        // Method to force reload all data
        forceReloadAllData: function () {
            this.resetLoadingFlags();
            this.loadDefaultAccess();
            this.loadBranches();
            this.loadLanguages();
            this.loadSettings();
        },

        fetchPendingOnlineOrdersCount: function () {
            if (this.branch.show_online_order_button != this.statusEnum.ACTIVE) {
                console.log('Online order button is not active. Skipping count pending dispatch.');
                return; // Exit if online order button is not active
            }

            // Check if user is still authenticated before making API call
            if (!this.$store.getters.authStatus) {
                console.log('User not authenticated. Clearing intervals.');
                this.clearAllIntervals();
                return;
            }

            const previousCount = this.previousPendingCount;
            this.$store
                .dispatch('onlineOrder/countPending')
                .then(() => {
                    const currentCount = this.pendingOnlineOrdersCount;
                    this.updateIntervalFrequency(previousCount, currentCount);
                    this.previousPendingCount = currentCount;
                })
                .catch((error) => {
                    console.error('Error fetching pending online orders count:', error);
                    this.handleApiError(error);
                });
        },

        setupDynamicInterval: function () {
            if (this.branch.show_online_order_button != this.statusEnum.ACTIVE) {
                console.log('Online order button is not active. Skipping interval setup.');
                return; // Exit if online order button is not active
            }
            // Initialize previous count
            this.previousPendingCount = this.pendingOnlineOrdersCount;


            const interval = this.pendingOnlineOrdersCount > 0 ? 5000 : 10000;
            this.pendingOrdersInterval = setInterval(() => {
                this.fetchPendingOnlineOrdersCount();
            }, interval);
        },

        updateIntervalFrequency: function (previousCount, currentCount) {
            let currentInterval;


            // If count hasn't changed, set interval to 10 seconds
            if (previousCount === currentCount) {
                currentInterval = 10000; // 10 seconds
            } else {
                // If count changed, use dynamic interval based on pending count
                currentInterval = this.pendingOnlineOrdersCount > 0 ? 5000 : 10000;
            }


            // Clear existing interval and set new one if frequency should change
            if (this.pendingOrdersInterval) {
                clearInterval(this.pendingOrdersInterval);
            }


            this.pendingOrdersInterval = setInterval(() => {
                this.fetchPendingOnlineOrdersCount();
            }, currentInterval);
        },

        fetchPendingOrdersCount: function () {
            if (this.branch.show_pending_order_button != this.statusEnum.ACTIVE) {
                console.log('Pending order button is not active. Skipping count pending dispatch.');
                return; // Exit if pending order button is not active
            }

            // Check if user is still authenticated before making API call
            if (!this.$store.getters.authStatus) {
                console.log('User not authenticated. Clearing intervals.');
                this.clearAllIntervals();
                return;
            }

            const previousCount = this.previousPendingOrdersCount;
            this.$store
                .dispatch('posOrder/countPending')
                .then(() => {
                    const currentCount = this.pendingOrdersCount;
                    // Update interval frequency based on pending count and whether it changed
                    this.updatePendingOrdersIntervalFrequency(previousCount, currentCount);
                    this.previousPendingOrdersCount = currentCount;
                })
                .catch((error) => {
                    console.error('Error fetching pending orders count:', error);
                    this.handleApiError(error);
                });
        },

        setupPendingOrdersInterval: function () {
            if (this.branch.show_pending_order_button != this.statusEnum.ACTIVE) {
                console.log('Pending order button is not active. Skipping interval setup.');
                return; // Exit if pending order button is not active
            }
            // Initialize previous count
            this.previousPendingOrdersCount = this.pendingOrdersCount;

            const interval = 5000; // Always 5 seconds for pending orders
            this.pendingOrdersCountInterval = setInterval(() => {
                this.fetchPendingOrdersCount();
            }, interval);
        },

        updatePendingOrdersIntervalFrequency: function (previousCount, currentCount) {
            const currentInterval = 5000; // Always 5 seconds for pending orders

            // Clear existing interval and set new one
            if (this.pendingOrdersCountInterval) {
                clearInterval(this.pendingOrdersCountInterval);
            }

            this.pendingOrdersCountInterval = setInterval(() => {
                this.fetchPendingOrdersCount();
            }, currentInterval);
        },

        // Helper method to handle API errors, especially authentication errors
        handleApiError: function (error) {
            // Check if the error is due to authentication
            if (error.response && (error.response.status === 401 || (error.response.data && error.response.data.message === 'Unauthenticated.'))) {
                console.log('Authentication error detected. Clearing intervals and resetting login data.');

                // Clear all intervals to stop making API calls
                this.clearAllIntervals();

                // Reset login data in store
                this.$store.dispatch('loginDataReset');

                // Redirect to login page
                this.$router.push({ name: 'auth.login' });
            }
        },

        // Helper method to clear all intervals
        clearAllIntervals: function () {
            if (this.pendingOrdersInterval) {
                clearInterval(this.pendingOrdersInterval);
                this.pendingOrdersInterval = null;
            }
            if (this.pendingOrdersCountInterval) {
                clearInterval(this.pendingOrdersCountInterval);
                this.pendingOrdersCountInterval = null;
            }
        },

        // Internet connectivity monitoring methods
        initInternetMonitoring: function () {
            // Add listener to the connectivity service
            internetConnectivityService.addListener(this.handleConnectivityChange);


            // Set initial status
            const status = internetConnectivityService.getStatus();
            this.internetStatus.isOnline = status.isOnline;
            this.internetStatus.lastCheckTime = status.lastCheckTime;
            this.internetStatus.showAlert = !status.isOnline;
        },

        handleConnectivityChange: function (isOnline) {
            this.internetStatus.isOnline = isOnline;
            this.internetStatus.showAlert = !isOnline;
            this.internetStatus.lastCheckTime = internetConnectivityService.getStatus().lastCheckTime;


            if (isOnline) {
                console.log('Internet connection restored');
                // Optional: Show success notification
                if (this.setting && this.setting.notification_audio) {
                    const audio = new Audio(this.setting.notification_audio);
                    audio.play().catch(() => {});
                }
            } else {
                console.log('Internet connection lost');
            }
        },

        checkInternetConnection: function () {
            this.loading.internetCheck = true;

            internetConnectivityService
                .forceCheck()
                .then((isOnline) => {
                    this.internetStatus.isOnline = isOnline;
                    this.internetStatus.showAlert = !isOnline;
                    this.internetStatus.lastCheckTime = internetConnectivityService.getStatus().lastCheckTime;
                    this.loading.internetCheck = false;
                })
                .catch(() => {
                    this.internetStatus.isOnline = false;
                    this.internetStatus.showAlert = true;
                    this.loading.internetCheck = false;
                });
        },

        formatLastCheckTime: function () {
            return internetConnectivityService.formatLastCheckTime();
        },


        // Override navigation when offline
        blockOfflineActions: function () {
            return internetConnectivityService.requireInternet(true);
        },
        toggleMenuType: function () {
            const newMenuType = this.showMenuType === this.menuTypeEnum.BACKEND
                ? this.menuTypeEnum.POS
                : this.menuTypeEnum.BACKEND;
            this.$store.dispatch('backendGlobalState/updateShowMenuType', newMenuType);

            // Route based on menu type
            if (newMenuType === this.menuTypeEnum.BACKEND) {
                if (document?.querySelector(".db-sidebar")?.classList?.contains("active")) {
                    document?.querySelector(".db-sidebar")?.classList?.remove("active");
                    document?.querySelector(".db-main")?.classList?.remove("expand");

                    const headerNav = document?.querySelector(".db-header-nav");
                    if (headerNav) {
                        headerNav.classList.remove("fa-bars");
                        headerNav.classList.add("fa-align-left");
                    }
                }

                // Switching to Back Office mode
                this.$router.push({ name: 'admin.orders' });
            } else {
                // Exiting Back Office mode (switching to POS mode)
                this.$router.push({ name: 'admin.pos' });
            }
        },
    },


    // Cleanup on component destroy
    beforeUnmount() {
        // Remove listener from connectivity service
        internetConnectivityService.removeListener(this.handleConnectivityChange);

        // Clear all intervals
        this.clearAllIntervals();
    },
};
</script>

<style scoped>
.small-icon {
    width: 15px; /* Set the desired width */
    height: 15px; /* Set the desired height */
}
</style>
