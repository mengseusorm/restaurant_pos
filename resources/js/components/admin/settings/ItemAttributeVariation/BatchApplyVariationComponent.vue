<template>
    <LoadingComponent :props="loading" />

    <div class="db-card">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.batch_apply_variations") }}</h3>
            <div class="db-card-filter">
                <button @click="clearAllVariations"
                    class="db-btn py-2 text-white bg-red-600 hover:bg-red-700">
                    <i class="lab lab-trash"></i>
                    <span>{{ $t("button.clear_all_variations") }}</span>
                </button>
                <button @click="applyVariations" :disabled="selectedVariations.length === 0"
                    class="db-btn py-2 text-white bg-primary" :class="{ 'opacity-50 cursor-not-allowed': selectedVariations.length === 0 }">
                    <i class="lab lab-save"></i>
                    <span>{{ $t("button.apply") }} ({{ selectedVariations.length }})</span>
                </button>
            </div>
        </div>

        <div class="db-card-body">
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <i class="lab lab-information-circle text-yellow-600 text-xl"></i>
                    <div class="text-sm text-gray-700">
                        <strong>{{ $t("label.note") }}:</strong>
                        {{ $t("message.variation_price_difference_note") }}
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th rowspan="2" class="border border-gray-300 px-4 py-2 text-left sticky left-0 bg-gray-100 z-10 min-w-[300px]">
                                {{ $t("label.item") }}
                            </th>
                            <th rowspan="2" class="border border-gray-300 px-4 py-2 text-left sticky left-[300px] bg-gray-100 z-10 min-w-[150px]">
                                {{ $t("label.price") }}
                            </th>
                            <template v-for="attribute in attributes" :key="'attr-' + attribute.id">
                                <th v-if="attribute && attribute.variations && attribute.variations.length > 0"
                                    :colspan="attribute.variations.length"
                                    class="border border-gray-300 px-4 py-2 text-center">
                                    <div class="flex flex-col gap-2">
                                        <div>
                                            {{ attribute.name }}
                                            <span v-if="attribute.require_input_price == askEnum.YES" class="text-xs text-blue-600">
                                                ({{ $t("label.require_input_price") }})
                                            </span>
                                        </div>
                                        <label class="flex items-center justify-center gap-2 cursor-pointer text-xs text-gray-600">
                                            <input type="checkbox"
                                                @change="toggleColumnSelection(attribute.id)"
                                                :checked="isColumnFullySelected(attribute.id)"
                                                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
                                            <span>{{ $t("label.select_all") }}</span>
                                        </label>
                                        <button v-if="attribute.require_input_price == askEnum.YES"
                                            @click="applyDefaultPrices(attribute.id)"
                                            class="px-2 py-1 text-xs text-white bg-green-600 hover:bg-green-700 rounded">
                                            <i class="lab lab-check-circle"></i>
                                            {{ $t("button.apply_default_price") }}
                                        </button>
                                    </div>
                                </th>
                            </template>
                        </tr>
                        <tr>
                            <template v-for="attribute in attributes" :key="'attr-vars-' + attribute.id">
                                <template v-if="attribute && attribute.variations && attribute.variations.length > 0">
                                    <th v-for="variation in attribute.variations" :key="'var-head-' + variation.id"
                                        class="border border-gray-300 px-2 py-2 text-center bg-gray-50 min-w-[150px]">
                                        <div class="text-xs font-medium">{{ variation.name }}</div>
                                        <div v-if="attribute.require_input_price == askEnum.YES" class="text-[10px] text-gray-500 mt-1">
                                            {{ $t("label.default") }}: {{ formatDecimal(variation.price || 0) }}
                                        </div>
                                    </th>
                                </template>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(itemsByCategory, categoryName) in groupedItems" :key="categoryName">
                            <tr class="bg-gray-200">
                                <td class="border border-gray-300 px-4 py-2 font-bold text-gray-700 sticky left-0 bg-gray-200 z-10">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                            @change="toggleCategorySelection(categoryName)"
                                            :checked="isCategoryFullySelected(categoryName)"
                                            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
                                        <span>{{ $t("label.select_all") }} - {{ categoryName }}</span>
                                    </label>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 sticky left-[300px] bg-gray-200 z-10"></td>
                                <template v-for="attribute in attributes" :key="'cat-attr-' + categoryName + '-' + attribute.id">
                                    <template v-if="attribute && attribute.variations && attribute.variations.length > 0">
                                        <td v-for="variation in attribute.variations" :key="'cat-var-' + categoryName + '-' + variation.id"
                                            class="border border-gray-300 px-2 py-2 text-center bg-gray-200">
                                            <label class="flex items-center justify-center gap-1 cursor-pointer text-xs text-gray-700">
                                                <input type="checkbox"
                                                    @change="toggleCategoryAttributeVariationSelection(categoryName, attribute.id, variation.id)"
                                                    :checked="isCategoryAttributeVariationFullySelected(categoryName, attribute.id, variation.id)"
                                                    class="w-3 h-3 text-primary border-gray-300 rounded focus:ring-primary" />
                                                <span class="text-[10px]">{{ $t("label.all") }}</span>
                                            </label>
                                        </td>
                                    </template>
                                </template>
                            </tr>
                            <tr v-for="item in itemsByCategory" :key="'item-' + item.id" class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2 sticky left-0 bg-white z-10 font-medium min-w-[300px]">
                                    
                                    <div>
                                        <span v-if="item.item_code" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full border border-green-300">
                                            {{ item.item_code }}
                                        </span>
                                    {{ item.name }}
                                </div>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 sticky left-[300px] bg-white z-10 min-w-[150px]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-blue-600 font-normal">{{ formatDecimal(item.price_with_tax || item.price || 0) }}</span>
                                        <button @click="togglePriceEdit(item.id)" 
                                            class="text-gray-500 hover:text-blue-600 transition-colors"
                                            :title="$t('button.edit')">
                                            <i class="lab lab-edit-2 text-sm"></i>
                                        </button>
                                    </div>
                                    <div v-if="editingPriceItemId === item.id" class="mt-2 space-y-2">
                                        <input type="number" step="0.01" min="0"
                                            v-model="editingPrice"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-primary focus:border-primary"
                                            :placeholder="$t('label.new_price')" />
                                        <div class="flex gap-2">
                                            <button @click="updateItemPrice(item.id)"
                                                class="flex-1 px-3 py-1 text-xs text-white bg-primary hover:bg-primary-dark rounded">
                                                <i class="lab lab-check"></i>
                                                {{ $t("button.update") }}
                                            </button>
                                            <button @click="cancelPriceEdit"
                                                class="flex-1 px-3 py-1 text-xs text-gray-700 bg-gray-200 hover:bg-gray-300 rounded">
                                                <i class="lab lab-close"></i>
                                                {{ $t("button.cancel") }}
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <template v-for="attribute in attributes" :key="'item-attr-' + item.id + '-' + attribute.id">
                                    <template v-if="attribute && attribute.variations && attribute.variations.length > 0">
                                        <td v-for="variation in attribute.variations" :key="'cell-' + item.id + '-' + variation.id"
                                            class="border border-gray-300 px-2 py-2 text-center"
                                            :class="{ 
                                                'bg-blue-50': isSelected(item.id, variation.id),
                                                'bg-green-50': hasExistingVariation(item, variation.id)
                                            }">
                                            <div class="flex flex-col items-center gap-2">
                                                <input type="checkbox" :value="variation.id"
                                                    @change="toggleVariation(item.id, variation.id, attribute.require_input_price)"
                                                    :checked="isSelected(item.id, variation.id)"
                                                    class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary" />
                                                
                                                <div v-if="isSelected(item.id, variation.id) && attribute.require_input_price == askEnum.YES" class="text-xs text-green-600 font-medium">
                                                    {{ formatDecimal(calculateTotalPrice(item, variation.id)) }}
                                                </div>
                                                <div v-else-if="hasExistingVariation(item, variation.id)" class="text-xs text-green-600 font-medium">
                                                    {{ formatDecimal(getExistingVariationPrice(item, variation.id)) }}
                                                </div>
                                                
                                                <div v-if="isSelected(item.id, variation.id) && attribute.require_input_price == askEnum.YES" class="w-full">
                                                    <input type="number" step="0.01"
                                                        :value="getVariationPrice(item.id, variation.id)"
                                                        @change="updateVariationPrice(item.id, variation.id, $event.target.value)"
                                                        class="w-full px-2 py-1 text-xs text-center border border-gray-300 rounded focus:ring-primary focus:border-primary"
                                                        :placeholder="$t('label.additional_price')" />
                                                </div>
                                            </div>
                                        </td>
                                    </template>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import alertService from "../../../../services/alertService";
