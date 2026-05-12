<?php

namespace App\Services;

use App\Models\Order;
use App\Models\FrontendOrder;
use App\Enums\OrderStatus;
use Exception;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Send order notification to Telegram user
     *
     * @param Order|FrontendOrder $order
     * @param string $notificationType
     * @return bool
     */
    public function sendOrderNotification(Order|FrontendOrder $order, string $notificationType): bool
    {
        try {
            // Determine which ID to use for sending message
            $chatId = $this->getChatIdFromOrder($order);
            
            if (empty($chatId)) {
                Log::info("No Telegram chat/user ID found for order #{$order->order_serial_no}");
                return false;
            }

            // Check if Telegram service is configured
            if (!$this->telegramService->isConfigured()) {
                Log::warning('Telegram service not configured');
                return false;
            }

            $message = $this->buildOrderMessage($order, $notificationType);
            
            if (empty($message)) {
                Log::warning("No message generated for notification type: {$notificationType}");
                return false;
            }

            // Build web app URL with parameters
            $webAppUrl = $this->buildOrderWebAppUrl($order, $this->getActionFromNotificationType($notificationType));
            $buttonText = $this->getButtonText($this->getActionFromNotificationType($notificationType));

            // Send message with web app button instead of regular message
            $result = $this->telegramService->sendMessageWithWebAppButton($chatId, $message, $buttonText, $webAppUrl);

            if ($result) {
                Log::info("Telegram notification sent successfully", [
                    'order_id' => $order->id,
                    'order_serial_no' => $order->order_serial_no,
                    'notification_type' => $notificationType,
                    'chat_id' => $chatId,
                    'used_field' => $this->getUsedIdField($order),
                    'web_app_url' => $webAppUrl
                ]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error("Failed to send Telegram order notification", [
                'order_id' => $order->id,
                'notification_type' => $notificationType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get the appropriate chat ID from order (prioritize telegram_user_id for private chats)
     *
     * @param Order $order
     * @return string|null
     */
    protected function getChatIdFromOrder(Order|FrontendOrder $order): ?string
    {
        // For private messages, use telegram_user_id if available
        if (!empty($order->telegram_user_id)) {
            return $order->telegram_user_id;
        }
        
        // For group chats or fallback, use telegram_chat_id
        if (!empty($order->telegram_chat_id)) {
            return $order->telegram_chat_id;
        }
        
        return null;
    }

    /**
     * Get which field was used for logging purposes
     *
     * @param Order $order
     * @return string
     */
    protected function getUsedIdField(Order|FrontendOrder $order): string
    {
        if (!empty($order->telegram_user_id)) {
            return 'telegram_user_id';
        }
        
        if (!empty($order->telegram_chat_id)) {
            return 'telegram_chat_id';
        }
        
        return 'none';
    }

    /**
     * Build Telegram mini app URL with parameters for web app button
     *
     * @param Order $order
     * @param string $action Optional action parameter (default: 'view')
     * @return string
     */
    protected function buildOrderWebAppUrl(Order|FrontendOrder $order, string $action = 'view'): string
    {
        // Get base URL from config - this should be your domain root
        $baseUrl = config('services.telegram.mini_app_web_url');
        
        // Fallback if web URL not configured - build from current app URL
        if (empty($baseUrl)) {
            $baseUrl = url('/');
        }
        
        // Remove trailing slash
        $baseUrl = rtrim($baseUrl, '/');
        
        // Get branch slug - default to 'pp' or try to get from order
        $branchSlug = 'pp'; // Default branch slug
        
        // Try to get branch slug from order's branch relationship
        if ($order instanceof Order && $order->branch && $order->branch->telegram_mini_app_slug) {
            $branchSlug = $order->branch->telegram_mini_app_slug;
        } elseif ($order instanceof FrontendOrder && isset($order->branch) && $order->branch->telegram_mini_app_slug) {
            $branchSlug = $order->branch->telegram_mini_app_slug;
        }
        
        // Build the complete URL path - point to menu page with parameters
        // This will be handled by the Vue.js menu component to redirect to order details
        $fullPath = "{$baseUrl}/telegram-mini-app/{$branchSlug}";
        
        // Build parameters for direct order access
        $params = [
            'order_id' => $order->id,
            'order_number' => $order->order_serial_no,
            'action' => $action,
            'redirect' => 'order_details'
        ];
        
        // Add customer information if available
        if ($order->telegram_user_id) {
            $params['user_id'] = $order->telegram_user_id;
        }
        
        // Add branch information if available for proper routing
        if (isset($order->branch_id) && $order->branch_id) {
            $params['branch_id'] = $order->branch_id;
        }
        
        // Build query string and return full URL
        $queryString = http_build_query($params);
        $finalUrl = "{$fullPath}?" . $queryString;
        
        // Log the generated URL for debugging
        Log::info("Generated Telegram web app URL", [
            'base_url' => $baseUrl,
            'branch_slug' => $branchSlug,
            'full_path' => $fullPath,
            'params' => $params,
            'final_url' => $finalUrl
        ]);
        
        return $finalUrl;
    }

    /**
     * Get button text based on action
     *
     * @param string $action
     * @return string
     */
    protected function getButtonText(string $action): string
    {
        return match($action) {
            'track' => '📱 Track Order',
            'view' => '👀 View Order',
            'receipt' => '🧾 View Receipt',
            'delivery' => '🚗 Track Delivery',
            'cancelled' => '❌ View Cancellation',
            'rejected' => '⚠️ View Details',
            default => '📱 View Order'
        };
    }

    /**
     * Get action from notification type
     *
     * @param string $notificationType
     * @return string
     */
    protected function getActionFromNotificationType(string $notificationType): string
    {
        return match($notificationType) {
            'order_created' => 'track',
            'order_accepted' => 'track',
            'order_processing' => 'track',
            'order_out_for_delivery' => 'delivery',
            'order_delivered' => 'receipt',
            'order_cancelled' => 'cancelled',
            'order_rejected' => 'rejected',
            default => 'view'
        };
    }

    /**
     * Build order message based on notification type
     *
     * @param Order $order
     * @param string $notificationType
     * @return string
     */
    protected function buildOrderMessage(Order|FrontendOrder $order, string $notificationType): string
    {
        $customerName = $order->customer_name ?: ($order->telegram_username ? "@{$order->telegram_username}" : 'Customer');
        $orderNumber = $order->order_serial_no;
        $total = number_format($order->total, 2);
        $currency = $order->currency ?: 'USD';

        switch ($notificationType) {
            case 'order_created':
                return $this->buildOrderCreatedMessage($order, $customerName, $orderNumber, $total, $currency);
                
            case 'order_accepted':
                return $this->buildOrderAcceptedMessage($order, $customerName, $orderNumber);
                
            case 'order_processing':
                return $this->buildOrderProcessingMessage($order, $customerName, $orderNumber);
                
            case 'order_out_for_delivery':
                return $this->buildOrderOutForDeliveryMessage($order, $customerName, $orderNumber);
                
            case 'order_delivered':
                return $this->buildOrderDeliveredMessage($order, $customerName, $orderNumber);
                
            case 'order_cancelled':
                return $this->buildOrderCancelledMessage($order, $customerName, $orderNumber);
                
            case 'order_rejected':
                return $this->buildOrderRejectedMessage($order, $customerName, $orderNumber);
                
            default:
                return '';
        }
    }

    /**
     * Build order created message
     */
    protected function buildOrderCreatedMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber, string $total, string $currency): string
    {
        $message = "🛍️ <b>Order Confirmation</b>\n\n";
        $message .= "Hello {$customerName}!\n\n";
        $message .= "Your order has been successfully placed.\n\n";
        $message .= "📋 <b>Order Details:</b>\n";
        $message .= "• Order Number: #{$orderNumber}\n";
        $message .= "• Total Amount: {$total} {$currency}\n";
        $message .= "• Order Date: " . $order->order_datetime->format('M d, Y H:i') . "\n\n";
        
        if ($order->orderItems && $order->orderItems->count() > 0) {
            $message .= "🍽️ <b>Items:</b>\n";
            foreach ($order->orderItems as $item) {
                $message .= "• {$item->quantity}x {$item->item_name}\n";
            }
            $message .= "\n";
        }
        
        $message .= "We'll notify you when your order status changes.\n\n";
        $message .= "Click the button below to track your order! 📱";
        
        return $message;
    }

    /**
     * Build order accepted message
     */
    protected function buildOrderAcceptedMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "✅ <b>Order Accepted</b>\n\n";
        $message .= "Great news {$customerName}!\n\n";
        $message .= "Your order #{$orderNumber} has been accepted and is being prepared.\n\n";
        
        if ($order->preparation_time) {
            $message .= "⏱️ <b>Estimated Preparation Time:</b> {$order->preparation_time} minutes\n\n";
        }
        
        $message .= "We'll notify you when your order is ready! 👨‍🍳\n\n";
        $message .= "Click the button below to track your order! 📱";
        
        return $message;
    }

    /**
     * Build order processing message
     */
    protected function buildOrderProcessingMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "👨‍🍳 <b>Order in Progress</b>\n\n";
        $message .= "Hi {$customerName}!\n\n";
        $message .= "Your order #{$orderNumber} is currently being prepared by our kitchen team.\n\n";
        $message .= "We're working hard to get your delicious meal ready! 🔥\n\n";
        $message .= "Click the button below to track your order! 📱";
        
        return $message;
    }

    /**
     * Build order out for delivery message
     */
    protected function buildOrderOutForDeliveryMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "🚗 <b>Order Out for Delivery</b>\n\n";
        $message .= "Hi {$customerName}!\n\n";
        $message .= "Your order #{$orderNumber} is now out for delivery! 📦\n\n";
        
        if ($order->delivery_time) {
            $message .= "⏰ <b>Expected Delivery:</b> {$order->delivery_time}\n\n";
        }
        
        $message .= "Our delivery person is on the way to your location.\n";
        $message .= "Please have your payment ready if you chose cash on delivery. 💰\n\n";
        $message .= "Click the button below to track your delivery! 🚗";
        
        return $message;
    }

    /**
     * Build order delivered message
     */
    protected function buildOrderDeliveredMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "🎉 <b>Order Delivered</b>\n\n";
        $message .= "Hi {$customerName}!\n\n";
        $message .= "Your order #{$orderNumber} has been successfully delivered! ✅\n\n";
        $message .= "We hope you enjoy your meal! 😋\n\n";
        $message .= "Thank you for choosing us. We'd love to serve you again soon! ❤️\n\n";
        $message .= "Click the button below to view your order receipt! 🧾\n\n";
        $message .= "If you have any feedback or concerns, please don't hesitate to contact us.";
        
        return $message;
    }

    /**
     * Build order cancelled message
     */
    protected function buildOrderCancelledMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "❌ <b>Order Cancelled</b>\n\n";
        $message .= "Hi {$customerName},\n\n";
        $message .= "Your order #{$orderNumber} has been cancelled.\n\n";
        
        if ($order->payment_status == \App\Enums\PaymentStatus::PAID) {
            $message .= "💰 Don't worry! If you made a payment, it will be refunded within 3-5 business days.\n\n";
        }
        
        $message .= "We apologize for any inconvenience caused.\n\n";
        $message .= "Click the button below to view cancellation details! ❌\n\n";
        $message .= "Feel free to place a new order anytime! 🛍️";
        
        return $message;
    }

    /**
     * Build order rejected message
     */
    protected function buildOrderRejectedMessage(Order|FrontendOrder $order, string $customerName, string $orderNumber): string
    {
        $message = "⚠️ <b>Order Rejected</b>\n\n";
        $message .= "Hi {$customerName},\n\n";
        $message .= "Unfortunately, we had to reject your order #{$orderNumber}.\n\n";
        
        if ($order->rejection_reason) {
            $message .= "📝 <b>Reason:</b> {$order->rejection_reason}\n\n";
        }
        
        if ($order->payment_status == \App\Enums\PaymentStatus::PAID) {
            $message .= "💰 Don't worry! If you made a payment, it will be refunded within 3-5 business days.\n\n";
        }
        
        $message .= "We apologize for the inconvenience and appreciate your understanding.\n\n";
        $message .= "Click the button below to view rejection details! ⚠️\n\n";
        $message .= "Please feel free to place a new order! 🛍️";
        
        return $message;
    }

    /**
     * Send order created notification
     */
    public function sendOrderCreatedNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_created');
    }

    /**
     * Send order accepted notification
     */
    public function sendOrderAcceptedNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_accepted');
    }

    /**
     * Send order processing notification
     */
    public function sendOrderProcessingNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_processing');
    }

    /**
     * Send order out for delivery notification
     */
    public function sendOrderOutForDeliveryNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_out_for_delivery');
    }

    /**
     * Send order delivered notification
     */
    public function sendOrderDeliveredNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_delivered');
    }

    /**
     * Send order cancelled notification
     */
    public function sendOrderCancelledNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_cancelled');
    }

    /**
     * Send order rejected notification
     */
    public function sendOrderRejectedNotification(Order|FrontendOrder $order): bool
    {
        return $this->sendOrderNotification($order, 'order_rejected');
    }

    /**
     * Send notification based on order status
     */
    public function sendNotificationByStatus(Order|FrontendOrder $order): bool
    {
        switch ($order->status) {
            case OrderStatus::PENDING:
                return $this->sendOrderAcceptedNotification($order);
                
            case OrderStatus::PROCESSING:
                return $this->sendOrderProcessingNotification($order);
                
            case OrderStatus::OUT_FOR_DELIVERY:
                return $this->sendOrderOutForDeliveryNotification($order);
                
            case OrderStatus::DELIVERED:
                return $this->sendOrderDeliveredNotification($order);
                
            case OrderStatus::CANCELED:
                return $this->sendOrderCancelledNotification($order);
                
            case OrderStatus::REJECTED:
                return $this->sendOrderRejectedNotification($order);
                
            default:
                Log::info("No Telegram notification configured for order status: {$order->status}");
                return false;
        }
    }
}