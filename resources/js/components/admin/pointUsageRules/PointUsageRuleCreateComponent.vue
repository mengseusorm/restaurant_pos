<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t('menu.point_usage_rules') }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12">
                        <label for="name" class="db-field-title required">{{ $t('label.name') }}</label>
                        <input v-model="props.form.name" :class="errors.name ? 'invalid' : ''" type="text" id="name" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-col-12 mb-3">
                        <label for="usage_type" class="db-field-title">{{ $t('label.usage_type') }}</label>
                        <div class="db-field-radio-group">
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input value="deduct_order" v-model="props.form.usage_type" id="usage_type_deduct_order" type="radio" class="custom-radio-field" />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="usage_type_deduct_order" class="db-field-label">{{ $t('label.deduct_order') }}</label>
                            </div>
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input value="exchange_gift" v-model="props.form.usage_type" id="usage_type_exchange_gift" type="radio" class="custom-radio-field" />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="usage_type_exchange_gift" class="db-field-label">{{ $t('label.exchange_gift') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="point_to_currency" class="db-field-title required">{{ $t('label.point_to_currency') }}</label>
                        <input v-model="props.form.point_to_currency" :class="errors.point_to_currency ? 'invalid' : ''" type="number" min="0" step="0.01" id="point_to_currency" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.point_to_currency">{{ errors.point_to_currency[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="min_point_usage" class="db-field-title required">{{ $t('label.min_point_usage') }}</label>
                        <input v-model="props.form.min_point_usage" :class="errors.min_point_usage ? 'invalid' : ''" type="number" min="1" id="min_point_usage" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.min_point_usage">{{ errors.min_point_usage[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="max_point_usage" class="db-field-title">{{ $t('label.max_point_usage') }}</label>
                        <input v-model="props.form.max_point_usage" :class="errors.max_point_usage ? 'invalid' : ''" type="number" min="1" id="max_point_usage" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.max_point_usage">{{ errors.max_point_usage[0] }}</small>
                    </div>

                    <div class="form-col-12">
                        <label for="is_active" class="db-field-title">{{ $t('label.status') }}</label>
                        <div class="db-field-radio-group">
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input :value="true" v-model="props.form.is_active" id="active" type="radio" class="custom-radio-field" />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="active" class="db-field-label">{{ $t('label.active') }}</label>
                            </div>
                            <div class="db-field-radio">
                                <div class="custom-radio">
                                    <input :value="false" v-model="props.form.is_active" id="inactive" type="radio" class="custom-radio-field" />
                                    <span class="custom-radio-span"></span>
                                </div>
                                <label for="inactive" class="db-field-label">{{ $t('label.inactive') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-col-12">
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button class="db-btn py-2 text-white bg-primary">
                                <i class="lab lab-save lab-font-size-16"></i>
                                <span>{{ $t('button.save') }}</span>
                            </button>
                            <button class="db-btn py-2 text-white bg-gray-600" @click="reset" type="button">
                                <i class="lab lab-undo lab-font-size-16"></i>
                                <span>{{ $t('button.reset') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "PointUsageRuleCreateComponent",
    components: {
        SmSidebarModalCreateComponent,
        LoadingComponent
    },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false
            },
            addButton: {
                title: this.$t("button.add_point_usage_rule")
            },
            errors: {},
            branchOptions: [] // Should be loaded from API or Vuex
        }
    },
    computed: {
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() {
        this.reset();
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch('pointUsageRule/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                branch_id: null,
                name: "",
                usage_type: "deduct_order",
                point_to_currency: 1,
                min_point_usage: 1,
                max_point_usage: null,
                is_active: true,
            };
        },
        save: function () {
            try {
                this.loading.isActive = true;
                this.props.form.branch_id = this.defaultAccess.branch_id;
                const tempId = this.$store.getters['pointUsageRule/temp'].temp_id;
                this.$store.dispatch('pointUsageRule/save', this.props).then(res => {
                    appService.sideDrawerHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t("menu.point_usage_rules"));
                    this.reset();
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                })
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        }
    }
}
</script>