import askEnum from "../../../../enums/modules/askEnum";
import appService from "../../../../services/appService";

export default {
    name: "BatchApplyVariationComponent",
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            items: [],
            attributes: [],
            selectedVariations: [],
            askEnum: askEnum,
            editingPriceItemId: null,
            editingPrice: 0,
        };
    },
    mounted() {
        this.loadData();
    },
    computed: {
        groupedItems: function () {
            const grouped = {};
            this.items.forEach(item => {
                const categoryName = item.category?.name || this.$t("label.uncategorized");
                if (!grouped[categoryName]) {
                    grouped[categoryName] = [];
                }
                grouped[categoryName].push(item);
            });
            return grouped;
        }
    },
    methods: {
        formatDecimal: function (amount) {
            return appService.formatDecimal(amount);
        },
        loadData: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("batchApplyVariation/getData")
                .then((res) => {
                    this.items = res.data.items || [];
                    this.attributes = res.data.attributes || [];
                    
                    // Pre-select existing variations and set their prices
                    this.items.forEach(item => {
                        if (item.variations && Array.isArray(item.variations)) {
                            item.variations.forEach(variation => {
                                const attributeVariationId = variation.item_attribute_variation_id;
                                if (attributeVariationId) {
                                    // Find the attribute to check if it requires input price
                                    const attribute = this.attributes.find(attr => 
                                        attr.variations && attr.variations.some(v => v.id === attributeVariationId)
                                    );
                                    
                                    // Add to selected variations if not already there
                                    const index = this.selectedVariations.findIndex(
                                        v => v.item_id === item.id && v.item_attribute_variation_id === attributeVariationId
                                    );
                                    
                                    if (index === -1) {
                                        this.selectedVariations.push({
                                            item_id: item.id,
                                            item_attribute_variation_id: attributeVariationId,
                                            price: attribute?.require_input_price == this.askEnum.YES 
                                                ? (variation.price || 0) 
                                                : undefined,
                                        });
                                    }
                                }
                            });
                        }
                    });
                    
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || err.message);
                });
        },
        toggleVariation: function (itemId, variationId, requireInputPrice) {
            const index = this.selectedVariations.findIndex(
                (v) => v.item_id === itemId && v.item_attribute_variation_id === variationId
            );

            if (index > -1) {
                this.selectedVariations.splice(index, 1);
            } else {
                this.selectedVariations.push({
                    item_id: itemId,
                    item_attribute_variation_id: variationId,
                    price: requireInputPrice == this.askEnum.YES ? 0 : undefined,
                });
            }
        },
        calculateTotalPrice: function (item, variationId) {
            const itemPrice = parseFloat(item.price_with_tax || item.price || 0);
            const variationPrice = parseFloat(this.getVariationPrice(item.id, variationId) || 0);
            return itemPrice + variationPrice;
        },
        isSelected: function (itemId, variationId) {
            return this.selectedVariations.some(
                (v) => v.item_id === itemId && v.item_attribute_variation_id === variationId
            );
        },
        getVariationPrice: function (itemId, variationId) {
            const variation = this.selectedVariations.find(
                (v) => (itemId === null || v.item_id === itemId) && v.item_attribute_variation_id === variationId
            );
            const price = variation?.price !== null && variation?.price !== undefined ? variation.price : (variation ? 0 : '');
            // Format to 2 decimal places if it's a number
            return price !== '' ? parseFloat(price).toFixed(2) : '';
        },
        updateVariationPrice: function (itemId, variationId, price) {
            const variation = this.selectedVariations.find(
                (v) => v.item_id === itemId && v.item_attribute_variation_id === variationId
            );
            if (variation) {
                // Allow empty or any number (positive, negative, or zero), default to 0
                variation.price = price === '' || price === null ? 0 : parseFloat(price);
            }
        },
        applyVariations: function () {
            if (this.selectedVariations.length === 0) {
                alertService.error(this.$t("message.please_select_variations"));
                return;
            }

            // Filter out undefined prices (for variations that don't require price input)
            // Ensure numbers are properly formatted
            const variationsToApply = this.selectedVariations.map(v => {
                const { price, ...rest } = v;
                if (price !== undefined) {
                    return { ...rest, price: parseFloat(price) || 0 };
                }
                return rest;
            });

            this.loading.isActive = true;
            this.$store
                .dispatch("batchApplyVariation/apply", { variations: variationsToApply })
                .then((res) => {
                    this.loading.isActive = false;
                    const result = res.data;
                    alertService.success(
                        this.$t("message.variations_applied_successfully", {
                            created: result.created,
                            updated: result.updated,
                            total: result.total
                        })
                    );
                    this.selectedVariations = [];
                    this.loadData();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || err.message);
                });
        },
        clearAllVariations: function () {
            appService.confirmDialog(
                this.$t("message.are_you_sure"),
                this.$t("message.clear_all_variations_warning"),
                "warning",
                this.$t("button.yes_clear_all"),
                this.$t("button.cancel")
            ).then((result) => {
                if (result) {
                    this.loading.isActive = true;
                    this.$store
                        .dispatch("batchApplyVariation/clearAll")
                        .then((res) => {
                            this.loading.isActive = false;
                            alertService.success(this.$t("message.all_variations_cleared_successfully"));
                            this.selectedVariations = [];
                            this.loadData();
                        })
                        .catch((err) => {
                            this.loading.isActive = false;
                            alertService.error(err.response?.data?.message || err.message);
                        });
                }
            });
        },
        toggleColumnSelection: function (attributeId) {
            const attribute = this.attributes.find(a => a.id === attributeId);
            if (!attribute || !attribute.variations || attribute.variations.length === 0) {
                return;
            }

            const isFullySelected = this.isColumnFullySelected(attributeId);
            
            // For each item, toggle all variations for this attribute
            this.items.forEach(item => {
                attribute.variations.forEach(variation => {
                    const index = this.selectedVariations.findIndex(
                        v => v.item_id === item.id && v.item_attribute_variation_id === variation.id
                    );

                    if (isFullySelected) {
                        // Unselect all
                        if (index > -1) {
                            this.selectedVariations.splice(index, 1);
                        }
                    } else {
                        // Select all
                        if (index === -1) {
                            this.selectedVariations.push({
                                item_id: item.id,
                                item_attribute_variation_id: variation.id,
                                price: attribute.require_input_price == this.askEnum.YES ? 0 : undefined,
                            });
                        }
                    }
                });
            });
        },
        isColumnFullySelected: function (attributeId) {
            const attribute = this.attributes.find(a => a.id === attributeId);
            if (!attribute || !attribute.variations || attribute.variations.length === 0) {
                return false;
            }

            // Check if all items have all variations of this attribute selected
            const totalPossibleSelections = this.items.length * attribute.variations.length;
            const currentSelections = this.selectedVariations.filter(v => {
                const variation = attribute.variations.find(av => av.id === v.item_attribute_variation_id);
                return variation !== undefined;
            }).length;

            return totalPossibleSelections > 0 && currentSelections === totalPossibleSelections;
        },
        hasExistingVariation: function (item, variationId) {
            if (!item.variations || !Array.isArray(item.variations)) {
                return false;
            }
            return item.variations.some(v => v.item_attribute_variation_id === variationId);
        },
        getExistingVariationPrice: function (item, variationId) {
            if (!item.variations || !Array.isArray(item.variations)) {
                return 0;
            }
            const existingVariation = item.variations.find(v => v.item_attribute_variation_id === variationId);
            const itemPrice = parseFloat(item.price || 0);
            const variationPrice = parseFloat(existingVariation?.price || 0);
            return existingVariation ? itemPrice + variationPrice : 0;
        },
        toggleCategorySelection: function (categoryName) {
            const items = this.groupedItems[categoryName] || [];
            const isFullySelected = this.isCategoryFullySelected(categoryName);

            // For each item in this category, toggle all variations for all attributes
            items.forEach(item => {
                this.attributes.forEach(attribute => {
                    if (attribute.variations && attribute.variations.length > 0) {
                        attribute.variations.forEach(variation => {
                            const index = this.selectedVariations.findIndex(
                                v => v.item_id === item.id && v.item_attribute_variation_id === variation.id
                            );

                            if (isFullySelected) {
                                // Unselect all
                                if (index > -1) {
                                    this.selectedVariations.splice(index, 1);
                                }
                            } else {
                                // Select all
                                if (index === -1) {
                                    this.selectedVariations.push({
                                        item_id: item.id,
                                        item_attribute_variation_id: variation.id,
                                        price: attribute.require_input_price == this.askEnum.YES ? 0 : undefined,
                                    });
                                }
                            }
                        });
                    }
                });
            });
        },
        isCategoryFullySelected: function (categoryName) {
            const items = this.groupedItems[categoryName] || [];
            if (items.length === 0 || this.attributes.length === 0) {
                return false;
            }

            // Calculate total possible selections for this category
            let totalPossibleSelections = 0;
            this.attributes.forEach(attribute => {
                if (attribute.variations && attribute.variations.length > 0) {
                    totalPossibleSelections += items.length * attribute.variations.length;
                }
            });

            if (totalPossibleSelections === 0) {
                return false;
            }

            // Count current selections for items in this category
            const itemIds = items.map(item => item.id);
            const currentSelections = this.selectedVariations.filter(v => itemIds.includes(v.item_id)).length;

            return currentSelections === totalPossibleSelections;
        },
        toggleCategoryAttributeSelection: function (categoryName, attributeId) {
            const items = this.groupedItems[categoryName] || [];
            const attribute = this.attributes.find(a => a.id === attributeId);
            
            if (!attribute || !attribute.variations || attribute.variations.length === 0) {
                return;
            }

            const isFullySelected = this.isCategoryAttributeFullySelected(categoryName, attributeId);

            // For each item in this category, toggle all variations for this specific attribute
            items.forEach(item => {
                attribute.variations.forEach(variation => {
                    const index = this.selectedVariations.findIndex(
                        v => v.item_id === item.id && v.item_attribute_variation_id === variation.id
                    );

                    if (isFullySelected) {
                        // Unselect all
                        if (index > -1) {
                            this.selectedVariations.splice(index, 1);
                        }
                    } else {
                        // Select all
                        if (index === -1) {
                            this.selectedVariations.push({
                                item_id: item.id,
                                item_attribute_variation_id: variation.id,
                                price: attribute.require_input_price == this.askEnum.YES ? 0 : undefined,
                            });
                        }
                    }
                });
            });
        },
        isCategoryAttributeFullySelected: function (categoryName, attributeId) {
            const items = this.groupedItems[categoryName] || [];
            const attribute = this.attributes.find(a => a.id === attributeId);
            
            if (!attribute || !attribute.variations || attribute.variations.length === 0 || items.length === 0) {
                return false;
            }

            // Calculate total possible selections for this category-attribute combination
            const totalPossibleSelections = items.length * attribute.variations.length;

            // Count current selections
            const itemIds = items.map(item => item.id);
            const variationIds = attribute.variations.map(v => v.id);
            const currentSelections = this.selectedVariations.filter(v => 
                itemIds.includes(v.item_id) && variationIds.includes(v.item_attribute_variation_id)
            ).length;

            return totalPossibleSelections > 0 && currentSelections === totalPossibleSelections;
        },
        applyDefaultPrices: function (attributeId) {
            const attribute = this.attributes.find(a => a.id === attributeId);
            if (!attribute || !attribute.variations || attribute.variations.length === 0) {
                return;
            }

            appService.confirmDialog(
                this.$t("message.are_you_sure"),
                this.$t("message.apply_default_price_warning"),
                "question",
                this.$t("button.yes_apply"),
                this.$t("button.cancel")
            ).then((result) => {
                if (result) {
                    // Apply default prices to all selected variations in this attribute column
                    attribute.variations.forEach(variation => {
                        this.selectedVariations.forEach(selectedVar => {
                            if (selectedVar.item_attribute_variation_id === variation.id) {
                                selectedVar.price = variation.price || 0;
                            }
                        });
                    });
                    alertService.success(this.$t("message.default_prices_applied_successfully"));
                }
            });
        },
        togglePriceEdit: function (itemId) {
            if (this.editingPriceItemId === itemId) {
                this.cancelPriceEdit();
            } else {
                const item = this.items.find(i => i.id === itemId);
                this.editingPriceItemId = itemId;
                this.editingPrice = item ? (item.price || 0) : 0;
            }
        },
        cancelPriceEdit: function () {
            this.editingPriceItemId = null;
            this.editingPrice = 0;
        },
        updateItemPrice: function (itemId) {
            const price = parseFloat(this.editingPrice);
            
            if (isNaN(price) || price < 0) {
                alertService.error(this.$t("message.please_enter_valid_price"));
                return;
            }

            this.loading.isActive = true;
            this.$store
                .dispatch("batchApplyVariation/updateItemPrice", { itemId, price })
                .then((res) => {
                    this.loading.isActive = false;
                    const updatedItem = res.data.item;
                    
                    // Update the item in the items array
                    const index = this.items.findIndex(i => i.id === itemId);
                    if (index !== -1) {
                        this.items.splice(index, 1, updatedItem);
                    }
                    
                    alertService.success(this.$t("message.item_price_updated_successfully"));
                    this.cancelPriceEdit();
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || err.message);
                });
        },
        toggleCategoryAttributeVariationSelection: function (categoryName, attributeId, variationId) {
            const items = this.groupedItems[categoryName] || [];
            const isFullySelected = this.isCategoryAttributeVariationFullySelected(categoryName, attributeId, variationId);
            const attribute = this.attributes.find(a => a.id === attributeId);

            if (!attribute) return;

            items.forEach(item => {
                const index = this.selectedVariations.findIndex(
                    v => v.item_id === item.id && v.item_attribute_variation_id === variationId
                );

                if (isFullySelected) {
                    // Unselect this variation for all items in category
                    if (index > -1) {
                        this.selectedVariations.splice(index, 1);
                    }
                } else {
                    // Select this variation for all items in category
                    if (index === -1) {
                        this.selectedVariations.push({
                            item_id: item.id,
                            item_attribute_variation_id: variationId,
                            price: attribute.require_input_price == this.askEnum.YES ? 0 : undefined,
                        });
                    }
                }
            });
        },
        isCategoryAttributeVariationFullySelected: function (categoryName, attributeId, variationId) {
            const items = this.groupedItems[categoryName] || [];
            
            if (items.length === 0) {
                return false;
            }

            // Check if all items in this category have this specific variation selected
            const itemIds = items.map(item => item.id);
            const selectedCount = this.selectedVariations.filter(v => 
                itemIds.includes(v.item_id) && v.item_attribute_variation_id === variationId
            ).length;

            return selectedCount === items.length;
        },
    },
};
</script>

<style scoped>
.sticky {
    position: sticky;
}
</style>
