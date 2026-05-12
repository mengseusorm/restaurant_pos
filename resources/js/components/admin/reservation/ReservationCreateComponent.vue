<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="reservationModal" class="modal">
        <div class="modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.reservations') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="customer_name" class="db-field-title required">{{ $t("label.customer_name") }}</label>
                            <input v-model="props.form.customer_name" v-bind:class="errors.customer_name ? 'invalid' : ''" type="text"
                                id="customer_name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_name">{{ errors.customer_name[0] }}</small>
                        </div>
                        
                        <div class="form-col-12 sm:form-col-6">
                            <label for="customer_phone" class="db-field-title required">{{ $t("label.phone") }}</label>
                            <input v-model="props.form.customer_phone" v-bind:class="errors.customer_phone ? 'invalid' : ''" type="text"
                                id="customer_phone" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_phone">{{ errors.customer_phone[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="customer_email" class="db-field-title">{{ $t("label.email") }}</label>
                            <input v-model="props.form.customer_email" v-bind:class="errors.customer_email ? 'invalid' : ''" type="email"
                                id="customer_email" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_email">{{ errors.customer_email[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="number_of_people" class="db-field-title required">{{ $t("label.number_of_guests") }}</label>
                            <input v-model="props.form.number_of_people" v-bind:class="errors.number_of_people ? 'invalid' : ''" type="number"
                                id="number_of_people" class="db-field-control" min="1">
                            <small class="db-field-alert" v-if="errors.number_of_people">{{ errors.number_of_people[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="reservation_date" class="db-field-title required">{{ $t("label.reservation_date") }}</label>
                            <input v-model="props.form.reservation_date" v-bind:class="errors.reservation_date ? 'invalid' : ''" type="date"
                                id="reservation_date" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.reservation_date">{{ errors.reservation_date[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="reservation_time" class="db-field-title required">{{ $t("label.reservation_time") }}</label>
                            <input v-model="props.form.reservation_time" v-bind:class="errors.reservation_time ? 'invalid' : ''" type="time"
                                id="reservation_time" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.reservation_time">{{ errors.reservation_time[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="duration_minutes" class="db-field-title">{{ $t("label.duration_minutes") }}</label>
                            <input v-model="props.form.duration_minutes" v-bind:class="errors.duration_minutes ? 'invalid' : ''" type="number"
                                id="duration_minutes" class="db-field-control" min="0">
                            <small class="db-field-alert" v-if="errors.duration_minutes">{{ errors.duration_minutes[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="table_id" class="db-field-title">{{ $t("label.table") }}</label>
                            <select v-model="props.form.table_id" v-bind:class="errors.table_id ? 'invalid' : ''"
                                id="table_id" class="db-field-control">
                                <option value="">{{ $t("label.select_table") }}</option>
                                <option v-for="table in tables" :key="table.id" :value="table.id">{{ table.name }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.table_id">{{ errors.table_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="status" class="db-field-title required">{{ $t("label.status") }}</label>
                            <select v-model="props.form.status" v-bind:class="errors.status ? 'invalid' : ''"
                                id="status" class="db-field-control">
                                <option :value="enums.reservationStatusEnum.PENDING">{{ $t("label.pending") }}</option>
                                <option :value="enums.reservationStatusEnum.CONFIRMED">{{ $t("label.confirmed") }}</option>
                                <option :value="enums.reservationStatusEnum.CHECKED_IN">{{ $t("label.checked_in") }}</option>
                                <option :value="enums.reservationStatusEnum.CANCELLED">{{ $t("label.cancelled") }}</option>
                                <option :value="enums.reservationStatusEnum.NO_SHOW">{{ $t("label.no_show") }}</option>
                                <option :value="enums.reservationStatusEnum.COMPLETED">{{ $t("label.completed") }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="deposit_amount" class="db-field-title">{{ $t("label.deposit_amount") }}</label>
                            <input v-model="props.form.deposit_amount" v-bind:class="errors.deposit_amount ? 'invalid' : ''" type="number"
                                id="deposit_amount" class="db-field-control" step="0.01" min="0">
                            <small class="db-field-alert" v-if="errors.deposit_amount">{{ errors.deposit_amount[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="payment_status" class="db-field-title">{{ $t("label.payment_status") }}</label>
                            <select v-model="props.form.payment_status" v-bind:class="errors.payment_status ? 'invalid' : ''"
                                id="payment_status" class="db-field-control">
                                <option :value="enums.paymentStatusEnum.UNPAID">{{ $t("label.unpaid") }}</option>
                                <option :value="enums.paymentStatusEnum.PARTIAL">{{ $t("label.partial") }}</option>
                                <option :value="enums.paymentStatusEnum.PAID">{{ $t("label.paid") }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.payment_status">{{ errors.payment_status[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="special_request" class="db-field-title">{{ $t("label.special_request") }}</label>
                            <textarea v-model="props.form.special_request" v-bind:class="errors.special_request ? 'invalid' : ''"
                                id="special_request" class="db-field-control" rows="3"></textarea>
                            <small class="db-field-alert" v-if="errors.special_request">{{ errors.special_request[0] }}</small>
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
import reservationStatusEnum from "../../../enums/modules/reservationStatusEnum";
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "ReservationCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                reservationStatusEnum: reservationStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
            },
            errors: {},
            tables: []
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_reservation') };
        },
    },
    mounted() {
        this.loadTables();
    },
    methods: {
        loadTables: function() {
            // Load available tables from API
            this.$store.dispatch('diningTable/lists', {paginate: 0}).then(res => {
                this.tables = res.data.data;
            }).catch((err) => {
                console.error('Error loading tables:', err);
            });
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch('reservation/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                customer_name: "",
                customer_phone: "",
                customer_email: "",
                reservation_date: "",
                reservation_time: "",
                number_of_people: 1,
                table_id: null,
                status: reservationStatusEnum.PENDING,
                special_request: "",
                branch_id: null,
                deposit_amount: 0,
                payment_status: paymentStatusEnum.UNPAID,
                duration_minutes: 120
            }
        },

        save: function () {
            try {
                const fd = new FormData();
                fd.append('customer_name', this.props.form.customer_name);
                fd.append('customer_phone', this.props.form.customer_phone);
                if (this.props.form.customer_email) {
                    fd.append('customer_email', this.props.form.customer_email);
                }
                fd.append('reservation_date', this.props.form.reservation_date);
                fd.append('reservation_time', this.props.form.reservation_time);
                fd.append('number_of_people', this.props.form.number_of_people);
                if (this.props.form.table_id) {
                    fd.append('table_id', this.props.form.table_id);
                }
                fd.append('status', this.props.form.status);
                if (this.props.form.special_request) {
                    fd.append('special_request', this.props.form.special_request);
                }
                if (this.props.form.branch_id) {
                    fd.append('branch_id', this.props.form.branch_id);
                }
                fd.append('deposit_amount', this.props.form.deposit_amount || 0);
                fd.append('payment_status', this.props.form.payment_status);
                if (this.props.form.duration_minutes) {
                    fd.append('duration_minutes', this.props.form.duration_minutes);
                }

                const tempId = this.$store.getters['reservation/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('reservation/save', {
                    form: fd,
                    search: this.props.search
                }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.reservations'));
                    this.props.form = {
                        customer_name: "",
                        customer_phone: "",
                        customer_email: "",
                        reservation_date: "",
                        reservation_time: "",
                        number_of_people: 1,
                        table_id: null,
                        status: reservationStatusEnum.PENDING,
                        special_request: "",
                        branch_id: null,
                        deposit_amount: 0,
                        payment_status: paymentStatusEnum.UNPAID,
                        duration_minutes: 120
                    }
                    this.errors = {};
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
