<template>
    <SmModalCreateComponent :props="addButton" />

    <div id="subSessionModal" class="modal">
        <div class="modal-dialog max-w-xl">
            <div class="modal-header">
                <h3 class="modal-title">
                    <span >{{ $t('label.massage_sessions') }}</span>
                </h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label for="sessionGroupId" class="db-field-title required">{{ $t('label.group_sessions') }}</label>
                            <select id="sessionGroupId" v-model="props.form.group_session_id" :class="errors.group_session_id ? 'invalid' : ''" class="db-field-control">
                                <option value="">-- {{ $t('label.group_sessions') }} --</option>
                                <option v-for="gs in groupSessions" :key="gs.id" :value="gs.id">#{{ gs.id }} {{ gs.customer_name || '' }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.group_session_id">{{ errors.group_session_id[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="sessionGuestName" class="db-field-title required">{{ $t('label.guest_name') }}</label>
                            <input type="text" id="sessionGuestName" v-model="props.form.guest_name" :class="errors.guest_name ? 'invalid' : ''" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.guest_name">{{ errors.guest_name[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="sessionPhone" class="db-field-title after:hidden">{{ $t('label.phone') }}</label>
                            <input type="text" id="sessionPhone" v-model="props.form.phone" :class="errors.phone ? 'invalid' : ''" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.phone">{{ errors.phone[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="sessionStatus" class="db-field-title required">{{ $t('label.status') }}</label>
                            <select id="sessionStatus" v-model="props.form.status" :class="errors.status ? 'invalid' : ''" class="db-field-control">
                                <option value="waiting">{{ $t('label.waiting') }}</option>
                                <option value="in_service">{{ $t('label.in_service') }}</option>
                                <option value="done">{{ $t('label.done') }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="sessionNotes" class="db-field-title after:hidden">{{ $t('label.notes') }}</label>
                            <textarea id="sessionNotes" v-model="props.form.notes" :class="errors.notes ? 'invalid' : ''" class="db-field-control" rows="2"></textarea>
                            <small class="db-field-alert" v-if="errors.notes">{{ errors.notes[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label class="db-field-title after:hidden flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="props.form.share_group_bill" class="w-4 h-4" />
                                {{ $t('label.share_group_bill') }}
                            </label>
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
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";

export default {
    name: "SubSessionCreateComponent",
    components: { SmModalCreateComponent },
    props: { props: Object },
    data() {
        return { 
            errors: {},
            loading: { isActive: false }
        };
    },
    computed: {
        addButton() {
            return { title: this.$t('button.add_massage_session'), modalId: '#subSessionModal' };
        },
        defaultAccess() {
            return this.$store.getters['defaultAccess/show'];
        },
        groupSessions() { return this.$store.getters['groupSession/lists'] ?? []; },
    },
    mounted() {
        this.$store.dispatch('defaultAccess/show');
        this.$store.dispatch('groupSession/lists', { paginate: 0 });
    },
    methods: {
        save() {
            this.errors = {};
            this.loading.isActive = true;

            if (!this.props.form.group_session_id) {
                this.errors.group_session_id = [this.$t('message.field_required')];
            }
            if (!this.props.form.guest_name) {
                this.errors.guest_name = [this.$t('message.field_required')];
            }
            if (!this.props.form.status) {
                this.errors.status = [this.$t('message.field_required')];
            }

            if (Object.keys(this.errors).length > 0) {
                this.loading.isActive = false;
                return;
            }

            const formData = {
                id:               this.props.form.id,
                group_session_id: parseInt(this.props.form.group_session_id),
                guest_name:       this.props.form.guest_name,
                phone:            this.props.form.phone || '',
                status:           this.props.form.status,
                notes:            this.props.form.notes || '',
                share_group_bill: this.props.form.share_group_bill || false,
            };
            
            this.$store.dispatch('subSession/save', { form: formData, search: this.props.search }).then(() => {
                this.loading.isActive = false;
                alertService.successFlip(formData.id > 0 ? 1 : null, this.$t('menu.massage_sessions'));
                this.reset();
            }).catch((err) => {
                this.loading.isActive = false;
                this.errors = err.response.data.errors;
            });
        },
        reset() {
            appService.modalHide('#subSessionModal');
            this.$store.dispatch('subSession/reset');
            this.errors = {};
            this.props.form = {
                id:               '',
                group_session_id: '',
                guest_name:       '',
                phone:            '',
                status:           'waiting',
                notes:            '',
                share_group_bill: false,
            };
        },
    },
};
</script>
