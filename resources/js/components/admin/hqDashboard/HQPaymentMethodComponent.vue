<template>
    <div class="db-card">
        <div class="db-card-header">
            <h5 class="db-card-title">{{ $t('label.payment_method_summary') }}</h5>
        </div>
        <div class="db-card-body">
            <div class="space-y-3">
                <div 
                    v-for="method in data" 
                    :key="method.payment_method"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
                >
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-900">{{ method.payment_method_name }}</span>
                        <span class="text-sm text-gray-500">{{ formatNumber(method.total_orders) }} orders</span>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-lg text-green-600" v-if="isMultiCurrency(method.total_amount)">
                            <div 
                                v-for="(amount, currency) in method.total_amount" 
                                :key="currency"
                                class="currency-amount-item"
                            >
                                <span class="text-xs text-gray-500 uppercase">{{ currency }}</span>
                                {{ formatCurrencyAmount(amount, currency) }}
                            </div>
                        </div>
                        <div class="font-bold text-lg text-green-600" v-else>
                            {{ formatCurrency(method.total_amount) }}
                        </div>
                    </div>
                </div>
            </div> 
            <div v-if="!data || data.length === 0" class="text-center py-8 text-gray-500">
                {{ $t('label.no_data_available') }}
            </div>
        </div>
    </div>  
</template>

<script>
export default {
    name: "HQPaymentMethodComponent",
    props: {
        data: {
            type: Array,
            required: true,
            default: () => []
        }
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
        isMultiCurrency(totalAmount) {
            return typeof totalAmount === 'object' && totalAmount !== null && !Array.isArray(totalAmount);
        }
    }
};
</script>

<style scoped>
.currency-amount-item {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-bottom: 4px;
}

.currency-amount-item:last-child {
    margin-bottom: 0;
}
</style>
