<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\Models\Activity;

class ActivityLoggerService
{
    /**
     * Log user authentication activities (login, logout)
     */
    public function logAuthentication(string $event, User $user, array $properties = []): void
    {
        try {
            $defaultProperties = [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
            ];
            Log::info("Logging authentication activity: ", [
                'event' => $event,
                'user_id' => $user->id,
                'properties' => $defaultProperties,
            ]);

            Log::info("User: ", [$user]);

            // Create activity log directly through the model to avoid authentication context issues
            // Temporarily disable global scopes to avoid BranchScope interference during login
            $activityLog = ActivityLog::withoutGlobalScopes()->create([
                'log_name' => 'auth',
                'description' => "User {$event}",
                'subject_type' => null,
                'subject_id' => null,
                'causer_type' => User::class,
                'causer_id' => $user->id,
                'properties' => array_merge($defaultProperties, $properties),
                'branch_id' => $user->branch_id ?? 0,
            ]);

            Log::info("Activity log created with ID: " . $activityLog->id);

            Log::info("Finish logging authentication activity");
        } catch (\Exception $e) {
            // Log error but don't break the authentication flow
            Log::error('Failed to log authentication activity', [
                'event' => $event,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log order-related activities
     */
    public function logOrderActivity(string $event, $order, array $properties = []): void
    {
        $user = Auth::user();
        $orderNumber = is_object($order) ? $order->order_serial_no : ($order['order_serial_no'] ?? 'N/A');
        
        $defaultProperties = [
            'order_id' => is_object($order) ? $order->id : ($order['id'] ?? null),
            'order_number' => $orderNumber,
            'order_total' => is_object($order) ? $order->total : ($order['total'] ?? null),
            'order_status' => is_object($order) ? $order->status : ($order['status'] ?? null),
            'payment_status' => is_object($order) ? $order->payment_status : ($order['payment_status'] ?? null),
        ];

        activity('order')
            ->causedBy($user)
            ->performedOn(is_object($order) ? $order : null)
            ->withProperties(array_merge($defaultProperties, $properties))
            ->log("Order {$event}: #{$orderNumber}");
    }

    /**
     * Log payment activities
     */
    public function logPaymentActivity(string $event, $order, array $paymentData = []): void
    {
        $user = Auth::user();
        $orderNumber = is_object($order) ? $order->order_serial_no : ($order['order_serial_no'] ?? 'N/A');
        
        $properties = [
            'order_id' => is_object($order) ? $order->id : ($order['id'] ?? null),
            'order_number' => $orderNumber,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'payment_amount' => $paymentData['amount'] ?? null,
            'payment_reference' => $paymentData['reference'] ?? null,
            'payment_status' => $paymentData['status'] ?? null,
        ];

        activity('payment')
            ->causedBy($user)
            ->performedOn(is_object($order) ? $order : null)
            ->withProperties($properties)
            ->log("Payment {$event} for order #{$orderNumber}");
    }

    /**
     * Log POS activities
     */
    public function logPosActivity(string $event, array $properties = []): void
    {
        $user = Auth::user();
        
        $defaultProperties = [
            'pos_session' => session()->getId(),
            'branch_id' => $user ? $user->branch_id : null,
        ];

        activity('pos')
            ->causedBy($user)
            ->withProperties(array_merge($defaultProperties, $properties))
            ->log("POS {$event}");
    }

    /**
     * Log table order activities
     */
    public function logTableOrderActivity(string $event, $table, $order = null, array $properties = []): void
    {
        $user = Auth::user();
        
        $defaultProperties = [
            'table_id' => is_object($table) ? $table->id : ($table['id'] ?? null),
            'table_name' => is_object($table) ? $table->name : ($table['name'] ?? null),
            'order_id' => $order ? (is_object($order) ? $order->id : $order['id']) : null,
            'order_number' => $order ? (is_object($order) ? $order->order_serial_no : $order['order_serial_no']) : null,
        ];

        activity('table_order')
            ->causedBy($user)
            ->performedOn(is_object($table) ? $table : null)
            ->withProperties(array_merge($defaultProperties, $properties))
            ->log("Table order {$event}");
    }

    /**
     * Log member activities
     */
    public function logMemberActivity(string $event, $member, array $properties = []): void
    {
        $user = Auth::user();
        
        $defaultProperties = [
            'member_id' => is_object($member) ? $member->id : ($member['id'] ?? null),
            'member_name' => is_object($member) ? $member->name : ($member['name'] ?? null),
            'member_phone' => is_object($member) ? $member->phone : ($member['phone'] ?? null),
            'member_points' => is_object($member) ? $member->point_balance : ($member['point_balance'] ?? null),
        ];

        activity('member')
            ->causedBy($user)
            ->performedOn(is_object($member) ? $member : null)
            ->withProperties(array_merge($defaultProperties, $properties))
            ->log("Member {$event}");
    }

    /**
     * Log inventory/stock activities
     */
    public function logInventoryActivity(string $event, $item, array $properties = []): void
    {
        $user = Auth::user();
        
        $defaultProperties = [
            'item_id' => is_object($item) ? $item->id : ($item['id'] ?? null),
            'item_name' => is_object($item) ? $item->name : ($item['name'] ?? null),
            'item_sku' => is_object($item) ? $item->sku : ($item['sku'] ?? null),
        ];

        activity('inventory')
            ->causedBy($user)
            ->performedOn(is_object($item) ? $item : null)
            ->withProperties(array_merge($defaultProperties, $properties))
            ->log("Inventory {$event}");
    }

    /**
     * Log general system activities
     */
    public function logSystemActivity(string $event, array $properties = []): void
    {
        $user = Auth::user();
        
        activity('system')
            ->causedBy($user)
            ->withProperties($properties)
            ->log("System {$event}");
    }

    /**
     * Get activity logs with filters
     */
    public function getActivityLogs(array $filters = [])
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['log_name'])) {
            $query->byLogName($filters['log_name']);
        }

        if (isset($filters['user_id'])) {
            $query->byCauser($filters['user_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->betweenDates($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['branch_id'])) {
            $query->forBranch($filters['branch_id']);
        }

        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Clean old activity logs
     */
    public function cleanOldLogs(int $days = 365): int
    {
        $cutoffDate = now()->subDays($days);
        return ActivityLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
