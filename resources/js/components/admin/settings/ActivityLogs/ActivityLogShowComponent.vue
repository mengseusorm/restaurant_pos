<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('menu.activity_logs') }}</h3>
                <div class="db-card-filter">
                    <button v-print="printObj" class="db-btn h-[37px] text-white bg-primary">
                        <i class="lab lab-printer-line lab-font-size-17"></i>
                        {{ $t('button.print') }}
                    </button>
                </div>
            </div>
            <div class="db-card-body" id="print">
            `  <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white shadow print:divide-black print:border-black print:shadow-none">
                    <!-- Title + icon -->
                    <li class="p-4 flex items-center gap-3">
                        <span class="text-black print:text-black">
                            <i class="fas fa-user-check"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-black print:text-black">{{ activityLogShow.description }}</p>
                            <p class="text-sm text-black print:text-black">{{ activityLogShow.formatted_created_at }}</p>
                        </div>
                    </li>

                    <!-- Causer -->
                    <li class="p-4">
                        <p class="text-sm text-black print:text-black">
                            <span class="font-medium print:font-bold">Causer:</span>
                            {{ activityLogShow.causer?.name }} ({{ activityLogShow.causer?.email }})
                        </p>
                    </li>

                    <!-- Properties -->
                    <li class="p-4">
                        <div v-for="(item, index) in activityLogShow.formatted_properties" :key="index">
                            <p class="text-sm text-black print:text-black">
                                <span class="font-medium print:font-bold">{{ item.key }}:</span> {{ item.value }}
                            </p>
                        </div>
                    </li>

                    <!-- Timestamps -->
                    <li class="p-4">
                        <p class="text-xs text-black print:text-black">
                            Created at: {{ activityLogShow.created_at }}
                        </p>
                    </li>
                </ul>
            </div>`
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import addressTypeEnum from "../../../enums/modules/addressTypeEnum";
import PrintComponent from "../components/buttons/export/PrintComponent";
import print from "vue3-print-nb";

export default {
    name: "ActivityLogShowComponent",
    components: {
        LoadingComponent,
        PrintComponent
    },
    directives: {
        print
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                addressTypeEnum: addressTypeEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            printObj: {
                id: "print",
                popTitle: this.$t("menu.activity_logs"),
            },
        };
    },
    computed: {
        activityLogShow: function () {
            return this.$store.getters["activityLog/show"];
        },
    },
    mounted() {
        this.loading.isActive = true;
        console.log('Fetching activity log details for ID:', this.$route.params.id);
        this.$store.dispatch("activityLog/show", this.$route.params.id).then((res) => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    methods: {
    },
};
</script>
