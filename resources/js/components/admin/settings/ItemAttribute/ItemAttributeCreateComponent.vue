<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog" :class="{ 'max-w-5xl': isEditMode && variations.length > 0 }">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.item_attributes") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="form-col-12 sm:form-col-12">
                            <label for="name" class="db-field-title required">{{
                                $t("label.name")
                                }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{
                                errors.name[0]
                                }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t("label.status") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active" class="db-field-label">{{ $t("label.active") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status"
                                            type="radio" id="inactive" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive" class="db-field-label">{{ $t("label.inactive") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="require_input_price_yes">{{
                                $t("label.require_input_price") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.YES" v-model="props.form.require_input_price"
                                            id="require_input_price_yes" type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="require_input_price_yes" class="db-field-label">{{ $t("label.yes")
                                        }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.NO" v-model="props.form.require_input_price"
                                            type="radio" id="require_input_price_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="require_input_price_no" class="db-field-label">{{ $t("label.no")
                                        }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
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

                <!-- Variations Management Section (only shown when editing) -->
                <div v-if="isEditMode" class="mt-6 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold">{{ $t("label.variations") }}</h4>
                        <button @click="openVariationForm()" type="button"
                            class="db-btn py-2 text-white bg-primary text-sm">
                            <i class="lab lab-add"></i>
                            <span>{{ $t("button.add_variation") }}</span>
                        </button>
                    </div>

                    <!-- Variation Form -->
                    <div v-if="showVariationForm" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="form-row">
                            <div class="form-col-12 sm:form-col-4">
                                <label for="variation_name" class="db-field-title required">{{ $t("label.name")
                                    }}</label>
                                <input v-model="variationForm.name" v-bind:class="variationErrors.name ? 'invalid' : ''"
                                    type="text" id="variation_name" class="db-field-control" />
                                <small class="db-field-alert" v-if="variationErrors.name">{{ variationErrors.name[0]
                                    }}</small>
                            </div>

                            <div class="form-col-12 sm:form-col-4">
                                <label for="variation_price" class="db-field-title required">{{ $t("label.price")
                                    }}</label>
                                <input v-model="variationForm.price"
                                    v-bind:class="variationErrors.price ? 'invalid' : ''" type="number" step="0.01"
                                    id="variation_price" class="db-field-control" />
                                <small class="db-field-alert" v-if="variationErrors.price">{{ variationErrors.price[0]
                                    }}</small>
                            </div>

                            <div class="form-col-12 sm:form-col-4">
                                <label class="db-field-title required">{{ $t("label.status") }}</label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input :value="enums.statusEnum.ACTIVE" v-model="variationForm.status"
                                                id="variation_active" type="radio" class="custom-radio-field" />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="variation_active" class="db-field-label">{{ $t("label.active")
                                            }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input :value="enums.statusEnum.INACTIVE" v-model="variationForm.status"
                                                type="radio" id="variation_inactive" class="custom-radio-field" />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="variation_inactive" class="db-field-label">{{ $t("label.inactive")
                                            }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-col-12">
                                <label for="variation_caution" class="db-field-title">{{ $t("label.caution") }}</label>
                                <textarea v-model="variationForm.caution" id="variation_caution"
                                    class="db-field-control" rows="2"></textarea>
                            </div>

                            <div class="form-col-12">
                                <div class="flex gap-2 justify-end">
                                    <button @click="cancelVariationForm" type="button" class="modal-btn-outline">
                                        <i class="lab lab-close"></i>
                                        <span>{{ $t("button.cancel") }}</span>
                                    </button>
                                    <button @click="saveVariation" type="button"
                                        class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-save"></i>
                                        <span>{{ $t("button.save") }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variations Table -->
                    <div class="db-table-responsive">
                        <table class="db-table stripe" v-if="variations.length > 0">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th">{{ $t("label.name") }}</th>
                                    <th class="db-table-head-th">{{ $t("label.default_addition_price") }}</th>
                                    <th class="db-table-head-th">{{ $t("label.status") }}</th>
                                    <th class="db-table-head-th">{{ $t("label.caution") }}</th>
                                    <th class="db-table-head-th">{{ $t("label.action") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="variation in variations" :key="variation.id">
                                    <td class="db-table-body-td">{{ variation.name }}</td>
                                    <td class="db-table-body-td">{{ variation.convert_price || variation.price }}</td>
                                    <td class="db-table-body-td">
                                        <span :class="statusClass(variation.status)">
                                            {{ enums.statusEnumArray[variation.status] }}
                                        </span>
                                    </td>
                                    <td class="db-table-body-td">{{ variation.caution || '-' }}</td>
                                    <td class="db-table-body-td">
                                        <div class="flex justify-start items-center gap-1.5">
                                            <button @click="editVariation(variation)" type="button"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button @click="deleteVariation(variation.id)" type="button"
                                                class="text-red-600 hover:text-red-800 ms-2">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-gray-500 text-center py-4">{{ $t("message.no_variations") }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import askEnum from "../../../../enums/modules/askEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import axios from "axios";

export default {
    name: "ItemAttributeCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ["props"],
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
                askEnum: askEnum,
                askEnumArray: {
                    [askEnum.YES]: this.$t("label.yes"),
                    [askEnum.NO]: this.$t("label.no"),
                },
            },
            errors: {},
            variations: [],
            showVariationForm: false,
            variationForm: {
                id: null,
                name: "",
                price: 0,
                status: statusEnum.ACTIVE,
                caution: "",
            },
            variationErrors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_item_attribute') };
        },
        isEditMode: function () {
            const tempId = this.$store.getters["itemAttribute/temp"].temp_id;
            return tempId !== null;
        },
        currentAttributeId: function () {
            return this.$store.getters["itemAttribute/temp"].temp_id;
        },
    },
    watch: {
        currentAttributeId: {
            immediate: true,
            handler(newId) {
                if (newId) {
                    this.loadVariations();
                } else {
                    this.variations = [];
                }
            }
        }
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("itemAttribute/reset").then().catch();
            this.errors = {};
            this.variations = [];
            this.showVariationForm = false;
            this.resetVariationForm();
            this.$props.props.form = {
                name: "",
                status: statusEnum.ACTIVE,
                require_input_price: askEnum.NO,
            };
        },

        save: function () {
            try {
                const tempId = this.$store.getters["itemAttribute/temp"].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch("itemAttribute/save", this.props).then((res) => {
                    this.loading.isActive = false;
                    
                    // If creating new attribute, close modal
                    if (tempId === null) {
                        appService.modalHide();
                        alertService.successFlip(0, this.$t("menu.item_attributes"));
                        this.props.form = {
                            name: "",
                            status: statusEnum.ACTIVE,
                            require_input_price: askEnum.NO,
                        };
                        this.errors = {};
                    } else {
                        // If editing, just show success message and reload list
                        alertService.successFlip(1, this.$t("menu.item_attributes"));
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },

        // Variation Management Methods
        loadVariations: function () {
            if (!this.currentAttributeId) return;
            
            this.loading.isActive = true;
            axios.get(`/admin/setting/item-attribute-variation?item_attribute_id=${this.currentAttributeId}`)
                .then((res) => {
                    this.variations = res.data.data;
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response?.data?.message || "Failed to load variations");
                });
        },

        openVariationForm: function () {
            this.resetVariationForm();
            this.showVariationForm = true;
        },

        resetVariationForm: function () {
            this.variationForm = {
                id: null,
                name: "",
                price: 0,
                status: statusEnum.ACTIVE,
                caution: "",
            };
            this.variationErrors = {};
        },

        cancelVariationForm: function () {
            this.showVariationForm = false;
            this.resetVariationForm();
        },

        saveVariation: function () {
            if (!this.currentAttributeId) {
                alertService.error("Please save the item attribute first");
                return;
            }

            const data = {
                item_attribute_id: this.currentAttributeId,
                name: this.variationForm.name,
                price: this.variationForm.price,
                status: this.variationForm.status,
                caution: this.variationForm.caution,
            };

            this.loading.isActive = true;
            
            const request = this.variationForm.id
                ? axios.put(`/admin/setting/item-attribute-variation/${this.variationForm.id}`, data)
                : axios.post('/admin/setting/item-attribute-variation', data);

            request
                .then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        this.variationForm.id ? 1 : 0,
                        this.$t("label.variation")
                    );
                    this.loadVariations();
                    this.cancelVariationForm();
                    // Reload the main list to update the variations badges
                    this.$store.dispatch("itemAttribute/lists", this.$parent.props.search);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.variationErrors = err.response?.data?.errors || {};
                    alertService.error(err.response?.data?.message || "Failed to save variation");
                });
        },

        editVariation: function (variation) {
            this.variationForm = {
                id: variation.id,
                name: variation.name,
                price: variation.price,
                status: variation.status,
                caution: variation.caution || "",
            };
            this.showVariationForm = true;
        },

        deleteVariation: function (id) {
            appService.destroyConfirmation().then((res) => {
                this.loading.isActive = true;
                axios.delete(`/admin/setting/item-attribute-variation/${id}`)
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t("label.variation"));
                        this.loadVariations();
                        // Reload the main list to update the variations badges
                        this.$store.dispatch("itemAttribute/lists", this.$parent.props.search);
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response?.data?.message || "Failed to delete variation");
                    });
            }).catch(() => {
                // User cancelled
            });
        },
    },
};
</script>