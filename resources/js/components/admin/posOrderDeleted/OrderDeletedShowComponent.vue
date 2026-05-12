<template>
    <LoadingComponent :props="loading" /> 
    <!-- Order Header Card -->
     <div class="col-12">
         <div class="db-card mb-12">
             <div class="db-card-header border-b border-gray-200">
                 <div class="flex justify-between items-center">
                    <h3 class="db-card-title text-xl font-semibold">{{ $t('label.order_details') }}</h3>
                    <div class="flex items-center space-x-3 ml-3">
                         <span class="db-badge" :class="getOrderStatusClass(orderDeleted.status)">
                             {{ getOrderStatusText(orderDeleted.status) }}
                         </span>
                         <span class="db-badge" :class="getPaymentStatusClass(orderDeleted.payment_status)">
                             {{ getPaymentStatusText(orderDeleted.payment_status) }}
                         </span>
                     </div>
                 </div>
             </div>
             <div class="db-card-body">
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                     <!-- Order Information -->
                     <div class="space-y-4">
                         <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">{{ $t('label.order_information') }}</h4>
                         
                         <div class="space-y-3">
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.order_id') }}:</span>
                                 <span class="font-mono text-sm px-2 py-1">#{{ orderDeleted.order_serial_no }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.date') }}:</span>
                                 <span class="text-sm">{{ orderDeleted.order_datetime }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.waiting_number') }}:</span>
                                 <span class="font-bold text-lg text-primary">{{ orderDeleted.waiting_number }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.order_type') }}:</span>
                                 <span class="db-badge bg-blue-100 text-blue-800">{{ getOrderTypeText(orderDeleted.order_type) }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.source') }}:</span>
                                 <span class="db-badge bg-purple-100 text-purple-800">{{ getSourceText(orderDeleted.source) }}</span>
                             </div> 
                         </div>
                     </div>
     
                     <!-- Customer Information -->
                     <div class="space-y-4">
                         <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">{{ $t('label.customer_information') }}</h4>
                         
                         <div class="space-y-3">
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.customer_name') }}:</span>
                                 <span class="font-semibold">{{ orderDeleted.customer_name }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.user') }}:</span>
                                 <span>{{ orderDeleted.user }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.branch') }}:</span>
                                 <span>{{ orderDeleted.branch }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.dining_table') }}:</span>
                                 <span class="db-badge bg-green-100 text-green-800" v-for="(item, index) in orderDeleted.dining_table" :key="index">{{ item || $t('label.no_table') }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.payment_method') }}:</span>
                                 <span class="db-badge bg-green-100 text-green-800">{{ (orderDeleted.payment_method) }}</span>
                             </div>
                         </div>
                     </div>
     
                     <!-- Payment Information -->
                     <div class="space-y-4">
                         <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">{{ $t('label.payment_information') }}</h4>
                         
                         <div class="space-y-3">
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.subtotal') }}:</span>
                                 <span class="font-semibold">${{ orderDeleted.subtotal }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.discount') }}:</span>
                                 <span class="text-red-600 font-semibold">-${{ orderDeleted.discount }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.total_tax') }}:</span>
                                 <span>{{ orderDeleted.total_tax_currency_price }}</span>
                             </div>
                             
                             <div class="flex justify-between border-t pt-2">
                                 <span class="text-lg font-bold text-gray-800">{{ $t('label.total') }}:</span>
                                 <span class="text-lg font-bold text-green-600">{{ orderDeleted.total_currency_price }}</span>
                             </div>
                             
                             <div class="flex justify-between">
                                 <span class="text-gray-600 font-medium">{{ $t('label.total_amount') }}:</span>
                                 <span class="font-semibold">{{ orderDeleted.total_amount_price }}</span>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     
         <!-- Deletion Information Card -->
         <div class="db-card" v-if="orderDeleted.deleted_at || orderDeleted.deleted_by || orderDeleted.deleted_reason">
             <div class="db-card-header border-b border-red-200 bg-red-50">
                 <h3 class="db-card-title text-red-800">{{ $t('label.deletion_information') }}</h3>
             </div>
             <div class="db-card-body">
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                     <div class="flex justify-between">
                         <span class="text-gray-600 font-medium">{{ $t('label.deleted_at') }}:</span>
                         <span>{{ orderDeleted.deleted_at || $t('label.not_deleted') }}</span>
                     </div>
                     
                     <div class="flex justify-between">
                         <span class="text-gray-600 font-medium">{{ $t('label.deleted_by') }}:</span>
                         <span>{{ orderDeleted.deleted_by || $t('label.not_applicable') }}</span>
                     </div>
                     
                     <div class="flex justify-between">
                         <span class="text-gray-600 font-medium">{{ $t('label.deleted_reason') }}:</span>
                         <span>{{ orderDeleted.deleted_reason || $t('label.not_provided') }}</span>
                     </div>
                 </div>
             </div>
         </div>
      
         <!-- <div class="db-card mt-6" v-if="orderDeleted.order_dinings && orderDeleted.order_dinings.length > 0">
             <div class="db-card-header">
                 <h3 class="db-card-title">{{ $t('label.order_items') }}</h3>
             </div>
             <div class="db-card-body">
                 <div class="space-y-3">
                     <div v-for="(item, index) in orderDeleted.order_dinings" :key="index" 
                          class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                         <div>
                             <h5 class="font-semibold">{{ item.name }}</h5>
                             <p class="text-sm text-gray-600">{{ item.description }}</p>
                         </div>
                         <div class="text-right">
                             <p class="font-semibold">{{ item.quantity }} x {{ item.price }}</p>
                             <p class="text-sm text-gray-600">{{ item.total }}</p>
                         </div>
                     </div>
                 </div>
             </div>
         </div> 
         <div class="db-card mt-6" v-if="orderDeleted.token">
             <div class="db-card-header">
                 <h3 class="db-card-title">{{ $t('label.token_information') }}</h3>
             </div>
             <div class="db-card-body">
                 <div class="bg-gray-100 p-4 rounded-lg font-mono text-sm break-all">
                     {{ orderDeleted.token }}
                 </div>
             </div>
         </div> -->
     </div>  

     <pre>
        {{ orderDeleted.order_dinings }}
     </pre>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum"; 
import appService from "../../../services/appService";
import { createEnumArrays } from "../../../enums/enumArrays"; 

export default {
    name: "OrderDeletedShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            statusEnum: statusEnum,
            enums: createEnumArrays(this.$t),
        }
    },
    computed: {
        orderDeleted: function () {
            return this.$store.getters['orderDeleted/show'];
        }
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('orderDeleted/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        
        getOrderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },

        getOrderStatusText: function (status) {
            return this.enums.orderStatusEnumArray[status] || 'Unknown';
        },

        getPaymentStatusClass: function (status) {
            const statusClasses = {
                5: 'bg-green-100 text-green-800',   // Paid
                10: 'bg-yellow-100 text-yellow-800', // Pending
                15: 'bg-red-100 text-red-800',      // Unpaid
                20: 'bg-blue-100 text-blue-800',    // Refunded
                25: 'bg-gray-100 text-gray-800'     // Cancelled
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },

        getPaymentStatusText: function (status) {
            return this.enums.paymentStatusEnumArray[status] || 'Unknown';
        },

        getOrderTypeText: function (type) {
            return this.enums.orderTypeEnumArray[type] || 'Unknown';
        }, 

        getSourceText: function (source) {
            return this.enums.sourceEnumArray[source] || 'Unknown';
        }
    }
}
</script>
