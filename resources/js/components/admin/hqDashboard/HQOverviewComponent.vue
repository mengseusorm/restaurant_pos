<template>
    <!-- Key Metrics Overview - Professional and Clean --> 
    <div class="p-4 sm:p-5 mb-9">
        <div class="metrics-grid">
            <div class="metric-card metric-sales">
                <div class="metric-content">
                    <div class="metric-header">
                        <div class="metric-info">
                            <h3 class="metric-title">{{ $t('label.total_sales') }}</h3>
                            <div class="metric-value-multi" v-if="isMultiCurrency(data.total_sales)">
                                <div 
                                    v-for="(amount, currency) in data.total_sales" 
                                    :key="currency"
                                    class="currency-item"
                                >
                                    <span class="currency-code">{{ currency }}</span>
                                    <span class="currency-amount">{{ formatCurrencyAmount(amount, currency) }}</span>
                                </div>
                            </div>
                            <div class="metric-value" v-else>{{ formatCurrency(data.total_sales) }}</div>
                        </div>
                        <div class="metric-icon">
                            <i class="lab lab-fill-moneys"></i>
                        </div>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-positive">
                            <i class="fas fa-arrow-up"></i>
                            +15.3%
                        </span>
                        <span class="trend-label">vs last period</span>
                    </div>
                </div>
            </div>
            
            <div class="metric-card metric-orders">
                <div class="metric-content">
                    <div class="metric-header">
                        <div class="metric-info">
                            <h3 class="metric-title">{{ $t('label.total_orders') }}</h3>
                            <div class="metric-value">{{ formatNumber(data.total_orders) }}</div>
                        </div>
                        <div class="metric-icon">
                            <i class="lab lab-pos-orders"></i>
                        </div>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-positive">
                            <i class="fas fa-arrow-up"></i>
                            +8.7%
                        </span>
                        <span class="trend-label">vs last period</span>
                    </div>
                </div>
            </div>
            
            <div class="metric-card metric-customers">
                <div class="metric-content">
                    <div class="metric-header">
                        <div class="metric-info">
                            <h3 class="metric-title">{{ $t('label.total_customers') }}</h3>
                            <div class="metric-value">{{ formatNumber(data.total_customers) }}</div>
                        </div>
                        <div class="metric-icon">
                            <i class="lab lab-customers"></i>
                        </div>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-positive">
                            <i class="fas fa-arrow-up"></i>
                            +12.1%
                        </span>
                        <span class="trend-label">vs last period</span>
                    </div>
                </div>
            </div>
            
            <div class="metric-card metric-branches">
                <div class="metric-content">
                    <div class="metric-header">
                        <div class="metric-info">
                            <h3 class="metric-title">{{ $t('label.total_branches') }}</h3>
                            <div class="metric-value">{{ formatNumber(data.total_branches) }}</div>
                        </div>
                        <div class="metric-icon">
                            <i class="lab lab-shop"></i>
                        </div>
                    </div>
                    <div class="metric-trend">
                        <span class="trend-neutral">
                            <i class="fas fa-check"></i>
                            All active
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "HQOverviewComponent",
    props: {
        data: {
            type: Object,
            required: true,
            default: () => ({
                total_sales: 0,
                total_orders: 0,
                total_customers: 0,
                total_branches: 0
            })
        }
    },
    methods: {
        formatCurrency(amount) {
            if (typeof amount === 'string') {
                amount = parseFloat(amount);
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount || 0);
        },
        formatCurrencyAmount(amount, currency) {
            if (typeof amount === 'string') {
                amount = parseFloat(amount);
            }
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency || 'USD'
            }).format(amount || 0);
        },
        formatNumber(number) {
            return new Intl.NumberFormat('en-US').format(number || 0);
        },
        isMultiCurrency(totalSales) {
            return typeof totalSales === 'object' && totalSales !== null && !Array.isArray(totalSales);
        }
    }
};
</script>

<style scoped>
/* Professional Metrics Overview Styles */
.metrics-overview {
    margin-bottom: 0;
}

.metrics-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 640px) {
    .metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .metrics-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
}

.metric-card {
    background: white;
    border-radius: 8px; 
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    overflow: hidden;
}
 

.metric-content {
    padding: 24px;
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.metric-info {
    flex: 1;
}

.metric-title {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.metric-value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
    font-variant-numeric: tabular-nums;
}

.metric-value-multi {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.currency-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.currency-code {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    min-width: 40px;
    text-align: center;
}

.currency-amount {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    font-variant-numeric: tabular-nums;
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.metric-sales .metric-icon {
    background: #dbeafe;
    color: #2563eb;
}

.metric-orders .metric-icon {
    background: #dcfce7;
    color: #16a34a;
}

.metric-customers .metric-icon {
    background: #fae8ff;
    color: #a855f7;
}

.metric-branches .metric-icon {
    background: #fed7aa;
    color: #ea580c;
}

.metric-trend {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.trend-positive {
    color: #16a34a;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.trend-neutral {
    color: #6b7280;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.trend-label {
    color: #6b7280;
}

/* Responsive adjustments */
@media (max-width: 639px) {
    .metric-content {
        padding: 20px;
    }
    
    .metric-value {
        font-size: 24px;
    }
    
    .metric-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
}
</style>
