<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TelegramNotificationService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {--chat-id= : Telegram Chat ID to send test message} {--order-id= : Order ID to use for testing} {--type=order_created : Notification type} {--force : Force run in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram notification system';

    protected TelegramService $telegramService;
    protected TelegramNotificationService $telegramNotificationService;

    public function __construct(
        TelegramService $telegramService,
        TelegramNotificationService $telegramNotificationService
    ) {
        parent::__construct();
        $this->telegramService = $telegramService;
        $this->telegramNotificationService = $telegramNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prevent running in production without explicit force flag
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('❌ This command is disabled in production environment.');
            $this->info('Use --force flag if you really need to test in production.');
            return 1;
        }

        $this->info('Testing Telegram Notification System...');
        $this->newLine();

        // Check bot configuration
        if (!$this->telegramService->isConfigured()) {
            $this->error('❌ Telegram bot token is not configured in .env file');
            return 1;
        }

        $this->info('✅ Bot token is configured');

        // Test bot connection
        $botInfo = $this->telegramService->getMe();
        if (!$botInfo || !isset($botInfo['result'])) {
            $this->error('❌ Failed to connect to Telegram bot. Check your bot token.');
            return 1;
        }

        $this->info('✅ Bot connection successful');
        $this->info('Bot Name: ' . $botInfo['result']['first_name']);
        $this->info('Bot Username: @' . ($botInfo['result']['username'] ?? 'N/A'));
        $this->newLine();

        $chatId = $this->option('chat-id');
        $orderId = $this->option('order-id');
        $notificationType = $this->option('type');

        if (!$chatId) {
            $chatId = $this->ask('Enter Telegram Chat ID or User ID to test with', '-3528580318546217153');
        }

        // Test basic message first
        $this->info('Sending test message...');
        $testMessage = "🤖 <b>Test Message</b>\n\nThis is a test message from your restaurant POS system!\nTime: " . now()->format('Y-m-d H:i:s');
        
        $result = $this->telegramService->sendMessage($chatId, $testMessage);
        
        if ($result) {
            $this->info('✅ Basic test message sent successfully!');
        } else {
            $this->error('❌ Failed to send basic test message. Check chat ID.');
            return 1;
        }

        $this->newLine();

        // Test with order notification if order ID provided or found
        if ($orderId) {
            $order = Order::find($orderId);
        } else {
            // Try to find any order, or create a test scenario
            $order = Order::first();
        }

        if ($order) {
            $this->info("Testing with Order ID: {$order->id}");
            $this->info("Order Serial: {$order->order_serial_no}");
            
            // Temporarily set the chat ID for testing
            $originalChatId = $order->telegram_chat_id;
            $originalUserId = $order->telegram_user_id;
            
            // Set the appropriate ID based on the format
            if (strpos($chatId, '-') === 0) {
                // Negative ID = group/channel chat
                $order->telegram_chat_id = $chatId;
                $order->telegram_user_id = null;
            } else {
                // Positive ID = user chat
                $order->telegram_user_id = $chatId;
                $order->telegram_chat_id = null;
            }
            
            // Send order notification
            $this->info("Sending {$notificationType} notification...");
            
            $success = $this->telegramNotificationService->sendOrderNotification($order, $notificationType);
            
            // Restore original IDs
            $order->telegram_chat_id = $originalChatId;
            $order->telegram_user_id = $originalUserId;
            
            if ($success) {
                $this->info('✅ Order notification sent successfully!');
            } else {
                $this->error('❌ Failed to send order notification');
            }
        } else {
            $this->warn('No orders found in database. Creating mock order data for testing...');
            
            // Create a mock order object for testing
            $mockOrder = new Order([
                'id' => 999,
                'order_serial_no' => 'TEST-' . date('dmy') . '999',
                'total' => 25.50,
                'currency' => 'USD',
                'customer_name' => 'Test Customer',
                'telegram_chat_id' => strpos($chatId, '-') === 0 ? $chatId : null,
                'telegram_user_id' => strpos($chatId, '-') === 0 ? null : $chatId,
                'telegram_username' => 'testuser',
                'order_datetime' => now(),
                'preparation_time' => 15,
                'status' => 1
            ]);
            
            // Set created_at and updated_at
            $mockOrder->created_at = now();
            $mockOrder->updated_at = now();
            
            $this->info("Sending {$notificationType} notification with mock data...");
            
            $success = $this->telegramNotificationService->sendOrderNotification($mockOrder, $notificationType);
            
            if ($success) {
                $this->info('✅ Mock order notification sent successfully!');
            } else {
                $this->error('❌ Failed to send mock order notification');
            }
        }

        $this->newLine();
        $this->info('🎉 Test completed!');

        return 0;
    }
}