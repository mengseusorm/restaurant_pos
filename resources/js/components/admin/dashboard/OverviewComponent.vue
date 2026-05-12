<template>
    <LoadingComponent :props="loading" />
    <div class="mb-9">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-[22px] leading-[34px] mb-3 capitalize">{{ $t("menu.overview") }}</h4>
            <div class="flex items-center gap-1">
                <div class="col-12 sm:col-6">
                    <label for="searchStartDate" class="db-field-title">{{ $t("label.start_date") }}</label>
                    <Datepicker autoApply v-model="first_date"></Datepicker>
                </div>
                <div class="col-12 sm:col-6">
                    <label for="searchEndDate" class="db-field-title">{{ $t("label.end_date") }}</label>
                    <Datepicker autoApply v-model="last_date"></Datepicker>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 sm:col-6 xl:col-3">
                <div class="p-4 rounded-lg flex items-center gap-4 bg-[#FF4F99]">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white">
                        <i class="lab lab-total-sale lab-font-size-24 lab-color-pink"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">{{ $t('label.total_sales') }}</h3>
                        <h4 class="font-semibold text-[22px] leading-[34px] text-white">{{ total_sales }}{{ branch.currency_id?.symbol }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 sm:col-6 xl:col-3">
                <div class="p-4 rounded-lg flex items-center gap-4 bg-[#8262FE]">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white">
                        <i class="lab lab-total-orders lab-font-size-24 lab-color-portage"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">{{ $t('label.total_orders') }}</h3>
                        <h4 class="font-semibold text-[22px] leading-[34px] text-white">{{ total_orders }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 sm:col-6 xl:col-3">
                <div class="p-4 rounded-lg flex items-center gap-4 bg-[#567DFF]">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white">
                        <i class="lab lab-total-customers lab-font-size-24 lab-color-cornflower-blue"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">{{ $t('label.total_customers') }}</h3>
                        <h4 class="font-semibold text-[22px] leading-[34px] text-white">{{ total_customers }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 sm:col-6 xl:col-3">
                <div class="p-4 rounded-lg flex items-center gap-4 bg-[#A953FF]">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white">
                        <i class="lab lab-total-menu-items lab-font-size-24 lab-color-heliotrope"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-white">{{ $t('label.total_menu_items') }}</h3>
                        <h4 class="font-semibold text-[22px] leading-[34px] text-white">{{ total_menu_items }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import appService from "../../../services/appService";

export default {
    name: "OverviewComponent",
    components: { LoadingComponent, Datepicker },
    data() {
        return {
            loading: {
                isActive: false,
            },
            first_date: null,
            last_date: null,
            total_sales: null,
            total_orders: null,
            total_customers: null,
            total_menu_items: null,
        };
    },
    mounted() {
        // Initialize dates: from yesterday to today
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        // Use branch open_time and close_time if available
        if (this.branch && this.branch.open_time) {
            const [hours, minutes] = this.branch.open_time.split(':');
            startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        } else {
            startDate.setHours(0, 0, 0, 0);
        }

        if (this.branch && this.branch.close_time) {
            const [hours, minutes] = this.branch.close_time.split(':');
            endDate.setHours(parseInt(hours), parseInt(minutes), 59, 999);
        } else {
            endDate.setHours(23, 59, 59, 999);
        }

        this.first_date = startDate;
        this.last_date = endDate;

        this.fetchAllData();
    },
    computed: {
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
    },
    watch: {
        first_date() {
            this.handleDateChange();
        },
        last_date() {
            this.handleDateChange();
        }
    },
    methods: {
        handleDateChange: function () {
            // Only fetch data if both dates are selected
            if (this.first_date && this.last_date) {
                // Ensure start date is before end date
                if (this.first_date > this.last_date) {
                    // Swap dates if start is after end
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.fetchAllData();
            }
        },
        fetchAllData: function () {
            this.totalSales();
            this.totalOrders();
            this.totalCustomers();
            this.totalMenuItems();
        },

        totalSales: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dashboard/totalSales", {
                from_date: appService.formatDateTime(this.first_date),
                to_date: appService.formatDateTime(this.last_date),
            }).then((res) => {
                this.total_sales = res.data.data.total_sales;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        totalOrders: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dashboard/totalOrders", {
                from_date: appService.formatDateTime(this.first_date),
                to_date: appService.formatDateTime(this.last_date),
            }).then((res) => {
                this.total_orders = res.data.data.total_orders;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        totalCustomers: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dashboard/totalCustomers", {
                from_date: appService.formatDateTime(this.first_date),
                to_date: appService.formatDateTime(this.last_date),
            }).then((res) => {
                this.total_customers = res.data.data.total_customers;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        totalMenuItems: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dashboard/totalMenuItems", {
                from_date: appService.formatDateTime(this.first_date),
                to_date: appService.formatDateTime(this.last_date),
            }).then((res) => {
                this.total_menu_items = res.data.data.total_menu_items;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
    },
}
</script>
