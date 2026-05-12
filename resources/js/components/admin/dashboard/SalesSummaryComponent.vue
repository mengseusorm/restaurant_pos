<template>
    <LoadingComponent :props="loading" />
    <div class="col-12 xl:col-6">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.sales_summary') }}</h3>
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
            <div class="db-card-body">
                <ul class="flex gap-11">
                    <li>
                        <div class="flex items-center gap-2.5">
                            <i class="lab lab-sale-summary lab-font-size-20 lab-font-color-2"></i>
                            <h3 class="font-bold text-[22px] leading-[34px]">{{ total_sales }}{{ branch.currency_id?.symbol }}</h3>
                        </div>
                        <p class="text-xs capitalize">{{ $t("label.total_sales") }}</p>
                    </li>
                    <li>
                        <div class="flex items-center gap-2.5">
                            <i class="lab lab-sale-summary lab-font-size-20 lab-font-color-2"></i>
                            <h3 class="font-bold text-[22px] leading-[34px]">{{ avg_per_day }}{{ branch.currency_id?.symbol }}</h3>
                        </div>
                        <p class="text-xs capitalize">{{ $t("label.avg_sales_per_day") }}</p>
                    </li>
                </ul>

                <div id="area-chart"></div>
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
    name: "SalesSummaryComponent",
    components: { LoadingComponent, Datepicker },
    data() {
        return {
            loading: {
                isActive: false,
            },
            first_date: null,
            last_date: null,
            total_sales: null,
            avg_per_day: null,
            chart: null,
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

        this.salesSummary();
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
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.salesSummary();
            }
        },

        salesSummary: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dashboard/salesSummary", {
                from_date: appService.formatDateTime(this.first_date),
                to_date: appService.formatDateTime(this.last_date)
            }).then((res) => {
                this.total_sales = res.data.data.total_sales;
                this.avg_per_day = res.data.data.avg_per_day;

                let options = {
                    series: [{
                        name: this.$t('label.sales'),
                        data: res.data.data.per_day_sales,
                    }],
                    chart: {
                        type: 'area',
                        height: 250,
                        fontFamily: 'inherit',
                        parentHeightOffset: 0,
                        zoom: { enabled: false },
                        toolbar: { show: false, },
                    },
                    xaxis: {
                        tooltip: { enabled: false },
                        axisBorder: { show: false },
                    },
                    stroke: {
                        width: 3,
                        lineCap: "round",
                        curve: "smooth",
                    },
                    colors: ["#FF4F99"],
                    grid: { show: false },
                    yaxis: { show: false },
                    dataLabels: { enabled: false, },
                };
                if (this.chart) {
                    this.chart.destroy();
                }

                // Wait for DOM to be ready and check if element exists
                this.$nextTick(() => {
                    const chartElement = document.querySelector("#area-chart");
                    if (chartElement) {
                        this.chart = new ApexCharts(chartElement, options);
                        this.chart.render().catch((error) => {
                            console.error('Error rendering area chart:', error);
                        });
                    } else {
                        console.error('Chart element #area-chart not found');
                    }
                });

                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    }
}
</script>
