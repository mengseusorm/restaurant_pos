<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="bedModal" class="modal">
        <div class="modal-dialog max-w-xl">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('menu.beds') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="bedRoomId" class="db-field-title required">{{ $t('label.room') }}</label>
                            <select v-model="props.form.room_id" :class="errors.room_id ? 'invalid' : ''"
                                id="bedRoomId" class="db-field-control">
                                <option value="">-- {{ $t('label.room') }} --</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.room_id">{{ errors.room_id[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="bedName" class="db-field-title required">{{ $t('label.name') }}</label>
                            <input v-model="props.form.name" :class="errors.name ? 'invalid' : ''"
                                type="text" id="bedName" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="bedStatus" class="db-field-title required">{{ $t('label.status') }}</label>
                            <select v-model="props.form.status" :class="errors.status ? 'invalid' : ''"
                                id="bedStatus" class="db-field-control">
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
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "BedCreateComponent",
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
            return { title: this.$t('button.add_bed') };
        },
        rooms() {
            return this.$store.getters['room/lists'] ?? [];
        },
        authBranch() {
            return this.$store.getters.authBranchId;
        },
    },
    watch: {
        'props.form.room_id'(newRoomId) {
            // Auto-set branch_id based on selected room
            const room = this.rooms.find(r => r.id === newRoomId);
            if (room && room.branch_id) {
                this.props.form.branch_id = room.branch_id;
            } else {
                this.props.form.branch_id = this.authBranch || '';
            }
        },
    },
    mounted() {
        this.$store.dispatch('room/lists', { paginate: 0 });
        // Auto-select branch on open
        if (!this.props.form.branch_id && this.authBranch) {
            this.props.form.branch_id = this.authBranch;
        }
    },
    methods: {
        reset() {
            appService.modalHide();
            this.$store.dispatch('bed/reset').then().catch();
            this.errors = {};
            this.$props.props.form = { branch_id: this.authBranch || '', room_id: '', name: '', status: 'available' };
        },
        save() {
            this.loading.isActive = true;
            // branch_id is auto-set from room selection
            const form = {
                branch_id: this.props.form.branch_id,
                room_id: this.props.form.room_id,
                name:    this.props.form.name,
                status:  this.props.form.status,
            };
            this.$store.dispatch('bed/save', { form, search: this.props.search })
                .then(() => {
                    this.loading.isActive = false;
                    this.reset();
                    alertService.successFlip(
                        this.$store.getters['bed/temp']?.temp_id,
                        this.$t('label.bed')
                    );
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response?.data?.errors ?? {};
                });
        },
    },
};
</script>
