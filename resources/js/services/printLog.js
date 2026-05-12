import axios from 'axios';
import store from '../store';

/**
 * Print Log Service
 * Handles all print logging API calls
 */
export default {
    /**
     * Create a print log entry
     * @param {Object} data - Print log data
     * @param {string} data.order_serial_number - Order serial number
     * @param {number} data.print_type - Print type (5=Menu, 10=Invoice, 15=Bill)
     * @param {boolean} data.print_success - Whether print was successful
     * @param {string|null} data.error_message - Error message if failed
     * @param {number|null} data.user_id - User ID (optional, defaults to current user)
     * @param {number|null} data.branch_id - Branch ID (optional, defaults to user's branch)
     * @returns {Promise} API response
     */
    async createPrintLog(data) {
        try {
            const response = await axios.post('admin/order-print-logs', {
                order_serial_number: data.order_serial_number,
                print_type: data.print_type,
                print_success: data.print_success,
                error_message: data.error_message || null,
                user_id: data.user_id || null,
                branch_id: data.branch_id || null
            });
            return response.data;
        } catch (error) {
            console.error('Failed to create print log:', error);
            // Don't throw error to avoid breaking the print process
            return null;
        }
    },

    /**
     * Log successful print
     * @param {string} orderSerialNumber - Order serial number
     * @param {number} printType - Print type (5=Menu, 10=Invoice, 15=Bill)
     * @param {number|null} userId - User ID (optional)
     * @param {number|null} branchId - Branch ID (optional)
     * @returns {Promise} API response
     */
    async logSuccess(orderSerialNumber, printType, userId = null, branchId = null) {
        try {
            if (userId === null) {
                userId = store.getters.authInfo.id || null;
            }
            if (branchId === null) {
                branchId = store.getters.authBranchId || null;
            }
            return this.createPrintLog({
                order_serial_number: orderSerialNumber,
                print_type: printType,
                print_success: true,
                error_message: null,
                user_id: userId,
                branch_id: branchId
            });
        } catch (error) {
            console.error('Failed to log success:', error);
            return null;
        }
    },

    /**
     * Log failed print
     * @param {string} orderSerialNumber - Order serial number
     * @param {number} printType - Print type (5=Menu, 10=Invoice, 15=Bill)
     * @param {string} errorMessage - Error message
     * @param {number|null} userId - User ID (optional)
     * @param {number|null} branchId - Branch ID (optional)
     * @returns {Promise} API response
     */
    async logFailure(orderSerialNumber, printType, errorMessage, userId = null, branchId = null) {
        try {
            if (userId === null) {
                userId = store.getters.authInfo.id || null;
            }
            if (branchId === null) {
                branchId = store.getters.authBranchId || null;
            }
            return this.createPrintLog({
                order_serial_number: orderSerialNumber,
                print_type: printType,
                print_success: false,
                error_message: errorMessage,
                user_id: userId,
                branch_id: branchId
            });
        } catch (error) {
            console.error('Failed to log failure:', error);
            return null;
        }
    },

    /**
     * Get all print logs with optional filters
     * @param {Object} filters - Filter parameters
     * @param {number} filters.page - Page number
     * @param {number} filters.per_page - Items per page
     * @param {number|null} filters.user_id - Filter by user ID
     * @param {number|null} filters.branch_id - Filter by branch ID
     * @param {string|null} filters.order_serial_number - Filter by order serial number
     * @param {number|null} filters.print_type - Filter by print type
     * @param {boolean|null} filters.print_success - Filter by success status
     * @param {string|null} filters.from_date - Filter from date (YYYY-MM-DD)
     * @param {string|null} filters.to_date - Filter to date (YYYY-MM-DD)
     * @returns {Promise} API response
     */
    async getPrintLogs(filters = {}) {
        try {
            const response = await axios.get('admin/order-print-logs', {
                params: filters
            });
            return response.data;
        } catch (error) {
            console.error('Failed to get print logs:', error);
            return null;
        }
    },

    /**
     * Get single print log by ID
     * @param {number} id - Print log ID
     * @returns {Promise} API response
     */
    async getPrintLog(id) {
        try {
            const response = await axios.get(`admin/order-print-logs/${id}`);
            return response.data;
        } catch (error) {
            console.error('Failed to get print log:', error);
            return null;
        }
    },

    /**
     * Delete print log by ID
     * @param {number} id - Print log ID
     * @returns {Promise} API response
     */
    async deletePrintLog(id) {
        try {
            const response = await axios.delete(`admin/order-print-logs/${id}`);
            return response.data;
        } catch (error) {
            console.error('Failed to delete print log:', error);
            return null;
        }
    }
};
