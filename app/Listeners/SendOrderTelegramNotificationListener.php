<?php

namespace App\Listeners;

use App\Events\SendOrderTelegramNotification;
use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderTelegramNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected TelegramNotificationService $telegramNotificationService;

    /**
     * Create the event listener.
     *
     * @param TelegramNotificationService $telegramNotificationService
     */
    public function __construct(TelegramNotificationService $telegramNotificationService)
    {
        $this->telegramNotificationService = $telegramNotificationService;
    }

    /**
     * Handle the event.
     *
     * @param SendOrderTelegramNotification $event
     * @return void
     */
    public function handle(SendOrderTelegramNotification $event)
    {
        try {
            Log::info("Processing Telegram notification", [
                'order_id' => $event->order->id,
                'order_serial_no' => $event->order->order_serial_no,
                'notification_type' => $event->notificationType,
                'telegram_chat_id' => $event->order->telegram_chat_id,
                'order_model_type' => get_class($event->order)
            ]);

            $this->telegramNotificationService->sendOrderNotification(
                $event->order, 
                $event->notificationType
            );
        } catch (\Exception $e) {
            Log::error("Failed to process Telegram notification", [
                'order_id' => $event->order->id,
                'notification_type' => $event->notificationType,
                'order_model_type' => get_class($event->order),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param SendOrderTelegramNotification $event
     * @param \Exception $exception
     * @return void
     */
    public function failed(SendOrderTelegramNotification $event, $exception)
    {
        Log::error("Telegram notification job failed", [
            'order_id' => $event->order->id,
            'notification_type' => $event->notificationType,
            'error' => $exception->getMessage()
        ]);
    }
}