<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t('menu.point_earn_rules') }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save" @keydown.enter.prevent>
                <div class="form-row">
                    <div class="form-col-12">
                        <label for="name" class="db-field-title required">{{ $t('label.name') }}</label>
                        <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text" id="name" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="currency_amount" class="db-field-title required">{{ $t('label.currency_amount') }}</label>
                        <input v-model="props.form.currency_amount" v-bind:class="errors.currency_amount ? 'invalid' : ''" type="number" min="0.01" step="0.01" id="currency_amount" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.currency_amount">{{ errors.currency_amount[0] }}</small>
                        <!-- <small class="text-xs text-gray-500">{{ $t('label.currency_amount_help') }}</small> -->
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="point" class="db-field-title required">{{ $t('label.point') }}</label>
                        <input v-model="props.form.point" v-bind:class="errors.point ? 'invalid' : ''" type="number" min="1" id="point" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.point">{{ errors.point[0] }}</small>
                        <!-- <small class="text-xs text-gray-500">{{ $t('label.point_help') }}</small> -->
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
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "PointEarnRuleCreateComponent",
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
                title: this.$t("button.add_point_earn_rule")
            },
            errors: {}
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
            this.$store.dispatch('pointEarnRule/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                currency_amount: 1.00,
                point: 1,
                is_active: true,
            };
        },
        save: function () {
            // console.log("Saving point earn rule with data1:", this.props);
            // alert("Hello")
            try {
                this.loading.isActive = true;

                this.props.form.branch_id = this.defaultAccess.branch_id;

                const tempId = this.$store.getters['pointEarnRule/temp'].temp_id;

                // console.log("Saving point earn rule with data:", this.props);
                this.$store.dispatch('pointEarnRule/save', this.props).then(res => {
                    appService.sideDrawerHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.point_earn_rules'));
                    this.reset();
                }).catch((err) => {
                    // console.error("Error saving point earn rule:", err);
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
