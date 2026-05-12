<template>
    <div id="openSessionModal" class="modal">
        <div class="modal-dialog max-w-xl">
            <div class="modal-header">
                <h3 class="modal-title">
                    {{ $t('label.open_session') }}
                    <span v-if="room"> &mdash; {{ room.name }}</span>
                </h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="close"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="osGuestName" class="db-field-title required">{{ $t('label.customer_name') }}</label>
                            <input type="text" id="osGuestName" v-model="form.guest_name" :class="errors.guest_name ? 'invalid' : ''" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.guest_name">{{ errors.guest_name[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="osGuestPhone" class="db-field-title after:hidden">{{ $t('label.phone') }} ({{ $t('label.optional') }})</label>
                            <input type="text" id="osGuestPhone" v-model="form.phone" class="db-field-control" />
                        </div>
                        <div class="form-col-12">
                            <label for="osNotes" class="db-field-title after:hidden">{{ $t('label.notes') }}</label>
                            <textarea id="osNotes" v-model="form.notes" class="db-field-control" rows="2"></textarea>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="close">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary" :disabled="loading">
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
import appService from "../../../services/appService";
import alertService from "../../../services/alertService";

export default {
    name: "OpenSessionModalComponent",
    props: {
        room: { type: Object, default: null },
    },
    emits: ['session-opened'],
    data() {
        return {
            loading: false,
            errors: {},
            form: {
                guest_name: '',
                phone: '',
                notes: '',
            },
        };
    },
    methods: {
        save() {
            this.loading = true;
            const payload = { ...this.form };
            this.$store.dispatch('frontDesk/openSession', payload)
                .then((res) => {
                    alertService.success(this.$t('message.session_opened'));
                    this.$emit('session-opened', res.data.data);
                    this.close();
                    this.loading = false;
                    this.errors = {};
                })
                .catch((err) => {
                    this.loading = false;
                    this.errors = err.response?.data?.errors ?? {};
                });
        },
        close() {
            appService.modalHide('#openSessionModal');
            this.errors = {};
            this.form = { guest_name: '', phone: '', notes: '' };
        },
    },
};
</script>
