<template>
    <!-- Professional List View for POS Items -->
    
    <div class="relative h-full flex flex-col w-full overflow-hidden">
        <!-- Loading Overlay -->
        <div v-if="loading" class="absolute inset-0 loading-overlay flex items-center justify-center z-50 rounded-lg bg-white/80 backdrop-blur-sm">
            <div class="flex flex-col items-center bg-white px-4 md:px-6 py-6 md:py-8 rounded-lg shadow-lg border">
                <div class="animate-spin rounded-full h-10 w-10 md:h-12 md:w-12 border-4 border-primary border-t-transparent mb-3 md:mb-4"></div>
                <p class="text-xs md:text-sm text-gray-600 font-medium">{{ $t('message.loading') || 'Loading items...' }}</p>
            </div>
        </div>
        
        <!-- Scrollable Items List Container -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden w-full">
            <!-- Items as Separate Cards/List -->
            <div class="space-y-2 md:space-y-3 px-2 md:px-3 py-2 pb-20">
                <div v-for="item in sortedItems" :key="item.id" 
                     class="bg-white rounded-md shadow-xs hover:shadow-md border border-gray-100 hover:border-primary/30 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden relative">
                    
                    <!-- Layered shadow effect -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/10 via-transparent to-purple-50/10 pointer-events-none"></div>
                    
                    <!-- Item Card Content -->
                    <div class="flex items-center gap-2 md:gap-3 p-3 md:p-4 relative">
                        <!-- Item Image -->
                        <div class="flex-shrink-0 w-12 h-12 md:w-16 md:h-16 bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition-shadow duration-200"
                             @click="order(item)">
                            <img :src="item.thumb" 
                                 :alt="item.name"
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <!-- Item Details - with proper overflow control -->
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <!-- Item Name - with line clamp and overflow hidden -->
                            <h3 class="text-sm md:text-base text-gray-900 cursor-pointer hover:text-primary transition-colors duration-200 line-clamp-2 break-words overflow-hidden"
                                @click="order(item)">
                                {{ item.name }}
                            </h3>
                            
                            <!-- Barcode -->
                            <div v-if="item.barcode && item.barcode != 'null'" class="mt-1 overflow-hidden">
                                <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 rounded text-[10px] md:text-xs font-mono text-gray-600 max-w-full">
                                    <i class="fa-solid fa-barcode mr-1 flex-shrink-0 text-[10px] md:text-xs"></i>
                                    <span class="truncate">{{ item.barcode }}</span>
                                </span>
                            </div>
                            
                            <!-- Price - with flex wrap for mobile -->
                            <div class="mt-1 overflow-hidden">
                                <div v-if="item.offer.length > 0" class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm md:text-base text-green-600 whitespace-nowrap">
                                        {{ item.offer[0].currency_price }}
                                    </span>
                                    <span class="text-xs text-gray-400 line-through whitespace-nowrap">
                                        {{ item.branch_currency_price }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-800 whitespace-nowrap">
                                        {{ $t('label.sale') || 'Sale' }}
                                    </span>
                                </div>
                                <div v-else class="text-sm md:text-base font-bold text-gray-900 truncate">
                                    {{ item.branch_currency_price }}
                                </div>
                            </div>
                            
                            <!-- Description (Desktop only) -->
                            <p class="hidden md:block text-xs text-gray-500 mt-1 line-clamp-1" v-if="item.description">
                                {{ textShortener(item.description, 60) }}
                            </p>
                        </div>
                        
                        <!-- Action Button - Fixed width to prevent push-out -->
                        <div class="flex-shrink-0">
                            <!-- Quick Add Button (if no variations) -->
                            <button v-if="!item.itemAttributes || item.itemAttributes.length === 0"
                                    @click="order(item)"
                                    class="inline-flex items-center justify-center w-12 h-12 md:w-auto md:px-4 md:py-2.5 bg-primary text-white text-xs md:text-sm font-medium rounded-full hover:bg-primary-dark hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1">
                                <i class="fa-solid fa-plus text-base"></i>
                            </button>
                            
                            <!-- Customize Button (if has variations) -->
                            <button v-else
                                    @click="variationModalShow(item)"
                                    class="inline-flex items-center justify-center w-12 h-12 md:w-auto md:px-4 md:py-2.5 bg-primary text-white text-xs md:text-sm font-medium rounded-full hover:bg-primary-dark hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1">
                                <i class="fa-solid fa-plus text-base"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
        <!-- Modal Backdrop -->
        
        <!-- Mobile: Fullscreen, Desktop: Centered with max dimensions -->
        <div class="modal-dialog bg-white z-[9999] overflow-hidden md:rounded-2xl md:shadow-2xl md:max-w-2xl md:max-h-[90vh]" v-if="item">
            <!-- Modal Header -->
            <div class="flex-shrink-0 bg-white border-b border-gray-200">
                <!-- Top Bar with Back and Close -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <button @click.prevent="variationModalHide" class="flex items-center gap-2 text-gray-700 hover:text-primary transition">
                        <i class="lab lab-arrow-left text-xl"></i>
                        <span class="text-sm font-medium">{{ $t('button.back') || 'Back' }}</span>
                    </button>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $t('label.customize_item') || 'Customize Item' }}</h3>
                    <button @click.prevent="variationModalHide" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="lab lab-close text-xl text-gray-600"></i>
                    </button>
                </div>
                
                <!-- Item Info Section -->
                <div class="p-4">
                    <div class="flex gap-4">
                        <!-- Item Image -->
                        <div class="flex-shrink-0">
                            <img class="w-20 h-20 md:w-24 md:h-24 object-cover rounded-xl shadow-md ring-2 ring-gray-100" :src="item.thumb" :alt="item.name">
                        </div>
                        
                        <!-- Item Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-2 mb-2">
                                <h3 class="text-lg md:text-xl font-bold text-gray-900 flex-1">{{ item.name }}</h3>
                                <button v-if="item.caution" type="button" @click.prevent="infoModalShow(item.name, item.caution)" class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition">
                                    <i class="lab lab-information text-primary text-base"></i>
                                </button>
                            </div>
                            <p v-if="item.description" class="text-sm text-gray-600 mb-3 line-clamp-2">{{ item.description }}</p>
                            <div class="flex items-center gap-2">
                                <span class="text-xl md:text-2xl font-bold text-primary">
                                    {{ item.offer.length > 0 ? item.offer[0].branch_currency_price : item.branch_currency_price }}
                                </span>
                                <span v-if="item.offer.length > 0" class="text-sm text-gray-400 line-through">
                                    {{ item.branch_currency_price }}
                                </span>
                                <span v-if="item.offer.length > 0" class="ml-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                    {{ $t('label.sale') || 'SALE' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body - Scrollable -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <!-- Quantity Selector -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-800">{{ $t('label.quantity') }}</h4>
                        <div class="flex items-center gap-3 bg-white rounded-lg px-2 py-1.5 shadow-sm">
                            <button @click.prevent="quantityDecrement" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-red-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-minus text-gray-700"></i>
                            </button>
                            <input type="number" v-on:keypress="onlyNumber($event)" v-on:keyup="quantityUp" v-model="temp.quantity" class="w-12 text-center text-base font-bold text-gray-800 bg-transparent focus:outline-none" />
                            <button @click.prevent="quantityIncrement" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-green-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-plus text-gray-700"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Multiple Attributes (Dropdowns) -->
                <div v-if="item.itemAttributes.length > 1" class="space-y-3">
                    <div v-for="itemAttribute in item.itemAttributes" :key="itemAttribute.id">
                        <label class="block text-sm font-semibold text-gray-800 mb-2">{{ itemAttribute.name }}</label>
                        <div class="relative">
                            <select @change.prevent="changeVariationAdjust(itemAttribute.id, temp.item_variations.variations[itemAttribute.id])" v-model="temp.item_variations.variations[itemAttribute.id]" class="w-full px-4 py-3 pr-10 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white appearance-none">
                                <option :value="variation.id" v-for="variation in item.variations[itemAttribute.id]" :key="variation.id">
                                    {{ variation.name }} <span v-if="variation.price > 0">+{{ variation.currency_price }}</span>
                                </option>
                            </select>
                            <i class="lab lab-arrow-down text-sm absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Single Attribute (Radio Buttons) -->
                <div v-else-if="item.itemAttributes.length > 0">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">{{ item.itemAttributes[0].name }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <label v-for="variation in item.variations[item.itemAttributes[0].id]" :key="variation.id" :for="variation.item_attribute_id + '-' + variation.name" :class="{ 'ring-2 ring-primary bg-primary/5': temp.item_variations.variations[variation.item_attribute_id] === variation.id }" class="relative cursor-pointer p-3 rounded-lg border border-gray-200 hover:border-primary transition">
                            <input :value="variation.id" @click="changeVariation(variation.item_attribute_id, variation.id, variation.name, variation.convert_price)" v-model="temp.item_variations.variations[variation.item_attribute_id]" type="radio" :id="variation.item_attribute_id + '-' + variation.name" class="absolute opacity-0" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-800">{{ textShortener(variation.name, 15) }}</span>
                                <span v-if="variation.price > 0" class="text-xs text-gray-600 mt-0.5">+{{ variation.currency_price }}</span>
                            </div>
                            <div v-if="temp.item_variations.variations[variation.item_attribute_id] === variation.id" class="absolute top-2 right-2 w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                <i class="lab-check text-white text-xs"></i>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Extras (Checkboxes) -->
                <div v-if="item.extras.length > 0">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">{{ $t('label.extras') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <label v-for="extra in item.extras" :key="extra.id" :for="extra.id + extra.name" class="relative cursor-pointer p-3 rounded-lg border border-gray-200 hover:border-primary transition bg-white">
                            <input :id="extra.id + extra.name" @change.prevent="changeExtra($event, extra.id, extra.name)" :value="extra.id" type="checkbox" class="absolute opacity-0 peer" />
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-primary peer-checked:border-primary flex items-center justify-center transition">
                                    <i class="lab-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-gray-800">{{ textShortener(extra.name, 20) }}</span>
                                    <span class="block text-xs text-gray-600 mt-0.5">+{{ extra.currency_price }}</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Addons -->
                <div v-if="item.addons.length > 0">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">{{ $t('label.addons') }}</h4>
                    <div class="space-y-2">
                        <div v-for="addon in item.addons" :key="addon.id" @click.prevent="changeAddon(addon)" class="relative cursor-pointer flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary transition bg-white" :class="{ 'ring-2 ring-primary bg-primary/5': typeof addons[addon.id] !== 'undefined' }">
                            <!-- Addon Image -->
                            <img :src="addon.thumb" alt="" class="w-16 h-16 object-cover rounded-lg flex-shrink-0" />
                            
                            <!-- Addon Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2 mb-1">
                                    <h5 class="text-sm font-semibold text-gray-800 line-clamp-1">{{ addon.addon_item_name }}</h5>
                                    <button type="button" @click.stop="infoModalShow(addon.addon_item_name, addon.caution)" class="flex-shrink-0 w-5 h-5 rounded-full hover:bg-gray-100 flex items-center justify-center">
                                        <i class="lab lab-information text-primary text-sm"></i>
                                    </button>
                                </div>
                                <p v-if="addon.variation_names.length > 0" class="text-xs text-gray-600 mb-1">
                                    <span v-for="(variation, idx) in addon.variation_names" :key="idx">{{ textShortener(variation.name, 8) }}<span v-if="idx < addon.variation_names.length - 1">, </span></span>
                                </p>
                                <span class="text-sm font-bold text-primary">{{ addon.total_currency_price }}</span>
                            </div>
                            
                            <!-- Quantity Controls -->
                            <div v-if="typeof addons[addon.id] !== 'undefined'" @click.stop class="flex items-center gap-2 bg-gray-50 rounded-lg px-2 py-1">
                                <button @click.prevent="addonQuantityDecrement(addon.id)" class="w-6 h-6 rounded-full bg-white hover:bg-red-100 flex items-center justify-center transition">
                                    <i class="lab-minus text-xs text-gray-700"></i>
                                </button>
                                <input v-on:keypress="onlyNumber($event)" v-on:keyup="addonQuantityUp(addon.id)" v-model="addonQuantity[addon.id]" type="number" class="w-8 text-center text-sm font-bold text-gray-800 bg-transparent focus:outline-none" />
                                <button @click.prevent="addonQuantityIncrement(addon.id)" class="w-6 h-6 rounded-full bg-white hover:bg-green-100 flex items-center justify-center transition">
                                    <i class="lab-plus text-xs text-gray-700"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">{{ $t('label.special_instructions') }}</label>
                    <textarea v-model="temp.instruction" :placeholder="$t('message.add_note')" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer - Fixed Bottom -->
            <div class="flex-shrink-0 p-4 border-t border-gray-100 bg-white">
                <button type="button" :disabled="temp.total_price <= 0" @click.prevent="addToCart" class="w-full py-4 rounded-xl text-base font-bold text-white bg-gradient-to-r from-primary to-blue-600 hover:from-primary-dark hover:to-blue-700 transition shadow-lg active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                    <i class="lab lab-bag-2 text-xl"></i>
                    <span>
                        {{ $t('button.add_to_cart') }} - {{ currencyFormat(temp.total_price, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}
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
    name: "QuickItemListViewComponent",
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