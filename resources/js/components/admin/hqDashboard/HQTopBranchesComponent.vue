<template>
    <div class="db-card">
        <div class="db-card-header">
            <div class="header-content">
                <h3 class="db-card-title">
                    {{ $t('label.top_performing_branches') }}
                    <span class="float-right"
                        ><router-link
                            :to="{
                                path: '/admin/branch-sale-report',
                                query: {
                                    from_date: from_date,
                                    to_date: to_date,
                                },
                            }"
                            class="view-more-btn"
                        >
                            {{ $t('label.view_more') }}
                        </router-link></span
                    >
                </h3>
                <!-- <span class="card-subtitle">Performance ranking by sales</span> -->
            </div>
        </div>
        <div class="db-card-body">
            <div v-if="data && data.length > 0" class="branches-list">
                <div v-for="(branch, index) in data" :key="branch.branch_id" class="branch-item" :class="{ 'is-top-performer': index < 3 }">
                    <div class="branch-rank">
                        <span class="rank-number" :class="getRankClass(index)">
                            {{ index + 1 }}
                        </span>
                    </div>

                    <div class="branch-info">
                        <div class="branch-header">
                            <div class="branch-name">
                                <i class="lab lab-shop branch-icon"></i>
                                <span class="name-text">{{ branch.branch_name }}</span>
                            </div>
                            <div class="branch-sales" v-if="isMultiCurrency(branch.total_sales)">
                                <div v-for="(amount, currency) in branch.total_sales" :key="currency" class="currency-sales-item">
                                    <span class="currency-code">{{ currency }}</span>
                                    {{ formatCurrencyAmount(amount, currency) }}
                                </div>
                            </div>
                            <div class="branch-sales" v-else>
                                {{ formatCurrency(branch.total_sales) }}
                            </div>
                        </div>

                        <div class="branch-metrics">
                            <div class="metric-item">
                                <span class="metric-label">Orders</span>
                                <span class="metric-value">{{ formatNumber(branch.total_orders) }}</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Avg. Order</span>
                                <span class="metric-value">{{ formatCurrency(getAverageOrderValue(branch)) }}</span>
                            </div>
                        </div>

                        <div class="performance-bar">
                            <div class="performance-track">
                                <div class="performance-fill" :class="getPerformanceClass(index)" :style="{ width: getPerformancePercentage(branch, index) + '%' }"></div>
                            </div>
                            <span class="performance-text"> {{ getPerformancePercentage(branch, index) }}% </span>
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
    name: 'HQTopBranchesComponent',
    props: {
        data: {
            type: Array,
            required: true,
            default: () => [],
        },
        from_date: {
            type: String,
            default: null,
        },
        to_date: {
            type: String,
            default: null,
        },
    },
    methods: {
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
        getAverageOrderValue(branch) {
            let sales = 0;

            if (this.isMultiCurrency(branch.total_sales)) {
                // For multi-currency, take the first currency value or sum if same base
                const amounts = Object.values(branch.total_sales);
                sales = parseFloat(amounts[0]) || 0;
            } else {
                sales = parseFloat(branch.total_sales) || 0;
            }

            const orders = parseInt(branch.total_orders) || 1;
            return sales / orders;
        },
        getPerformancePercentage(branch, index) {
            if (!this.data || this.data.length === 0) return 0;

            let maxSales = 0;
            let currentSales = 0;

            // Handle multi-currency for max sales calculation
            if (this.isMultiCurrency(this.data[0].total_sales)) {
                const amounts = Object.values(this.data[0].total_sales);
                maxSales = parseFloat(amounts[0]) || 1;
            } else {
                maxSales = parseFloat(this.data[0].total_sales) || 1;
            }

            // Handle multi-currency for current sales calculation
            if (this.isMultiCurrency(branch.total_sales)) {
                const amounts = Object.values(branch.total_sales);
                currentSales = parseFloat(amounts[0]) || 0;
            } else {
                currentSales = parseFloat(branch.total_sales) || 0;
            }

            return Math.round((currentSales / maxSales) * 100);
        },
        getRankClass(index) {
            if (index === 0) return 'rank-gold';
            if (index === 1) return 'rank-silver';
            if (index === 2) return 'rank-bronze';
            return 'rank-default';
        },
        getPerformanceClass(index) {
            if (index === 0) return 'performance-gold';
            if (index === 1) return 'performance-silver';
            if (index === 2) return 'performance-bronze';
            return 'performance-default';
        },
    },
};
</script>

<style scoped>
/* Professional Top Branches Component */
.top-branches-card {
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
    padding: 16px 20px 20px 20px;
}

/* Branches List */
.branches-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.branch-item {
    display: flex;
    align-items: flex-start;
    padding: 16px;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    background: #fafbfc;
    transition: all 0.2s ease-in-out;
}

.branch-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateY(-1px);
}

.branch-item.is-top-performer {
    background: linear-gradient(135deg, #fef7cd 0%, #fef3c7 100%);
    border-color: #f59e0b;
}

.branch-item.is-top-performer:hover {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

/* Rank Number */
.branch-rank {
    margin-right: 16px;
    flex-shrink: 0;
}

.rank-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    color: white;
}

.rank-number.rank-gold {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.rank-number.rank-silver {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    box-shadow: 0 2px 4px rgba(107, 114, 128, 0.3);
}

.rank-number.rank-bronze {
    background: linear-gradient(135deg, #92400e, #78350f);
    box-shadow: 0 2px 4px rgba(146, 64, 14, 0.3);
}

.rank-number.rank-default {
    background: linear-gradient(135deg, #9ca3af, #6b7280);
    box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3);
}

/* Branch Info */
.branch-info {
    flex: 1;
    min-width: 0;
}

.branch-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.branch-name {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}

.branch-icon {
    color: #3b82f6;
    font-size: 16px;
    flex-shrink: 0;
}

.name-text {
    font-weight: 600;
    color: #111827;
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.branch-sales {
    font-weight: 700;
    color: #059669;
    font-size: 16px;
    white-space: nowrap;
}

/* Branch Metrics */
.branch-metrics {
    display: flex;
    gap: 20px;
    margin-bottom: 12px;
}

.metric-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.metric-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.metric-value {
    font-size: 14px;
    color: #374151;
    font-weight: 600;
}

/* Performance Bar */
.performance-bar {
    display: flex;
    align-items: center;
    gap: 12px;
}

.performance-track {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.performance-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease-in-out;
}

.performance-fill.performance-gold {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.performance-fill.performance-silver {
    background: linear-gradient(90deg, #6b7280, #4b5563);
}

.performance-fill.performance-bronze {
    background: linear-gradient(90deg, #92400e, #78350f);
}

.performance-fill.performance-default {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.performance-text {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    min-width: 35px;
    text-align: right;
}

/* Multi-currency styles */
.currency-sales-item {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}

.currency-sales-item:last-child {
    margin-bottom: 0;
}

.currency-code {
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    background: #f3f4f6;
    padding: 1px 4px;
    border-radius: 3px;
    text-transform: uppercase;
    min-width: 28px;
    text-align: center;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-text {
    font-size: 14px;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 640px) {
    .branch-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .branch-metrics {
        gap: 16px;
    }

    .card-body {
        padding: 12px 16px 16px 16px;
    }

    .branch-item {
        padding: 12px;
    }

    .branch-rank {
        margin-right: 12px;
    }

    .rank-number {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }
}
</style>
