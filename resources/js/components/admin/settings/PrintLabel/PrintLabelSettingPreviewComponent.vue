<template>
    <LoadingComponent :props="loading" />   
    <div class="row">

        <!-- Print Label Setting -->
        <div class="col-12 sm:col-6">
            <div class="row">
                <div class="col-12">  
                    <div class="db-card mb-4 shadow-sm border-0">
                        <div class="db-card-header bg-gradient-to-r from-blue-600 to-blue-700 text-white border-0">
                            <h3 class="db-card-title text-white font-semibold flex items-center">
                                <i class="lab lab-settings lab-font-size-16 mr-2"></i>
                                {{ $t('label.print_label_setting') }}
                            </h3> 
                        </div>
                        <div class="db-card-body p-6 bg-gray-50">
                            <form @submit.prevent="save" class="space-y-6">
                                
                                <!-- Basic Information Section -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                        <i class="lab lab-info-circle lab-font-size-14 mr-2 text-blue-600"></i>
                                        Basic Information
                                    </h4>
                                    <div class="form-row space-y-4">
                                        <div class="form-col-12">
                                            <label class="db-field-title required text-gray-700 font-medium" for="name">{{ $t("label.name") }}</label>
                                            <input 
                                                v-model="form.name" 
                                                type="text" 
                                                id="name" 
                                                class="db-field-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" 
                                                :placeholder="$t('label.name')"
                                                required>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.name">{{
                                                errors.name[0]
                                            }}</small>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="db-field-title required text-gray-700 font-medium" for="label_width">
                                                    {{ $t("label.label_width") }} (mm)
                                                </label>
                                                <input 
                                                    v-model="form.label_width" 
                                                    type="number" 
                                                    id="label_width" 
                                                    class="db-field-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" 
                                                    min="10" 
                                                    max="500"
                                                    placeholder="50"
                                                    required>
                                                <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.label_width">{{
                                                    errors.label_width[0]
                                                }}</small>
                                            </div>
                                            <div>
                                                <label class="db-field-title required text-gray-700 font-medium" for="label_height">
                                                    {{ $t("label.label_height") }} (mm)
                                                </label>
                                                <input 
                                                    v-model="form.label_height" 
                                                    type="number" 
                                                    id="label_height" 
                                                    class="db-field-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors" 
                                                    min="10" 
                                                    max="500"
                                                    placeholder="30"
                                                    required>
                                                <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.label_height">{{
                                                    errors.label_height[0]
                                                }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Company Information Section -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                        <i class="lab lab-building lab-font-size-14 mr-2 text-green-600"></i>
                                        Company Information
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_company_name") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_company_name"
                                                            id="show_company_name_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_company_name_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_company_name"
                                                            type="radio" id="show_company_name_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_company_name_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_company_name">{{
                                                errors.show_company_name[0]
                                            }}</small>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_branch_name") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_branch_name"
                                                            id="show_branch_name_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_branch_name_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_branch_name"
                                                            type="radio" id="show_branch_name_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_branch_name_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_branch_name">{{
                                                errors.show_branch_name[0]
                                            }}</small>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_phone_number") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_phone_number"
                                                            id="show_phone_number_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_phone_number_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_phone_number"
                                                            type="radio" id="show_phone_number_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_phone_number_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_phone_number">{{
                                                errors.show_phone_number[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Information Section -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                        <i class="lab lab-receipt lab-font-size-14 mr-2 text-purple-600"></i>
                                        Order Information
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_order_number") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_order_number"
                                                            id="show_order_number_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_number_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_order_number"
                                                            type="radio" id="show_order_number_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_number_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_order_number">{{
                                                errors.show_order_number[0]
                                            }}</small>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_order_number_barcode") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_order_number_barcode"
                                                            id="show_order_number_barcode_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_number_barcode_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_order_number_barcode"
                                                            type="radio" id="show_order_number_barcode_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_number_barcode_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_order_number_barcode">{{
                                                errors.show_order_number_barcode[0]
                                            }}</small>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <label class="db-field-title required text-gray-700 font-medium text-sm">{{ $t("label.show_order_qr_code") }}</label>
                                            <div class="db-field-radio-group mt-2 flex space-x-4">
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_order_qr_code"
                                                            id="show_order_qr_code_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_qr_code_yes" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.yes")
                                                    }}</label>
                                                </div>
                                                <div class="db-field-radio flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_order_qr_code"
                                                            type="radio" id="show_order_qr_code_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_order_qr_code_no" class="db-field-label ml-2 text-sm text-gray-600">{{
                                                        $t("label.no")
                                                    }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_order_qr_code">{{
                                                errors.show_order_qr_code[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Information Section -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="lab lab-bag text-green-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $t('label.item_information') }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Show Item -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_item") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_item"
                                                            id="show_item_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_item"
                                                            type="radio" id="show_item_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_item">{{
                                                errors.show_item[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Item Quantity -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_item_qty") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_item_qty"
                                                            id="show_item_qty_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_qty_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_item_qty"
                                                            type="radio" id="show_item_qty_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_qty_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_item_qty">{{
                                                errors.show_item_qty[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Item Price -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_item_price") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_item_price"
                                                            id="show_item_price_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_price_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_item_price"
                                                            type="radio" id="show_item_price_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_item_price_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_item_price">{{
                                                errors.show_item_price[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div> 
                                <!-- Customer Information Section -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="lab lab-user text-blue-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $t('label.customer_information') }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Show Customer Name -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_customer_name") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_customer_name"
                                                            id="show_customer_name_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_customer_name_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_customer_name"
                                                            type="radio" id="show_customer_name_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_customer_name_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_customer_name">{{
                                                errors.show_customer_name[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Customer Phone -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_customer_phone_number") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_customer_phone_number"
                                                            id="show_customer_phone_number_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_customer_phone_number_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_customer_phone_number"
                                                            type="radio" id="show_customer_phone_number_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_customer_phone_number_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_customer_phone_number">{{
                                                errors.show_customer_phone_number[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Delivery Address -->
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_delivery_address") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_delivery_address"
                                                            id="show_delivery_address_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_delivery_address_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_delivery_address"
                                                            type="radio" id="show_delivery_address_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_delivery_address_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_delivery_address">{{
                                                errors.show_delivery_address[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Information Section -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <i class="lab lab-wallet text-yellow-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $t('label.payment_information') }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Show Payment Status -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_payment_status") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_payment_status"
                                                            id="show_payment_status_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_status_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_payment_status"
                                                            type="radio" id="show_payment_status_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_status_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_payment_status">{{
                                                errors.show_payment_status[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Payment QR Code -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_payment_qr_code") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_payment_qr_code"
                                                            id="show_payment_qr_code_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_qr_code_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_payment_qr_code"
                                                            type="radio" id="show_payment_qr_code_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_qr_code_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_payment_qr_code">{{
                                                errors.show_payment_qr_code[0]
                                            }}</small>
                                        </div>

                                        <!-- Show Payment Method -->
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.show_payment_method") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.show_payment_method"
                                                            id="show_payment_method_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_method_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.show_payment_method"
                                                            type="radio" id="show_payment_method_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="show_payment_method_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.show_payment_method">{{
                                                errors.show_payment_method[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Label Settings Section -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="lab lab-setting text-indigo-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $t('label.label_settings') }}</h3>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Print Quantity -->
                                        <div class="space-y-2">
                                            <label for="print_qty" class="text-sm font-medium text-gray-700">{{ $t("label.print_qty") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <input
                                                        id="print_qty"
                                                        type="number"
                                                        min="1"
                                                        v-model.number="form.print_qty"
                                                        class="w-28 px-3 py-2 border border-gray-300 rounded text-sm bg-white"
                                                    />
                                                    <span class="ml-3 text-sm text-gray-600">copies</span>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.print_qty">{{
                                                errors.print_qty[0]
                                            }}</small>
                                        </div>

                                        <!-- Separate Item -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.separate_item") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.separate_item"
                                                            id="separate_item_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="separate_item_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.separate_item"
                                                            type="radio" id="separate_item_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="separate_item_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.separate_item">{{
                                                errors.separate_item[0]
                                            }}</small>
                                        </div>

                                        <!-- Separate Quantity -->
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="text-sm font-medium text-gray-700">{{ $t("label.separate_qty") }}</label>
                                            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.ACTIVE" v-model="form.separate_qty"
                                                            id="separate_qty_yes" type="radio" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="separate_qty_yes" class="ml-2 text-sm text-gray-600">{{ $t("label.yes") }}</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <div class="custom-radio">
                                                        <input :value="enums.statusEnum.INACTIVE" v-model="form.separate_qty"
                                                            type="radio" id="separate_qty_no" class="custom-radio-field" />
                                                        <span class="custom-radio-span"></span>
                                                    </div>
                                                    <label for="separate_qty_no" class="ml-2 text-sm text-gray-600">{{ $t("label.no") }}</label>
                                                </div>
                                            </div>
                                            <small class="db-field-alert text-red-500 text-xs mt-1" v-if="errors.separate_qty">{{
                                                errors.separate_qty[0]
                                            }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Style Section -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <i class="lab lab-code text-purple-600"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">Custom CSS Style</h3>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label for="label_style_custom" class="text-sm font-medium text-gray-700">Custom CSS</label>
                                        <textarea
                                            id="label_style_custom"
                                            v-model="form.label_style_custom"
                                            rows="12"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50 font-mono"
                                            placeholder="Enter custom CSS styles here..."
                                        ></textarea>
                                        <small class="text-xs text-gray-500">
                                            Use class names like .company_name, .branch_name, .item_name, etc. to style specific elements in the label preview.
                                        </small>
                                        <small class="db-field-alert text-red-500 text-xs mt-1 block" v-if="errors.label_style_custom">{{
                                            errors.label_style_custom[0]
                                        }}</small>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center gap-2">
                                        <i class="lab lab-save"></i>
                                        <span>{{ $t("button.save") }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Print Label Setting -->

        <!-- Start of Print Label Preview -->
        <div class="col-12 sm:col-6">
            <div class="row">
                <div class="col-12">  
                    <div class="db-card mb-4"  >
                        <div class="db-card-header">
                            <h3 class="db-card-title">{{ $t('label.preview') }}</h3> 
                            <button type="button" class="flex items-center justify-center gap-1.2 py-1 px-2 rounded text-white bg-primary">
                                <i class="lab lab-print-bold lab-font-size-16"></i> 
                                <span class="text-xs leading-5 capitalize">
                                    {{ $t("button.test_print") }}
                                </span> 
                            </button>
                        </div>
                        <div class="db-card-body"> 
                            <!-- Label Size Information -->
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="text-sm font-semibold text-blue-800 mb-1">Preview Label Size</div>
                                <div class="text-xs text-blue-600">
                                    <span class="font-mono">{{ form.label_width || 50 }}mm × {{ form.label_height || 30 }}mm</span>
                                    <span class="ml-2 text-gray-500">({{ ((form.label_width || 50) / 25.4).toFixed(1) }}" × {{ ((form.label_height || 30) / 25.4).toFixed(1) }}")</span>
                                </div>
                                <div class="text-xs text-blue-500 mt-1">
                                    Preview is scaled 3:1 for visibility. Actual print size will be smaller.
                                </div>
                            </div>

                            <!-- Preview Label Explanation -->
                            <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="text-sm font-semibold text-gray-800 mb-2">Label Separation Logic:</div>
                                <div class="text-xs text-gray-600 space-y-1">
                                    <div><strong>separate_item ACTIVE:</strong> Creates {{ previewItems.length }} labels (one per unique item)</div>
                                    <div><strong>separate_qty ACTIVE:</strong> Creates {{ getTotalQuantity() }} labels (one per individual quantity)</div>
                                    <div><strong>Both INACTIVE:</strong> Creates 1 combined label with all items</div>
                                </div>
                            </div>

                            <div class="form-row">  
                                
                                <!-- When separate_qty is ACTIVE: one label per individual quantity -->
                                <template v-if="form.separate_qty == enums.statusEnum.ACTIVE">
                                    <div class="w-full">
                                        <div class="mb-3 p-2 bg-red-50 border border-red-200 rounded text-center">
                                            <div class="font-semibold text-red-800">QUANTITY SEPARATION MODE</div>
                                            <div class="text-xs text-red-600">Creating {{ getTotalQuantity() }} individual labels (one per item quantity)</div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-3 justify-start max-w-full overflow-x-auto">
                                            <!-- Show first 8 individual quantity labels -->
                                            <div v-for="(labelItem, index) in getQuantityLabels().slice(0, 8)" :key="index"
                                                 class="bg-white border-2 border-dashed border-gray-400 shadow-sm text-xs font-mono overflow-hidden relative"
                                                 :style="{ 
                                                     width: (form.label_width || 50) * 2 + 'px', 
                                                     height: (form.label_height || 30) * 2 + 'px',
                                                     minWidth: '120px',
                                                     minHeight: '80px'
                                                 }">
                                                
                                                <!-- Label indicators -->
                                                <div class="absolute top-0.5 right-0.5 bg-black bg-opacity-75 text-white text-2xs px-1 py-0.5 rounded">
                                                    {{ form.label_width || 50 }}×{{ form.label_height || 30 }}mm
                                                </div>
                                                <div class="absolute top-0.5 left-0.5 bg-red-600 text-white text-2xs px-1 py-0.5 rounded">
                                                    {{ index + 1 }}
                                                </div>
                                                
                                                <div class="p-1">
                                                    <!-- Header Section -->
                                                    <div class="text-center border-b border-dashed border-gray-300 pb-0.5 mb-0.5" v-if="form.show_company_name == enums.statusEnum.ACTIVE || form.show_branch_name == enums.statusEnum.ACTIVE || form.show_phone_number == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_company_name == enums.statusEnum.ACTIVE" class="font-bold text-gray-800 text-2xs company_name">RESTAURANT POS22</div>
                                                        <div v-if="form.show_branch_name == enums.statusEnum.ACTIVE" class="text-gray-600 text-2xs branch_name">{{ branch?.name || 'Main Branch' }}</div>
                                                        <div v-if="form.show_phone_number == enums.statusEnum.ACTIVE" class="text-gray-500 text-2xs phone_number">Tel: (855) 12-345-678</div>
                                                    </div>
                                                    
                                                    <!-- Order Information -->
                                                    <div class="mb-0.5" v-if="form.show_order_number == enums.statusEnum.ACTIVE || form.show_order_qr_code == enums.statusEnum.ACTIVE || form.show_order_number_barcode == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_order_number == enums.statusEnum.ACTIVE" class="font-semibold text-2xs order_number">Order #: ORD-2025-001</div>
                                                        <div class="flex justify-center items-center gap-1 mt-0.5">
                                                            <img v-if="form.show_order_qr_code == enums.statusEnum.ACTIVE" src="https://api.qrserver.com/v1/create-qr-code/?size=15x15&data=ORD-2025-001" alt="QR" class="border border-dashed border-gray-400 order_qr_code">
                                                            <img v-if="form.show_order_number_barcode == enums.statusEnum.ACTIVE" src="https://barcode.tec-it.com/barcode.ashx?data=ORD2025001&code=Code128&dpi=96&imagetype=png" alt="Barcode" class="h-2 order_number_barcode">
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Single Item with Quantity 1 -->
                                                    <div v-if="form.show_item == enums.statusEnum.ACTIVE" class="border border-dashed border-red-300 rounded p-0.5 mb-0.5 bg-red-50">
                                                        <div class="font-semibold text-red-700 mb-0.5 text-2xs">QTY SEPARATED</div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-medium text-2xs item_name">{{ labelItem.name }}</span>
                                                            <div class="text-right">
                                                                <div v-if="form.show_item_qty == enums.statusEnum.ACTIVE && form.show_item_price == enums.statusEnum.ACTIVE" class="text-2xs item_qty item_price">1 × ${{ labelItem.price }}</div>
                                                                <div v-else-if="form.show_item_qty == enums.statusEnum.ACTIVE" class="text-2xs item_qty">Qty: 1</div>
                                                                <div v-else-if="form.show_item_price == enums.statusEnum.ACTIVE" class="text-2xs item_price">${{ labelItem.price }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Customer Information -->
                                                    <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE || form.show_customer_phone_number == enums.statusEnum.ACTIVE || form.show_delivery_address == enums.statusEnum.ACTIVE" class="mb-0.5 text-2xs">
                                                        <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE" class="font-medium">Customer: John Doe</div>
                                                        <div v-if="form.show_customer_phone_number == enums.statusEnum.ACTIVE">Phone: (855) 98-765-432</div>
                                                        <div v-if="form.show_delivery_address == enums.statusEnum.ACTIVE" class="text-gray-600">123 Street, Phnom Penh</div>
                                                    </div>
                                                    
                                                    <!-- Payment Information -->
                                                    <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE || form.show_payment_method == enums.statusEnum.ACTIVE || form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="mb-0.5 text-2xs">
                                                        <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE" class="text-green-600 font-medium payment_status">Status: PAID</div>
                                                        <div v-if="form.show_payment_method == enums.statusEnum.ACTIVE" class="payment_method">Method: Credit Card</div>
                                                        <div v-if="form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="flex justify-center mt-0.5">
                                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=12x12&data=payment-qr" alt="Payment QR" class="border border-dashed border-gray-400 payment_qr_code">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Show more indicator if there are additional labels -->
                                            <div v-if="getQuantityLabels().length > 8" 
                                                 class="bg-gray-100 border-2 border-dashed border-gray-400 shadow-lg text-xs font-mono overflow-hidden relative flex items-center justify-center"
                                                 :style="{ 
                                                     width: (form.label_width || 50) * 2 + 'px', 
                                                     height: (form.label_height || 30) * 2 + 'px',
                                                     minWidth: '120px',
                                                     minHeight: '80px'
                                                 }">
                                                <div class="text-center text-gray-500">
                                                    <div class="text-lg font-bold">+{{ getQuantityLabels().length - 8 }}</div>
                                                    <div class="text-xs">More Labels</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- When only separate_item is ACTIVE: one label per unique item -->
                                <template v-else-if="form.separate_item == enums.statusEnum.ACTIVE">
                                    <div class="w-full">
                                        <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded text-center">
                                            <div class="font-semibold text-blue-800">ITEM SEPARATION MODE</div>
                                            <div class="text-xs text-blue-600">Creating {{ previewItems.length }} labels (one per unique item)</div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-4 justify-start max-w-full overflow-x-auto">
                                            <div v-for="(labelItem, index) in previewItems" :key="index"
                                                 class="bg-white border-2 border-dashed border-gray-400 shadow-sm text-xs font-mono overflow-hidden relative"
                                                 :style="{ 
                                                     width: (form.label_width || 50) * 2.5 + 'px', 
                                                     height: (form.label_height || 30) * 2.5 + 'px',
                                                     minWidth: '150px',
                                                     minHeight: '100px'
                                                 }">
                                                
                                                <!-- Label indicators -->
                                                <div class="absolute top-0.5 right-0.5 bg-black bg-opacity-75 text-white text-2xs px-1 py-0.5 rounded">
                                                    {{ form.label_width || 50 }}×{{ form.label_height || 30 }}mm
                                                </div>
                                                <div class="absolute top-0.5 left-0.5 bg-blue-600 text-white text-2xs px-1 py-0.5 rounded">
                                                    {{ index + 1 }}
                                                </div>
                                                
                                                <div class="p-1">
                                                    <!-- Header Section -->
                                                    <div class="text-center border-b border-dashed border-gray-300 pb-0.5 mb-0.5" v-if="form.show_company_name == enums.statusEnum.ACTIVE || form.show_branch_name == enums.statusEnum.ACTIVE || form.show_phone_number == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_company_name == enums.statusEnum.ACTIVE" class="font-bold text-gray-800 text-2xs company_name">RESTAURANT POS</div>
                                                        <div v-if="form.show_branch_name == enums.statusEnum.ACTIVE" class="text-gray-600 text-2xs branch_name">{{ branch?.name || 'Main Branch' }}</div>
                                                        <div v-if="form.show_phone_number == enums.statusEnum.ACTIVE" class="text-gray-500 text-2xs phone_number">Tel: (855) 12-345-678</div>
                                                    </div>
                                                    
                                                    <!-- Order Information -->
                                                    <div class="mb-0.5" v-if="form.show_order_number == enums.statusEnum.ACTIVE || form.show_order_qr_code == enums.statusEnum.ACTIVE || form.show_order_number_barcode == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_order_number == enums.statusEnum.ACTIVE" class="font-semibold text-2xs order_number">Order #: ORD-2025-001</div>
                                                        <div class="flex justify-center items-center gap-1 mt-0.5">
                                                            <img v-if="form.show_order_qr_code == enums.statusEnum.ACTIVE" src="https://api.qrserver.com/v1/create-qr-code/?size=18x18&data=ORD-2025-001" alt="QR" class="border border-dashed border-gray-400 order_qr_code">
                                                            <img v-if="form.show_order_number_barcode == enums.statusEnum.ACTIVE" src="https://barcode.tec-it.com/barcode.ashx?data=ORD2025001&code=Code128&dpi=96&imagetype=png" alt="Barcode" class="h-2 order_number_barcode">
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Single Item with Original Quantity -->
                                                    <div v-if="form.show_item == enums.statusEnum.ACTIVE" class="border border-dashed border-blue-300 rounded p-0.5 mb-0.5 bg-blue-50">
                                                        <div class="font-semibold text-blue-700 mb-0.5 text-2xs">ITEM SEPARATED</div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="font-medium text-2xs item_name">{{ labelItem.name }}</span>
                                                            <div class="text-right">
                                                                <div v-if="form.show_item_qty == enums.statusEnum.ACTIVE && form.show_item_price == enums.statusEnum.ACTIVE" class="text-2xs">{{ labelItem.qty }} × ${{ labelItem.price }}</div>
                                                                <div v-else-if="form.show_item_qty == enums.statusEnum.ACTIVE" class="text-2xs">Qty: {{ labelItem.qty }}</div>
                                                                <div v-else-if="form.show_item_price == enums.statusEnum.ACTIVE" class="text-2xs">${{ labelItem.price }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Customer Information -->
                                                    <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE || form.show_customer_phone_number == enums.statusEnum.ACTIVE || form.show_delivery_address == enums.statusEnum.ACTIVE" class="mb-0.5 text-2xs">
                                                        <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE" class="font-medium customer_name">Customer: John Doe</div>
                                                        <div v-if="form.show_customer_phone_number == enums.statusEnum.ACTIVE" class="customer_phone_number">Phone: (855) 98-765-432</div>
                                                        <div v-if="form.show_delivery_address == enums.statusEnum.ACTIVE" class="text-gray-600 delivery_address">123 Street, Phnom Penh</div>
                                                    </div>
                                                    
                                                    <!-- Payment Information -->
                                                    <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE || form.show_payment_method == enums.statusEnum.ACTIVE || form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="mb-0.5 text-2xs">
                                                        <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE" class="text-green-600 font-medium payment_status">Status: PAID</div>
                                                        <div v-if="form.show_payment_method == enums.statusEnum.ACTIVE" class="payment_method">Method: Credit Card</div>
                                                        <div v-if="form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="flex justify-center mt-0.5">
                                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=15x15&data=payment-qr" alt="Payment QR" class="border border-dashed border-gray-400 payment_qr_code">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- When both separate_item and separate_qty are INACTIVE: single combined label -->
                                <template v-else>
                                    <div class="w-full flex justify-center">
                                        <div class="text-center">
                                            <div class="mb-3 p-2 bg-green-50 border border-green-200 rounded">
                                                <div class="font-semibold text-green-800">COMBINED LABEL MODE</div>
                                                <div class="text-xs text-green-600">Creating 1 label with all items combined</div>
                                            </div>
                                            
                                            <div 
                                                id="label" 
                                                class="bg-white border-2 border-dashed border-gray-400 shadow-sm text-xs font-mono overflow-hidden relative"
                                                :style="{ 
                                                    width: (form.label_width || 50) * 3 + 'px', 
                                                    height: (form.label_height || 30) * 3 + 'px',
                                                    minWidth: '200px',
                                                    minHeight: '150px'
                                                }">
                                                
                                                <!-- Size indicator overlay -->
                                                <div class="absolute top-1 right-1 bg-black bg-opacity-75 text-white text-xs px-1 py-0.5 rounded">
                                                    {{ form.label_width || 50 }}×{{ form.label_height || 30 }}mm
                                                </div>
                                                
                                                <!-- Content overflow warning -->
                                                <div v-if="isContentOverflowing" class="absolute bottom-1 left-1 bg-red-600 text-white text-xs px-1 py-0.5 rounded animate-pulse">
                                                    ⚠ Content may overflow
                                                </div>
                                                
                                                <div class="p-2">
                                                    <!-- Header Section -->
                                                    <div class="text-center border-b border-dashed border-gray-300 pb-1 mb-1" v-if="form.show_company_name == enums.statusEnum.ACTIVE || form.show_branch_name == enums.statusEnum.ACTIVE || form.show_phone_number == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_company_name == enums.statusEnum.ACTIVE" class="font-bold text-gray-800 text-sm company_name">RESTAURANT POS</div>
                                                        <div v-if="form.show_branch_name == enums.statusEnum.ACTIVE" class="text-gray-600 text-xs branch_name">{{ branch?.name || 'Main Branch' }}</div>
                                                        <div v-if="form.show_phone_number == enums.statusEnum.ACTIVE" class="text-gray-500 text-xs phone_number">Tel: (855) 12-345-678</div>
                                                    </div>
                                                    
                                                    <!-- Order Information -->
                                                    <div class="mb-1" v-if="form.show_order_number == enums.statusEnum.ACTIVE || form.show_order_qr_code == enums.statusEnum.ACTIVE || form.show_order_number_barcode == enums.statusEnum.ACTIVE">
                                                        <div v-if="form.show_order_number == enums.statusEnum.ACTIVE" class="font-semibold text-xs order_number">Order #: ORD-2025-001</div>
                                                        <div class="flex justify-center items-center gap-1 mt-1">
                                                            <img v-if="form.show_order_qr_code == enums.statusEnum.ACTIVE" src="https://api.qrserver.com/v1/create-qr-code/?size=25x25&data=ORD-2025-001" alt="QR" class="border border-dashed border-gray-400 order_qr_code">
                                                            <img v-if="form.show_order_number_barcode == enums.statusEnum.ACTIVE" src="https://barcode.tec-it.com/barcode.ashx?data=ORD2025001&code=Code128&dpi=96&imagetype=png" alt="Barcode" class="h-3 order_number_barcode">
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- All Items Combined -->
                                                    <div v-if="form.show_item == enums.statusEnum.ACTIVE" class="border border-dashed border-green-300 rounded p-1 mb-1 bg-green-50">
                                                        <div class="font-semibold text-green-700 mb-1 text-xs">ALL ITEMS COMBINED</div>
                                                        <div class="space-y-0.5">
                                                            <div v-for="(item, index) in previewItems" :key="index" class="flex justify-between items-center">
                                                                <span class="font-medium text-xs item_name">{{ item.name }}</span>
                                                                <div class="text-right">
                                                                    <div v-if="form.show_item_qty == enums.statusEnum.ACTIVE && form.show_item_price == enums.statusEnum.ACTIVE" class="text-xs item_qty item_price">{{ item.qty }} × ${{ item.price }}</div>
                                                                    <div v-else-if="form.show_item_qty == enums.statusEnum.ACTIVE" class="text-xs item_qty">Qty: {{ item.qty }}</div>
                                                                    <div v-else-if="form.show_item_price == enums.statusEnum.ACTIVE" class="text-xs item_price">${{ item.price }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Customer Information -->
                                                    <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE || form.show_customer_phone_number == enums.statusEnum.ACTIVE || form.show_delivery_address == enums.statusEnum.ACTIVE" class="mb-1 text-xs">
                                                        <div v-if="form.show_customer_name == enums.statusEnum.ACTIVE" class="font-medium customer_name">Customer: John Doe</div>
                                                        <div v-if="form.show_customer_phone_number == enums.statusEnum.ACTIVE" class="customer_phone_number">Phone: (855) 98-765-432</div>
                                                        <div v-if="form.show_delivery_address == enums.statusEnum.ACTIVE" class="text-gray-600 delivery_address">123 Street, Phnom Penh</div>
                                                    </div>
                                                    
                                                    <!-- Payment Information -->
                                                    <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE || form.show_payment_method == enums.statusEnum.ACTIVE || form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="mb-1 text-xs">
                                                        <div v-if="form.show_payment_status == enums.statusEnum.ACTIVE" class="text-green-600 font-medium payment_status">Status: PAID</div>
                                                        <div v-if="form.show_payment_method == enums.statusEnum.ACTIVE" class="payment_method">Method: Credit Card</div>
                                                        <div v-if="form.show_payment_qr_code == enums.statusEnum.ACTIVE" class="flex justify-center mt-1">
                                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=20x20&data=payment-qr" alt="Payment QR" class="border border-dashed border-gray-400 payment_qr_code">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                            </div>
                            
                            <!-- Label size guide -->
                            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="text-xs text-gray-600">
                                    <div class="font-semibold mb-1">Common Label Sizes:</div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between">
                                            <span>Small Labels:</span>
                                            <span class="font-mono">30×20mm - 40×25mm</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Standard Labels:</span>
                                            <span class="font-mono">50×30mm - 60×40mm</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Large Labels:</span>
                                            <span class="font-mono">80×50mm - 100×60mm</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-xs text-amber-600">
                                        💡 Tip: Smaller labels work best with fewer content sections enabled
                                    </div>
                                </div>
                            </div>  
                        </div>  
                    </div>  
                </div> 
            </div> 
        </div>  
        <!-- End of Print Label Preview -->  
    </div>
</template>
<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum"; 
export default {
    name: "PrintLabelSettingPreviewComponent",
    components: { SmModalCreateComponent, LoadingComponent}, 
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.yes"),
                    [statusEnum.INACTIVE]: this.$t("label.no"),
                }, 
            }, 
            errors: {},
            form: { 
                name: 'Default Label Setting',
                show_company_name: statusEnum.ACTIVE, // ACTIVE by default
                show_branch_name: statusEnum.ACTIVE, // ACTIVE by default
                show_phone_number: statusEnum.ACTIVE, // ACTIVE by default
                show_order_number: statusEnum.ACTIVE, // ACTIVE by default
                show_order_number_barcode: statusEnum.INACTIVE, // INACTIVE by default
                show_order_qr_code: statusEnum.INACTIVE, // INACTIVE by default
                show_item: statusEnum.ACTIVE, // ACTIVE by default
                show_item_qty: statusEnum.ACTIVE, // ACTIVE by default
                show_item_price: statusEnum.ACTIVE, // ACTIVE by default
                show_customer_name: statusEnum.INACTIVE, // INACTIVE by default
                show_customer_phone_number: statusEnum.INACTIVE, // INACTIVE by default
                show_delivery_address: statusEnum.INACTIVE, // INACTIVE by default
                show_payment_status: statusEnum.INACTIVE, // INACTIVE by default
                show_payment_qr_code: statusEnum.INACTIVE, // INACTIVE by default
                show_payment_method: statusEnum.INACTIVE, // INACTIVE by default
                print_qty: 1, // number of copies to print (integer)
                label_title: 1, // ACTIVE by default
                label_width: 50, // 50mm standard label width
                label_height: 30, // 30mm standard label height
                separate_item: statusEnum.ACTIVE, // INACTIVE by default
                separate_qty: statusEnum.ACTIVE, // INACTIVE by default
                label_style_custom: `.label_company_name {\n\n}\n\n.label_branch_name {\n\n}\n\n.label_phone_number {\n\n}\n\n.label_order_number {\n\n}\n\n.label_items {\n\n}.label_item_name {\n\n}\n\n.label_item_qty {\n\n}\n\n.label_item_price {\n\n}\n\n.label_customer_name {\n\n}\n\n.label_customer_phone {\n\n}\n\n.label_delivery_address {\n\n}\n\n.label_payment_status {\n\n}\n\n.label_payment_method {\n\n}`,
            },
            printLabel: {
                
            }
        };
    }, 
    mounted() { 
        this.loading.isActive = true;
        this.$store.dispatch("printLabelSetting/show", this.$route.params.id)
            .then((res) => {   
                const printLabel = res.data.data 
                this.form = {
                    name: printLabel.name,
                    show_company_name: printLabel.show_company_name, 
                    show_branch_name: printLabel.show_branch_name,
                    show_phone_number: printLabel.show_phone_number,
                    show_order_number: printLabel.show_order_number,
                    show_order_number_barcode: printLabel.show_order_number_barcode,
                    show_order_qr_code: printLabel.show_order_qr_code,
                    show_item: printLabel.show_item,
                    show_item_qty: printLabel.show_item_qty,
                    show_item_price: printLabel.show_item_price,
                    show_customer_name: printLabel.show_customer_name,
                    show_customer_phone_number: printLabel.show_customer_phone_number,
                    show_delivery_address: printLabel.show_delivery_address,
                    show_payment_status: printLabel.show_payment_status,
                    show_payment_qr_code: printLabel.show_payment_qr_code,
                    show_payment_method: printLabel.show_payment_method,
                    print_qty: printLabel.print_qty,
                    label_title: printLabel.label_title,
                    label_width: printLabel.label_width,
                    label_height: printLabel.label_height,
                    separate_item: printLabel.separate_item,
                    separate_qty: printLabel.separate_qty,
                    label_style_custom: printLabel.label_style_custom || this.form.label_style_custom 
                }
                this.loading.isActive = false;
                this.injectCustomStyles();
            }).catch((error) => {
                this.loading.isActive = false;
                this.injectCustomStyles();
            }); 
    },
    watch: {
        'form.label_style_custom': function(newVal) {
            this.injectCustomStyles();
        }
    },
    computed:{
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        previewItems: function () {
            return [
                { name: 'Beef Burger', qty: 2, price: 12.50 },
                { name: 'Chicken Wings', qty: 1, price: 8.75 },
                { name: 'Caesar Salad', qty: 1, price: 6.25 },
                { name: 'Boklahong', qty: 5, price: 20 }
            ];
        },
        previewQtyItems: function () {
            const items = [];
            this.previewItems.forEach(item => {
                for (let i = 0; i < item.qty; i++) {
                    items.push({ ...item, qty: 1 });
                }
            });
            return items;
        },
        isContentOverflowing: function () {
            const width = this.form.label_width || 50;
            const height = this.form.label_height || 30;
            
            // Calculate content sections that are active
            let contentSections = 0;
            if (this.form.show_company_name == 1 || this.form.show_branch_name == 1 || this.form.show_phone_number == 1) contentSections++;
            if (this.form.show_order_number == 1 || this.form.show_order_qr_code == 1 || this.form.show_order_number_barcode == 1) contentSections++;
            if (this.form.show_item == 1) contentSections++;
            if (this.form.show_customer_name == 1 || this.form.show_customer_phone_number == 1 || this.form.show_delivery_address == 1) contentSections++;
            if (this.form.show_payment_status == 1 || this.form.show_payment_method == 1 || this.form.show_payment_qr_code == 1) contentSections++;
            
            // Warning thresholds: small labels with too much content
            if (width < 40 && contentSections > 3) return true;
            if (height < 25 && contentSections > 2) return true;
            if (width < 30 || height < 20) return true;
            
            return false;
        }
    },
    methods: {    
        getContentPadding: function() {
            // Use minimal padding for maximum data visibility
            return 'p-1';
        },
        getHeaderFontSize: function() {
            // Use consistently small text for more data visibility
            return 'text-2xs';
        },
        getSmallFontSize: function() {
            // Use consistently small text for more data visibility
            return 'text-2xs';
        },
        getTinyFontSize: function() {
            // Use consistently small text for more data visibility
            return 'text-2xs';
        },
        getPreviewLabels: function() {
            // Return appropriate labels based on separation settings
            if (this.form.separate_item == this.enums.statusEnum.ACTIVE && this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                // Both: separate all quantities
                return this.previewQtyItems;
            } else if (this.form.separate_item == this.enums.statusEnum.ACTIVE) {
                // Only separate items: each item gets its own label with original quantity
                return this.previewItems;
            } else if (this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                // Only separate quantities: each quantity gets its own label
                return this.previewQtyItems;
            }
            // Default: combined label
            return this.previewItems;
        },
        getLabelBackgroundClass: function() {
            if (this.form.separate_item == this.enums.statusEnum.ACTIVE && this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'bg-red-50';
            } else if (this.form.separate_item == this.enums.statusEnum.ACTIVE) {
                return 'bg-blue-50';
            } else if (this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'bg-yellow-50';
            }
            return 'bg-green-50';
        },
        getLabelTextClass: function() {
            if (this.form.separate_item == this.enums.statusEnum.ACTIVE && this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'text-red-700';
            } else if (this.form.separate_item == this.enums.statusEnum.ACTIVE) {
                return 'text-blue-700';
            } else if (this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'text-yellow-700';
            }
            return 'text-green-700';
        },
        getLabelTypeText: function() {
            if (this.form.separate_item == this.enums.statusEnum.ACTIVE && this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'ITEM + QTY SEPARATED';
            } else if (this.form.separate_item == this.enums.statusEnum.ACTIVE) {
                return 'ITEM SEPARATED';
            } else if (this.form.separate_qty == this.enums.statusEnum.ACTIVE) {
                return 'QUANTITY SEPARATED';
            }
            return 'COMBINED LABEL';
        },
        getTotalQuantity: function() {
            return this.previewItems.reduce((total, item) => total + item.qty, 0);
        },
        getQuantityLabels: function() {
            const labels = [];
            this.previewItems.forEach(item => {
                for (let i = 0; i < item.qty; i++) {
                    labels.push({ ...item, qty: 1 });
                }
            });
            return labels;
        },
        injectCustomStyles: function() {
            // Remove existing custom style element if it exists
            const existingStyle = document.getElementById('custom-label-styles');
            if (existingStyle) {
                existingStyle.remove();
            }

            // Create new style element
            const styleElement = document.createElement('style');
            styleElement.id = 'custom-label-styles';
            styleElement.textContent = this.form.label_style_custom || '';
            
            // Append to document head
            document.head.appendChild(styleElement);
        },    
        // reset: function () {
        //     appService.modalHide();
        //     this.$store.dispatch("printLabelSetting/reset").then().catch();
        //     this.errors = {};
        //     this.$form = { 
        //         show_company_name: statusEnum.ACTIVE, 
        //         show_branch_name: statusEnum.ACTIVE,
        //         show_phone_number: statusEnum.ACTIVE,
        //         show_order_number: statusEnum.ACTIVE,
        //         show_order_number_barcode: statusEnum.ACTIVE,
        //         show_order_qr_code: statusEnum.ACTIVE,
        //         show_item: statusEnum.ACTIVE,
        //         show_item_qty: statusEnum.ACTIVE,
        //         show_item_price: statusEnum.ACTIVE,
        //         show_customer_name: statusEnum.ACTIVE,
        //         show_customer_phone_number: statusEnum.ACTIVE,
        //         show_delivery_address: statusEnum.ACTIVE,
        //         show_payment_status: statusEnum.ACTIVE,
        //         show_payment_qr_code: statusEnum.ACTIVE,
        //         show_payment_method: statusEnum.ACTIVE,
        //         print_qty: statusEnum.ACTIVE,
        //         label_size: statusEnum.ACTIVE,
        //         label_title: statusEnum.ACTIVE 
        //     };
        // }, 
        // save: function () {
        //     try {    
        //         const data = {
        //             show_company_name: this.form.show_company_name, 
        //             show_branch_name: this.form.show_branch_name,
        //             show_phone_number: this.form.show_phone_number,
        //             show_order_number: this.form.show_order_number,
        //             show_order_number_barcode: this.form.show_order_number_barcode,
        //             show_order_qr_code: this.form.show_order_qr_code,
        //             show_item: this.form.show_item,
        //             show_item_qty: this.form.show_item_qty,
        //             show_item_price: this.form.show_item_price,
        //             show_customer_name: this.form.show_customer_name,
        //             show_customer_phone_number: this.form.show_customer_phone_number,
        //             show_delivery_address: this.form.show_delivery_address,
        //             show_payment_status: this.form.show_payment_status,
        //             show_payment_qr_code: this.form.show_payment_qr_code,
        //             show_payment_method: this.form.show_payment_method,
        //             print_qty: this.form.print_qty,
        //             // label_size: this.form.label_size,
        //             // label_title: this.form.label_title
        //             label_size: 5,
        //             label_title: 5
        //         }  
        //         const tempId = this.$store.getters["printLabelSetting/temp"].temp_id;
        //         this.loading.isActive = true;  

        //         this.$store.dispatch("printLabelSetting/save", {
        //                 form: data,
        //             }).then((res) => {
        //             appService.modalHide();
        //             this.loading.isActive = false;
        //             alertService.successFlip(
        //                 tempId === null ? 0 : 1,
        //                 this.$t("menu.print_label")
        //             );
        //             this.form = {
        //                 show_company_name: statusEnum.ACTIVE, 
        //                 show_branch_name: statusEnum.ACTIVE,
        //                 show_phone_number: statusEnum.ACTIVE,
        //                 show_order_number: statusEnum.ACTIVE,
        //                 show_order_number_barcode: statusEnum.ACTIVE,
        //                 show_order_qr_code: statusEnum.ACTIVE,
        //                 show_item: statusEnum.ACTIVE,
        //                 show_item_qty: statusEnum.ACTIVE,
        //                 show_item_price: statusEnum.ACTIVE,
        //                 show_customer_name: statusEnum.ACTIVE,
        //                 show_customer_phone_number: statusEnum.ACTIVE,
        //                 show_delivery_address: statusEnum.ACTIVE,
        //                 show_payment_status: statusEnum.ACTIVE,
        //                 show_payment_qr_code: statusEnum.ACTIVE,
        //                 show_payment_method: statusEnum.ACTIVE,
        //                 print_qty: statusEnum.ACTIVE,
        //                 label_size: statusEnum.ACTIVE,
        //                 label_title: statusEnum.ACTIVE 
        //             };
        //             this.errors = {};
        //         }).catch((err) => {
        //             console.log("ERROR:",err)
        //             this.loading.isActive = false;
        //             this.errors = err.response.data.errors;
        //         });
        //     } catch (err) {
        //         this.loading.isActive = false;
        //         alertService.error(err);
        //     }
        // }, 
        save: function () {
            try {    
                const data = {
                    name: this.form.name,
                    show_company_name: this.form.show_company_name, 
                    show_branch_name: this.form.show_branch_name,
                    show_phone_number: this.form.show_phone_number,
                    show_order_number: this.form.show_order_number,
                    show_order_number_barcode: this.form.show_order_number_barcode,
                    show_order_qr_code: this.form.show_order_qr_code,
                    show_item: this.form.show_item,
                    show_item_qty: this.form.show_item_qty,
                    show_item_price: this.form.show_item_price,
                    show_customer_name: this.form.show_customer_name,
                    show_customer_phone_number: this.form.show_customer_phone_number,
                    show_delivery_address: this.form.show_delivery_address,
                    show_payment_status: this.form.show_payment_status,
                    show_payment_qr_code: this.form.show_payment_qr_code,
                    show_payment_method: this.form.show_payment_method,
                    print_qty: this.form.print_qty,
                    label_title: this.form.label_title,
                    label_width: this.form.label_width,
                    label_height: this.form.label_height,
                    separate_item: this.form.separate_item,
                    separate_qty: this.form.separate_qty,
                    label_style_custom: this.form.label_style_custom
                }  

                this.loading.isActive = true;  
                this.$store.dispatch("printLabelSetting/update", {
                    form: data,
                    id: this.$route.params.id
                }).then((res) => {
                    this.loading.isActive = false;
                    this.$toast.success(this.$t('message.updated'));
                }).catch((err) => {
                    console.log("ERROR:",err)
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                console.error(err);
            }
        }
    },
};
</script>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #label, #label * {
            visibility: visible;
        }
        #label {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        #print-button {
            display: none;
        }
    }
</style>
<style>
    /* Custom CSS styles will be injected here */
</style>