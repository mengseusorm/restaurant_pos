<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.branches") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title required">{{ $t("label.name")  }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input v-model="props.form.name_kh" v-bind:class="errors.name_kh ? 'invalid' : ''" type="text"
                                id="name_kh" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input v-model="props.form.name_cn" v-bind:class="errors.name_cn ? 'invalid' : ''" type="text"
                                id="name_cn" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_en" class="db-field-title">{{ $t("label.name_en") }}</label>
                            <input v-model="props.form.name_en" v-bind:class="errors.name_en ? 'invalid' : ''" type="text"
                                id="name_en" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_en">{{ errors.name_en[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="code" class="db-field-title">{{ $t("label.code") }}</label>
                            <input v-model="props.form.code" v-bind:class="errors.code ? 'invalid' : ''" type="text"
                                id="code" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.code">{{ errors.code[0] }}</small>
                        </div>


                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="latitude">{{ $t("label.latitude") }}/{{ $t("label.longitude") }}</label>
                            <div class="db-multiple-field">
                                <input v-model="props.form.latitude" v-bind:class="errors.latitude ? 'invalid' : ''  " type="text" id="latitude" />
                                <input v-model="props.form.longitude" v-bind:class="errors.longitude ? 'invalid' : '' " type="text" id="longitude" />
                                <button @click="add" v-on:click="isMap = true" type="button"
                                    class="fa-solid fa-map-location-dot" data-modal="#branchMap"></button>
                            </div>

                            <small class="db-field-alert" v-if="errors.latitude">{{ errors.latitude[0] }}</small>
                            <small class="db-field-alert" v-if="errors.longitude">{{ errors.longitude[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="email" class="db-field-title">{{ $t("label.email") }}</label>
                            <input v-model="props.form.email" v-bind:class="errors.email ? 'invalid' : ''" type="email"
                                id="email" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.email">{{  errors.email[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="phone" class="db-field-title">{{ $t("label.phone")  }}</label>
                            <input v-model="props.form.phone" v-bind:class="errors.phone ? 'invalid' : ''" type="text"
                                id="phone" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.phone">{{ errors.phone[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="city" class="db-field-title required">{{ $t("label.city") }}</label>
                            <input v-model="props.form.city" v-bind:class="errors.city ? 'invalid' : ''" type="text"
                                id="city" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.city">{{ errors.city[0]}}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="state" class="db-field-title required">{{ $t("label.state") }}</label>
                            <input v-model="props.form.state" v-bind:class="errors.state ? 'invalid' : ''" type="text"
                                id="state" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.state">{{ errors.state[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="zip_code" class="db-field-title required">{{ $t("label.zip_code") }}</label>
                            <input v-model="props.form.zip_code" v-bind:class="errors.zip_code ? 'invalid' : ''"
                                type="text" id="zip_code" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.zip_code">{{ errors.zip_code[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="language_id" class="db-field-title required">{{ $t("label.language") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="language_id"
                                v-bind:class="errors.language_id ? 'invalid' : ''"
                                v-model="props.form.language_id" :options="languages" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--"  />
                            <small class="db-field-alert" v-if="errors.language_id">{{ errors.language_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="shop_category_id" class="db-field-title required">{{ $t("label.shop_categories") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="shop_category_id"
                                v-bind:class="errors.shop_category_id ? 'invalid' : ''"
                                v-model="props.form.shop_category_id" :options="shopCategories" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--"  />
                            <small class="db-field-alert" v-if="errors.shop_category_id">{{ errors.shop_category_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="open_time" class="db-field-title">{{ $t("label.open_time") }}</label>
                            <input v-model="props.form.open_time" v-bind:class="errors.open_time ? 'invalid' : ''"
                                type="time" id="open_time" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.open_time">{{ errors.open_time[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="close_time" class="db-field-title">{{ $t("label.close_time") }}</label>
                            <input v-model="props.form.close_time" v-bind:class="errors.close_time ? 'invalid' : ''"
                                type="time" id="close_time" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.close_time">{{ errors.close_time[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="address" class="db-field-title required">{{ $t("label.address") }}</label>
                            <textarea v-model="props.form.address" v-bind:class="errors.address ? 'invalid' : ''"
                                id="address" class="db-field-control"></textarea>
                            <small class="db-field-alert" v-if="errors.address">{{ errors.address[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="open_time" class="db-field-title">{{ $t("label.open_time") }}</label>
                            <input v-model="props.form.open_time" v-bind:class="errors.open_time ? 'invalid' : ''" type="time"
                                id="open_time" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.open_time">{{ errors.open_time[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="close_time" class="db-field-title">{{ $t("label.close_time") }}</label>
                            <input v-model="props.form.close_time" v-bind:class="errors.close_time ? 'invalid' : ''" type="time"
                                id="close_time" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.close_time">{{ errors.close_time[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-2 db-field-title after:hidden"><b>{{ $t('label.branch_setting') }}</b></label>
                            <hr>
                        </div>
                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">{{
                                $t('label.current_business_day')
                            }}</label>
                            <Datepicker autoApply :enableTimePicker="false" utc="false"
                                  @update:modelValue="handleDate"
                                  v-model="props.form.current_business_day" >
                                <template #yearly="{ label, range, presetDateRange }">
                                    <span @click="presetDateRange(range)">{{ label }}</span>
                                </template>
                            </Datepicker>
                        </div> -->

                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label for="businessCloseTime" class="db-field-title after:hidden">{{
                                $t('label.close_business_day_time')
                            }}</label>
                            <input type="time" v-model="props.form.close_business_day_time" class="db-field-control"  />
                            <small class="db-field-alert" v-if="errors.close_business_day_time">{{ errors.close_business_day_time[0] }}</small>
                        </div> -->

                        <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.currencies") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="currency_id"
                                v-bind:class="errors.currency_id ? 'invalid' : ''"
                                v-model="props.form.currency_id" :options="currencies" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--"  />
                            <small class="db-field-alert" v-if="errors.item_category_id">{{ errors.item_category_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t("label.status") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active_status"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_status" class="db-field-label">{{ $t("label.active") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status"
                                            type="radio" id="inactive_status" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_status" class="db-field-label">{{ $t("label.inactive") }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.show_unpaid_button_on_topbar") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_unpaid_button" id="active_show_unpaid_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_show_unpaid_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_unpaid_button"
                                            type="radio" id="inactive_show_unpaid_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_show_unpaid_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>  -->


                        <!-- Start POS order button setting -->
                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-4 db-field-title after:hidden"><b>{{ $t('label.order_setting') }}</b></label>
                            <hr>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.change_status_from_paid_to_unpaid") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.change_status_paid_to_unpaid" id="active_change_status_paid_to_unpaid"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_change_status_paid_to_unpaid" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.change_status_paid_to_unpaid"
                                            type="radio" id="inactive_change_status_paid_to_unpaid" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_change_status_paid_to_unpaid" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.show_delete_order_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_delete_order_button" id="active_show_delete_order_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_show_delete_order_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_delete_order_button"
                                            type="radio" id="inactive_show_delete_order_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_show_delete_order_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>
                        <!-- End of Start POS order button setting -->

                        <!-- ============================================================================================================ -->


                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-4 db-field-title after:hidden"><b>{{ $t('label.pos_order_type_setting') }}</b></label>
                            <hr>
                        </div>

                       <div class="form-col-12 sm:form-col-12">
                            <label class="db-field-title required">
                                {{ $t("label.default_selected_order_type") }}
                            </label>

                            <div class="db-field-radio-group">
                                <!-- POS -->
                                <!-- <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.POS"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_pos"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_pos" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[15] }}
                                    </label>
                                </div> -->

                                <!-- DELIVERY -->
                                <!-- <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.DELIVERY"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_delivery"
                                            :checked="props.form.default_selected_order_type === enums.orderTypeEnum.DELIVERY"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_delivery" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[5] }}
                                    </label>
                                </div> -->

                                <!-- DINING_TABLE -->
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.DINING_TABLE"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_dining_table"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_dining_table" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[20] }}
                                    </label>
                                </div>


                                <!-- ONLINE_ORDER -->
                                <!-- <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.ONLINE_ORDER"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_online_order"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_online_order" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[30] }}
                                    </label>
                                </div> -->

                                <!-- TOKEN -->
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.TOKEN"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_token"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_token" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[25] }}
                                    </label>
                                </div>

                                        <!-- TAKEAWAY -->
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input
                                            type="radio"
                                            class="custom-radio-field"
                                            :value="enums.orderTypeEnum.TAKEAWAY"
                                            v-model="props.form.default_selected_order_type"
                                            id="default_selected_order_type_takeaway"
                                        />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="default_selected_order_type_takeaway" class="db-field-label">
                                        {{ enums.orderTypeEnumArray[10] }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label for="show_select_table" class="db-field-title required">{{ $t("label.show_select_customer_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_select_customer" id="on_show_select_customer"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_select_customer" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_select_customer"
                                            type="radio" id="off_show_select_customer" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_select_customer" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_select_table" class="db-field-title required">{{ $t("label.show_input_number_of_people_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_input_number_of_people" id="on_show_input_number_of_people"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_input_number_of_people" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_input_number_of_people"
                                            type="radio" id="off_show_input_number_of_people" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_input_number_of_people" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_select_table" class="db-field-title required">{{ $t("label.show_select_dinning_table_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_select_table" id="on_show_select_table"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_select_table" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_select_table"
                                            type="radio" id="off_show_select_table" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_select_table" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_select_table" class="db-field-title required">{{ $t("label.show_select_table_list") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_select_table_list" id="on_show_select_table_list"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_select_table_list" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_select_table_list"
                                            type="radio" id="off_show_select_table_list" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_select_table_list" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_token" class="db-field-title required">{{ $t("label.show_token_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_token" id="on_show_token"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_token" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_token"
                                            type="radio" id="off_show_token" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_token" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_delivery" class="db-field-title required">{{ $t("label.show_delivery_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_delivery" id="on_show_delivery"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_delivery" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_delivery"
                                            type="radio" id="off_show_delivery" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_delivery" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_waiting_number" class="db-field-title required">{{ $t("label.show_waiting_number_on_invoice") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_waiting_number" id="on_show_waiting_number"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_waiting_number" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_waiting_number"
                                            type="radio" id="off_show_waiting_number" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_waiting_number" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-4 db-field-title after:hidden"><b>{{ $t('label.pos_order_button_setting') }}</b></label>
                            <hr>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_suspense_button" class="db-field-title required">{{ $t("label.show_suspense_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_suspense_button" id="on_show_suspense_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_suspense_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_suspense_button"
                                            type="radio" id="off_show_suspense_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_suspense_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_paid_order_button" class="db-field-title required">{{ $t("label.show_paid_order_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_paid_order_button" id="on_show_paid_order_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_paid_order_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_paid_order_button"
                                            type="radio" id="off_show_paid_order_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_paid_order_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.show_unpaid_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_unpaid_button" id="active_show_unpaid_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_show_unpaid_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_unpaid_button"
                                            type="radio" id="inactive_show_unpaid_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_show_unpaid_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_sidebar_table_list" class="db-field-title required">{{ $t("label.show_sidebar_table_list") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_sidebar_table_list" id="on_show_sidebar_table_list"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_sidebar_table_list" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_sidebar_table_list"
                                            type="radio" id="off_show_sidebar_table_list" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_sidebar_table_list" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_sidebar_table_list" class="db-field-title required">{{ $t("label.unpaid_order_show_invoice") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.unpaid_order_show_invoice" id="on_unpaid_order_show_invoice"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_unpaid_order_show_invoice" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.unpaid_order_show_invoice"
                                            type="radio" id="off_unpaid_order_show_invoice" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_unpaid_order_show_invoice" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>



                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-4 db-field-title after:hidden"><b>{{ $t('label.order_payment_setting') }}</b></label>
                            <hr>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="currency_id" class="db-field-title required">{{ $t("label.show_receive_amount") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_receive_amount" id="active_show_receive_amount"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active_show_receive_amount" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_receive_amount"
                                            type="radio" id="inactive_show_receive_amount" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive_show_receive_amount" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label for="searchStartDate" class="mt-4 db-field-title after:hidden"><b>{{ $t('label.pos_order_member_setting') }}</b></label>
                            <hr>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_select_member" class="db-field-title required">{{ $t("label.show_select_member_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_select_member" id="on_show_select_member"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_select_member" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_select_member"
                                            type="radio" id="off_show_select_member" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_select_member" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="member_can_redeem_point" class="db-field-title required">{{ $t("label.member_can_redeem_point_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.member_can_redeem_point" id="on_member_can_redeem_point"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_member_can_redeem_point" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.member_can_redeem_point"
                                            type="radio" id="off_member_can_redeem_point" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_member_can_redeem_point" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_online_order_button" class="db-field-title required">{{ $t("label.show_online_order_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_online_order_button" id="on_show_online_order_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_online_order_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_online_order_button"
                                            type="radio" id="off_show_online_order_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_online_order_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_pending_order_button" class="db-field-title required">{{ $t("label.show_pending_order_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_pending_order_button" id="on_show_pending_order_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_pending_order_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_pending_order_button"
                                            type="radio" id="off_show_pending_order_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_pending_order_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_pos_button" class="db-field-title required">{{ $t("label.show_pos_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_pos_button" id="on_show_pos_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_pos_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_pos_button"
                                            type="radio" id="off_show_pos_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_pos_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_retail_pos_button" class="db-field-title required">{{ $t("label.show_retail_pos_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_retail_pos_button" id="on_show_retail_pos_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_retail_pos_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_retail_pos_button"
                                            type="radio" id="off_show_retail_pos_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_retail_pos_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_quick_pos_button" class="db-field-title required">{{ $t("label.show_quick_pos_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_quick_pos_button" id="on_show_quick_pos_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_quick_pos_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_quick_pos_button"
                                            type="radio" id="off_show_quick_pos_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_quick_pos_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_floor_plan" class="db-field-title required">{{ $t("label.show_floor_plan_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_floor_plan" id="on_show_floor_plan"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_floor_plan" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_floor_plan"
                                            type="radio" id="off_show_floor_plan" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_floor_plan" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_table_button" class="db-field-title required">{{ $t("label.show_table_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_table_button" id="on_show_table_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_table_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_table_button"
                                            type="radio" id="off_show_table_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_table_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_customer_view_button" class="db-field-title required">{{ $t("label.show_customer_view_button_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_customer_view_button" id="on_show_customer_view_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_customer_view_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_customer_view_button"
                                            type="radio" id="off_show_customer_view_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_customer_view_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_navbar_button_text" class="db-field-title required">{{ $t("label.show_navbar_button_text_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_navbar_button_text" id="on_show_navbar_button_text"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_navbar_button_text" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_navbar_button_text"
                                            type="radio" id="off_show_navbar_button_text" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_navbar_button_text" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_customer_name" class="db-field-title required">{{ $t("label.show_customer_name_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_customer_name" id="on_show_customer_name"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_customer_name" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_customer_name"
                                            type="radio" id="off_show_customer_name" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_customer_name" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_customer_phone_number" class="db-field-title required">{{ $t("label.show_customer_phone_number_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_customer_phone_number" id="on_show_customer_phone_number"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_customer_phone_number" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_customer_phone_number"
                                            type="radio" id="off_show_customer_phone_number" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_customer_phone_number" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_customer_address" class="db-field-title required">{{ $t("label.show_customer_address_option") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_customer_address" id="on_show_customer_address"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_customer_address" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_customer_address"
                                            type="radio" id="off_show_customer_address" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_customer_address" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label class="mt-2 db-field-title after:hidden"><b>{{ $t('label.printer') }}</b></label>
                            <hr>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_btn_print_web" class="db-field-title required">{{ $t("label.show_btn_print_web") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_btn_print_web" id="show_btn_print_web"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_btn_print_web" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_btn_print_web"
                                            type="radio" id="off_show_btn_print_web" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_btn_print_web" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_btn_print" class="db-field-title required">{{ $t("label.show_btn_print") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_btn_print" id="show_btn_print"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_btn_print" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_btn_print"
                                            type="radio" id="off_show_btn_print" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_btn_print" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_print_label_button" class="db-field-title required">{{ $t("label.show_print_label_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_print_label_button" id="show_print_label_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_print_label_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_print_label_button"
                                            type="radio" id="off_show_print_label_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_print_label_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="show_discount_button" class="db-field-title required">{{ $t("label.show_discount_button") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_discount_button" id="show_discount_button"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_show_discount_button" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_discount_button"
                                            type="radio" id="off_show_discount_button" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_show_discount_button" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="create_paid_order_confirm" class="db-field-title required">{{ $t("label.create_paid_order_confirm") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.create_paid_order_confirm" id="create_paid_order_confirm"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_create_paid_order_confirm" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.create_paid_order_confirm"
                                            type="radio" id="off_create_paid_order_confirm" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_create_paid_order_confirm" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="create_unpaid_order_confirm" class="db-field-title required">{{ $t("label.create_unpaid_order_confirm") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.create_unpaid_order_confirm" id="create_unpaid_order_confirm"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_create_unpaid_order_confirm" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.create_unpaid_order_confirm"
                                            type="radio" id="off_create_unpaid_order_confirm" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_create_unpaid_order_confirm" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="create_paid_order_auto_print" class="db-field-title required">{{ $t("label.create_paid_order_auto_print") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.create_paid_order_auto_print" id="create_paid_order_auto_print"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_create_paid_order_auto_print" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.create_paid_order_auto_print"
                                            type="radio" id="off_create_paid_order_auto_print" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_create_paid_order_auto_print" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="create_unpaid_order_auto_print" class="db-field-title required">{{ $t("label.create_unpaid_order_auto_print") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.create_unpaid_order_auto_print" id="create_unpaid_order_auto_print"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_create_unpaid_order_auto_print" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.create_unpaid_order_auto_print"
                                            type="radio" id="off_create_unpaid_order_auto_print" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_create_unpaid_order_auto_print" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="void_order_auto_print" class="db-field-title required">{{ $t("label.void_order_auto_print") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.void_order_auto_print" id="void_order_auto_print"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_void_order_auto_print" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.void_order_auto_print"
                                            type="radio" id="off_void_order_auto_print" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_void_order_auto_print" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="change_item_qty_auto_print" class="db-field-title required">{{ $t("label.change_item_qty_auto_print") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.change_item_qty_auto_print" id="change_item_qty_auto_print"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_change_item_qty_auto_print" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.change_item_qty_auto_print"
                                            type="radio" id="off_change_item_qty_auto_print" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_change_item_qty_auto_print" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="unpaid_print_bill" class="db-field-title required">{{ $t("label.unpaid_print_bill") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.unpaid_print_bill" id="unpaid_print_bill"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_unpaid_print_bill" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.unpaid_print_bill"
                                            type="radio" id="off_unpaid_print_bill" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_unpaid_print_bill" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="unpaid_print_invoice" class="db-field-title required">{{ $t("label.unpaid_print_invoice") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.unpaid_print_invoice" id="unpaid_print_invoice"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_unpaid_print_invoice" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.unpaid_print_invoice"
                                            type="radio" id="off_unpaid_print_invoice" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_unpaid_print_invoice" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="open_table_confirm" class="db-field-title required">{{ $t("label.open_table_confirm") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.open_table_confirm" id="open_table_confirm"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_open_table_confirm" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.open_table_confirm"
                                            type="radio" id="off_open_table_confirm" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_open_table_confirm" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="payment_auto_release_table" class="db-field-title required">{{ $t("label.payment_auto_release_table") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.payment_auto_release_table" id="payment_auto_release_table"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="on_payment_auto_release_table" class="db-field-label">{{ $t("label.on") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.payment_auto_release_table"
                                            type="radio" id="off_payment_auto_release_table" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="off_payment_auto_release_table" class="db-field-label">{{ $t("label.off") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-12">
                            <label class="mt-2 db-field-title after:hidden"><b>{{ $t('label.plateform_setting') }}</b></label>
                            <hr>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="online_order_slug" class="db-field-title">{{ $t("label.online_order_slug")  }}</label>
                            <input v-model="props.form.online_order_slug" v-bind:class="errors.online_order_slug ? 'invalid' : ''" type="text"
                                id="online_order_slug" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.online_order_slug">{{ errors.online_order_slug[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="telegram_mini_app_slug" class="db-field-title">{{ $t("label.telegram_mini_app_slug")  }}</label>
                            <input v-model="props.form.telegram_mini_app_slug" v-bind:class="errors.telegram_mini_app_slug ? 'invalid' : ''" type="text"
                                id="telegram_mini_app_slug" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.telegram_mini_app_slug">{{ errors.telegram_mini_app_slug[0] }}</small>
                        </div>


                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline py-2 text-white bg-secondary mr-2" @click="setDefaultSettings">
                                    <i class="lab lab-fill"></i>
                                    <span>{{ $t("button.set_default") }}</span>
                                </button>
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="branchMap" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("label.address") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="mapReset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 map-height">
                            <MapComponent v-if="isMap"
                                :location="{ lat: props.form.latitude, lng: props.form.longitude }"
                                :position="location" />
                        </div>

                        <div class="form-col-12">
                            <label for="apartment" class="db-field-title font-medium text-sm my-0">
                                {{ address }}
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import MapComponent from "../../../admin/components/MapComponent";
import Datepicker from "@vuepic/vue-datepicker";
import { ref } from 'vue';
import orderTypeEnum from "../../../../enums/modules/orderTypeEnum";
export default {
    name: "BranchCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent, MapComponent,Datepicker},
    props: ["props"],
    setup() {
        const date = ref();
        const presetRanges = ref([
            { label: 'Today', range: [new Date()] },
        ]);

        return {
            date,
            presetRanges,
        }
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                },
            },
            isMap: false,
            address: "",
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_branch') };
        },
        languages: function () {
            return this.$store.getters["language/lists"];
        },
        shopCategories: function () {
            return this.$store.getters["shopCategory/lists"];
        },
        currencies:function () {
            return this.$store.getters["currency/lists"]
        }

    },
    mounted(){
        this.$store.dispatch("language/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
        this.$store.dispatch("currency/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
        this.$store.dispatch("shopCategory/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
    },
    methods: {
        add: function () {
            appService.modalShow('#branchMap');
        },
        location: function (e) {
            this.address = e.address;
            this.props.form.latitude = e.location.lat;
            this.props.form.longitude = e.location.lng;
            this.props.form.city = e.other.city;
            this.props.form.state = e.other.state;
            this.props.form.zip_code = e.other.zipCode;
            this.props.form.address = e.address;
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("branch/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                name_kh: "",
                name_cn: "",
                name_en: "",
                code: "",
                online_order_slug: "",
                telegram_mini_app_slug: "",
                email: "",
                phone: "",
                latitude: "",
                longitude: "",
                city: "",
                state: "",
                zip_code: "",
                address: "",
                status: statusEnum.ACTIVE,
                currency_id: 1,
                language_id: 1,
                close_business_day_time:null,
                current_business_day:null,
                open_time: null,
                close_time: null,
                show_unpaid_button: statusEnum.ACTIVE,
                change_status_paid_to_unpaid: statusEnum.ACTIVE,
                show_delete_order_button: statusEnum.ACTIVE,
                //option
                show_select_table: statusEnum.ACTIVE,
                show_select_table_list: statusEnum.ACTIVE,
                show_token: statusEnum.ACTIVE,
                show_delivery: statusEnum.ACTIVE,
                show_waiting_number: statusEnum.ACTIVE,
                show_suspense_button: statusEnum.ACTIVE,
                show_paid_order_button: statusEnum.ACTIVE,
                show_sidebar_table_list: statusEnum.ACTIVE,
                unpaid_order_show_invoice: statusEnum.ACTIVE,
                show_receive_amount: statusEnum.ACTIVE,
                show_select_customer: statusEnum.ACTIVE,
                show_input_number_of_people: statusEnum.ACTIVE,
                default_selected_order_type: orderTypeEnum.POS,
                show_select_member: statusEnum.ACTIVE,
                member_can_redeem_point: statusEnum.ACTIVE,
                show_online_order_button: statusEnum.ACTIVE,
                show_pending_order_button: statusEnum.INACTIVE,
                show_pos_button: statusEnum.ACTIVE,
                show_retail_pos_button: statusEnum.ACTIVE,
                show_quick_pos_button: statusEnum.INACTIVE,
                show_floor_plan: statusEnum.ACTIVE,
                show_table_button: statusEnum.ACTIVE,
                show_customer_view_button: statusEnum.ACTIVE,
                show_navbar_button_text: statusEnum.ACTIVE,
                show_customer_name: statusEnum.ACTIVE,
                show_customer_phone_number: statusEnum.ACTIVE,
                show_customer_address: statusEnum.ACTIVE,
                shop_category_id:null,
                show_btn_print_web: statusEnum.INACTIVE,
                show_btn_print: statusEnum.ACTIVE,
                show_print_label_button: statusEnum.INACTIVE,
                show_discount_button: statusEnum.ACTIVE,
                create_paid_order_confirm: statusEnum.INACTIVE,
                create_unpaid_order_confirm: statusEnum.INACTIVE,
                create_paid_order_auto_print: statusEnum.INACTIVE,
                create_unpaid_order_auto_print: statusEnum.INACTIVE,
                void_order_auto_print: statusEnum.INACTIVE,
                change_item_qty_auto_print: statusEnum.INACTIVE,
                unpaid_print_bill: statusEnum.INACTIVE,
                unpaid_print_invoice: statusEnum.INACTIVE,
                open_table_confirm: statusEnum.INACTIVE,
                payment_auto_release_table: statusEnum.INACTIVE,
                open_time: "00:00",
                close_time: "23:59",
            };
        },
        mapReset: function () {
            appService.modalHide('#branchMap');
        },

        save: function () {
            try {
                const data = {
                    name: this.props.form.name,
                    name_kh: this.props.form.name_kh,
                    name_cn: this.props.form.name_cn,
                    name_en: this.props.form.name_en,
                    code: this.props.form.code,
                    online_order_slug: this.props.form.online_order_slug,
                    telegram_mini_app_slug: this.props.form.telegram_mini_app_slug,
                    email: this.props.form.email,
                    phone: this.props.form.phone,
                    latitude: this.props.form.latitude,
                    longitude: this.props.form.longitude,
                    city: this.props.form.city,
                    state: this.props.form.state,
                    zip_code: this.props.form.zip_code,
                    address: this.props.form.address,
                    status: this.props.form.status,
                    currency_id: this.props.form.currency_id,
                    language_id: this.props.form.language_id,
                    close_business_day_time: this.props.form.close_business_day_time,
                    current_business_day: this.props.form.current_business_day,
                    open_time: this.props.form.open_time,
                    close_time: this.props.form.close_time,
                    show_unpaid_button: this.props.form.show_unpaid_button,
                    change_status_paid_to_unpaid: this.props.form.change_status_paid_to_unpaid,
                    show_delete_order_button: this.props.form.show_delete_order_button,
                    // add option
                    show_select_table: this.props.form.show_select_table,
                    show_select_table_list: this.props.form.show_select_table_list,
                    show_token: this.props.form.show_token,
                    show_delivery: this.props.form.show_delivery,
                    show_waiting_number: this.props.form.show_waiting_number,
                    show_suspense_button: this.props.form.show_suspense_button,
                    show_paid_order_button: this.props.form.show_paid_order_button,
                    show_sidebar_table_list: this.props.form.show_sidebar_table_list,
                    unpaid_order_show_invoice: this.props.form.unpaid_order_show_invoice,
                    show_receive_amount: this.props.form.show_receive_amount,
                    show_select_customer: this.props.form.show_select_customer,
                    show_input_number_of_people: this.props.form.show_input_number_of_people,
                    default_selected_order_type: this.props.form.default_selected_order_type,
                    show_select_member: this.props.form.show_select_member,
                    member_can_redeem_point: this.props.form.member_can_redeem_point,
                    show_online_order_button: this.props.form.show_online_order_button,
                    show_pending_order_button: this.props.form.show_pending_order_button,
                    show_pos_button: this.props.form.show_pos_button,
                    show_retail_pos_button: this.props.form.show_retail_pos_button,
                    show_quick_pos_button: this.props.form.show_quick_pos_button,
                    show_floor_plan: this.props.form.show_floor_plan,
                    show_table_button: this.props.form.show_table_button,
                    show_customer_view_button: this.props.form.show_customer_view_button,
                    show_navbar_button_text: this.props.form.show_navbar_button_text,
                    show_customer_name: this.props.form.show_customer_name,
                    show_customer_phone_number: this.props.form.show_customer_phone_number,
                    show_customer_address: this.props.form.show_customer_address,
                    shop_category_id: this.props.form.shop_category_id,
                    show_btn_print_web: this.props.form.show_btn_print_web,
                    show_btn_print: this.props.form.show_btn_print,
                    show_print_label_button: this.props.form.show_print_label_button,
                    show_discount_button: this.props.form.show_discount_button,
                    create_paid_order_confirm: this.props.form.create_paid_order_confirm,
                    create_unpaid_order_confirm: this.props.form.create_unpaid_order_confirm,
                    create_paid_order_auto_print: this.props.form.create_paid_order_auto_print,
                    create_unpaid_order_auto_print: this.props.form.create_unpaid_order_auto_print,
                    void_order_auto_print: this.props.form.void_order_auto_print,
                    change_item_qty_auto_print: this.props.form.change_item_qty_auto_print,
                    unpaid_print_bill: this.props.form.unpaid_print_bill,
                    unpaid_print_invoice: this.props.form.unpaid_print_invoice,
                    open_table_confirm: this.props.form.open_table_confirm,
                    payment_auto_release_table: this.props.form.payment_auto_release_table,
                    open_time: this.props.form.open_time,
                    close_time: this.props.form.close_time,
                }
                const tempId = this.$store.getters["branch/temp"].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch("branch/save", {
                        form: data,
                    }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.branches")
                    );

                    // Refresh branch data if we have a valid branch ID from the response
                    if (res.data && res.data.data && res.data.data.id) {
                        this.$store.dispatch('backendGlobalState/branchShow', res.data.data.id).then().catch();
                    }

                    this.props.form = {
                        name: "",
                        online_order_slug: "",
                        telegram_mini_app_slug: "",
                        email: "",
                        phone: "",
                        latitude: "",
                        longitude: "",
                        city: "",
                        state: "",
                        zip_code: "",
                        address: "",
                        status: statusEnum.ACTIVE,
                        currency_id:1,
                        language_id:1,
                        close_business_day_time:null,
                        current_business_day:null,
                        show_unpaid_button: statusEnum.ACTIVE,
                        change_status_paid_to_unpaid: statusEnum.ACTIVE,
                        show_delete_order_button: statusEnum.ACTIVE,
                        // add option
                        show_select_table: statusEnum.ACTIVE,
                        show_select_table_list: statusEnum.ACTIVE,
                        show_token: statusEnum.ACTIVE,
                        show_delivery: statusEnum.ACTIVE,
                        show_waiting_number: statusEnum.ACTIVE,
                        show_suspense_button: statusEnum.ACTIVE,
                        show_paid_order_button: statusEnum.ACTIVE,
                        show_sidebar_table_list: statusEnum.ACTIVE,
                        unpaid_order_show_invoice: statusEnum.ACTIVE,
                        show_receive_amount: statusEnum.ACTIVE,
                        show_select_customer: statusEnum.ACTIVE,
                        show_input_number_of_people: statusEnum.ACTIVE,
                        default_selected_order_type: orderTypeEnum.POS,
                        show_select_member: statusEnum.ACTIVE,
                        member_can_redeem_point: statusEnum.ACTIVE,
                        show_online_order_button: statusEnum.ACTIVE,
                        show_pending_order_button: statusEnum.INACTIVE,
                        show_pos_button: statusEnum.ACTIVE,
                        show_retail_pos_button: statusEnum.ACTIVE,
                        show_quick_pos_button: statusEnum.INACTIVE,
                        show_floor_plan: statusEnum.ACTIVE,
                        show_table_button: statusEnum.ACTIVE,
                        show_customer_view_button: statusEnum.ACTIVE,
                        show_navbar_button_text: statusEnum.ACTIVE,
                        show_customer_name: statusEnum.ACTIVE,
                        show_customer_phone_number: statusEnum.ACTIVE,
                        show_customer_address: statusEnum.ACTIVE,
                        shop_category_id:null,
                        show_btn_print_web: statusEnum.INACTIVE,
                        show_btn_print: statusEnum.ACTIVE,
                        show_print_label_button: statusEnum.INACTIVE,
                        show_discount_button: statusEnum.ACTIVE,
                        create_paid_order_confirm: statusEnum.INACTIVE,
                        create_unpaid_order_confirm: statusEnum.INACTIVE,
                        create_paid_order_auto_print: statusEnum.INACTIVE,
                        create_unpaid_order_auto_print: statusEnum.INACTIVE,
                        void_order_auto_print: statusEnum.INACTIVE,
                        change_item_qty_auto_print: statusEnum.INACTIVE,
                    };
                    this.errors = {};
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },

        setDefaultSettings: function () {
            // Set all statusEnum fields to INACTIVE
            this.props.form.status = statusEnum.ACTIVE;
            this.props.form.show_unpaid_button = statusEnum.INACTIVE;
            this.props.form.change_status_paid_to_unpaid = statusEnum.INACTIVE;
            this.props.form.show_delete_order_button = statusEnum.INACTIVE;
            this.props.form.show_select_table = statusEnum.ACTIVE;
            this.props.form.show_select_table_list = statusEnum.ACTIVE;
            this.props.form.show_token = statusEnum.ACTIVE;
            this.props.form.show_delivery = statusEnum.ACTIVE;
            this.props.form.show_waiting_number = statusEnum.ACTIVE;
            this.props.form.show_suspense_button = statusEnum.ACTIVE;
            this.props.form.show_paid_order_button = statusEnum.ACTIVE;
            this.props.form.show_sidebar_table_list = statusEnum.ACTIVE;
            this.props.form.unpaid_order_show_invoice = statusEnum.INACTIVE;
            this.props.form.show_receive_amount = statusEnum.ACTIVE;
            this.props.form.show_select_customer = statusEnum.ACTIVE;
            this.props.form.show_input_number_of_people = statusEnum.ACTIVE;
            this.props.form.show_select_member = statusEnum.INACTIVE;
            this.props.form.member_can_redeem_point = statusEnum.INACTIVE;
            this.props.form.show_online_order_button = statusEnum.ACTIVE;
            this.props.form.show_pending_order_button = statusEnum.ACTIVE;
            this.props.form.show_pos_button = statusEnum.ACTIVE;
            this.props.form.show_retail_pos_button = statusEnum.ACTIVE;
            this.props.form.show_quick_pos_button = statusEnum.INACTIVE;
            this.props.form.show_floor_plan = statusEnum.ACTIVE;
            this.props.form.show_table_button = statusEnum.ACTIVE;
            this.props.form.show_customer_view_button = statusEnum.ACTIVE;
            this.props.form.show_navbar_button_text = statusEnum.ACTIVE;
            this.props.form.show_customer_name = statusEnum.ACTIVE;
            this.props.form.show_customer_phone_number = statusEnum.ACTIVE;
            this.props.form.show_customer_address = statusEnum.ACTIVE;
            this.props.form.show_btn_print_web = statusEnum.ACTIVE;
            this.props.form.show_btn_print = statusEnum.ACTIVE;
            this.props.form.unpaid_print_bill = statusEnum.INACTIVE;
            this.props.form.unpaid_print_invoice = statusEnum.INACTIVE;
            this.props.form.open_table_confirm = statusEnum.INACTIVE;
            this.props.form.payment_auto_release_table = statusEnum.INACTIVE;
        },

        handleDate: function (e) {
            let formattedDatetime = e.replace('T', ' ').replace('Z', '');
            return formattedDatetime;
        },
        formatTime: function(time) {
            const pad = (num) => String(num).padStart(2, '0');
            const getTime = `${pad(time.hours)}:${pad(time.minutes)}:${pad(time.seconds)}`;
            return getTime;
        }
    },
};
</script>
<style>
    .db-field-title{
        text-transform: capitalize;
        font-size: medium;
    }
</style>
