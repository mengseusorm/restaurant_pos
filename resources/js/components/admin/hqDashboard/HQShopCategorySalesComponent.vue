<template>
    <div class="db-card">
        <div class="db-card-header">
            <div class="header-content">
                <h3 class="db-card-title">
                    {{ $t('label.sales_by_shop_category') }}
                </h3>
                <span class="db-card-subtitle">Sales distribution by shop categories</span> 
            </div>
        </div>
        <div class="db-card-body">
            <div v-if="data && data.length > 0" class="category-content">
                <!-- Left Side: Pie Chart -->
                <div class="chart-section">
                    <div id="shop-category-pie-chart" class="chart-container-pie"></div>
                </div>
                <!-- Right Side: List View -->
                <div class="list-section">
                    <div v-for="(category, index) in data" :key="category.category_id" class="category-item">
                        <div class="category-row">
                            <div class="category-header">
                                <div class="category-indicator" :style="{ backgroundColor: getColorByIndex(index) }"></div>
                                <div class="category-info">
                                    <div class="category-name">
                                        <i class="lab lab-shop category-icon"></i>
                                        <span class="name-text">{{ category.category_name }}</span>
                                    </div>
                                    <div class="category-sales" v-if="isMultiCurrency(category.total_sales)">
                                        <div v-for="(amount, currency) in category.total_sales" :key="currency" class="currency-sales-item">
                                            <span class="currency-code">{{ currency }}</span>
                                            {{ formatCurrencyAmount(amount, currency) }}
                                        </div>
                                    </div>
                                    <div class="category-sales" v-else>
                                        {{ formatCurrency(category.total_sales) }}
                                    </div>
                                </div>
                            </div>

                            <div class="category-metrics">
                                <div class="metric-item">
                                    <span class="metric-label">Orders</span>
                                    <span class="metric-value">{{ formatNumber(category.total_orders) }}</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-label">Share</span>
                                    <span class="metric-value">{{ getPercentage(category) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <div class="empty-icon">
                    <i class="lab lab-shop"></i>
                </div>
                <p class="empty-text">{{ $t('label.no_data_available') }}</p>
            </div>
        </div>
    </div> 
</template>

<script>
export default {
    name: 'HQShopCategorySalesComponent',
    props: {
        data: {
            type: Array,
            required: true,
            default: () => [],
        },
    },
    data() {
        return {
            chartInstance: null,
            chartColors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'],
        };
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
            deep: true,
        },
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
                setTimeout(() => {
                    this.waitForApexCharts();
                }, 100);
            }
        },
        createChart() {
            if (typeof window.ApexCharts === 'undefined') {
                console.error('ApexCharts is not available');
                return;
            }

            if (!this.data || this.data.length === 0) {
                return;
            }

            const chartData = this.getChartData();

            const options = {
                series: chartData.values,
                chart: {
                    type: 'pie',
                    height: 350,
                    fontFamily: 'inherit',
                },
                labels: chartData.labels,
                colors: this.chartColors,
                legend: {
                    show: false,
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '0%',
                        },
                    },
                },
                tooltip: {
                    y: {
                        formatter: (value) => {
                            return this.formatNumber(value);
                        },
                    },
                },
                responsive: [
                    {
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 300,
                            },
                        },
                    },
                ],
            };

            const chartElement = document.querySelector('#shop-category-pie-chart');
            if (chartElement && !this.chartInstance) {
                this.chartInstance = new window.ApexCharts(chartElement, options);
                this.chartInstance.render();
            }
        },
        updateChart() {
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }
            this.$nextTick(() => {
                this.createChart();
            });
        },
        getChartData() {
            const labels = [];
            const values = [];

            this.data.forEach((category) => {
                labels.push(category.category_name);

                let totalSales = 0;
                if (this.isMultiCurrency(category.total_sales)) {
                    const amounts = Object.values(category.total_sales);
                    totalSales = parseFloat(amounts[0]) || 0;
                } else {
                    totalSales = parseFloat(category.total_sales) || 0;
                }

                values.push(totalSales);
            });

            return { labels, values };
        },
        formatNumber(number) {
            return new Intl.NumberFormat('en-US').format(number || 0);
        },
        formatCurrency(amount) {
            if (typeof amount === 'string') {
                amount = parseFloat(amount);
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(amount || 0);
        },
        formatCurrencyAmount(amount, currency) {
            if (typeof amount === 'string') {
                amount = parseFloat(amount);
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency || 'USD',
            }).format(amount || 0);
        },
        isMultiCurrency(totalSales) {
            return typeof totalSales === 'object' && totalSales !== null && !Array.isArray(totalSales);
        },
        getPercentage(category) {
            if (!this.data || this.data.length === 0) return 0;

            let totalSum = 0;
            let currentValue = 0;

            this.data.forEach((cat) => {
                let sales = 0;
                if (this.isMultiCurrency(cat.total_sales)) {
                    const amounts = Object.values(cat.total_sales);
                    sales = parseFloat(amounts[0]) || 0;
                } else {
                    sales = parseFloat(cat.total_sales) || 0;
                }
                totalSum += sales;
            });

            if (this.isMultiCurrency(category.total_sales)) {
                const amounts = Object.values(category.total_sales);
                currentValue = parseFloat(amounts[0]) || 0;
            } else {
                currentValue = parseFloat(category.total_sales) || 0;
            }

            return totalSum > 0 ? Math.round((currentValue / totalSum) * 100) : 0;
        },
        getColorByIndex(index) {
            return this.chartColors[index % this.chartColors.length];
        },
    },
};
</script>

<style scoped>
/* Shop Category Sales Component */
.shop-category-sales-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    height: 100%;
}

.card-header {
    padding: 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 4px 0;
}

.card-subtitle {
    font-size: 13px;
    color: #6b7280;
}

.card-body {
    padding: 20px;
}

.category-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
}

@media (max-width: 1024px) {
    .category-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

/* Chart Section */
.chart-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
}

.chart-container-pie {
    width: 100%;
    max-width: 350px;
}

/* List Section */
.list-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 450px;
    overflow-y: auto;
    padding-right: 10px;
}

.list-section::-webkit-scrollbar {
    width: 6px;
}

.list-section::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.list-section::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.list-section::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.category-item {
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f9fafb;
    transition: all 0.2s ease;
}

.category-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    background: white;
}

.category-row {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.category-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.category-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
}

.category-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.category-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
}

.category-icon {
    font-size: 16px;
    color: #6b7280;
}

.name-text {
    flex: 1;
}

.category-sales {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 15px;
    font-weight: 700;
    color: #059669;
}

.currency-sales-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.currency-code {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
}

.category-metrics {
    display: flex;
    gap: 24px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
}

.metric-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.metric-label {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.metric-value {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    color: #e5e7eb;
    margin-bottom: 16px;
}

.empty-text {
    font-size: 14px;
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-body {
        padding: 16px;
    }

    .category-content {
        gap: 16px;
    }

    .category-item {
        padding: 12px;
    }

    .category-name {
        font-size: 13px;
    }

    .category-sales {
        font-size: 14px;
    }

    .metric-item {
        gap: 2px;
    }
}
</style>
