<?php

namespace App\Events;

use App\Models\Order;
use App\Models\FrontendOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendOrderTelegramNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order|FrontendOrder $order;
    public string $notificationType;

    /**
     * Create a new event instance.
     *
     * @param Order|FrontendOrder $order
     * @param string $notificationType
     */
    public function __construct(Order|FrontendOrder $order, string $notificationType)
    {
        $this->order = $order;
        $this->notificationType = $notificationType;
    }
}