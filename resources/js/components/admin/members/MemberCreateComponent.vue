<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">{{ $t('menu.members') }}</h3>
            <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
        </div>
        <div class="drawer-body">
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="name" class="db-field-title required">{{ $t('label.name') }}</label>
                        <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text" id="name" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="phone" class="db-field-title required">{{ $t('label.phone') }}</label>
                        <input v-model="props.form.phone" v-bind:class="errors.phone ? 'invalid' : ''" v-on:keypress="phoneNumber($event)" type="text" id="phone" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.phone">{{ errors.phone[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="card_number" class="db-field-title">{{ $t('label.card_number') }}</label>
                        <input v-model="props.form.card_number" v-bind:class="errors.card_number ? 'invalid' : ''" type="text" id="card_number" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.card_number">{{ errors.card_number[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="point_balance" class="db-field-title">{{ $t('label.initial_points') }}</label>
                        <input v-model="props.form.point_balance" v-bind:class="errors.point_balance ? 'invalid' : ''" type="number" min="0" id="point_balance" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.point_balance">{{ errors.point_balance[0] }}</small>
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
                        <small class="db-field-alert" v-if="errors.is_active">{{ errors.is_active[0] }}</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col-12">
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" class="db-btn py-2 text-white bg-primary">
                                <i class="lab lab-fill-save lab-font-size-16"></i>
                                <span>{{ $t('button.save') }}</span>
                            </button>
                            <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                <i class="lab lab-fill-close-circle lab-font-size-16"></i>
                                <span>{{ $t('button.close') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import SmSidebarModalCreateComponent from '../components/buttons/SmSidebarModalCreateComponent';
import LoadingComponent from '../components/LoadingComponent';
import appService from '../../../services/appService';
import alertService from '../../../services/alertService';

export default {
    name: 'MemberCreateComponent',
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false,
            },
            addButton: {
                title: this.$t('button.add_member'),
            },
            errors: {},
        };
    },
    computed: {
        members: function () {
            return this.$store.getters['member/lists'];
        },
        defaultAccess: function () {
            return this.$store.getters['defaultAccess/show'];
        },
    },
    methods: {
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        reset: function () {
            appService.sideDrawerHide();
            this.$store.dispatch('member/reset').then().catch();
            this.errors = {};
            this.props.form = {
                name: '',
                phone: '',
                card_number: '',
                point_balance: 0,
                is_active: true,
            };
        },
        save: function () {
            try {

                console.log(this.defaultAccess);

                this.loading.isActive = true;
                this.props.form.branch_id = this.defaultAccess.branch_id;
                this.$store
                    .dispatch('member/save', {
                        form: this.props.form,
                        search: this.props.search,
                    })
                    .then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(this.$store.getters['member/temp'].isEditing === true ? 1 : 0, this.$t('label.member'));
                        this.props.form = {
                            branch_id: null,
                            name: '',
                            phone: '',
                            card_number: '',
                            point_balance: 0,
                            is_active: true,
                        };
                        this.errors = {};
                        this.$store.dispatch('member/reset').then().catch();
                        appService.sideDrawerHide();
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            }
        },
    },
};
</script>
