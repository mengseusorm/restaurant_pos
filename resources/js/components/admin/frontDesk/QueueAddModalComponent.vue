<template>
    <div id="queueAddModal" class="modal">
        <div class="modal-dialog max-w-2xl">
            <div class="modal-header">
                <h3 class="modal-title flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-sm flex-shrink-0">
                        <i class="lab lab-queue"></i>
                    </span>
                    {{ $t('label.add_to_queue') }}
                </h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="close"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">

                    <!-- Customer Info -->
                    <div class="mb-4 pb-4 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ $t('label.customer_information') }}</p>
                        <div class="form-row">
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qCustomerName" class="db-field-title required">{{ $t('label.customer_name') }}</label>
                                <input
                                    type="text" id="qCustomerName"
                                    v-model="form.customer_name"
                                    class="db-field-control"
                                    :class="errors.customer_name ? 'invalid' : ''"
                                />
                                <small class="db-field-alert" v-if="errors.customer_name">{{ errors.customer_name[0] }}</small>
                            </div>
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qCustomerPhone" class="db-field-title after:hidden">{{ $t('label.phone') }}</label>
                                <input type="text" id="qCustomerPhone" v-model="form.customer_phone" class="db-field-control" />
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <div class="mb-4 pb-4 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ $t('label.preferences') }}</p>
                        <div class="form-row">
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qServiceId" class="db-field-title after:hidden">{{ $t('label.service') }}</label>
                                <select id="qServiceId" v-model="form.service_id" class="db-field-control">
                                    <option value="">-- {{ $t('label.service') }} --</option>
                                    <option v-for="item in services" :key="item.id" :value="item.id">{{ item.name }}</option>
                                </select>
                            </div>
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qRoomId" class="db-field-title after:hidden">{{ $t('label.preferred_room') }}</label>
                                <select id="qRoomId" v-model="form.room_id" class="db-field-control">
                                    <option value="">-- {{ $t('label.room') }} --</option>
                                    <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                                </select>
                            </div>
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qTherapistId" class="db-field-title after:hidden">{{ $t('label.preferred_therapist') }}</label>
                                <select id="qTherapistId" v-model="form.therapist_id" class="db-field-control">
                                    <option value="">-- {{ $t('label.therapist') }} --</option>
                                    <option v-for="t in therapists" :key="t.id" :value="t.user_id">
                                        {{ t.user ? t.user.name : t.user_id }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-col-12 sm:form-col-6">
                                <label for="qStatus" class="db-field-title after:hidden">{{ $t('label.status') }}</label>
                                <select id="qStatus" v-model="form.status" class="db-field-control">
                                    <option value="waiting"> {{ $t('label.waiting') }}</option>
                                    <option value="called">{{ $t('label.called') }}</option>
                                    <option value="seated">{{ $t('label.seated') }}</option>
                                    <option value="cancelled">{{ $t('label.cancelled') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="qNotes" class="db-field-title after:hidden">{{ $t('label.notes') }}</label>
                        <textarea id="qNotes" v-model="form.notes" class="db-field-control" rows="2" :placeholder="$t('placeholder.note')"></textarea>
                    </div>

                    <div class="modal-btns">
                        <button type="button" class="modal-btn-outline modal-close" @click="close">
                            <i class="lab lab-close"></i>
                            <span>{{ $t('button.close') }}</span>
                        </button>
                        <button type="submit" class="db-btn py-2 text-white bg-primary" :disabled="loading">
                            <i class="lab lab-save mr-1"></i>
                            <span>{{ $t('button.save') }}</span>
                        </button>
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
    name: "QueueAddModalComponent",
    props: {
        branchId: { type: [Number, String], default: null },
    },
    emits: ['queue-added'],
    data() {
        return {
            loading: false,
            errors: {},
            form: {
                customer_name:  '',
                customer_phone: '',
                service_id:     '',
                room_id:        '',
                therapist_id:   '',
                status:         'waiting',
                notes:          '',
            },
        };
    },
    computed: { 
        services()   { return (this.$store.getters['item/lists'] || []).filter(i => i.item_kind === 2); }, 
        rooms()      { return this.$store.getters['room/lists'] || []; },
        therapists() { return this.$store.getters['therapistProfile/lists'] || []; },
    },
    mounted() {
        this.$store.dispatch('item/lists', { paginate: 0 });
        this.$store.dispatch('room/lists', { paginate: 0 });
        this.$store.dispatch('therapistProfile/lists', { paginate: 0 });
    },
    methods: {
        save() {
            this.errors = {};
            this.loading = true;
            const formData = { ...this.form, branch_id: this.branchId || 0 };
            this.$store.dispatch('sessionQueue/save', { form: formData, search: { paginate: 0, status: 'waiting,called', branch_id: this.branchId || 0 } })
                .then(() => {
                    alertService.success(this.$t('message.queue_added'));
                    this.$emit('queue-added');
                    this.close();
                })
                .catch((err) => {
                    this.errors = err.response?.data?.errors ?? {};
                })
                .finally(() => { this.loading = false; });
        },
        close() {
            appService.modalHide('#queueAddModal');
            this.errors = {};
            Object.assign(this.form, {
                customer_name: '', customer_phone: '',
                service_id: '', room_id: '', therapist_id: '',
                status: 'waiting', notes: '',
            });
        },
    },
};
</script>
