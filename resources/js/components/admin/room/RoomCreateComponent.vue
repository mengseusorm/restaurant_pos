<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="roomModal" class="modal">
        <div class="modal-dialog max-w-xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.rooms') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <!-- branch selection (radio pattern) -->
                        <div class="form-col-12">
                            <label class="db-field-title required" for="current_branch">{{ $t('label.branch') }}</label>
                            <div class="db-field-radio-group" v-if="branches.length > 1 && authBranch === 0">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            :class="errors.branch_id ? 'is-invalid' : ''"
                                            id="current_branch"
                                            :value="defaultAccess.branch_id" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="current_branch" class="db-field-label">
                                        {{ $t('label.current_branch') }}
                                        <span v-if="currentBranchName" class="text-xs text-gray-500 ml-1">({{ currentBranchName }})</span>
                                    </label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input class="custom-radio-field" v-model="props.form.branch_id" type="radio"
                                            :class="errors.branch_id ? 'is-invalid' : ''"
                                            id="all_branch"
                                            :value="0" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="all_branch" class="db-field-label">{{ $t('label.all_branch') }}</label>
                                </div>
                            </div>
                            <!-- When radio is hidden, show current branch read-only -->
                            <div v-else class="text-sm text-gray-600 mt-1">
                                {{ currentBranchName || $t('label.current_branch') }}
                            </div>
                            <small class="db-field-alert" v-if="errors.branch_id">{{ errors.branch_id[0] }}</small>
                        </div>
                        <!-- end -->

                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title required">{{ $t('label.name') }}</label>
                            <input v-model="props.form.name" :class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control">
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="status" class="db-field-title required">{{ $t('label.status') }}</label>
                            <select v-model="props.form.status" :class="errors.status ? 'invalid' : ''"
                                id="status" class="db-field-control">
                                <option value="available">{{ $t('label.available') }}</option>
                                <option value="occupied">{{ $t('label.occupied') }}</option>
                                <option value="cleaning">{{ $t('label.cleaning') }}</option>
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
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "RoomCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: { isActive: false },
            errors: {},
        };
    },
    computed: {
        addButton() {
            return { title: this.$t('button.add_room') };
        },
        defaultAccess() {
            return this.$store.getters["defaultAccess/show"];
        },
        branches() {
            return this.$store.getters["branch/lists"];
        },
        authBranch() {
            return this.$store.getters.authBranchId;
        },
        currentBranchName() {
            const id = this.defaultAccess?.branch_id;
            if (!id) return '';
            const branch = this.branches.find(b => b.id === id);
            return branch?.name || '';
        },
    },
    watch: {
        // Set branch_id as soon as defaultAccess resolves (handles async load)
        'defaultAccess.branch_id': {
            immediate: true,
            handler(val) {
                // Always sync to current branch when form is in create mode (branch_id is null)
                if (val != null && this.props.form.branch_id == null) {
                    this.props.form.branch_id = val;
                }
            },
        },
    },
    mounted() {
        this.loading.isActive = true;
        Promise.all([
            this.$store.dispatch("defaultAccess/show"),
            this.$store.dispatch("branch/lists", {
                order_column: "id",
                order_type: "asc",
                status: statusEnum.ACTIVE,
            }),
        ]).then(() => {
            // Set branch_id to current branch once data is loaded
            if (!this.props.form.branch_id && this.defaultAccess?.branch_id) {
                this.props.form.branch_id = this.defaultAccess.branch_id;
            }
            this.loading.isActive = false;
        }).catch(() => { this.loading.isActive = false; });
    },
    methods: {
        reset() {
            appService.modalHide();
            this.$store.dispatch('room/reset').then().catch();
            this.errors = {};
            this.$props.props.form = { name: '', status: 'available', branch_id: this.defaultAccess?.branch_id ?? null };
        },
        save() {
            this.loading.isActive = true;
            const fd = new FormData();
            fd.append('name', this.props.form.name);
            fd.append('status', this.props.form.status);
            fd.append('branch_id', this.props.form.branch_id);
            if (this.props.form.qr_code_token) {
                fd.append('qr_code_token', this.props.form.qr_code_token);
            }
            this.$store.dispatch('room/save', { form: fd, search: this.props.search }).then((res) => {
                this.loading.isActive = false;
                this.reset();
                alertService.successFlip(this.$store.getters['room/temp'].temp_id, this.$t('label.room'));
            }).catch((err) => {
                this.loading.isActive = false;
                this.errors = err.response.data.errors;
            });
        },
    },
};
</script>
