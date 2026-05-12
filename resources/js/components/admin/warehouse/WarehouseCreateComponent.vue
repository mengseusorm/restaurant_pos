<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="warehouseCreate" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('label.warehouse') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-12">
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-12" v-if="branches.length > 1 && authBranch === 0">
                            <label class="db-field-title required" for="branch_id">{{
                                $t("label.branch_id")
                                }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            v-bind:class="errors.branch_id ? 'is-invalid' : ''" id="current_branch"
                                            :value="defaultAccess.branch_id" :checked="
                                                props.form.branch_id === '' ||
                                                props.form.branch_id === null ||
                                                props.form.branch_id === defaultAccess.branch_id
                                            " />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="current_branch" class="db-field-label">{{
                                        $t("label.current_branch")
                                        }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            v-bind:class="errors.branch_id ? 'is-invalid' : ''" id="all_branch"
                                            :value="0" :checked="props.form.branch_id === 0" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="all_branch" class="db-field-label">{{
                                        $t("label.all_branch")
                                        }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-col-12">
                            <label for="description" class="db-field-title">{{$t("label.description")}}</label>
                            <textarea v-model="props.form.description"
                                v-bind:class="errors.description ? 'invalid' : ''" id="description"
                                class="db-field-control"></textarea>
                            <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t('button.save') }}</span>
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
import SmModalCreateComponent from "../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import PrintComponent from "../components/buttons/export/PrintComponent";

export default {
    name: "WarehouseCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent, PrintComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            errors: {},
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_stock_warehouse') };
        },
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
        branches: function () {
            return this.$store.getters["branch/lists"];
        },
        authBranch: function () {
            return this.$store.getters.authBranchId;
        },
        roles: function () {
            return this.$store.getters["role/lists"];
        },
        authInfo: function () {
            return this.$store.getters.authInfo.id;
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("defaultAccess/show");
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
        this.$store.dispatch('company/lists').then(companyRes => {
            this.$store.dispatch('countryCode/show', companyRes.data.data.company_country_code).then(res => {
                if (this.props.form.country_code === "") {
                    this.props.form.country_code = res.data.data.calling_code;
                    this.country_code = res.data.data.calling_code;
                }
                this.flag = res.data.data.flag_emoji;
                this.loading.isActive = false;

            }).catch((err) => {
                this.loading.isActive = false;

            });
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        save: function () {
            try {
                const fd = new FormData();
                fd.append('name', this.props.form.name);
                fd.append('description', this.props.form.description);

                // Handle branch_id logic: use selected branch or default to 1
                let branchId = 1; // Default fallback
                if (this.branches.length > 1 && this.authBranch === 0) {
                    // If multiple branches and user can access all branches
                    if (this.props.form.branch_id === 0) {
                        branchId = 1; // All branches - use default branch 1
                    } else if (this.props.form.branch_id && this.props.form.branch_id !== null && this.props.form.branch_id !== '') {
                        branchId = this.props.form.branch_id; // Use selected branch
                    } else if (this.defaultAccess && this.defaultAccess.branch_id) {
                        branchId = this.defaultAccess.branch_id; // Use current user's branch
                    }
                } else {
                    // Single branch or limited access - use auth branch or default
                    if (this.authBranch && this.authBranch !== 0) {
                        branchId = this.authBranch;
                    } else if (this.defaultAccess && this.defaultAccess.branch_id) {
                        branchId = this.defaultAccess.branch_id;
                    }
                }

                fd.append('branch_id', branchId);

                const tempId = this.$store.getters['warehouse/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('warehouse/save', {
                    form: fd,
                    search: this.props.search
                }).then((res) => {
                    this.loading.isActive = false;
                    appService.modalHide();
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.item_stock'));
                    this.props.form = {
                        name: "",
                        description: "",
                        branch_id: this.authBranch && this.authBranch !== 0 ? this.authBranch : (this.defaultAccess?.branch_id || 1),
                    }
                    this.errors = {};
                }).catch((err) => {
                    console.log('ERROR:',err)
                    this.errors = err.response.data.errors;
                    this.loading.isActive = false;
                })
            } catch (err) {
                console.log('ERROR:', err)
                this.loading.isActive = false;
                alertService.error(err)
            }
        },
        reset: function () {
            appService.modalHide('#warehouseCreate')
        }
    }
}
</script>
