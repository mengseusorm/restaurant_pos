<template>
    <div id="memberSearchModal" class="modal">
        <div class="modal-dialog max-w-[840px]">
            <div class="modal-header hidden-print">
                <h3 class="drawer-title">{{ $t('label.search_member') }}</h3>
                <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
            </div>
            <div class="modal-body">
                <!-- Search Section -->
                <div class="mb-6">
                    <div class="flex gap-3 mb-4">
                        <div class="flex-1">
                            <label for="searchInput" class="db-field-title">{{ $t('label.search_by_phone_or_card') }}</label>
                            <input v-model="searchQuery" @keyup.enter="searchMember" type="text" id="searchInput" class="db-field-control" :placeholder="$t('label.phone_or_card_number')" />
                        </div>
                        <div class="flex items-end">
                            <button @click="searchMember" type="button" class="db-btn text-white bg-primary" :disabled="loading.isActive">
                                <i class="lab lab-search-normal"></i>
                                <span>{{ $t('button.search') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchResults.length > 0" class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b">
                            <h4 class="text-sm font-medium">{{ $t('label.search_results') }}</h4>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <table class="w-full">
                                <thead class="bg-primary-light">
                                    <tr class="h-9">
                                        <th class="capitalize text-xs font-normal font-rubik text-left pl-3 text-heading">
                                            {{ $t('label.name') }}
                                        </th>
                                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                                            {{ $t('label.phone') }}
                                        </th>
                                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                                            {{ $t('label.card_number') }}
                                        </th>
                                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                                            {{ $t('label.points') }}
                                        </th>
                                        <th class="capitalize text-xs font-normal font-rubik text-left px-3 text-heading">
                                            {{ $t('label.action') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="member in searchResults" :key="member.id" class="border-b border-[#EFF0F6]">
                                        <td class="pl-3 py-3 text-sm">
                                            {{ member.name }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            {{ member.phone }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            {{ member.card_number || '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            {{ member.point_balance || 0 }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            <button @click="selectMember(member)" class="db-btn text-white bg-green-500">
                                                <i class="lab lab-check"></i>
                                                <span>{{ $t('button.select_member') }}</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- No Results Message -->
                    <div v-else-if="searchPerformed && !loading.isActive" class="text-center py-4 text-gray-500">
                        <p>{{ $t('message.no_member_found') }}</p>
                    </div>
                </div>

                <!-- Add New Member Section -->
                <div class="mt-6 p-6 border rounded-lg">
                    <h4 class="text-sm font-medium mb-4">{{ $t('label.or_add_new_member') }}</h4>
                    <form @submit.prevent="save">
                        <div class="form-row">
                            <div class="form-col-12 sm:form-col-6">
                                <label for="add_new_member_name" class="db-field-title required">{{ $t('label.name') }}</label>
                                <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text" id="add_new_member_name" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="add_new_member_phone" class="db-field-title required">{{ $t('label.phone') }}</label>
                                <div :class="errors.phone ? 'invalid' : ''" class="db-field-control flex items-center">
                                    <div class="w-fit flex-shrink-0 dropdown-group">
                                        <button type="button" class="flex items-center gap-1 dropdown-btn">
                                            {{ flag }}
                                            <span class="whitespace-nowrap flex-shrink-0 text-xs">
                                                {{ props.form.country_code }}
                                            </span>
                                            <input type="hidden" v-model="props.form.country_code" />
                                        </button>
                                    </div>
                                    <input v-model="props.form.phone" v-on:keypress="phoneNumber($event)" v-bind:class="errors.phone ? 'invalid' : ''" type="text" id="add_new_member_phone" class="pl-2 text-sm w-full h-full" />
                                </div>
                                <small class="db-field-alert" v-if="errors.phone">
                                    {{ errors.phone[0] }}
                                </small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label for="add_new_member_card_number" class="db-field-title">{{ $t('label.card_number') }}</label>
                                <input v-model="props.form.card_number" v-bind:class="errors.card_number ? 'invalid' : ''" type="text" id="add_new_member_card_number" class="db-field-control" />
                                <small class="db-field-alert" v-if="errors.card_number">{{ errors.card_number[0] }}</small>
                            </div>

                            <div class="form-col-12 sm:form-col-6">
                                <label class="db-field-title required" for="add_new_member_active">
                                    {{ $t('label.status') }}
                                </label>
                                <div class="db-field-radio-group">
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="add_new_member_active" type="radio" class="custom-radio-field" />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="add_new_member_active" class="db-field-label">{{ $t('label.active') }}</label>
                                    </div>
                                    <div class="db-field-radio">
                                        <div class="custom-radio">
                                            <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status" type="radio" id="add_new_member_inactive" class="custom-radio-field" />
                                            <span class="custom-radio-span"></span>
                                        </div>
                                        <label for="add_new_member_inactive" class="db-field-label">{{ $t('label.inactive') }}</label>
                                    </div>
                                </div>
                                <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                            </div>

                            <div class="form-col-12">
                                <div class="flex flex-wrap gap-3 mt-4 justify-end">
                                    <button type="submit" class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-save"></i>
                                        <span>{{ $t('label.save') }}</span>
                                    </button>
                                    <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                        <i class="lab lab-close"></i>
                                        <span>{{ $t('button.close') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import appService from '../../../services/appService';
import statusEnum from '../../../enums/modules/statusEnum';
import LoadingComponent from '../components/LoadingComponent';
import alertService from '../../../services/alertService';

export default {
    name: 'MemberSelectComponent',
    components: {
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t('label.active'),
                    [statusEnum.INACTIVE]: this.$t('label.inactive'),
                },
            },
            props: {
                form: {
                    name: '',
                    phone: '',
                    card_number: '',
                    country_code: '',
                    status: statusEnum.ACTIVE,
                    point_balance: 0,
                },
            },
            errors: {},
            flag: '',
            country_code: '',
            searchQuery: '',
            searchResults: [],
            searchPerformed: false,
        };
    },
    mounted() {
        this.loading.isActive = true;
        this.$store
            .dispatch('company/lists')
            .then((companyRes) => {
                this.$store
                    .dispatch('countryCode/show', companyRes.data.data.company_country_code)
                    .then((res) => {
                        if (this.props.form.country_code === '') {
                            this.props.form.country_code = res.data.data.calling_code;
                            this.country_code = res.data.data.calling_code;
                        }
                        this.flag = res.data.data.flag_emoji;
                        this.loading.isActive = false;
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                    });
            })
            .catch((err) => {
                this.loading.isActive = false;
            });
    },
    methods: {
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },

        searchMember: function () {
            if (!this.searchQuery.trim()) {
                alertService.error(this.$t('message.please_enter_search_term'));
                return;
            }

            this.loading.isActive = true;
            this.searchPerformed = true;

            this.$store
                .dispatch('member/findByPhoneOrCard', this.searchQuery.trim())
                .then((res) => {
                    this.loading.isActive = false;
                    if (res.data.data) {
                        this.searchResults = [res.data.data];
                    } else {
                        this.searchResults = [];
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.searchResults = [];
                    alertService.error(err.response?.data?.message || this.$t('message.search_error'));
                });
        },

        selectMember: function (member) {
            // Emit event to parent component with selected member data
            this.$emit('onMemberSelect', {
                id: member.id,
                name: member.name,
                phone: member.phone,
                card_number: member.card_number,
                point_balance: member.point_balance,
            });

            // Close modal and reset
            this.reset();
            // alertService.success(this.$t('message.member_selected'));
        },

        reset: function () {
            appService.modalHide('#memberSearchModal');
            this.errors = {};
            this.searchQuery = '';
            this.searchResults = [];
            this.searchPerformed = false;
            this.props.form = {
                name: '',
                phone: '',
                card_number: '',
                status: statusEnum.ACTIVE,
                country_code: this.country_code,
                point_balance: 0,
            };
        },

        save: function () {
            try {
                this.loading.isActive = true;

                // Get default access to set branch_id
                this.$store
                    .dispatch('defaultAccess/show')
                    .then((accessRes) => {
                        this.props.form.branch_id = accessRes.data.data.branch_id;

                        // Save member using the member store
                        this.$store
                            .dispatch('member/save', {
                                form: this.props.form,
                                search: {}, // Empty search for member creation
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(0, this.$t('label.member'));

                                // Emit the new member data to parent
                                this.$emit('onMemberCreate', {
                                    id: res.data.data.id,
                                    name: res.data.data.name,
                                    phone: res.data.data.phone,
                                    card_number: res.data.data.card_number,
                                    point_balance: res.data.data.point_balance || 0,
                                });

                                this.reset();
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                this.errors = err.response.data.errors;
                            });
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response?.data?.message || this.$t('message.error_occurred'));
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
};
</script>
