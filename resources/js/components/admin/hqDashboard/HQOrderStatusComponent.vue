<template>
    <div class="db-card">
        <div class="db-card-header">
            <h5 class="db-card-title">{{ $t('label.order_status_summary') }}</h5>
        </div>
        <div class="db-card-body">
            <div class="space-y-4">
                <div 
                    v-for="status in data" 
                    :key="status.status"
                    class="flex items-center justify-between p-3 rounded-lg"
                    :class="getStatusClass(status.status)"
                >
                    <div class="flex items-center space-x-3">
                        <div 
                            class="w-3 h-3 rounded-full"
                            :class="getStatusDotClass(status.status)"
                        ></div>
                        <span class="font-medium">{{ status.status_name }}</span>
                    </div>
                    <span class="font-bold text-lg">{{ formatNumber(status.count) }}</span>
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
    name: "HQOrderStatusComponent",
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
        getStatusClass(status) {
            const classes = {
                1: 'bg-yellow-50 border border-yellow-200', // Pending
                2: 'bg-blue-50 border border-blue-200',     // Processing
                3: 'bg-purple-50 border border-purple-200', // Out for delivery
                4: 'bg-green-50 border border-green-200',   // Delivered
                5: 'bg-red-50 border border-red-200',       // Canceled
                6: 'bg-orange-50 border border-orange-200', // Returned
                7: 'bg-gray-50 border border-gray-200'      // Rejected
            };
            return classes[status] || 'bg-gray-50 border border-gray-200';
        },
        getStatusDotClass(status) {
            const classes = {
                1: 'bg-yellow-500', // Pending
                2: 'bg-blue-500',   // Processing
                3: 'bg-purple-500', // Out for delivery
                4: 'bg-green-500',  // Delivered
                5: 'bg-red-500',    // Canceled
                6: 'bg-orange-500', // Returned
                7: 'bg-gray-500'    // Rejected
            };
            return classes[status] || 'bg-gray-500';
        }
    }
};
</script>
