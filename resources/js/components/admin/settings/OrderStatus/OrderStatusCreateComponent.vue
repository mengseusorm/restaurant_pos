<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.order_statuses") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <BranchSelectComponent
                                v-model="props.form.branch_id"
                                :label="$t('label.branch')"
                                :select-class="errors.branch_id ? 'db-field-control f-b-custom-select invalid' : 'db-field-control f-b-custom-select'"
                                :error="errors.branch_id ? errors.branch_id[0] : null" />
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="status_code" class="db-field-title required">{{  
                                $t("label.status_code")
                            }}</label>
                            <input v-model="props.form.status_code" v-bind:class="errors.status_code ? 'invalid' : ''"
                                type="number" id="status_code" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.status_code">{{  
                                errors.status_code[0]
                                }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
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
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input v-model="props.form.name_kh" v-bind:class="errors.name_kh ? 'invalid' : ''"
                                type="text" id="name_kh" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input v-model="props.form.name_cn" v-bind:class="errors.name_cn ? 'invalid' : ''"
                                type="text" id="name_cn" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_en" class="db-field-title">{{ $t("label.name_en") }}</label>
                            <input v-model="props.form.name_en" v-bind:class="errors.name_en ? 'invalid' : ''"
                                type="text" id="name_en" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_en">{{ errors.name_en[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="status_order" class="db-field-title">{{
                                $t("label.status_order")
                                }}</label>
                            <input v-model="props.form.status_order" v-bind:class="errors.status_order ? 'invalid' : ''"
                                type="number" id="status_order" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.status_order">{{ errors.status_order[0]
                                }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t('label.status') }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active"
                                            type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active" class="db-field-label">{{ $t('label.active') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status"
                                            type="radio" id="inactive" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive" class="db-field-label">{{ $t('label.inactive') }}</label>
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
            </div>
        </div>
    </div>
</template>
<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import BranchSelectComponent from "../../components/BranchSelectComponent";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import statusEnum from "../../../../enums/modules/statusEnum";

export default {
    name: "OrderStatusCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent, BranchSelectComponent },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
            },
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_order_status') };
        },
    },
    methods: {
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("orderStatus/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                branch_id: 0,
                status_code: "",
                name: "",
                name_kh: "",
                name_cn: "",
                name_en: "",
                status_order: 0,
                status: statusEnum.ACTIVE,
            };
        },

        save: function () {
            try {
                const tempId = this.$store.getters["orderStatus/temp"].temp_id;
                this.loading.isActive = true;

                this.$store.dispatch("orderStatus/save", this.props).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.order_statuses")
                    );
                    this.props.form = {
                        status_code: "",
                        name: "",
                        name_kh: "",
                        name_cn: "",
                        name_en: "",
                        status_order: 0,
                        status: statusEnum.ACTIVE,
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
    },
};
</script>
