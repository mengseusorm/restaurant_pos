<template>
    <div class="db-card">
        <div class="db-card-header border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                <h5 class="db-card-title text-lg lg:text-xl font-semibold">{{ $t('label.sales_trend') }}</h5> 
            </div>
        </div>
        <div class="db-card-body p-4 lg:p-6">
            <div id="hq-sales-trend-chart" class="min-h-[300px] lg:min-h-[400px] xl:min-h-[450px]"></div>
        </div>
    </div> 
</template> 
<script>
export default {
    name: "HQSalesTrendComponent",
    props: {
        data: {
            type: Object,
            required: true,
            default: () => ({
                labels: [],
                data: []
            })
        }
    },
    data() {
        return {
            chartInstance: null
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.waitForApexCharts();
        });
    },
    watch: {
        data: {
            handler() {
                this.updateChart();
            },
            deep: true
        }
    },
    beforeUnmount() {
        if (this.chartInstance) {
            this.chartInstance.destroy();
        }
    },
    methods: {
        waitForApexCharts() {
            if (typeof window.ApexCharts !== 'undefined') {
                this.createChart();
            } else {
                // Check every 100ms for ApexCharts to be available
                setTimeout(() => {
                    this.waitForApexCharts();
                }, 100);
            }
        },
        createChart() {
            // Check if ApexCharts is available
            if (typeof window.ApexCharts === 'undefined') {
                console.error('ApexCharts is not available');
                return;
            }
            
            // Handle multi-currency data
            const seriesData = this.getSeriesData();
            
            const options = {
                series: seriesData,
                chart: {
                    type: 'area',
                    height: window.innerWidth >= 1280 ? 400 : window.innerWidth >= 1024 ? 350 : 300,
                    fontFamily: 'inherit',
                    parentHeightOffset: 0,
                    zoom: { enabled: false },
                    toolbar: { 
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                xaxis: {
                    categories: this.data.labels || [],
                    tooltip: { enabled: false },
                    axisBorder: { show: false },
                },
                stroke: {
                    width: 3,
                    lineCap: "round",
                    curve: "smooth",
                },
                colors: ["#3B82F6", "#10B981", "#F59E0B", "#EF4444", "#8B5CF6"],
                grid: { 
                    show: true,
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3
                },
                yaxis: { 
                    show: true,
                    labels: {
                        formatter: function(value) {
                            return new Intl.NumberFormat('en-US').format(value);
                        }
                    }
                },
                dataLabels: { enabled: false },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.3,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                            const seriesName = w.config.series[seriesIndex].name;
                            const currency = seriesName.split(' ')[0]; // Extract currency from series name
                            return new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: currency
                            }).format(value);
                        }
                    }
                },
                legend: {
                    show: seriesData.length > 1,
                    position: 'top',
                    horizontalAlign: 'right'
                }
            };

            this.chartInstance = new window.ApexCharts(document.querySelector("#hq-sales-trend-chart"), options);
            this.chartInstance.render();
        },
        updateChart() {
            if (this.chartInstance) {
                const seriesData = this.getSeriesData();
                
                this.chartInstance.updateOptions({
                    xaxis: {
                        categories: this.data.labels || []
                    }
                });
                this.chartInstance.updateSeries(seriesData);
            }
        },
        getSeriesData() {
            // Check if data is multi-currency (object) or single currency (array)
            if (Array.isArray(this.data.data)) {
                // Single currency - legacy format
                return [{
                    name: 'Sales',
                    data: this.data.data
                }];
            } else if (typeof this.data.data === 'object' && this.data.data !== null) {
                // Multi-currency format
                return Object.entries(this.data.data).map(([currency, values]) => ({
                    name: `${currency} Sales`,
                    data: values
                }));
            } else {
                // Fallback
                return [{
                    name: 'Sales',
                    data: []
                }];
            }
        }
    }
};
</script>
