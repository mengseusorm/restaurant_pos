<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />
    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t("menu.expenses") }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save" @keydown.enter.prevent>
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="expense_date" class="db-field-title required">{{ $t("label.expense_date") }}</label>
                        <input v-model="props.form.expense_date" v-bind:class="errors.expense_date ? 'invalid' : ''" type="date"
                            id="expense_date" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.expense_date">{{ errors.expense_date[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="expense_type_id" class="db-field-title required">{{ $t("label.expense_type") }}</label>
                        <vue-select class="db-field-control f-b-custom-select" id="expense_type_id"
                            v-bind:class="errors.expense_type_id ? 'invalid' : ''"
                            v-model="props.form.expense_type_id"
                            :options="expenseTypes"
                            label-by="name"
                            value-by="id"
                            :closeOnSelect="true"
                            :searchable="true"
                            :clearOnClose="true"
                            placeholder="--"
                            search-placeholder="--" />
                        <small class="db-field-alert" v-if="errors.expense_type_id">{{ errors.expense_type_id[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="amount" class="db-field-title required">{{ $t("label.amount") }}</label>
                        <input v-model="props.form.amount" v-bind:class="errors.amount ? 'invalid' : ''" type="number"
                            id="amount" class="db-field-control" step="0.01" min="0">
                        <small class="db-field-alert" v-if="errors.amount">{{ errors.amount[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="payment_method_id" class="db-field-title required">{{ $t("label.payment_method") }}</label>
                        <vue-select class="db-field-control f-b-custom-select" id="payment_method_id"
                            v-bind:class="errors.payment_method_id ? 'invalid' : ''"
                            v-model="props.form.payment_method_id"
                            :options="paymentMethods"
                            label-by="name"
                            value-by="id"
                            :closeOnSelect="true"
                            :searchable="true"
                            :clearOnClose="true"
                            placeholder="--"
                            search-placeholder="--" />
                        <small class="db-field-alert" v-if="errors.payment_method_id">{{ errors.payment_method_id[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="paid_to" class="db-field-title">{{ $t("label.paid_to") }}</label>
                        <input v-model="props.form.paid_to" v-bind:class="errors.paid_to ? 'invalid' : ''" type="text"
                            id="paid_to" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.paid_to">{{ errors.paid_to[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="reference_no" class="db-field-title">{{ $t("label.reference_no") }}</label>
                        <input v-model="props.form.reference_no" v-bind:class="errors.reference_no ? 'invalid' : ''" type="text"
                            id="reference_no" class="db-field-control">
                        <small class="db-field-alert" v-if="errors.reference_no">{{ errors.reference_no[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-12">
                        <label class="db-field-title">{{ $t("label.receipt_image") }}</label>
                        <input @change="changeImage" v-bind:class="errors.receipt_image ? 'invalid' : ''" id="receipt_image" type="file"
                            class="db-field-control" ref="imageProperty" accept="image/png, image/jpeg, image/jpg">
                        <small class="db-field-alert" v-if="errors.receipt_image">{{ errors.receipt_image[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-12">
                        <label for="description" class="db-field-title">{{ $t("label.description") }}</label>
                        <textarea v-model="props.form.description" v-bind:class="errors.description ? 'invalid' : ''"
                            id="description" rows="3" class="db-field-control"></textarea>
                        <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-12">
                        <div class="p-3 pt-2 rounded-lg border border-[#D9DBE9]">
                            <label class="db-field-title">{{ $t("label.status") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input type="radio" id="pending" value="pending" v-model="props.form.status" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="pending" class="db-field-label">{{ $t('label.pending') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input type="radio" id="approved" value="approved" v-model="props.form.status" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="approved" class="db-field-label">{{ $t('label.approved') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input type="radio" id="rejected" value="rejected" v-model="props.form.status" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="rejected" class="db-field-label">{{ $t('label.rejected') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

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
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import SmSidebarModalCreateComponent from "../../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import statusEnum from "../../../../enums/modules/statusEnum";

export default {
    name: "ExpenseCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            image: "",
            errors: {}
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_expense') };
        },
        expenseTypes: function () {
            return this.$store.getters['expenseType/lists'];
        },
        paymentMethods: function () {
            return this.$store.getters['expensePaymentMethod/lists'];
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch('expenseType/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.$store.dispatch('expensePaymentMethod/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.loading.isActive = false;
        this.reset();
    },
    methods: {
        changeImage: function (e) {
            this.image = e.target.files[0];
        },
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch('expense/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                expense_date: new Date().toISOString().split('T')[0],
                expense_type_id: null,
                amount: "",
                payment_method_id: null,
                description: "",
                paid_to: "",
                reference_no: "",
                status: "pending"
            };
            if (this.image) {
                this.image = "";
                this.$refs.imageProperty.value = null;
            }
        },
        save: function () {
            try {
                this.loading.isActive = true;

                const fd = new FormData();
                fd.append('branch_id', this.defaultAccess.branch_id);
                fd.append('expense_date', this.props.form.expense_date);
                fd.append('expense_type_id', this.props.form.expense_type_id || '');
                fd.append('amount', this.props.form.amount);
                fd.append('payment_method_id', this.props.form.payment_method_id || '');
                fd.append('description', this.props.form.description || '');
                fd.append('paid_to', this.props.form.paid_to || '');
                fd.append('reference_no', this.props.form.reference_no || '');
                fd.append('status', this.props.form.status);

                if (this.image) {
                    fd.append('receipt_image', this.image);
                }

                const tempId = this.$store.getters['expense/temp'].temp_id;

                this.$store.dispatch('expense/save', {
                    form: fd,
                    search: this.props.search
                }).then((res) => {
                    appService.sideDrawerHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.expenses'));
                    this.reset();
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
