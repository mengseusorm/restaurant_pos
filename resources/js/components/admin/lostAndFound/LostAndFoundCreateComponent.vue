<template>
    <LoadingComponent :props="loading" />
    <button class="db-btn py-2 text-white bg-primary" @click="openModal">
        <i class="lab lab-line-add-to-plus-circle lab-font-size-16"></i>
        <span>{{ $t('button.add_lost_and_found') }}</span>
    </button>

    <div id="lostAndFoundModal" class="modal">
        <div class="modal-dialog max-w-3xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.lost_and_found') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="item_name" class="db-field-title required">{{ $t('label.item_name') }}</label>
                            <input v-model="props.form.item_name" v-bind:class="errors.item_name ? 'invalid' : ''" type="text" id="item_name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.item_name">{{ errors.item_name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="found_date" class="db-field-title required">{{ $t('label.found_date') }}</label>
                            <input v-model="props.form.found_date" @change="checkFoundDate" v-bind:class="errors.found_date ? 'invalid' : ''" type="date" id="found_date" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.found_date">{{ errors.found_date[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="found_by" class="db-field-title">{{ $t('label.found_by') }}</label>
                            <input v-model="props.form.found_by" v-bind:class="errors.found_by ? 'invalid' : ''" type="text" id="found_by" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.found_by">{{ errors.found_by[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="found_location" class="db-field-title required">{{ $t('label.found_location') }}</label>
                            <input v-model="props.form.found_location" v-bind:class="errors.found_location ? 'invalid' : ''" type="text" id="found_location" class="db-field-control" :placeholder="$t('placeholder.found_location')">
                            <small class="db-field-alert" v-if="errors.found_location">{{ errors.found_location[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="storage_location" class="db-field-title">{{ $t('label.storage_location') }}</label>
                            <input v-model="props.form.storage_location" v-bind:class="errors.storage_location ? 'invalid' : ''" type="text" id="storage_location" class="db-field-control" :placeholder="$t('placeholder.storage_location')">
                            <small class="db-field-alert" v-if="errors.storage_location">{{ errors.storage_location[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="status" class="db-field-title required">{{ $t('label.status') }}</label>
                            <vue-select v-model="props.form.status" :options="enums.lostAndFoundStatusEnumArray" label-by="name" value-by="value" :closeOnSelect="true" :searchable="false" :clearOnClose="true" placeholder="Select Status" id="status" class="db-field-control f-b-custom-select" :class="errors.status ? 'invalid' : ''"></vue-select>
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                        </div>

                        <!-- Customer Information -->
                        <div class="form-col-12">
                            <h4 class="text-sm font-medium text-gray-700 mb-2 mt-2">{{ $t('label.customer_information') }}</h4>
                        </div>

                        <div class="form-col-12 sm:form-col-4">
                            <label for="customer_name" class="db-field-title">{{ $t('label.customer_name') }}</label>
                            <input v-model="props.form.customer_name" v-bind:class="errors.customer_name ? 'invalid' : ''" type="text" id="customer_name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_name">{{ errors.customer_name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-4">
                            <label for="customer_phone" class="db-field-title">{{ $t('label.customer_phone') }}</label>
                            <input v-model="props.form.customer_phone" v-bind:class="errors.customer_phone ? 'invalid' : ''" type="text" id="customer_phone" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_phone">{{ errors.customer_phone[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-4">
                            <label for="customer_email" class="db-field-title">{{ $t('label.customer_email') }}</label>
                            <input v-model="props.form.customer_email" v-bind:class="errors.customer_email ? 'invalid' : ''" type="email" id="customer_email" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.customer_email">{{ errors.customer_email[0] }}</small>
                        </div>

                        <!-- Claimed Information (if status is claimed) -->
                        <template v-if="props.form.status === enums.lostAndFoundStatusEnum.CLAIMED">
                            <div class="form-col-12">
                                <h4 class="text-sm font-medium text-gray-700 mb-2 mt-2">{{ $t('label.claim_information') }}</h4>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="claimed_by" class="db-field-title">{{ $t('label.claimed_by') }}</label>
                                <input v-model="props.form.claimed_by" v-bind:class="errors.claimed_by ? 'invalid' : ''" type="text" id="claimed_by" class="db-field-control">
                                <small class="db-field-alert" v-if="errors.claimed_by">{{ errors.claimed_by[0] }}</small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="claimed_date" class="db-field-title">{{ $t('label.claimed_date') }}</label>
                                <input v-model="props.form.claimed_date" v-bind:class="errors.claimed_date ? 'invalid' : ''" type="datetime-local" id="claimed_date" class="db-field-control">
                                <small class="db-field-alert" v-if="errors.claimed_date">{{ errors.claimed_date[0] }}</small>
                            </div>
                        </template>

                        <!-- Disposal Information (if status is disposed) -->
                        <template v-if="props.form.status === enums.lostAndFoundStatusEnum.DISPOSED">
                            <div class="form-col-12">
                                <h4 class="text-sm font-medium text-gray-700 mb-2 mt-2">{{ $t('label.disposal_information') }}</h4>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="disposal_date" class="db-field-title">{{ $t('label.disposal_date') }}</label>
                                <input v-model="props.form.disposal_date" v-bind:class="errors.disposal_date ? 'invalid' : ''" type="date" id="disposal_date" class="db-field-control">
                                <small class="db-field-alert" v-if="errors.disposal_date">{{ errors.disposal_date[0] }}</small>
                            </div>
                        </template>

                        <div class="form-col-12">
                            <label for="notes" class="db-field-title">{{ $t('label.notes') }}</label>
                            <textarea v-model="props.form.notes" v-bind:class="errors.notes ? 'invalid' : ''" id="notes" class="db-field-control" rows="3"></textarea>
                            <small class="db-field-alert" v-if="errors.notes">{{ errors.notes[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label for="image" class="db-field-title">{{ $t('label.image') }}</label>
                            <input @change="changeImage" v-bind:class="errors.image ? 'invalid' : ''" type="file" id="image" class="db-field-control" ref="imageInput" accept="image/*">
                            <small class="db-field-alert" v-if="errors.image">{{ errors.image[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-fill-close-circle"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-fill-save"></i>
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
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import lostAndFoundStatusEnum from "../../../enums/modules/lostAndFoundStatusEnum";
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name: "LostAndFoundCreateComponent",
    components: { LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                lostAndFoundStatusEnum: lostAndFoundStatusEnum,
                lostAndFoundStatusEnumArray: [
                    { value: lostAndFoundStatusEnum.FOUND, name: this.$t('label.found') },
                    { value: lostAndFoundStatusEnum.CLAIMED, name: this.$t('label.claimed') },
                    { value: lostAndFoundStatusEnum.DISPOSED, name: this.$t('label.disposed') }
                ]
            },
            image: "",
            errors: {}
        }
    },
    computed: {
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
    },
    mounted() {
        this.props.form.branch_id = this.defaultAccess?.branch_id || null;
        // Set default found_date to today
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        this.props.form.found_date = `${yyyy}-${mm}-${dd}`;
    },
    methods: {
        changeImage: function (e) {
            this.image = e.target.files[0];
        },
        checkFoundDate: function () {
            const selectedDate = new Date(this.props.form.found_date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate > today) {
                alertService.warning(this.$t('message.found_date_future_warning'));
            }
        },
        openModal: function () {
            appService.modalShow("#lostAndFoundModal");
        },
        reset: function () {
            appService.modalHide("#lostAndFoundModal");
            this.$store.dispatch('lostAndFound/reset').then().catch();
            this.errors = {};
            const currentBranchId = this.defaultAccess?.branch_id || null;
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            this.$props.props.form = {
                item_name: "",
                found_date: `${yyyy}-${mm}-${dd}`,
                found_by: "",
                found_location: "",
                customer_name: "",
                customer_phone: "",
                customer_email: "",
                status: lostAndFoundStatusEnum.FOUND,
                claimed_by: "",
                claimed_date: "",
                notes: "",
                branch_id: currentBranchId,
                storage_location: "",
                disposal_date: ""
            };
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
            this.image = "";
        },
        save: function () {
            try {
                this.loading.isActive = true;
                const fd = new FormData();
                fd.append('item_name', this.props.form.item_name);
                fd.append('found_date', this.props.form.found_date);
                fd.append('found_by', this.props.form.found_by);
                fd.append('found_location', this.props.form.found_location);
                fd.append('status', this.props.form.status);
                fd.append('branch_id', this.props.form.branch_id);
                
                if (this.props.form.customer_name) fd.append('customer_name', this.props.form.customer_name);
                if (this.props.form.customer_phone) fd.append('customer_phone', this.props.form.customer_phone);
                if (this.props.form.customer_email) fd.append('customer_email', this.props.form.customer_email);
                if (this.props.form.claimed_by) fd.append('claimed_by', this.props.form.claimed_by);
                if (this.props.form.claimed_date) fd.append('claimed_date', this.props.form.claimed_date);
                if (this.props.form.notes) fd.append('notes', this.props.form.notes);
                if (this.props.form.storage_location) fd.append('storage_location', this.props.form.storage_location);
                if (this.props.form.disposal_date) fd.append('disposal_date', this.props.form.disposal_date);
                if (this.image) fd.append('image', this.image);

                this.$store.dispatch('lostAndFound/save', { form: fd, search: this.props.search }).then((res) => {
                    appService.modalHide("#lostAndFoundModal");
                    this.loading.isActive = false;
                    alertService.successFlip(res.config.method === 'put' ? 1 : 0, this.$t('menu.lost_and_found'));
                    const currentBranchId = this.defaultAccess?.branch_id || null;
                    this.props.form = {
                        item_name: "",
                        found_date: "",
                        found_by: null,
                        found_location: "",
                        customer_name: "",
                        customer_phone: "",
                        customer_email: "",
                        status: lostAndFoundStatusEnum.FOUND,
                        claimed_by: "",
                        claimed_date: "",
                        notes: "",
                        branch_id: currentBranchId,
                        storage_location: "",
                        disposal_date: ""
                    };
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = '';
                    }
                    this.image = "";
                    this.errors = {};
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        }
    }
}
</script>
