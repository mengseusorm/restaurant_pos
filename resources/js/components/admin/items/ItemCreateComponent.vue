<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />
    <div id="sidebar" class="drawer" data-drawer="#sidebar">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t("menu.items") }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <div class="grid grid-cols-1 sm:grid-cols-3 mb-4 sm:mb-0">
                <button type="button" class="db-tabBtn active" data-tab="#general-information">
                    {{ $t('label.general_information') }}
                </button>
                <button type="button" class="db-tabBtn" data-tab="#pricing-tax">
                    {{ $t('label.price') }}
                </button>
                <button type="button" class="db-tabBtn" data-tab="#item-settings">
                    {{ $t('label.item_settings') }}
                </button>
            </div>

            <form @submit.prevent="save" @keydown.enter.prevent>
                <div class="db-tabDiv active" id="general-information">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="item_code" class="db-field-title">{{ $t("label.item_code") }}</label>
                            <input v-model="props.form.item_code" v-bind:class="errors.item_code ? 'invalid' : ''"
                                type="text" id="item_code" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.item_code">{{ errors.item_code[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="barcode" class="db-field-title">{{ $t("label.barcode") }}</label>
                            <input v-model="props.form.barcode" v-bind:class="errors.barcode ? 'invalid' : ''"
                                type="text" id="barcode" class="db-field-control" @input="handleBarcodeInput">
                            <small class="db-field-alert" v-if="errors.barcode">{{ errors.barcode[0] }}</small>
                        </div>

                        <!-- Basic Information Section -->
                        <div class="form-col-12 sm:form-col-12">
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input v-model="props.form.name_kh" v-bind:class="errors.name_kh ? 'invalid' : ''"
                                type="text" id="name_kh" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input v-model="props.form.name_cn" v-bind:class="errors.name_cn ? 'invalid' : ''"
                                type="text" id="name_cn" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_en" class="db-field-title">{{ $t("label.name_en") }}</label>
                            <input v-model="props.form.name_en" v-bind:class="errors.name_en ? 'invalid' : ''"
                                type="text" id="name_en" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_en">{{ errors.name_en[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="item_category_id" class="db-field-title required">{{ $t("label.category")
                                }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="item_category_id"
                                v-bind:class="errors.item_category_id ? 'invalid' : ''"
                                v-model="props.form.item_category_id" :options="itemCategories" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                            <small class="db-field-alert" v-if="errors.item_category_id">{{ errors.item_category_id[0]
                                }}</small> 
                        </div> 

                        <!-- Images & Description Section -->
                        <div class="form-col-12 sm:form-col-12">
                            <label class="db-field-title">{{ $t("label.image") }}</label>
                            <input @change="changeImage" v-bind:class="errors.image ? 'invalid' : ''" id="image"
                                type="file" class="db-field-control" ref="imageProperty"
                                accept="image/png, image/jpeg, image/jpg">
                            <small class="db-field-alert" v-if="errors.image">{{ errors.image[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="caution" class="db-field-title">{{ $t("label.caution") }}</label>
                            <textarea v-model="props.form.caution" v-bind:class="errors.caution ? 'invalid' : ''"
                                id="caution" rows="2" class="db-field-control"></textarea>
                            <small class="db-field-alert" v-if="errors.caution">{{
                                errors.caution[0]
                                }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="description" class="db-field-title">{{ $t("label.description") }}</label>
                            <textarea v-model="props.form.description"
                                v-bind:class="errors.description ? 'invalid' : ''" id="description"
                                class="db-field-control"></textarea>
                            <small class="db-field-alert" v-if="errors.description">{{
                                errors.description[0]
                                }}</small>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Tax Tab -->
                <div class="db-tabDiv" id="pricing-tax">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <!-- <label for="tax_id" class="db-field-title">{{ $t("label.tax") }} ({{ $t("label.including")}})</label> -->
                            <label for="tax_id" class="db-field-title">{{ $t("label.tax") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="tax_id"
                                v-bind:class="errors.tax_id ? 'invalid' : ''" v-model="props.form.tax_id"
                                :options="[{ id: null, code: '--' }, ...taxes]" label-by="code" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" @update:modelValue="onTaxChange" />
                            <small class="db-field-alert" v-if="errors.tax_id">{{ errors.tax_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="price" class="db-field-title required">{{ $t("label.price") }}</label>
                            <input v-model="props.form.price" v-bind:class="errors.price ? 'invalid' : ''" type="number"
                                id="price" class="db-field-control" step="0.01" min="0" @input="calculateTaxValues">
                            <small class="db-field-alert" v-if="errors.price">{{ errors.price[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tax_amount" class="db-field-title">{{ $t("label.tax_amount") }}</label>
                            <input v-model="props.form.tax_amount" v-bind:class="errors.tax_amount ? 'invalid' : ''"
                                type="number" id="tax_amount" class="db-field-control" step="0.01" readonly>
                            <small class="db-field-alert" v-if="errors.tax_amount">{{ errors.tax_amount[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="price_with_tax" class="db-field-title">{{ $t("label.price_with_tax") }}</label>
                            <input v-model="props.form.price_with_tax"
                                v-bind:class="errors.price_with_tax ? 'invalid' : ''" type="number" id="price_with_tax"
                                class="db-field-control" step="0.01" readonly>
                            <small class="db-field-alert" v-if="errors.price_with_tax">{{ errors.price_with_tax[0]
                                }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tax_rate" class="db-field-title">{{ $t("label.tax_rate") }}</label>
                            <input v-model="props.form.tax_rate" v-bind:class="errors.tax_rate ? 'invalid' : ''"
                                type="number" id="tax_rate" class="db-field-control" step="0.01" readonly>
                            <small class="db-field-alert" v-if="errors.tax_rate">{{ errors.tax_rate[0] }}</small>
                        </div>
                    </div>
                </div>

                <div class="db-tabDiv" id="item-settings">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title" for="item_type">{{ $t("label.item_kind") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.item_kind" id="item_kind_product"
                                                :value="enums.itemKindEnum.PRODUCT" class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="item_kind_product" class="db-field-label">{{ $t('label.item_kind_product') }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.item_kind" id="item_kind_service"
                                                :value="enums.itemKindEnum.SERVICE">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="item_kind_service" class="db-field-label">{{ $t('label.item_kind_service') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="props.form.item_kind === enums.itemKindEnum.SERVICE" class="form-col-12 sm:form-col-6">
                            <label for="duration" class="db-field-title required">{{ $t('label.duration_minutes') }}</label>
                            <input v-model="props.form.duration" :class="errors.duration ? 'invalid' : ''"
                                type="number" id="duration" class="db-field-control" min="1" placeholder="60">
                            <small class="db-field-alert" v-if="errors.duration">{{ errors.duration[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title" for="veg">{{ $t("label.item_type") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.item_type" id="veg"
                                                :value="enums.itemTypeEnum.VEG" class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="veg" class="db-field-label">{{ $t('label.veg') }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.item_type" id="nonVeg"
                                                :value="enums.itemTypeEnum.NON_VEG">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="nonVeg" class="db-field-label">{{ $t('label.non_veg') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title" for="yes">{{ $t("label.is_featured") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.is_featured" id="is_featured_yes"
                                                :value="enums.askEnum.YES" class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_featured_yes" class="db-field-label">{{ $t('label.yes')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.is_featured" id="is_featured_no"
                                                :value="enums.askEnum.NO">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_featured_no" class="db-field-label">{{ $t('label.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.status") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.status" id="status_active"
                                                :value="enums.statusEnum.ACTIVE" class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="status_active" class="db-field-label">{{ $t('label.active')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field" v-model="props.form.status"
                                                id="status_inactive" :value="enums.statusEnum.INACTIVE">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="status_inactive" class="db-field-label">{{ $t('label.inactive')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ================ manage stock=============== -->
                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.stock_management") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.stockManagement"
                                                id="stock_management_active" :value="enums.stockManagementEnum.YES"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="stock_management_active" class="db-field-label">{{ $t('label.yes')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.stockManagement" id="stock_management_inactive"
                                                :value="enums.stockManagementEnum.NO">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="stock_management_inactive" class="db-field-label">{{ $t('label.no')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.is_print_menu") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.is_print_menu"
                                                id="is_print_menu_yes" :value="enums.askEnum.YES"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_print_menu_yes" class="db-field-label">{{ $t('label.yes')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.is_print_menu" id="is_print_menu_no"
                                                :value="enums.askEnum.NO">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_print_menu_no" class="db-field-label">{{ $t('label.no')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="kitchen_printer_id" class="db-field-title">{{ $t("label.kitchen_printer")
                                }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="kitchen_printer_id"
                                v-bind:class="errors.kitchen_printer_id ? 'invalid' : ''"
                                v-model="props.form.kitchen_printer_id"
                                :options="[{ name: 'Choose', id: null }, ...menuPrinters]" label-by="name" value-by="id"
                                :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
                            <small class="db-field-alert" v-if="errors.kitchen_printer_id">{{
                                errors.kitchen_printer_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.is_print_label") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.is_print_label"
                                                id="is_print_label_yes" :value="enums.askEnum.YES"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_print_label_yes" class="db-field-label">{{ $t('label.yes')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.is_print_label" id="is_print_label_no"
                                                :value="enums.askEnum.NO">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="is_print_label_no" class="db-field-label">{{ $t('label.no')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="label_printer_id" class="db-field-title">{{ $t("label.label_printer") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="label_printer_id"
                                v-bind:class="errors.label_printer_id ? 'invalid' : ''"
                                v-model="props.form.label_printer_id"
                                :options="(labelPrinters && labelPrinters.length > 0) ? [{ name: 'Choose', id: null }, ...labelPrinters] : [{ name: 'Choose', id: null }]"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />

                            <small class="db-field-alert" v-if="errors.label_printer_id">{{ errors.label_printer_id[0]
                                }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.can_input_custom_name") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.can_input_custom_name"
                                                id="can_input_custom_name_yes" :value="enums.statusEnum.ACTIVE"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="can_input_custom_name_yes" class="db-field-label">{{ $t('label.yes')
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.can_input_custom_name" id="can_input_custom_name_no"
                                                :value="enums.statusEnum.INACTIVE">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="can_input_custom_name_no" class="db-field-label">{{ $t('label.no')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                                <label class="db-field-title">{{ $t("label.can_input_custom_unit_price") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" v-model="props.form.can_input_custom_unit_price"
                                                id="can_input_custom_unit_price_yes" :value="enums.statusEnum.ACTIVE"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="can_input_custom_unit_price_yes" class="db-field-label">{{
                                            $t('label.yes') }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input type="radio" class="custom-radio-field"
                                                v-model="props.form.can_input_custom_unit_price"
                                                id="can_input_custom_unit_price_no" :value="enums.statusEnum.INACTIVE">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="can_input_custom_unit_price_no" class="db-field-label">{{
                                            $t('label.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Buttons (Always Visible) -->
                <div class="col-12">
                    <div class="flex flex-wrap gap-3 mt-4">
                        <button type="submit" class="db-btn py-2 text-white bg-primary">
                            <i class="lab lab-save"></i>
                            <span>{{ $t("label.save") }}</span>
                        </button>
                        <button type="button" class="modal-btn-outline modal-close" @click="reset">
                            <i class="lab lab-close"></i>
                            <span>{{ $t("button.close") }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import itemTypeEnum from "../../../enums/modules/itemTypeEnum";
import itemKindEnum from "../../../enums/modules/itemKindEnum";
import askEnum from "../../../enums/modules/askEnum";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import stockManagementEnum from "../../../enums/modules/stockManagementEnum";
import taxTypeEnum from "../../../enums/modules/taxTypeEnum";
import printerTypeEnum from "../../../enums/modules/printerTypeEnum"; 
export default {
    name: "ItemCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                stockManagementEnum: stockManagementEnum,
                stockManagementEnumArray: {
                    [stockManagementEnum.YES]: this.$t("label.yes"),
                    [stockManagementEnum.NO]: this.$t("label.no")
                },
                statusEnum: statusEnum,
                itemTypeEnum: itemTypeEnum,
                itemKindEnum: itemKindEnum,
                askEnum: askEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                },
                itemTypeEnumArray: {
                    [itemTypeEnum.VEG]: this.$t("label.veg"),
                    [itemTypeEnum.NON_VEG]: this.$t("label.non_veg")
                },
                askEnumArray: {
                    [askEnum.YES]: this.$t("label.yes"),
                    [askEnum.NO]: this.$t("label.no")
                }
            },
            image: "",
            errors: {},
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_item') };
        },
        itemCategories: function () {
            return this.$store.getters['itemCategory/lists'];
        },
        taxes: function () {
            return this.$store.getters['tax/lists'];
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        menuPrinters: function () {
            const printers = this.$store.getters['printer/lists'];
            return printers
            // return printers.filter(printer => printer.printer_type === printerTypeEnum.PRINTMENU);
        },
        labelPrinters: function () {
            const printers = this.$store.getters['printer/lists'] || [];
            return printers.filter(printer => printer.printer_type === printerTypeEnum.PRINTLABEL);
        },
        branches: function () {
            return this.$store.getters["branch/lists"];
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() {
        this.loading.isActive = true;

        // Get defaultAccess first
        this.$store.dispatch("defaultAccess/show").then(() => {
            const defaultAccess = this.$store.getters["defaultAccess/show"];
            if (defaultAccess && defaultAccess.branch_id) {
                this.props.form.branch_id = defaultAccess.branch_id;
            }
        });

        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
        this.$store.dispatch("role/lists", {
            order_column: "id",
            order_type: "asc",
            excepts: "1|2|3|4|5",
        });
        this.loading.isActive = true;
        this.$store.dispatch('itemCategory/lists', {
            order_column: 'sort',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.$store.dispatch('tax/lists', {
            order_column: 'id',
            order_type: 'asc'
        });
        this.$store.dispatch('printer/lists', {
            order_column: 'id',
        });
        this.loading.isActive = false;

        // Calculate initial tax values and validate printer IDs
        this.$nextTick(() => {
            this.calculateTaxValues();
            this.validatePrinterIds();
        });
    },
    watch: {
        defaultAccess: {
            immediate: true,
            handler: function (newVal) {
                if (newVal && newVal.branch_id) {
                    this.props.form.branch_id = newVal.branch_id;
                }
            }
        },
        'props.form.price': function (newPrice) {
            this.calculateTaxValues();
        },
        'props.form.tax_id': function (newTaxId) {
            this.calculateTaxValues();
        },
        labelPrinters: function (newLabelPrinters) {
            // If label_printer_id has a value but is not in the labelPrinters list, set it to null
            if (this.props.form.label_printer_id !== null && this.props.form.label_printer_id !== undefined) {
                const printerExists = newLabelPrinters.some(printer => printer.id === this.props.form.label_printer_id);
                if (!printerExists) {
                    this.props.form.label_printer_id = null;
                }
            }
        },
        menuPrinters: function (newMenuPrinters) {
            // If kitchen_printer_id has a value but is not in the menuPrinters list, set it to null
            if (this.props.form.kitchen_printer_id !== null && this.props.form.kitchen_printer_id !== undefined) {
                const printerExists = newMenuPrinters.some(printer => printer.id === this.props.form.kitchen_printer_id);
                if (!printerExists) {
                    this.props.form.kitchen_printer_id = null;
                }
            }
        }
    },
    methods: {
        calculateTaxValues: function () {
            const price = parseFloat(this.props.form.price) || 0;
            const tax = this.taxes.find(t => t.id === this.props.form.tax_id);

            if (tax) {
                this.props.form.tax_name = tax.code;
                this.props.form.tax_rate = parseFloat(tax.tax_rate);
                this.props.form.tax_type = tax.type;

                const taxAmount = tax.type === taxTypeEnum.FIXED ?
                    parseFloat(tax.tax_rate) :
                    (price * parseFloat(tax.tax_rate)) / 100;

                this.props.form.tax_amount = parseFloat(taxAmount.toFixed(2));
                this.props.form.price_with_tax = parseFloat((price + taxAmount).toFixed(2));
            } else {
                // When no tax is selected, show 0 values but calculated based on current price
                this.props.form.tax_name = '';
                this.props.form.tax_rate = 0;
                this.props.form.tax_type = 0;
                this.props.form.tax_amount = 0;
                this.props.form.price_with_tax = price;
            }
        },
        onTaxChange: function (value) {
            this.calculateTaxValues();
        },
        validatePrinterIds: function () {
            // Validate label_printer_id
            if (this.props.form.label_printer_id !== null && this.props.form.label_printer_id !== undefined) {
                const labelPrinterExists = this.labelPrinters.some(printer => printer.id === this.props.form.label_printer_id);
                if (!labelPrinterExists) {
                    this.props.form.label_printer_id = null;
                }
            }

            // Validate kitchen_printer_id
            if (this.props.form.kitchen_printer_id !== null && this.props.form.kitchen_printer_id !== undefined) {
                const menuPrinterExists = this.menuPrinters.some(printer => printer.id === this.props.form.kitchen_printer_id);
                if (!menuPrinterExists) {
                    this.props.form.kitchen_printer_id = null;
                }
            }
        },
        handleBarcodeInput: function (e) {
            const value = e.target.value;
            // Auto set to null when empty, undefined, or contains only whitespace
            if (!value || value.trim() === '') {
                this.props.form.barcode = null;
            } else {
                this.props.form.barcode = value.trim(); // Also trim whitespace from valid values
            }
        },
        changeImage: function (e) {
            this.image = e.target.files[0];
        },
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch('item/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                name_kh: "",
                name_cn: "",
                name_en: "",
                item_code: "",
                price: "",

                tax_name: 0,
                tax_rate: 0,
                tax_type: 0,
                tax_amount: 0,
                price_with_tax: 0,

                description: "",
                caution: "",
                is_featured: askEnum.YES,
                item_type: itemTypeEnum.VEG,
                item_category_id: null,
                tax_id: null,
                status: statusEnum.ACTIVE,
                kitchen_printer_id: null,
                label_printer_id: null,
                branch_id: null,
                barcode: null,
                stockManagement: stockManagementEnum.NO,
                is_print_menu: askEnum.YES,
                is_print_label: askEnum.YES,
                can_input_custom_name: statusEnum.INACTIVE,
                can_input_custom_unit_price: statusEnum.INACTIVE,
                item_kind: itemKindEnum.PRODUCT,
                duration: null,
            };
            if (this.image) {
                this.image = "";
                this.$refs.imageProperty.value = null;
            }

            // Calculate tax values after reset
            this.$nextTick(() => {
                this.calculateTaxValues();
            });
        },

        save: function () {
            try {

                // Auto set barcode to null when empty or contains only whitespace
                if (this.props.form.barcode === 'null' || !this.props.form.barcode || (typeof this.props.form.barcode === 'string' && this.props.form.barcode.trim() === '')) {
                    this.props.form.barcode = null;
                }

                const fd = new FormData();
                fd.append('name', this.props.form.name);
                fd.append('name_kh', this.props.form.name_kh == null ? '' : this.props.form.name_kh);
                fd.append('name_cn', this.props.form.name_cn == null ? '' : this.props.form.name_cn);
                fd.append('name_en', this.props.form.name_en == null ? '' : this.props.form.name_en);
                fd.append('item_code', this.props.form.item_code == null ? '' : this.props.form.item_code);
                fd.append('price', this.props.form.price);

                fd.append('tax_name', this.props.form.tax_name);
                fd.append('tax_rate', this.props.form.tax_rate);
                fd.append('tax_type', this.props.form.tax_type);
                fd.append('tax_amount', this.props.form.tax_amount);
                fd.append('price_with_tax', this.props.form.price_with_tax);

                fd.append('item_category_id', this.props.form.item_category_id == null ? '' : this.props.form.item_category_id);
                fd.append('tax_id', this.props.form.tax_id == null ? '' : this.props.form.tax_id);
                fd.append('item_type', this.props.form.item_type);
                fd.append('is_featured', this.props.form.is_featured);
                fd.append('description', this.props.form.description);
                fd.append('caution', this.props.form.caution);
                fd.append('order', 1);
                fd.append('status', this.props.form.status);
                fd.append('kitchen_printer_id', this.props.form.kitchen_printer_id == null ? '' : this.props.form.kitchen_printer_id);
                fd.append('label_printer_id', this.props.form.label_printer_id == null ? '' : this.props.form.label_printer_id);
                // Get branch_id from defaultAccess if not set
                const defaultAccess = this.$store.getters["defaultAccess/show"];
                const branchId = this.props.form.branch_id || (defaultAccess ? defaultAccess.branch_id : null);
                fd.append('branch_id', branchId);
                fd.append('barcode', this.props.form.barcode == null ? '' : this.props.form.barcode);
                fd.append('manage_stock', this.props.form.stockManagement);
                fd.append('is_print_menu', this.props.form.is_print_menu);
                fd.append('is_print_label', this.props.form.is_print_label);
                fd.append('can_input_custom_name', this.props.form.can_input_custom_name);
                fd.append('can_input_custom_unit_price', this.props.form.can_input_custom_unit_price);
                fd.append('item_kind', this.props.form.item_kind ?? 1);
                fd.append('duration', this.props.form.duration == null ? '' : this.props.form.duration);


                if (this.image) {
                    fd.append('image', this.image);
                }

                const tempId = this.$store.getters['item/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('item/save', {
                    form: fd,
                    search: this.props.search
                }).then((res) => {
                    appService.sideDrawerHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.items'));
                    this.props.form = {
                        name: "",
                        name_kh: "",
                        name_cn: "",
                        name_en: "",
                        item_code: "",
                        price: "",
                        description: "",
                        caution: "",
                        is_featured: askEnum.YES,
                        item_type: itemTypeEnum.VEG,
                        item_category_id: null,
                        tax_id: null,
                        status: statusEnum.ACTIVE,
                        kitchen_printer_id: null,
                        label_printer_id: null,
                        branch_id: null,
                        barcode: null,
                        stockManagement: stockManagementEnum.NO,
                        is_print_menu: askEnum.YES,
                        is_print_label: askEnum.YES,
                        can_input_custom_name: statusEnum.INACTIVE,
                        can_input_custom_unit_price: statusEnum.INACTIVE,
                        item_kind: itemKindEnum.PRODUCT,
                        duration: null,

                        tax_name: 0,
                        tax_rate: 0,
                        tax_type: 0,
                        tax_amount: 0,
                        price_with_tax: 0,

                    };
                    this.image = "";
                    this.errors = {};
                    this.$refs.imageProperty.value = null;

                    this.loading.isActive = true;
                    this.$store.dispatch('item/lists', this.props.search).then(res => {
                        this.loading.isActive = false;
                    }).catch((err) => {
                        this.loading.isActive = false;
                    });

                    // Calculate tax values after save
                    this.$nextTick(() => {
                        this.calculateTaxValues();
                    });
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = {};
                    if (err.response && err.response.data && err.response.data.errors) {
                        this.errors = err.response.data.errors;
                    } else {
                        alertService.error(err.response.data.message);
                    }
                })
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err)
            }
        }
    }
}
</script>
