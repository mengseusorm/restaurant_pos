<template>
    <div class="db-card h-full">
        <div class="db-card-header border-b border-gray-200">
            <h5 class="db-card-title text-lg lg:text-xl font-semibold">{{ $t('label.branch_performance') }}</h5> 
        </div>
         <div class="db-card-body p-4 lg:p-6">
            <div id="hq-branch-performance-chart"></div>
        </div>
    </div>  
</template>

<script>
export default {
    name: "HQBranchPerformanceComponent",
    props: {
        data: {
            type: Array,
            required: true,
            default: () => []
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

            const labels = this.data.map(item => item.branch_name || 'Unknown');
            const salesData = this.data.map(item => {
                if (typeof item.total_sales === 'object') {
                    // For multi-currency, we'll use the first currency or sum all if they're in same base
                    const currencies = Object.values(item.total_sales);
                    return parseFloat(currencies[0]) || 0;
                }
                return parseFloat(item.total_sales) || 0;
            });

            const options = {
                series: salesData,
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'inherit',
                },
                labels: labels,
                colors: [
                    '#3B82F6', // Blue
                    '#10B981', // Green
                    '#F59E0B', // Yellow
                    '#EF4444', // Red
                    '#8B5CF6', // Purple
                    '#EC4899', // Pink
                    '#06B6D4', // Cyan
                    '#FB923C'  // Orange
                ],
                stroke: {
                    width: 2,
                    colors: ['#fff']
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%'
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    markers: {
                        width: 8,
                        height: 8,
                        radius: 4
                    }
                },
                tooltip: {
                    y: {
                        formatter: (value, { seriesIndex, dataPointIndex, w }) => {
                            // Check if w.globals exists and has seriesTotals
                            if (!w || !w.globals || !w.globals.seriesTotals) {
                                return `$${new Intl.NumberFormat('en-US').format(value)}`;
                            }

                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);

                            // Check if original data has multiple currencies
                            const originalItem = this.data && this.data[seriesIndex];
                            if (originalItem && typeof originalItem.total_sales === 'object') {
                                const currencyEntries = Object.entries(originalItem.total_sales);
                                if (currencyEntries.length > 1) {
                                    return currencyEntries.map(([currency, amount]) =>
                                        `${currency}: ${new Intl.NumberFormat('en-US', {
                                            style: 'currency',
                                            currency: currency
                                        }).format(amount)}`
                                    ).join('<br>') + `<br>(${percentage}%)`;
                                } else {
                                    const [currency, amount] = currencyEntries[0];
                                    return `${new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: currency
                                    }).format(amount)} (${percentage}%)`;
                                }
                            }

                            return `$${new Intl.NumberFormat('en-US').format(value)} (${percentage}%)`;
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            this.chartInstance = new window.ApexCharts(document.querySelector("#hq-branch-performance-chart"), options);
            this.chartInstance.render();
        },
        updateChart() {
            if (this.chartInstance) {
                const labels = this.data.map(item => item.branch_name || 'Unknown');
                const salesData = this.data.map(item => {
                    if (typeof item.total_sales === 'object') {
                        // For multi-currency, we'll use the first currency or sum all if they're in same base
                        const currencies = Object.values(item.total_sales);
                        return parseFloat(currencies[0]) || 0;
                    }
                    return parseFloat(item.total_sales) || 0;
                });

                this.chartInstance.updateOptions({
                    labels: labels
                });
                this.chartInstance.updateSeries(salesData);
            }
        }
    }
};
</script>
