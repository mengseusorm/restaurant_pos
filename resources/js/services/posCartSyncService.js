/**
 * Service to synchronize POS cart data between components
 * This handles cross-tab synchronization and real-time updates
 */
export default {
    /**
     * Save cart data to localStorage and dispatch events for real-time sync
     */
    syncCartData(store) {
        try {
            const cartData = {
                carts: store?.state?.posCart?.lists || [],
                subtotal: store?.state?.posCart?.subtotal || 0,
                discount: store?.state?.posCart?.discount || 0,
                totalTax: store?.state?.posCart?.totalTax || 0,
                discountPercentage: store?.state?.posCart?.discountPercentage || 0,
                selectedMember: store?.state?.posCart?.selectedMember || null,
                timestamp: Date.now()
            }; 
            // Save to localStorage for cross-tab synchronization
            localStorage.setItem('posCartCustomerView', JSON.stringify(cartData));

            // Dispatch custom event for same-window synchronization
            window.dispatchEvent(new CustomEvent('posCartUpdated', {
                detail: cartData
            }));

            // Also trigger storage event manually for same-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'posCartCustomerView',
                newValue: JSON.stringify(cartData)
            }));

        } catch (error) {
            console.error('Error syncing cart data:', error);
        }
    },

    /**
     * Sync current route information for CustomerView display mode detection
     */
    syncCurrentRoute(currentRoute) {
        try {
            const routeData = {
                path: currentRoute,
                timestamp: Date.now()
            };
            // Save to localStorage for cross-tab synchronization
            localStorage.setItem('primaryScreenRoute', JSON.stringify(routeData));

            // Dispatch storage event for cross-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'primaryScreenRoute',
                newValue: JSON.stringify(routeData),
                url: window.location.href
            }));

        } catch (error) {
            console.error('Error syncing route data:', error);
        }
    },

    /**
     * Initialize cart sync watcher on store
     */
    initCartSync(store) {
        if (!store) return;

        // Watch for any changes to posCart state
        store.watch(
            (state) => state.posCart,
            () => {
                this.syncCartData(store);
            },
            { deep: true }
        );
    },

    /**
     * Clear cart data from localStorage
     */
    clearCartData() {
        try {
            localStorage.removeItem('posCartCustomerView');

            // Create empty cart data for clearing
            const emptyCartData = {
                carts: [],
                subtotal: 0,
                discount: 0,
                totalTax: 0,
                discountPercentage: 0,
                selectedMember: null,
                timestamp: Date.now()
            };

            // Dispatch custom event for same-window synchronization
            window.dispatchEvent(new CustomEvent('posCartUpdated', {
                detail: emptyCartData
            }));

            // Also dispatch storage event for cross-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'posCartCustomerView',
                newValue: null,
                url: window.location.href
            }));

            // Clear payment method as well when cart is cleared
            this.clearPaymentMethod();

            console.log('Cart data and payment method cleared, events dispatched');
        } catch (error) {
            console.error('Error clearing cart data:', error);
        }
    },

    /**
     * Sync selected payment method for CustomerView display
     */
    syncPaymentMethod(paymentMethod) {
        try {
            console.log('Syncing payment method:', paymentMethod);

            // Save to localStorage for cross-tab synchronization
            localStorage.setItem('selectedPaymentMethod', JSON.stringify(paymentMethod));

            // Dispatch storage event for cross-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'selectedPaymentMethod',
                newValue: JSON.stringify(paymentMethod),
                url: window.location.href
            }));

        } catch (error) {
            console.error('Error syncing payment method:', error);
        }
    },

    /**
     * Clear selected payment method
     */
    clearPaymentMethod() {
        try {
            localStorage.removeItem('selectedPaymentMethod');

            // Dispatch storage event to notify other components
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'selectedPaymentMethod',
                newValue: null,
                url: window.location.href
            }));
        } catch (error) {
            console.error('Error clearing payment method:', error);
        }
    },

    /**
     * Generic method to sync any data to CustomerView
     * @param {string} eventType - Type of event (e.g., 'showPaymentQR', 'hidePaymentQR', 'paymentComplete')
     * @param {object} data - Data to sync
     */
    syncToCustomerView(eventType, data) {
        try {
            console.log('Syncing to CustomerView:', eventType, data);

            const syncData = {
                eventType: eventType,
                data: data,
                timestamp: Date.now()
            };

            // Save to localStorage for cross-tab synchronization
            localStorage.setItem('customerViewSync', JSON.stringify(syncData));

            // Dispatch custom event for same-window synchronization
            window.dispatchEvent(new CustomEvent('customerViewSync', {
                detail: syncData
            }));

            // Also trigger storage event manually for same-tab communication
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'customerViewSync',
                newValue: JSON.stringify(syncData),
                url: window.location.href
            }));

        } catch (error) {
            console.error('Error syncing to CustomerView:', error);
        }
    }
};
