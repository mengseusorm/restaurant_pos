<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.order_types") }}</h3>
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
                            <label for="type_code" class="db-field-title required">{{ $t("label.type_code") }}</label>
                            <input type="number" v-model="props.form.type_code" id="type_code"
                                class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.type_code">{{ errors.type_code[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input type="text" v-model="props.form.name" id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input type="text" v-model="props.form.name_kh" id="name_kh" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input type="text" v-model="props.form.name_cn" id="name_cn" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_en" class="db-field-title">{{ $t("label.name_en") }}</label>
                            <input type="text" v-model="props.form.name_en" id="name_en" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_en">{{ errors.name_en[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="type_order" class="db-field-title required">{{ $t("label.type_order") }}</label>
                            <input type="number" v-model="props.form.type_order" id="type_order"
                                class="db-field-control" min="0" />
                            <small class="db-field-alert" v-if="errors.type_order">{{ errors.type_order[0] }}</small>
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
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
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
    </div>
</template>

<script>
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import BranchSelectComponent from "../../components/BranchSelectComponent";
import appService from "../../../../services/appService";
import alertService from "../../../../services/alertService";
import statusEnum from "../../../../enums/modules/statusEnum";

export default {
    name: "OrderTypeCreateComponent",
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
            return { title: this.$t('button.add_order_type') };
        },
    },
    methods: {
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("orderType/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                branch_id: 0,
                type_code: "",
                name: "",
                name_kh: "",
                name_cn: "",
                name_en: "",
                type_order: 0,
                status: statusEnum.ACTIVE,
            };
        },
        save: function () {
            try {
                const fd = new FormData();
                fd.append("branch_id", this.props.form.branch_id ?? 0);
                fd.append("type_code", this.props.form.type_code);
                fd.append("name", this.props.form.name);
                fd.append("name_kh", this.props.form.name_kh ?? "");
                fd.append("name_cn", this.props.form.name_cn ?? "");
                fd.append("name_en", this.props.form.name_en ?? "");
                fd.append("type_order", this.props.form.type_order);
                fd.append("status", this.props.form.status);

                const tempId = this.$store.getters["orderType/temp"].temp_id;
                this.loading.isActive = true;
                this.$store
                    .dispatch("orderType/save", {
                        form: fd,
                        search: this.props.search,
                    })
                    .then((res) => {
                        appService.modalHide();
                        this.loading.isActive = false;
                        alertService.successFlip(
                            tempId === null ? 0 : 1,
                            this.$t("menu.order_types")
                        );
                        this.props.form = {
                            branch_id: 0,
                            type_code: "",
                            name: "",
                            name_kh: "",
                            name_cn: "",
                            name_en: "",
                            type_order: 0,
                            status: statusEnum.ACTIVE,
                        };
                        this.errors = {};
                    })
                    .catch((err) => {
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
