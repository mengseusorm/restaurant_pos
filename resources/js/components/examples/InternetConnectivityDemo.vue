<!-- Example component showing how to use internet connectivity -->
<template>
    <div class="example-component">
        <h3>Internet Connectivity Demo</h3>
        
        <!-- Display connection status -->
        <div class="status-indicator" :class="{ 'online': isOnline, 'offline': !isOnline }">
            <i :class="isOnline ? 'fas fa-wifi' : 'fas fa-wifi-slash'"></i>
            {{ isOnline ? 'Connected' : 'Disconnected' }}
        </div>
        
        <!-- Example action buttons that require internet -->
        <div class="action-buttons">
            <button @click="saveOrder" class="btn btn-primary">
                Save Order (Requires Internet)
            </button>
            
            <button @click="syncData" class="btn btn-secondary">
                Sync Data (Requires Internet)
            </button>
        </div>
        
        <div class="connection-info">
            <p>Last connectivity check: {{ lastCheckTime }}</p>
            <button @click="forceCheck" class="btn btn-outline">
                Force Check Connection
            </button>
        </div>
    </div>
</template>

<script>
import internetConnectivityService from '../../../services/internetConnectivityService';
import alertService from '../../../services/alertService';

export default {
    name: 'InternetConnectivityDemo',
    
    data() {
        return {
            isOnline: internetConnectivityService.getStatus().isOnline,
            lastCheckTime: internetConnectivityService.formatLastCheckTime()
        }
    },
    
    methods: {
        // Example method that requires internet connectivity
        async saveOrder() {
            try {
                // Use the service wrapper for API calls
                await internetConnectivityService.withConnectivity(async () => {
                    // Your API call here
                    console.log('Saving order...');
                    // Example: return this.$store.dispatch('orders/save', orderData);
                    
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    alertService.success('Order saved successfully');
                });
            } catch (error) {
                if (error.message.includes('internet connection')) {
                    alertService.error('Cannot save order: No internet connection');
                } else {
                    alertService.error('Failed to save order: ' + error.message);
                }
            }
        },
        
        // Another example method
        async syncData() {
            if (!internetConnectivityService.requireInternet()) {
                return; // Service will show the alert
            }
            
            try {
                // Your sync logic here
                console.log('Syncing data...');
                await new Promise(resolve => setTimeout(resolve, 2000));
                alertService.success('Data synchronized successfully');
            } catch (error) {
                alertService.error('Sync failed: ' + error.message);
            }
        },
        
        // Force connectivity check
        async forceCheck() {
            try {
                const isConnected = await internetConnectivityService.forceCheck();
                alertService.success(`Connection status: ${isConnected ? 'Online' : 'Offline'}`);
            } catch (error) {
                alertService.error('Failed to check connectivity');
            }
        },
        
        // Handle connectivity changes
        handleConnectivityChange(isOnline) {
            this.isOnline = isOnline;
            this.lastCheckTime = internetConnectivityService.formatLastCheckTime();
            
            if (isOnline) {
                alertService.success('Internet connection restored');
            } else {
                alertService.error('Internet connection lost');
            }
        }
    },
    
    mounted() {
        // Register for connectivity change notifications
        internetConnectivityService.addListener(this.handleConnectivityChange);
    },
    
    beforeUnmount() {
        // Clean up listener
        internetConnectivityService.removeListener(this.handleConnectivityChange);
    }
}
</script>

<style scoped>
.example-component {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 20px 0;
}

.status-indicator {
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
    text-align: center;
    font-weight: bold;
}

.status-indicator.online {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-indicator.offline {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.action-buttons {
    margin: 15px 0;
}

.btn {
    padding: 8px 16px;
    margin: 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #007bff;
    color: #007bff;
}

.connection-info {
    margin-top: 15px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
</style>
