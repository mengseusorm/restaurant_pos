<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="therapistProfileModal" class="modal">
        <div class="modal-dialog max-w-xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.therapist_profiles') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpName" class="db-field-title required">{{ $t('label.name') }}</label>
                            <input v-model="form.name" :class="errors.name ? 'invalid' : ''"
                                type="text" id="tpName" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpEmail" class="db-field-title required">{{ $t('label.email') }}</label>
                            <input v-model="form.email" :class="errors.email ? 'invalid' : ''"
                                type="email" id="tpEmail" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.email">{{ errors.email[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpPassword" class="db-field-title" :class="{ required: !isEditing }">{{ $t('label.password') }}</label>
                            <input v-model="form.password" :class="errors.password ? 'invalid' : ''"
                                type="password" id="tpPassword" class="db-field-control" autocomplete="new-password" />
                            <small class="db-field-alert" v-if="errors.password">{{ errors.password[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpPasswordConfirm" class="db-field-title" :class="{ required: !isEditing }">{{ $t('label.password_confirmation') }}</label>
                            <input v-model="form.password_confirmation" :class="errors.password_confirmation ? 'invalid' : ''"
                                type="password" id="tpPasswordConfirm" class="db-field-control" autocomplete="new-password" />
                            <small class="db-field-alert" v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpPhone" class="db-field-title after:hidden">{{ $t('label.phone') }}</label>
                            <div :class="errors.phone ? 'invalid' : ''" class="db-field-control flex items-center">
                                <div class="w-fit flex-shrink-0 dropdown-group">
                                    <button type="button" class="flex items-center gap-1 dropdown-btn">
                                        {{ flag }}
                                        <span class="whitespace-nowrap flex-shrink-0 text-xs">{{ form.country_code }}</span>
                                        <input type="hidden" v-model="form.country_code" />
                                    </button>
                                </div>
                                <input v-model="form.phone" @keypress="phoneNumber($event)"
                                    :class="errors.phone ? 'invalid' : ''"
                                    type="text" id="tpPhone" class="pl-2 text-sm w-full h-full" />
                            </div>
                            <small class="db-field-alert" v-if="errors.phone">{{ errors.phone[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpCode" class="db-field-title after:hidden">{{ $t('label.code') }}</label>
                            <input v-model="form.code" :class="errors.code ? 'invalid' : ''"
                                type="text" id="tpCode" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.code">{{ errors.code[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="tpVerifyCode" class="db-field-title after:hidden">{{ $t('label.verify_code') }}</label>
                            <input v-model="form.verify_code" :class="errors.verify_code ? 'invalid' : ''"
                                type="text" id="tpVerifyCode" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.verify_code">{{ errors.verify_code[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="commission_rate" class="db-field-title required">{{ $t('label.commission_rate') }} (%)</label>
                            <input v-model="form.commission_rate" :class="errors.commission_rate ? 'invalid' : ''" type="number"
                                id="commission_rate" class="db-field-control" step="0.01" min="0" max="100">
                            <small class="db-field-alert" v-if="errors.commission_rate">{{ errors.commission_rate[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="therapistStatus" class="db-field-title required">{{ $t('label.status') }}</label>
                            <select v-model="form.status" :class="errors.status ? 'invalid' : ''"
                                id="therapistStatus" class="db-field-control">
                                <option value="available">{{ $t('label.available') }}</option>
                                <option value="busy">{{ $t('label.busy') }}</option>
                                <option value="away">{{ $t('label.away') }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
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
export default {
    name: "TherapistProfileCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: { isActive: false },
            errors: {},
            flag: '',
            country_code: '',
            form: {
                name:                  '',
                email:                 '',
                phone:                 '',
                country_code:          '',
                password:              '',
                password_confirmation: '',
                branch_id:             0,
                user_id:               '',
                code:                  '',
                verify_code:           '',
                commission_rate:       0,
                status:                'available',
            },
        };
    },
    computed: {
        addButton() {
            return { title: this.$t('button.add_therapist'), modalId: '#therapistProfileModal' };
        },
        defaultAccess() {
            return this.$store.getters['defaultAccess/show'];
        },
        isEditing() {
            return this.$store.getters['therapistProfile/temp']?.isEditing ?? false;
        },
    },
    watch: {
        'props.form': {
            deep: true,
            handler(val) {
                if (this.isEditing && val) {
                    this.form.name            = val.name            ?? '';
                    this.form.email           = val.email           ?? '';
                    this.form.phone           = val.phone           ?? '';
                    this.form.country_code    = val.country_code    ?? this.country_code;
                    this.form.password        = '';
                    this.form.password_confirmation = '';
                    this.form.branch_id       = val.branch_id       ?? (this.defaultAccess?.branch_id || 0);
                    this.form.user_id         = val.user_id         ?? '';
                    this.form.code            = val.code            ?? '';
                    this.form.verify_code     = val.verify_code     ?? '';
                    this.form.commission_rate = val.commission_rate ?? 0;
                    this.form.status          = val.status          ?? 'available';
                }
            },
        },
    },
    mounted() {
        this.$store.dispatch('defaultAccess/show');
        this.loading.isActive = true;
        this.$store.dispatch('company/lists').then(companyRes => {
            this.$store.dispatch('countryCode/show', companyRes.data.data.company_country_code).then(res => {
                if (!this.form.country_code) {
                    this.form.country_code = res.data.data.calling_code;
                    this.country_code = res.data.data.calling_code;
                }
                this.flag = res.data.data.flag_emoji;
                this.loading.isActive = false;
            }).catch(() => { this.loading.isActive = false; });
        }).catch(() => { this.loading.isActive = false; });
    },
    methods: {
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        reset() {
            appService.modalHide();
            this.$store.dispatch('therapistProfile/reset');
            this.errors = {};
            this.form = {
                name: '', email: '', phone: '', country_code: this.country_code,
                password: '', password_confirmation: '',
                branch_id: 0, user_id: '', code: '', verify_code: '', commission_rate: 0, status: 'available',
            };
        },
        save() {
            this.errors = {};
            this.loading.isActive = true;

            const wasEditing = this.isEditing;
            const fd = new FormData();
            fd.append('name',                  this.form.name || '');
            fd.append('email',                 this.form.email || '');
            fd.append('phone',                 this.form.phone || '');
            fd.append('country_code',          this.form.country_code || this.country_code || '+855');
            fd.append('password',              this.form.password || '');
            fd.append('password_confirmation', this.form.password_confirmation || '');
            fd.append('branch_id',             this.form.branch_id || this.defaultAccess?.branch_id || 0);
            fd.append('user_id',               this.form.user_id || '');
            fd.append('code',                  this.form.code || '');
            fd.append('verify_code',           this.form.verify_code || '');
            fd.append('commission_rate',       this.form.commission_rate || 0);
            fd.append('status',                this.form.status);

            this.$store.dispatch('therapistProfile/save', {
                form:   fd,
                search: this.props.search,
            }).then(() => {
                appService.modalHide();
                this.loading.isActive = false;
                alertService.successFlip(wasEditing ? 1 : 0, this.$t('menu.therapist_profiles'));
                this.reset();
            }).catch((err) => {
                this.loading.isActive = false;
                this.errors = err.response?.data?.errors ?? {};
                if (err.response?.data?.message) {
                    alertService.error(err.response.data.message);
                }
            });
        },
    },
};
</script>
