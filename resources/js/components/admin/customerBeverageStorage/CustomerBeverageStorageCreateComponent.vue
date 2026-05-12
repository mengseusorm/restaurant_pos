<template>
    <div>
        <button @click="openModal" type="button" class="db-btn h-[38px] text-white bg-primary">
            <i class="lab lab-line-add-circle"></i>
            <span>{{ $t('button.add_customer_beverage_storage') }}</span>
        </button>

        <div id="customerBeverageStorageModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title">{{ $t('menu.customer_beverage_storage') }}</h3>
                    <button @click="reset" type="button" class="modal-close">
                        <i class="lab lab-line-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="save">
                        <div class="form-row">
                            <div class="form-col-12 sm:form-col-6">
                                <label for="customer_name" class="db-field-title required">
                                    {{ $t('label.customer_name') }}
                                </label>
                                <input v-model="props.form.customer_name" v-bind:class="errors.customer_name ? 'invalid' : ''"
                                    type="text" id="customer_name" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.customer_name">
                                    {{ errors.customer_name[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="customer_phone" class="db-field-title required">
                                    {{ $t('label.customer_phone') }}
                                </label>
                                <input v-model="props.form.customer_phone" v-bind:class="errors.customer_phone ? 'invalid' : ''"
                                    type="text" id="customer_phone" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.customer_phone">
                                    {{ errors.customer_phone[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="beverage_name" class="db-field-title required">
                                    {{ $t('label.beverage_name') }}
                                </label>
                                <input v-model="props.form.beverage_name" v-bind:class="errors.beverage_name ? 'invalid' : ''"
                                    type="text" id="beverage_name" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.beverage_name">
                                    {{ errors.beverage_name[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="quantity" class="db-field-title">
                                    {{ $t('label.quantity') }}
                                </label>
                                <input v-model="props.form.quantity" v-bind:class="errors.quantity ? 'invalid' : ''"
                                    type="number" step="0.01" id="quantity" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.quantity">
                                    {{ errors.quantity[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="unit" class="db-field-title required">
                                    {{ $t('label.unit') }}
                                </label>
                                <input v-model="props.form.unit" v-bind:class="errors.unit ? 'invalid' : ''"
                                    type="text" id="unit" class="db-field-control"
                                    placeholder="e.g., bottle, can, liter" />
                                <small class="db-field-alert" v-if="errors.unit">
                                    {{ errors.unit[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="storage_location" class="db-field-title">
                                    {{ $t('label.storage_location') }}
                                </label>
                                <input v-model="props.form.storage_location" v-bind:class="errors.storage_location ? 'invalid' : ''"
                                    type="text" id="storage_location" class="db-field-control"
                                    placeholder="e.g., Fridge A, Shelf B1" />
                                <small class="db-field-alert" v-if="errors.storage_location">
                                    {{ errors.storage_location[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="store_date" class="db-field-title required">
                                    {{ $t('label.store_date') }}
                                </label>
                                <Datepicker
                                    v-model="store_date"
                                    :enableTimePicker="false"
                                    autoApply
                                    :format="'yyyy-MM-dd'"
                                    :monthChangeOnScroll="false"
                                    :class="errors.store_date ? 'invalid' : ''"
                                    placeholder="Select date"
                                />
                                <small class="db-field-alert" v-if="errors.store_date">
                                    {{ errors.store_date[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="expiry_date" class="db-field-title">
                                    {{ $t('label.expiry_date') }}
                                </label>
                                <Datepicker
                                    v-model="expiry_date"
                                    :enableTimePicker="false"
                                    autoApply
                                    :format="'yyyy-MM-dd'"
                                    :monthChangeOnScroll="false"
                                    :class="errors.expiry_date ? 'invalid' : ''"
                                    placeholder="Auto-calculated (+14 days)"
                                />
                                <small class="text-xs text-gray-500 mt-1">
                                    {{ $t('message.auto_calculated_14_days') }}
                                </small>
                                <small class="db-field-alert" v-if="errors.expiry_date">
                                    {{ errors.expiry_date[0] }}
                                </small>
                            </div>



                            <div class="form-col-12 sm:form-col-6">
                                <label for="status" class="db-field-title required">
                                    {{ $t('label.status') }}
                                </label>
                                <vue-select class="db-field-control f-b-custom-select" id="status"
                                    v-model="props.form.status" :options="statusOptions" label-by="name" value-by="id"
                                    :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--"
                                    v-bind:class="errors.status ? 'invalid' : ''" />
                                <small class="db-field-alert" v-if="errors.status">
                                    {{ errors.status[0] }}
                                </small>
                            </div>

                            <div class="form-col-12">
                                <label for="notes" class="db-field-title">
                                    {{ $t('label.notes') }}
                                </label>
                                <textarea v-model="props.form.notes" v-bind:class="errors.notes ? 'invalid' : ''"
                                    id="notes" class="db-field-control" rows="3"></textarea>
                                <small class="db-field-alert" v-if="errors.notes">
                                    {{ errors.notes[0] }}
                                </small>
                            </div>

                            <div class="form-col-12">
                                <label for="image" class="db-field-title">
                                    {{ $t('label.image') }}
                                </label>
                                <input @change="changeImage" type="file" id="image" class="db-field-control"
                                    accept="image/png, image/jpeg, image/jpg" ref="imageProperty"
                                    v-bind:class="errors.image ? 'invalid' : ''" />
                                <small class="db-field-alert" v-if="errors.image">
                                    {{ errors.image[0] }}
                                </small>
                            </div>

                            <div class="form-col-12" v-if="props.form.image || imagePreview">
                                <img :src="imagePreview || props.form.image" alt="Preview" class="w-32 h-32 object-cover rounded">
                            </div>

                            <div class="form-col-12">
                                <div class="modal-btns">
                                    <button type="button" class="modal-btn-cancel" @click="reset">
                                        {{ $t('button.cancel') }}
                                    </button>
                                    <button type="submit" class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-line-save"></i>
                                        <span>{{ $t('button.save') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";
import customerBeverageStorageStatusEnum from "../../../enums/modules/customerBeverageStorageStatusEnum";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "CustomerBeverageStorageCreateComponent",
    components: {
        Datepicker,
    },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            errors: {},
            imagePreview: null,
            store_date: null,
            expiry_date: null,
            statusOptions: [
                { id: customerBeverageStorageStatusEnum.STORED, name: this.$t("label.stored") },
                { id: customerBeverageStorageStatusEnum.CLAIMED, name: this.$t("label.claimed") },
                { id: customerBeverageStorageStatusEnum.EXPIRED, name: this.$t("label.expired") },
                { id: customerBeverageStorageStatusEnum.DISPOSED, name: this.$t("label.disposed") }
            ]
        }
    },
    computed: {
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        }
    },
    watch: {
        store_date(newVal) {
            if (newVal) {
                const date = new Date(newVal);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                this.props.form.store_date = `${year}-${month}-${day}`;
            } else {
                this.props.form.store_date = "";
            }
        },
        expiry_date(newVal) {
            if (newVal) {
                const date = new Date(newVal);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                this.props.form.expiry_date = `${year}-${month}-${day}`;
            } else {
                this.props.form.expiry_date = "";
            }
        }
    },
    methods: {
        openModal: function () {
            // Set default store_date to today
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

            this.store_date = today;
            this.props.form.store_date = `${year}-${month}-${day}`;

            // Set default status to STORED
            this.props.form.status = customerBeverageStorageStatusEnum.STORED;

            appService.modalShow("#customerBeverageStorageModal");
        },
        changeImage: function (e) {
            this.props.form.image = e.target.files[0];
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        },
        reset: function () {
            appService.modalHide("#customerBeverageStorageModal");
            this.$store.dispatch('customerBeverageStorage/reset').then().catch();
            this.errors = {};
            this.imagePreview = null;
            this.store_date = null;
            this.expiry_date = null;
            const currentBranchId = this.defaultAccess?.branch_id || null;

            // Get today's date for store_date
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

            this.$props.props.form = {
                customer_name: "",
                customer_phone: "",
                beverage_name: "",
                quantity: "",
                original_quantity: "",
                unit: "bottle",
                store_date: `${year}-${month}-${day}`,
                expiry_date: "",
                status: customerBeverageStorageStatusEnum.STORED,
                storage_location: "",
                claimed_date: "",
                disposed_date: "",
                disposed_reason: "",
                notes: "",
                branch_id: currentBranchId,
                image: ""
            };
            if (this.$refs.imageProperty) {
                this.$refs.imageProperty.value = null;
            }
        },
        save: function () {
            try {
                const fd = new FormData();
                fd.append('customer_name', this.props.form.customer_name);
                fd.append('customer_phone', this.props.form.customer_phone);
                fd.append('beverage_name', this.props.form.beverage_name);
                fd.append('quantity', this.props.form.quantity);
                fd.append('unit', this.props.form.unit);
                fd.append('store_date', this.props.form.store_date);
                fd.append('status', this.props.form.status);
                if (this.props.form.expiry_date) {
                    fd.append('expiry_date', this.props.form.expiry_date);
                }
                if (this.props.form.storage_location) {
                    fd.append('storage_location', this.props.form.storage_location);
                }
                if (this.props.form.notes) {
                    fd.append('notes', this.props.form.notes);
                }
                if (this.props.form.image) {
                    fd.append('image', this.props.form.image);
                }
                if (this.props.form.branch_id) {
                    fd.append('branch_id', this.props.form.branch_id);
                }

                const tempId = this.$store.getters['customerBeverageStorage/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('customerBeverageStorage/save', {
                    form: fd,
                    search: this.props.search,
                    id: tempId
                }).then((res) => {
                    appService.modalHide("#customerBeverageStorageModal");
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t('menu.customer_beverage_storage')
                    );

                    // Get today's date for store_date
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');

                    this.props.form = {
                        customer_name: "",
                        customer_phone: "",
                        beverage_name: "",
                        quantity: "",
                        original_quantity: "",
                        unit: "bottle",
                        store_date: `${year}-${month}-${day}`,
                        expiry_date: "",
                        status: customerBeverageStorageStatusEnum.STORED,
                        storage_location: "",
                        claimed_date: "",
                        disposed_date: "",
                        disposed_reason: "",
                        notes: "",
                        branch_id: this.defaultAccess?.branch_id || null,
                        image: ""
                    };
                    this.imagePreview = null;
                    this.store_date = null;
                    this.expiry_date = null;
                    this.errors = {};
                    if (this.$refs.imageProperty) {
                        this.$refs.imageProperty.value = null;
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                    alertService.error(err.response.data.message);
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        }
    }
}
</script>
