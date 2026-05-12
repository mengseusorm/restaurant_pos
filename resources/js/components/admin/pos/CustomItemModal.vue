<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b flex-shrink-0">
                <h3 class="text-lg font-semibold text-heading">{{ $t('label.edit_order_item') }}</h3>
                <button @click.prevent="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-4 space-y-4 overflow-y-auto flex-1">
                <!-- Item Name / Custom Name -->
                <div>
                    <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.item_name') }}</label>
                    <div v-if="!isEditingName" class="flex items-center justify-between px-3 py-3 border border-gray-300 rounded-md bg-gray-50">
                        <span class="text-lg">{{ displayName }}</span>
                        <button v-if="canInputCustomName" @click.prevent="startEditingName" type="button" class="text-primary hover:text-primary-dark ml-2">
                            <i class="fa-solid fa-pen text-lg"></i>
                        </button>
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <input v-model="form.customName" type="text" ref="customNameInput"
                            class="flex-1 px-3 py-3 text-lg border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :placeholder="$t('label.enter_custom_name')"
                            @keyup.enter="stopEditingName" />
                        <button @click.prevent="stopEditingName" type="button" class="flex-shrink-0 w-10 h-10 flex items-center justify-center text-green-600 hover:text-green-700">
                            <i class="fa-solid fa-check text-xl"></i>
                        </button>
                        <button @click.prevent="cancelEditingName" type="button" class="flex-shrink-0 w-10 h-10 flex items-center justify-center text-red-600 hover:text-red-700">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Quantity and Unit Price Row -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.quantity') }}</label>
                        <div class="flex items-center gap-2">
                            <button @click.prevent="decreaseQuantity" type="button"
                                class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md font-bold text-xl">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input v-model.number="form.quantity" type="number" min="1" step="1"
                                @input="calculateTotal"
                                class="flex-1 px-3 py-3 text-lg border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-center"
                                :placeholder="$t('label.quantity')" />
                            <button @click.prevent="increaseQuantity" type="button"
                                class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-primary hover:bg-primary-dark text-white rounded-md font-bold text-xl">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.unit_price') }}</label>
                        <input v-model.number="form.unitPrice" type="number" min="0" step="0.01"
                            @input="calculateTotal"
                            :readonly="!canInputCustomUnitPrice"
                            :class="canInputCustomUnitPrice ? 'w-full px-3 py-3 text-lg border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent' : 'w-full px-3 py-3 text-lg border border-gray-300 rounded-md bg-gray-100 text-gray-600'"
                            :placeholder="$t('label.unit_price')" />
                    </div>
                </div>


                <!-- Add Discount Button / Discount Section -->
                <div v-if="permissionChecker('discount')">
                    <div v-if="!showDiscount">
                        <button @click.prevent="toggleDiscount" type="button"  class="w-full px-4 py-3 text-lg font-medium text-primary bg-primary/10 border border-primary rounded-md hover:bg-primary/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>{{ $t('label.add_discount') }}</span>
                        </button>
                    </div>
                    <div v-else>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-heading">{{ $t('label.discount') }}</h4>
                            <button @click.prevent="removeDiscount" type="button" class="text-red-600 hover:text-red-700">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>

                        <div class="space-y-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <!-- Discount Type -->
                            <div>
                                <label class="block text-sm font-medium text-heading mb-2">{{ $t('label.discount_type') }}:</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" :value="discountTypeEnum.PERCENTAGE" v-model="form.discountType" @change="onDiscountTypeChange"
                                            class="w-4 h-4 text-primary border-gray-300 focus:ring-2 focus:ring-primary" />
                                        <span class="ml-2 text-sm text-gray-700">{{ $t('label.percentage') }} {{ "(%)" }}</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" :value="discountTypeEnum.FIXED" v-model="form.discountType" @change="onDiscountTypeChange"
                                            class="w-4 h-4 text-primary border-gray-300 focus:ring-2 focus:ring-primary" />
                                        <span class="ml-2 text-sm text-gray-700">{{ $t('label.fixed') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Discount Value and Percentage Row -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-heading mb-2">
                                        {{ form.discountType == discountTypeEnum.PERCENTAGE ? $t('label.discount_percentage')  + " (%)" : $t('label.discount_amount') }}
                                    </label>
                                    <input v-model.number="form.discountInput" type="number" min="0" step="0.01"
                                        @input="onDiscountInput"
                                        :max="form.discountType == discountTypeEnum.PERCENTAGE ? 100 : form.subtotal"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
                                        :placeholder="form.discountType == discountTypeEnum.PERCENTAGE ? '0.00%' : '0.00'" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-heading mb-2">
                                        {{ form.discountType == discountTypeEnum.PERCENTAGE ? $t('label.discount_amount') : $t('label.discount_percentage') }}
                                    </label>
                                    <input v-model.number="form.discountCalculated" type="number" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600"
                                        :placeholder="form.discountType == discountTypeEnum.PERCENTAGE ? '0.00' : '0.00%'" />
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="form.errorMessage" class="text-red-500 text-sm">
                    {{ form.errorMessage }}
                </div>
                <!-- Subtotal and Total Display -->
                <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $t('label.subtotal') }}:</span>
                        <span class="font-medium">{{ currencyFormat(form.subtotal, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $t('label.discount') }}:</span>
                        <span class="font-medium text-red-600">-{{ currencyFormat(form.discount, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold border-t pt-2">
                        <span class="text-heading">{{ $t('label.total') }}:</span>
                        <span class="text-primary">{{ currencyFormat(form.total, setting.site_digit_after_decimal_point, branch.currency_id?.symbol, setting.site_currency_position) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-4 border-t flex-shrink-0">
                <button @click.prevent="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    {{ $t('button.cancel') }}
                </button>
                <button @click.prevent="saveChanges"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-dark">
                    {{ $t('button.save') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import discountTypeEnum from '../../../enums/modules/discountTypeEnum';
import statusEnum from '../../../enums/modules/statusEnum';
import appService from '../../../services/appService';
import alertService from '../../../services/alertService';

export default {
    name: 'CustomItemModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        cartItem: {
            type: Object,
            default: null
        },
        cartIndex: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            discountTypeEnum: discountTypeEnum,
            isEditingName: false,
            showDiscount: false,
            originalItemName: '',
            canInputCustomName: false,
            canInputCustomUnitPrice: false,
            form: {
                customName: '',
                quantity: 1,
                unitPrice: 0,
                subtotal: 0,
                discount: 0,
                discountPercentage: 0,
                discountType: discountTypeEnum.PERCENTAGE,
                discountInput: 0,
                discountCalculated: 0,
                total: 0,
                errorMessage: ''
            }
        };
    },
    computed: {
        branch() {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        setting() {
            return this.$store.getters['frontendSetting/lists'];
        },
        displayName() {
            return this.form.customName || this.originalItemName || this.$t('label.custom_item');
        }
    },
    watch: {
        show(newVal) {
            if (newVal && this.cartItem) {
                this.initializeForm();
            }
        },
        cartItem: {
            handler(newVal) {
                if (newVal && this.show) {
                    this.initializeForm();
                }
            },
            deep: true
        }
    },
    methods: {
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        initializeForm() {
            if (!this.cartItem) return;

            console.log("Cart Item:", this.cartItem);

            // Initialize permissions
            this.canInputCustomName = this.cartItem.can_input_custom_name === statusEnum.ACTIVE;
            this.canInputCustomUnitPrice = this.cartItem.can_input_custom_unit_price === statusEnum.ACTIVE;

            // Store original item name
            this.originalItemName = this.cartItem.item_name || this.cartItem.name || '';
            this.form.customName = this.cartItem.order_item_custom_name || '';
            this.isEditingName = false;

            this.form.quantity = this.cartItem.quantity || 1;
            this.form.unitPrice = this.cartItem.price || this.cartItem.convert_price || 0;
            this.form.discount = this.cartItem.discount || 0;
            this.form.discountPercentage = this.cartItem.discount_percentage || 0;

            // Show discount section if there's existing discount
            this.showDiscount = (this.form.discount > 0 || this.form.discountPercentage > 0);

            // Initialize discount type based on what's set
            if (this.form.discountPercentage > 0) {
                this.form.discountType = discountTypeEnum.PERCENTAGE;
                this.form.discountInput = this.form.discountPercentage;
            } else if (this.form.discount > 0) {
                this.form.discountType = discountTypeEnum.FIXED;
                this.form.discountInput = this.form.discount;
            } else {
                this.form.discountType = discountTypeEnum.PERCENTAGE;
                this.form.discountInput = 0;
            }

            this.calculateTotal();
        },
        calculateTotal() {
            this.form.errorMessage = '';

            // Calculate subtotal
            this.form.subtotal = this.form.quantity * this.form.unitPrice;

            // Calculate discount based on type
            if (this.form.discountType === discountTypeEnum.PERCENTAGE) {
                // Calculate discount from percentage
                const percentage = Math.min(Math.max(this.form.discountInput || 0, 0), 100);
                this.form.discount = (this.form.subtotal * percentage) / 100;
                this.form.discountPercentage = percentage;
                this.form.discountCalculated = this.form.discount;
            } else {
                // Calculate percentage from fixed discount
                const discountAmount = Math.min(Math.max(this.form.discountInput || 0, 0), this.form.subtotal);
                this.form.discount = discountAmount;
                this.form.discountPercentage = this.form.subtotal > 0 ? (discountAmount / this.form.subtotal) * 100 : 0;
                this.form.discountCalculated = this.form.discountPercentage;
            }

            // Calculate total
            this.form.total = Math.max(this.form.subtotal - this.form.discount, 0);

            // Round values
            this.form.subtotal = parseFloat(this.form.subtotal.toFixed(2));
            this.form.discount = parseFloat(this.form.discount.toFixed(2));
            this.form.discountPercentage = parseFloat(this.form.discountPercentage.toFixed(2));
            this.form.discountCalculated = parseFloat(this.form.discountCalculated.toFixed(2));
            this.form.total = parseFloat(this.form.total.toFixed(2));
        },
        onDiscountTypeChange() {
            // Reset discount input when switching types
            this.form.discountInput = 0;
            this.form.discountCalculated = 0;
            this.calculateTotal();
        },
        onDiscountInput() {
            this.calculateTotal();
        },
        decreaseQuantity() {
            if (this.form.quantity > 1) {
                this.form.quantity--;
                this.calculateTotal();
            }
        },
        increaseQuantity() {
            this.form.quantity++;
            this.calculateTotal();
        },
        startEditingName() {
            this.isEditingName = true;
            // If customName is empty, initialize with original item name
            if (!this.form.customName) {
                this.form.customName = this.originalItemName;
            }
            this.$nextTick(() => {
                this.$refs.customNameInput?.focus();
            });
        },
        stopEditingName() {
            this.isEditingName = false;
        },
        cancelEditingName() {
            this.form.customName = this.cartItem.order_item_custom_name || '';
            this.isEditingName = false;
        },
        permissionChecker(e) {
            console.log('e',e);
            return appService.permissionChecker(e);
        },
        toggleDiscount() {
            if(this.permissionChecker('discount')){
                this.showDiscount = true;
            }else{
                alertService.error(this.$t('message.permission_denied'));
            }

        },
        removeDiscount() {
            this.showDiscount = false;
            this.form.discountInput = 0;
            this.form.discountCalculated = 0;
            this.form.discount = 0;
            this.form.discountPercentage = 0;
            this.calculateTotal();
        },
        closeModal() {
            this.form.errorMessage = '';
            this.isEditingName = false;
            this.$emit('close');
        },
        saveChanges() {
            // Validate form
            if (this.form.quantity <= 0) {
                this.form.errorMessage = this.$t('message.quantity_must_be_greater_than_zero');
                return;
            }

            if (this.form.unitPrice < 0) {
                this.form.errorMessage = this.$t('message.unit_price_cannot_be_negative');
                return;
            }

            // Emit save event with form data
            this.$emit('save', {
                index: this.cartIndex,
                customName: this.form.customName,
                quantity: this.form.quantity,
                unitPrice: this.form.unitPrice,
                discount: this.form.discount,
                discountPercentage: this.form.discountPercentage,
                total: this.form.total
            });

            this.closeModal();
        }
    }
};
</script>
