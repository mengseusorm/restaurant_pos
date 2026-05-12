<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="categoryModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.item_categories') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <!-- branch selection (radio pattern) -->
                        <div class="form-col-12 sm:form-col-12">
                            <label class="db-field-title required" for="current_branch">{{ $t('label.branch') }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            :class="errors.branch_id ? 'is-invalid' : ''" id="current_branch"
                                            :value="defaultAccess.branch_id"
                                            :checked="props.form.branch_id === '' || props.form.branch_id === null || props.form.branch_id === defaultAccess.branch_id" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="current_branch" class="db-field-label">{{ $t('label.current_branch') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            :class="errors.branch_id ? 'is-invalid' : ''" id="all_branch" value="0"
                                            :checked="props.form.branch_id === 0" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="all_branch" class="db-field-label">{{ $t('label.all_branch') }}</label>
                                </div>
                            </div>
                            <small class="db-field-alert" v-if="errors.branch_id">{{ errors.branch_id[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input v-model="props.form.name_kh" v-bind:class="errors.name_kh ? 'invalid' : ''" type="text"
                                id="name_kh" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input v-model="props.form.name_cn" v-bind:class="errors.name_cn ? 'invalid' : ''" type="text"
                                id="name_cn" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_en" class="db-field-title">{{ $t("label.name_en") }}</label>
                            <input v-model="props.form.name_en" v-bind:class="errors.name_en ? 'invalid' : ''" type="text"
                                id="name_en" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name_en">{{ errors.name_en[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="item_category_code" class="db-field-title">{{ $t("label.item_category_code")
                                }}</label>
                            <input v-model="props.form.item_category_code"
                                v-bind:class="errors.item_category_code ? 'invalid' : ''" type="text"
                                id="item_category_code" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.item_category_code">{{
                                errors.item_category_code[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="image" class="db-field-title">{{ $t('label.image') }} (74px,48px)</label>
                            <input @change="changeImage" v-bind:class="errors.image ? 'invalid' : ''" id="image"
                                type="file" class="db-field-control" ref="imageProperty"
                                accept="image/png, image/jpeg, image/jpg">
                            <small class="db-field-alert" v-if="errors.image">{{ errors.image[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="sort" class="db-field-title required">{{ $t("label.sort") }}</label>
                            <input v-model="props.form.sort" v-bind:class="errors.sort ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.sort">{{ errors.sort[0] }}</small>
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
                            <label for="description" class="db-field-title">{{ $t("label.description") }}</label>
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
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import BranchSelectComponent from "../../components/BranchSelectComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "ItemCategoryCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent, BranchSelectComponent },
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
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            image: "",
            errors: {},
        }
    },
    computed: {
        addButton: function () {
            return {
                title: this.$t('button.add_item_category'),
                modalId: '#categoryModal'
            };
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
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
            status: this.enums.statusEnum.ACTIVE,
        });
        // Always set default branch_id to current shop (defaultAccess.branch_id)
        this.$nextTick(() => {
            const defaultAccess = this.$store.getters["defaultAccess/show"];
            if (defaultAccess && defaultAccess.branch_id) {
                this.props.form.branch_id = defaultAccess.branch_id;
            }
            this.loading.isActive = false;
        });
    },
    methods: {
        changeImage: function (e) {
            this.image = e.target.files[0];
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch('itemCategory/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                name_kh: "",
                name_cn: "",
                name_en: "",
                item_category_code: "",
                description: "",
                status: statusEnum.ACTIVE,
                branch_id: this.defaultAccess.branch_id,
                sort: null
            };
            if (this.image) {
                this.image = "";
                this.$refs.imageProperty.value = null;
            }
        },

        save: function () {
            try {
                const fd = new FormData();
                fd.append('name', this.props.form.name);
                fd.append('name_kh', this.props.form.name_kh || '');
                fd.append('name_cn', this.props.form.name_cn || '');
                fd.append('name_en', this.props.form.name_en || '');
                fd.append('item_category_code', this.props.form.item_category_code == null ? '' : this.props.form.item_category_code);
                fd.append('status', this.props.form.status);
                fd.append('description', this.props.form.description);
                fd.append('branch_id', this.props.form.branch_id);
                fd.append('sort', this.props.form.sort);

                if (this.image) {
                    fd.append('image', this.image);
                }

                const tempId = this.$store.getters['itemCategory/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('itemCategory/save', {
                    form: fd,
                    search: this.props.search
                }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.item_categories'));
                    this.props.form = {
                        name: "",
                        name_kh: "",
                        name_cn: "",
                        name_en: "",
                        item_category_code: "",
                        description: "",
                        status: statusEnum.ACTIVE,
                        branch_id: null,
                        sort: null
                    }
                    this.image = "";
                    this.errors = {};
                    this.$refs.imageProperty.value = null;

                    this.$store.dispatch('itemCategory/lists', this.props.search).then(res => {
                        this.loading.isActive = false;
                    }).catch((err) => {
                        this.loading.isActive = false;
                    });
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                })
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err)
            }
        }
    }
}
</script>
