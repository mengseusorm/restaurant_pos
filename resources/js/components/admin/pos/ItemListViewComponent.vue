<template>
    <!-- Professional List View for POS Items -->
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-8 md:mb-0 overflow-hidden flex flex-col h-full relative">
        <!-- Loading Overlay -->
        <div v-if="loading" class="absolute inset-0 loading-overlay flex items-center justify-center z-50 rounded-lg">
            <div class="flex flex-col items-center bg-white px-6 py-8 rounded-lg shadow-lg border">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4"></div>
                <p class="text-sm text-gray-600 font-medium">{{ $t('message.loading') || 'Loading items...' }}</p>
            </div>
        </div>
        
        <!-- Fixed Table Header -->
        <div class="flex-shrink-0 w-full bg-gray-50 border-b border-gray-200">
            <table class="w-full table-fixed">
                <thead>
                    <tr>
                        <th class="w-2/5 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $t('label.item') || 'Item' }}
                        </th>
                        <th class="w-1/5 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            {{ $t('label.code') || 'Code' }}
                        </th>
                        <th class="w-1/5 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $t('label.price') || 'Price' }}
                        </th>
                        <th class="w-1/5 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $t('label.action') || 'Action' }}
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
        
        <!-- Scrollable Table Body Container -->
        <div class="flex-1 overflow-y-auto pb-4">
            <table class="w-full table-fixed">
                <!-- Table Body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in sortedItems" :key="item.id" 
                        class="hover:bg-gray-50 transition-colors duration-200">
                        
                        <!-- Item Info (Image + Name + Description) -->
                        <td class="w-2/5 px-4 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:shadow-md transition-shadow duration-200"
                                     @click="order(item)">
                                    <img :src="item.thumb" 
                                         :alt="item.name"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 cursor-pointer hover:text-primary transition-colors duration-200 truncate"
                                        @click="order(item)">
                                        {{ item.name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1 truncate" v-if="item.description">
                                        {{ textShortener(item.description, 50) }}
                                    </p>
                                    <!-- Stock Status Indicator -->
                                    <div class="flex items-center mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                                            {{ $t('label.available') || 'Available' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Barcode (Hidden on mobile) -->
                        <td class="w-1/5 px-4 py-4 text-center hidden lg:table-cell">
                            <span v-if="item.barcode && item.barcode != 'null'" 
                                  class="inline-flex items-center px-2 py-1 bg-gray-100 rounded-md text-xs font-mono text-gray-600 truncate max-w-full">
                                <i class="fa-solid fa-barcode mr-1 flex-shrink-0"></i>
                                <span class="truncate">{{ item.barcode }}</span>
                            </span>
                            <span v-else class="text-xs text-gray-400">-</span>
                        </td>
                        
                        <!-- Price -->
                        <td class="w-1/5 px-4 py-4 text-right">
                            <div v-if="item.offer.length > 0" class="space-y-1">
                                <div class="text-sm font-bold text-green-600 truncate">
                                    {{ item.offer[0].currency_price }}
                                </div>
                                <div class="text-xs text-gray-400 line-through truncate">
                                    {{ item.branch_currency_price }}
                                </div>
                                <div class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $t('label.sale') || 'Sale' }}
                                </div>
                            </div>
                            <div v-else class="text-sm font-bold text-gray-900 truncate">
                                {{ item.branch_currency_price }}
                            </div>
                        </td>
                        
                        <!-- Actions -->
                        <td class="w-1/5 px-4 py-4 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <!-- Quick Add Button (if no variations) -->
                                <button v-if="!item.itemAttributes || item.itemAttributes.length === 0"
                                        @click="order(item)"
                                        class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1">
                                    <i class="fa-solid fa-plus mr-1"></i>
                                    {{ $t('button.add') || 'Add' }}
                                </button>
                                
                                <!-- Customize Button (if has variations) -->
                                <button v-else
                                        @click="variationModalShow(item)"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-1">
                                    <i class="fa-solid fa-cog mr-1"></i>
                                    <span class="hidden sm:inline">{{ $t('button.choose') || 'Choose' }}</span>
                                    <span class="sm:hidden">{{ $t('button.add') || 'Add' }}</span>
                                </button>
                                
                                <!-- Mobile Barcode (shown only on mobile as a small badge) -->
                                <div class="lg:hidden ml-1" v-if="item.barcode && item.barcode != 'null'">
                                    <span class="inline-flex items-center px-1 py-0.5 bg-gray-100 rounded text-xs font-mono text-gray-600 max-w-16 truncate">
                                        {{ textShortener(item.barcode, 6) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="!sortedItems || sortedItems.length === 0" class="absolute inset-0 flex items-center justify-center bg-white">
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-box-open text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $t('message.no_items_found') || 'No items found' }}</h3>
                <p class="text-gray-500">{{ $t('message.try_different_category') || 'Try browsing a different category' }}</p>
            </div>
        </div>
    </div>
    <!--========INFO PART START=========-->
    <div id="item-info-modal" ref="itemInfoModal" class="modal ff-modal info-modal">
        <div class="modal-dialog" v-if="itemInfo">
            <div class="modal-header">
                <h3 class="modal-title text-base font-medium">{{ itemInfo.name }}</h3>
                <button class="modal-close fa-regular fa-circle-xmark" @click.prevent="infoModalHide"></button>
            </div>
            <div class="modal-body">
                {{ itemInfo.caution }}
            </div>
        </div>
    </div>
    <!--========INFO PART END===========-->

    <!--========VARIATION PART START=========-->
    <div id="item-variation-modal" ref="itemVariationModal" class="modal ff-modal">
        <div class="modal-dialog max-w-[647px]" v-if="item">
            <div class="modal-header items-start border-none pb-0">
                <div class="flex gap-4">
                    <img class="flex-shrink-0 w-[72px] h-[72px] object-cover rounded-lg" :src="item.thumb"
                        alt="thumbnail">
                    <div class="flex-auto">
                        <div class="flex items-start gap-2 mb-1">
                            <h3 class="text-sm font-semibold capitalize">{{ item.name }}</h3>
                            <button type="button" class="info-btn mt-0.5" data-modal="#item-info-modal"
                                @click.prevent="infoModalShow(item.name, item.caution)">
                                <i class="lab lab-information font-fill-paragraph transition lab-font-size-16"></i>
                            </button>
                        </div>
                        <p class="text-xs mb-2">{{ item.description }}</p>
                        <!-- <h4 class="text-sm font-semibold">{{ item.offer.length > 0 ? item.offer[0].currency_price : item.currency_price }}</h4> -->
                        <h4 class="text-sm font-semibold">{{ item.offer.length > 0 ? item.offer[0].branch_currency_price
                            : item.branch_currency_price }}</h4>
                        <!-- <h4 class="text-sm font-semibold">{{ item.offer.length > 0 ? item.offer[0].currency_price : item.flat_price }} {{ branch.currency_id ? branch.currency_id.symbol : ''}}</h4> -->
                    </div>
                </div>
                <button class="modal-close lab-close-circle-line font-fill-danger lab-font-size-24"
                    @click.prevent="variationModalHide"></button>
            </div>
            <div class="modal-body">
                <div class="flex items-center gap-2 mb-4">
                    <h3 class="text-sm leading-6 font-medium first-letter:uppercase text-heading">
                        {{ $t('label.quantity') }}:</h3>
                    <div class="flex items-center indec-group py-1 px-2 rounded-xl bg-[#F7F7FC]">
                        <button @click.prevent="quantityDecrement"
                            class="fa-solid fa-minus text-[10px] w-[18px] h-[18px] leading-4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"></button>
                        <input type="number" v-on:keypress="onlyNumber($event)" v-on:keyup="quantityUp"
                            v-model="temp.quantity"
                            class="text-center w-7 text-xs font-semibold text-heading indec-value">
                        <button @click.prevent="quantityIncrement"
                            class="fa-solid fa-plus text-[10px] w-[18px] h-[18px] leading4 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                    </div>
                </div>
                <div class="mb-4" v-if="item.itemAttributes.length > 1">
                    <div class="row">
                        <div v-for="itemAttribute in item.itemAttributes" class="col-12 sm:col-6">
                            <label class="text-sm leading-6 block font-medium capitalize mb-1.5 text-heading">
                                {{ itemAttribute.name }}
                            </label>
                            <div class="relative">
                                <i
                                    class="lab lab-arrow-down text-sm absolute top-1/2 right-2.5 -translate-y-1/2 lab-font-size-16"></i>
                                <select
                                    @change.prevent="changeVariationAdjust(itemAttribute.id, temp.item_variations.variations[itemAttribute.id])"
                                    v-model="temp.item_variations.variations[itemAttribute.id]"
                                    class="text-xs capitalize rounded-lg h-10 w-full py-1.5 px-2.5 appearance-none transition border border-[#EFF0F6] text-heading hover:border-primary/30">
                                    <option :value="variation.id" v-for="variation in item.variations[itemAttribute.id]"
                                        :key="variation">{{ variation.name }} +{{ variation.currency_price }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4" v-else-if="item.itemAttributes.length > 0">
                    <h3 class="text-sm leading-6 font-medium capitalize mb-2 text-heading">
                        {{ item.itemAttributes[0].name }}
                    </h3>
                    <div class="swiper size-swiper">
                        <div class="size-tabs">
                            <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16">
                                <SwiperSlide class="!w-fit"
                                    v-for="variation in item.variations[item.itemAttributes[0].id]" :key="variation">
                                    <label
                                        :class="temp.item_variations.variations[variation.item_attribute_id] === variation.id ? 'active' : ''"
                                        :for="variation.item_attribute_id + '-' + variation.name"
                                        class="variation-margin-right w-full h-[52px] cursor-pointer py-2 px-3 gap-2 rounded-lg flex items-center border transition border-[#F7F7FC] bg-[#F7F7FC]">
                                        <div class="custom-radio sm">
                                            <input :value="variation.id"
                                                @click="changeVariation(variation.item_attribute_id, variation.id, variation.name, variation.convert_price)"
                                                v-model="temp.item_variations.variations[variation.item_attribute_id]"
                                                type="radio" :id="variation.item_attribute_id + '-' + variation.name"
                                                class="custom-radio-field">
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <div>
                                            <h3 class="block capitalize text-xs text-heading">
                                                {{ textShortener(variation.name, 15) }}</h3>
                                            <h4 v-if="variation.price > 0"
                                                class="block text-xs font-medium text-heading">
                                                +{{ variation.currency_price }}
                                            </h4>
                                        </div>
                                    </label>
                                </SwiperSlide>
                            </Swiper>
                        </div>
                    </div>
                </div>
                <div class="mb-4" v-if="item.extras.length > 0">
                    <h3 class="text-sm leading-6 font-medium capitalize mb-2 text-heading">{{ $t('label.extras') }}</h3>
                    <div class="extra-swiper">
                        <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16">
                            <SwiperSlide v-for="extra in item.extras" :key="extra" class="!w-fit !relative">
                                <label :for="extra.id + extra.name"
                                    class="extra w-full h-[52px] cursor-pointer py-2 px-3 gap-3 rounded-lg flex items-center border transition border-[#F7F7FC] bg-[#F7F7FC]">
                                    <div class="custom-checkbox w-3 h-3">
                                        <input :id="extra.id + extra.name"
                                            @change.prevent="changeExtra($event, extra.id, extra.name)"
                                            :value="extra.id" type="checkbox" class="custom-checkbox-field">
                                        <i
                                            class="fa-solid fa-check custom-checkbox-icon leading-[9px] text-[9px] rounded-[3px]"></i>
                                    </div>
                                    <div>
                                        <h3 class="block capitalize mb-1 text-xs text-heading">
                                            {{ textShortener(extra.name, 15) }}</h3>
                                        <h4 class="block text-xs font-medium text-heading">+{{
                                            extra.currency_price
                                            }}</h4>
                                    </div>
                                </label>
                            </SwiperSlide>
                        </Swiper>
                    </div>
                </div>

                <div class="mb-5" v-if="item.addons.length > 0">
                    <h3 class="text-sm leading-6 font-medium capitalize mb-2 text-heading">{{ $t('label.addons') }}</h3>
                    <div class="swiper addon-swiper">
                        <Swiper :speed="1000" slidesPerView="auto" :spaceBetween="16">
                            <SwiperSlide v-for="addon in item.addons" :key="addon">
                                <div class="!w-fit !relative">
                                    <div @click.prevent="changeAddon(addon)"
                                        class="addon cursor-pointer w-fit min-w-[200px] h-[70px] rounded-lg flex border border-[#EFF0F6]">
                                        <img class="w-[68px] h-full object-cover ltr:rounded-l-lg rtl:rounded-r-lg flex-shrink-0"
                                            :src="addon.thumb" alt="thumbnail">
                                        <div class="ltr:rounded-r-lg rtl:rounded-l-lg w-full py-1 px-2">
                                            <span
                                                class="block text-xs text-ellipsis whitespace-nowrap overflow-hidden w-fit max-w-[100px] capitalize text-heading">
                                                {{ addon.addon_item_name }}
                                            </span>
                                            <p v-if="addon.variation_names.length > 0"
                                                class=" text-left text-[10px] leading-4 capitalize mb-1.5 cursor-pointer">
                                                <span v-for="variation in addon.variation_names">
                                                    {{ textShortener(variation.name, 8) }}, &nbsp;
                                                </span>
                                            </p>
                                            <span
                                                class="block text-xs font-semibold text-heading ltr:text-left rtl:text-right">
                                                {{ addon.total_currency_price }}
                                            </span>
                                        </div>
                                    </div>
                                    <div
                                        class="flex flex-col items-end justify-between h-full absolute top-0 ltr:right-0 rtl:left-0 z-10 p-2">
                                        <button type="button" class="info-btn" data-modal="#item-info-modal"
                                            @click.prevent="infoModalShow(addon.addon_item_name, addon.caution)">
                                            <i
                                                class="lab lab-information font-fill-paragraph transition lab-font-size-16"></i>
                                        </button>

                                        <div class="flex items-center indec-group">
                                            <button @click.prevent="addonQuantityDecrement(addon.id)"
                                                class="fa-solid fa-minus text-[8px] w-4 h-4 leading-3 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-minus"></button>
                                            <input v-on:keypress="onlyNumber($event)"
                                                v-on:keyup="addonQuantityUp(addon.id)" v-model="addonQuantity[addon.id]"
                                                type="number"
                                                class="text-center w-5 text-xs font-semibold text-heading indec-value">
                                            <button @click.prevent="addonQuantityIncrement(addon.id)"
                                                class="fa-solid fa-plus text-[8px] w-4 h-4 leading-3 text-center rounded-full border transition text-primary border-primary hover:bg-primary hover:text-white indec-plus"></button>
                                        </div>
                                    </div>
                                </div>
                            </SwiperSlide>
                        </Swiper>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-xs leading-6 font-medium capitalize mb-2 text-heading">
                        {{ $t('label.special_instructions') }}
                    </h3>
                    <textarea v-model="temp.instruction" :placeholder="$t('message.add_note')"
                        class="h-12 w-full rounded-lg border py-1.5 px-2 placeholder:text-[10px] placeholder:text-[#6E7191] border-[#D9DBE9]"></textarea>
                </div>
                <button type="button" :disabled="temp.total_price <= 0" @click.prevent="addToCart"
                    class="flex items-center justify-center gap-3 rounded-3xl text-base py-3 px-3 font-medium w-full text-white bg-primary">
                    <i class="icon-bag-2"></i>
                    <span>
                        {{ $t('button.add_to_cart') }} -
                        <!-- {{
                            currencyFormat(temp.total_price, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position)
                        }} -->
                        {{
                        currencyFormat(temp.total_price, setting.site_digit_after_decimal_point,
                        branch.currency_id?.symbol, setting.site_currency_position)
                        }}
                    </span>
                </button>
            </div>
        </div>
    </div>
    <!--========VARIATION PART END===========-->
</template>

<script>
import appService from "../../../services/appService";
import _ from "lodash";
import alertService from "../../../services/alertService";
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import { currency } from "../../../store/modules/currency";
export default {
    name: "ItemListViewComponent",
    components: {
        Swiper,
        SwiperSlide,
    },
    props: {
        items: Object,
        loading: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return { 
            item: null,
            itemInfo: null,
            addons: {},
            addonQuantity: {},
            itemArrays: [],
            sortBy: 'name',
            sortOrder: 'asc',
            settings: {
                itemsToShow: 4.3,
                wrapAround: false,
                snapAlign: "start"
            },
            addonSettings: {
                itemsToShow: 3,
                wrapAround: false,
                snapAlign: "start"
            },
            temp: {
                name: "",
                image: "",
                item_id: 0,
                quantity: 0,
                discount: 0,
                currency_price: 0,
                convert_price: 0,
                item_variations: {
                    variations: {},
                    names: {}
                },
                item_extras: {
                    extras: [],
                    names: []
                },
                item_variation_total: 0,
                item_extra_total: 0,
                total_price: 0,
                instruction: "",

                price_with_tax: 0,
                tax_amount: 0,
            },
        }
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        sortedItems: function () {
            if (!this.items || !Array.isArray(this.items)) {
                return [];
            }
            
            const sorted = [...this.items].sort((a, b) => {
                let aValue, bValue;
                
                switch (this.sortBy) {
                    case 'name':
                        aValue = a.name?.toLowerCase() || '';
                        bValue = b.name?.toLowerCase() || '';
                        break;
                    case 'price':
                        aValue = a.offer?.length > 0 ? a.offer[0].convert_price : a.convert_price;
                        bValue = b.offer?.length > 0 ? b.offer[0].convert_price : b.convert_price;
                        break;
                    default:
                        return 0;
                }
                
                if (aValue < bValue) return this.sortOrder === 'asc' ? -1 : 1;
                if (aValue > bValue) return this.sortOrder === 'asc' ? 1 : -1;
                return 0;
            });
            
            return sorted;
        }
    }, 
    methods: { 
        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        textShortener: function (text, number) {
            return appService.textShortener(text, number);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        infoModalShow: function (name, caution) {
            this.itemInfo = {
                name: name,
                caution: caution
            };
            const modalTarget = this.$refs.itemInfoModal;
            modalTarget?.classList?.add("active");
            document.body.style.overflowY = "hidden";
        },
        infoModalHide: function () {
            this.itemInfo = null;
            const modalDiv = this.$refs.itemInfoModal;
            modalDiv?.classList?.remove("active");
            document.body.style.overflowY = "auto";
        },
        variationModalShow: function (item) {  
            this.item = item; 
            if (this.item.itemAttributes.length > 0) {
                _.forEach(this.item.itemAttributes, (element) => {
                    if (typeof this.item.variations[element.id][0] !== "undefined") {
                        this.temp.item_variations.variations[this.item.variations[element.id][0].item_attribute_id] = this.item.variations[element.id][0].id;
                        this.temp.item_variations.names[element.name] = this.item.variations[element.id][0].name;
                        this.temp.item_variation_total += this.item.variations[element.id][0].convert_price;
                    }
                });
            }

            if (this.item.addons.length > 0) {
                _.forEach(this.item.addons, (addon) => {
                    this.addonQuantity[addon.id] = 1;
                });
            }

            this.temp.name = this.item.name;
            this.temp.image = this.item.thumb;
            this.temp.item_id = this.item.id;
            this.temp.quantity = 1;
            this.temp.discount = 0;
            this.temp.convert_price = item.offer.length > 0 ? item.offer[0].convert_price : item.convert_price;
            this.temp.currency_price = item.offer.length > 0 ? item.offer[0].currency_price : item.currency_price;
            this.temp.total_price = (item.offer.length > 0 ? item.offer[0].convert_price : item.convert_price) + this.temp.item_variation_total;

            const taxRate = item.tax_rate ? item.tax_rate / 100 : 0;
            this.temp.price_with_tax = parseFloat(this.temp.total_price * (1 + taxRate));
            this.temp.tax_amount = parseFloat(this.temp.total_price * taxRate);

            const modalTarget = this.$refs.itemVariationModal;
            modalTarget?.classList?.add("active");
            document.body.style.overflowY = "hidden";  
        },
        variationModalHide: function () {
            this.item = null;

            this.temp.name = "";
            this.temp.image = "";
            this.temp.item_id = 0;
            this.temp.quantity = 0;
            this.temp.discount = 0;
            this.temp.currency_price = 0;
            this.temp.convert_price = 0;
            this.temp.item_variations = {
                variations: {},
                names: {}
            };
            this.temp.item_extras = {
                extras: [],
                names: []
            };
            this.temp.item_variation_total = 0;
            this.temp.item_extra_total = 0;
            this.temp.total_price = 0;
            this.temp.instruction = "";

            this.temp.price_with_tax = 0;
            this.temp.tax_amount = 0;

            const modalDiv = this.$refs.itemVariationModal;
            modalDiv?.classList?.remove("active");
            document.body.style.overflowY = "auto";
        },
        changeVariation: function (attributeId, variationId, variationName, variationPrice) {
            this.temp.item_variations.variations[attributeId] = variationId;
            _.forEach(this.item.itemAttributes, (element) => {
                if (element.id === attributeId) {
                    this.temp.item_variations.names[element.name] = variationName;
                }
            });
            this.totalPriceSetup();
        },
        changeVariationAdjust: function (attributeId, variationId) {
            _.forEach(this.item.variations[attributeId], (variation) => {
                if (variation.id === variationId) {
                    this.changeVariation(attributeId, variationId, variation.name, variation.convert_price);
                }
            });
        },
        changeExtra: function (e, id, name) {
            if (e.target.checked) {
                this.temp.item_extras.extras.push(id);
                this.temp.item_extras.names.push(name);
            } else {
                for (let i = 0; i < this.temp.item_extras.extras.length; i++) {
                    if (this.temp.item_extras.extras[i] === id) {
                        this.temp.item_extras.extras.splice(i, 1);
                    }
                }
                for (let i = 0; i < this.temp.item_extras.names.length; i++) {
                    if (this.temp.item_extras.names[i] === name) {
                        this.temp.item_extras.names.splice(i, 1);
                    }
                }
            }
            this.totalPriceSetup();
        },
        totalPriceSetup: function () {
            let item_variation_total = 0;
            let item_extra_total = 0;
            let item_addon_total = 0;
            _.forEach(this.temp.item_variations.variations, (variationId, attributeId) => {
                _.forEach(this.item.variations[attributeId], (itemVariation) => {
                    if (variationId === itemVariation.id) {
                        item_variation_total += itemVariation.convert_price;
                    }
                });
            });

            _.forEach(this.temp.item_extras.extras, (extraId) => {
                _.forEach(this.item.extras, (itemExtra) => {
                    if (extraId === itemExtra.id) {
                        item_extra_total += itemExtra.convert_price;
                    }
                });
            });

            _.forEach(this.addons, (addon) => {
                item_addon_total += (addon.total_price * addon.quantity);
            });

            this.temp.item_variation_total = item_variation_total;
            this.temp.item_extra_total = item_extra_total;
            this.temp.total_price = parseFloat((((this.item.offer.length > 0 ? this.item.offer[0].convert_price : this.item.convert_price) + this.temp.item_variation_total + this.temp.item_extra_total) * this.temp.quantity) + item_addon_total);
        },
        quantityUp: function () {
            if (this.temp.quantity === 0) {
                this.temp.quantity = 1;
            }
            this.totalPriceSetup();
        },
        quantityIncrement: function () {
            this.temp.quantity++;
            if (this.temp.quantity <= 0) {
                this.temp.quantity = 1;
            }
            this.totalPriceSetup();
        },
        quantityDecrement: function () {
            this.temp.quantity--;
            if (this.temp.quantity <= 0) {
                this.temp.quantity = 1;
            }
            this.totalPriceSetup();
        },
        addonQuantityUp: function (id) {
            if (typeof this.addonQuantity[id] !== "undefined") {
                if (this.addonQuantity[id] === 0) {
                    this.addonQuantity[id] = 1;
                }
            }
            if (typeof this.addons[id] !== "undefined") {
                this.addons[id].quantity = this.addonQuantity[id];
            }

            this.totalPriceSetup();
        },
        addonQuantityIncrement: function (id) {
            if (typeof this.addonQuantity[id] !== "undefined") {
                this.addonQuantity[id]++;
                if (this.addonQuantity[id] <= 0) {
                    this.addonQuantity[id] = 1;
                }
                if (typeof this.addons[id] !== "undefined") {
                    this.addons[id].quantity = this.addonQuantity[id];
                }
                this.totalPriceSetup();
            }
        },
        addonQuantityDecrement: function (id) {
            if (typeof this.addonQuantity[id] !== "undefined") {
                this.addonQuantity[id]--;
                if (this.addonQuantity[id] <= 0) {
                    this.addonQuantity[id] = 1;
                }
                if (typeof this.addons[id] !== "undefined") {
                    this.addons[id].quantity = this.addonQuantity[id];
                }
                this.totalPriceSetup();
            }
        },
        changeAddon: function (addon) {
            if (typeof this.addons[addon.id] === "undefined") {
                this.addons[addon.id] = {
                    name: addon.addon_item_name,
                    image: addon.thumb,
                    item_id: addon.item_addon_id,
                    quantity: this.addonQuantity[addon.id],
                    discount: 0,
                    currency_price: addon.offer.length > 0 ? addon.offer[0].currency_price : addon.addon_item_currency_price,
                    convert_price: addon.offer.length > 0 ? addon.offer[0].convert_price : addon.addon_item_convert_price,
                    item_variations: {
                        variations: {},
                        names: {}
                    },
                    item_extras: {
                        extras: [],
                        names: []
                    },
                    item_variation_total: addon.variation_total_convert_price,
                    item_extra_total: 0,
                    total_price: addon.total_convert_price,
                    instruction: "",
                };
                if (addon.variations !== "undefined" && Object.keys(addon.variations).length !== 0) {
                    _.forEach(addon.variations, (variationId, attributeId) => {
                        this.addons[addon.id].item_variations.variations[attributeId] = variationId;
                    });

                }
                if (addon.variation_names.length > 0) {
                    _.forEach(addon.variation_names, (variation) => {
                        this.addons[addon.id].item_variations.names[variation.attribute_name] = variation.name;
                    });
                }
            } else {
                delete this.addons[addon.id];
            }
            this.totalPriceSetup();
        },
        addToCart: function () {
            this.itemArrays = [
                {
                    name: this.temp.name,
                    image: this.temp.image,
                    item_id: this.temp.item_id,
                    quantity: this.temp.quantity,
                    discount: this.temp.discount,
                    currency_price: this.temp.currency_price,
                    convert_price: this.temp.convert_price,
                    item_variations: this.temp.item_variations,
                    item_extras: this.temp.item_extras,
                    item_variation_total: this.temp.item_variation_total,
                    item_extra_total: this.temp.item_extra_total,
                    instruction: this.temp.instruction,

                    price_with_tax: this.temp.price_with_tax,
                    tax_amount: this.temp.tax_amount
                }
            ];   
            if (this.addons !== "undefined" && Object.keys(this.addons).length !== 0) {
                _.forEach(this.addons, (addon) => {
                    this.itemArrays.push({
                        name: addon.name,
                        image: addon.image,
                        item_id: addon.item_id,
                        quantity: addon.quantity,
                        discount: addon.discount,
                        price: addon.price,
                        currency_price: addon.currency_price,
                        convert_price: addon.convert_price,
                        item_variations: addon.item_variations,
                        item_extras: addon.item_extras,
                        item_variation_total: addon.item_variation_total,
                        item_extra_total: addon.item_extra_total,
                        instruction: addon.instruction,

                        price_with_tax: addon.price_with_tax,
                        tax_amount: addon.tax_amount
                    });
                });
            } 
            if (this.itemArrays.length > 0) { 
                this.$store.dispatch("posCart/lists", this.itemArrays).then((res) => {
                    this.item = null;
                    this.temp.name = "";
                    this.temp.image = "";
                    this.temp.item_id = 0;
                    this.temp.quantity = 0;
                    this.temp.discount = 0;
                    this.temp.currency_price = 0;
                    this.temp.convert_price = 0;
                    this.temp.item_variations = {
                        variations: {},
                        names: {}
                    };
                    this.temp.item_extras = {
                        extras: [],
                        names: []
                    };
                    this.temp.item_variation_total = 0;
                    this.temp.item_extra_total = 0;
                    this.temp.total_price = 0;
                    this.temp.instruction = "";

                    this.temp.price_with_tax = 0;
                    this.temp.tax_amount = 0;

                    this.addons = {};
                    this.itemArrays = [];

                    alertService.success(this.$t('message.add_to_cart'));
                    appService.modalHide('#item-variation-modal');
                }).catch();
            }
        },
        order: async function (item) {
            this.item = item;

            // Reset temp object
            this.temp = {
                name: "",
                image: "",
                item_id: 0,
                quantity: 1,
                discount: 0,
                currency_price: 0,
                convert_price: 0,
                item_variations: {
                    variations: {},
                    names: {}
                },
                item_extras: {
                    extras: [],
                    names: []
                },
                item_variation_total: 0,
                item_extra_total: 0,
                total_price: 0,
                instruction: "",
                price_with_tax: 0,
                tax_amount: 0,
            };

            if (this.item.itemAttributes.length > 0) {
                _.forEach(this.item.itemAttributes, (element) => {
                    if (typeof this.item.variations[element.id][0] !== "undefined") {
                        this.temp.item_variations.variations[this.item.variations[element.id][0].item_attribute_id] = this.item.variations[element.id][0].id;
                        this.temp.item_variations.names[element.name] = this.item.variations[element.id][0].name;
                        this.temp.item_variation_total += this.item.variations[element.id][0].convert_price;
                    }
                });
            }

            if (this.item.addons.length > 0) {
                _.forEach(this.item.addons, (addon) => {
                    this.addonQuantity[addon.id] = 1;
                });
            }

            this.temp.name = this.item.name;
            this.temp.image = this.item.thumb;
            this.temp.item_id = this.item.id;
            this.temp.quantity = 1;
            this.temp.discount = 0;
            this.temp.convert_price = item.offer.length > 0 ? item.offer[0].convert_price : item.convert_price;
            this.temp.currency_price = item.offer.length > 0 ? item.offer[0].currency_price : item.currency_price;
            this.temp.total_price = (item.offer.length > 0 ? item.offer[0].convert_price : item.convert_price) + this.temp.item_variation_total;

            // Calculate price_with_tax and tax_amount based on total_price (using offer price if available)
            // If item.tax_rate is 10, treat as 10%
            const taxRate = item.tax_rate ? item.tax_rate / 100 : 0;
            this.temp.price_with_tax = parseFloat(this.temp.total_price * (1 + taxRate));
            this.temp.tax_amount = parseFloat(this.temp.total_price * taxRate);

            await this.addToCart() 
        }
    }, 

}
</script>

<style scoped>
/* Professional POS List View Styles */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Fixed header table layout */
.table-container {
    height: 100%;
    display: flex;
    flex-direction: column;
}

/* Fixed header styling */
.table-header {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

/* Scrollable body */
.table-body-container {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

/* Custom scrollbar for the table body */
.table-body-container::-webkit-scrollbar {
    width: 6px;
}

.table-body-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.table-body-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.table-body-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Loading spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Loading overlay backdrop */
.loading-overlay {
    backdrop-filter: blur(2px);
    background-color: rgba(255, 255, 255, 0.85);
}

/* Table layout improvements */
table {
    table-layout: fixed;
    width: 100%;
}

/* Ensure table cells don't break layout */
td, th {
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 0;
}

/* Mobile responsive table adjustments */
@media (max-width: 1024px) {
    /* When code column is hidden, redistribute widths */
    table {
        table-layout: fixed;
    }
    
    .lg\:table-cell {
        display: none !important;
    }
    
    /* Adjust column widths when code column is hidden */
    th:nth-child(1), td:nth-child(1) { width: 45%; }
    th:nth-child(3), td:nth-child(3) { width: 25%; }
    th:nth-child(4), td:nth-child(4) { width: 30%; }
}

@media (max-width: 768px) {
    /* More mobile-friendly distribution */
    th:nth-child(1), td:nth-child(1) { width: 40%; }
    th:nth-child(3), td:nth-child(3) { width: 25%; }
    th:nth-child(4), td:nth-child(4) { width: 35%; }
    
    /* Smaller padding on mobile */
    td, th {
        padding: 0.75rem 0.5rem !important;
    }
    
    /* Smaller images on mobile */
    .w-12.h-12 {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    
    /* Adjust button text on mobile */
    .sm\:hidden {
        display: inline !important;
    }
    
    .hidden.sm\:inline {
        display: none !important;
    }
}

/* Hover effects for rows */
tr:hover img {
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
}

/* Price highlight animation */
@keyframes price-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.price-highlight {
    animation: price-pulse 2s infinite;
}

/* Button hover effects */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Status indicator */
.status-indicator {
    position: relative;
}

.status-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(45deg, #10b981, #34d399);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: xor;
}

/* Responsive grid adjustments */
@media (max-width: 768px) {
    .grid-cols-12 {
        grid-template-columns: 1fr auto;
        gap: 0.75rem;
    }
    
    .col-span-5 {
        grid-column: span 1;
    }
    
    .col-span-3 {
        grid-column: span 1;
    }
    
    .col-span-2 {
        display: none;
    }
}

/* Enhanced shadows and borders */
.shadow-card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}

.border-gradient {
    background: linear-gradient(white, white) padding-box,
                linear-gradient(45deg, #e5e7eb, #f3f4f6) border-box;
    border: 1px solid transparent;
}

/* Loading states */
.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Focus states for accessibility */
button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Professional color scheme */
.bg-primary {
    background-color: #3b82f6;
}

.bg-primary:hover,
.bg-primary-dark {
    background-color: #2563eb;
}

.text-primary {
    color: #3b82f6;
}

.border-primary {
    border-color: #3b82f6;
}

/* Table-like appearance */
.item-row {
    transition: all 0.2s ease-in-out;
}

.item-row:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Badge styles */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background-color: #dcfce7;
    color: #166534;
}

.badge-sale {
    background-color: #fef2f2;
    color: #dc2626;
}

.badge-code {
    background-color: #f1f5f9;
    color: #475569;
    font-family: 'Courier New', monospace;
}
</style>