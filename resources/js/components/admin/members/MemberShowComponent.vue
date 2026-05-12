<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">

        <div class="grid grid-cols-2 sm:grid-cols-2 mb-4 sm:mb-0">
            <button type="button" class="db-tabBtn active" data-tab="#member-info">
                <i class="lab lab-line-circle-user lab-font-size-18"></i>
                {{ $t('label.member_information') }}
            </button>
            <button type="button" class="db-tabBtn" data-tab="#points">
                <!-- <i class="lab lab-image lab-font-size-16"></i> -->
                <i class="lab lab-line-gift lab-font-size-18"></i>
                {{ $t('label.point_transactions') }}
            </button>
        </div>
        
        <div class="db-tabDiv active" id="member-info">

            <div class="row py-2">
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.name') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ member.name || 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.phone') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ member.phone || 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.card_number') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ member.card_number || 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.points') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2 font-semibold text-primary">{{ member.point_balance || 0 }}</span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.status') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2" :class="statusClass(member.is_active)">
                            {{ member.is_active ? $t('label.active') : $t('label.inactive') }}
                        </span>
                    </div>
                </div>
                <div class="col-12 sm:col-6 !py-1.5" v-if="member.branch">
                    <div class="db-list-item p-0">
                        <span class="db-list-item-title w-full sm:w-1/2">{{ $t('label.branch') }}</span>
                        <span class="db-list-item-text w-full sm:w-1/2">{{ member.branch.name || 'N/A' }}</span>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="db-tabDiv" id="points">
            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.points') }}</th>
                            <th class="db-table-head-th">{{ $t('label.description') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="member.point_transactions && member.point_transactions.length > 0">
                        <tr class="db-table-body-tr" v-for="transaction in member.point_transactions" :key="transaction.id">
                            <td class="db-table-body-td">
                                {{ dateFormat(transaction.created_at) }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="transaction.type === 'earn' ? 'text-green-600' : 'text-red-600'">
                                    {{ $t(`label.${transaction.type}`) }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                <span :class="transaction.type === 'earn' ? 'text-green-600' : 'text-red-600'"> {{ transaction.type === 'earn' ? '+' : '-' }}{{ transaction.points }} </span>
                            </td>
                            <td class="db-table-body-td">
                                {{ transaction.description || '-' }}
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="4" class="text-center py-4">{{ $t('message.no_data_found') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from '../components/LoadingComponent';
import appService from '../../../services/appService';

export default {
    name: 'MemberShowComponent',
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            activeTab: 'member-info',
        };
    },
    mounted() {
        this.show();
    },
    computed: {
        member: function () {
            return this.$store.getters['member/show'];
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        dateFormat: function (date) {
            return appService.dateFormat(date);
        },
        show: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('member/show', this.$route.params.id)
                .then((res) => {
                    console.log('Member data loaded', res);
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
    },
};
</script>
