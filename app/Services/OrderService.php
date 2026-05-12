<?php

namespace App\Services;

use App\Enums\ManageStock;
use App\Enums\ItemKind;
use App\Enums\OnlinePaymentGatewayEnum;
use App\Enums\SubSessionStatus;
use Exception;
use App\Models\Tax;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\Member;
use App\Enums\TaxType;
use App\Models\Address;
use App\Models\GroupSession;
use App\Enums\OrderType;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\Status;
use App\Models\OrderAddress;
use App\Libraries\AppLibrary;
use App\Models\FrontendOrder;
use App\Events\SendOrderGotSms;
use App\Events\SendOrderGotMail;
use App\Events\SendOrderGotPush;
use App\Events\SendOrderTelegramNotification;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Services\MemberService;
use App\Services\TelegramNotificationService;
use App\Services\PointEarnRuleService;
use App\Http\Requests\OnlineOrderOrderRequest;
use App\Http\Requests\TelegramMiniAppOrderRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PosOrderRequest;
use App\Http\Requests\TableOrderRequest;
use Smartisan\Settings\Facades\Settings;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Requests\PosPaymentMethodRequest;
use App\Http\Requests\TableOrderTokenRequest;
use App\Models\DiningTable;
use App\Models\ItemStock;
use App\Models\OrderDeleted;
use App\Models\OrderDining;
use App\Models\OrderItemDeleted;
use App\Models\PaymentMethod;
use App\Models\SessionItem;
use App\Models\StockRecord;
use App\Models\SubSession;
use App\Models\Transaction;
use App\Models\Currency;
use App\Services\ActivityLoggerService;
use App\Models\PaymentOrder;
use App\Services\HuionePayment\HuioneService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TelegramService;

class OrderService
{
    public object $order;
    public object $lastOrderTimeItems;
    protected MemberService $memberService;
    protected PointEarnRuleService $pointEarnRuleService;
    protected ActivityLoggerService $activityLogger;
    protected HuioneService $huioneService;
    protected TelegramNotificationService $telegramNotificationService;

    public function __construct(
        MemberService $memberService,
        PointEarnRuleService $pointEarnRuleService,
        ActivityLoggerService $activityLogger,
        HuioneService $huioneService,
        TelegramNotificationService $telegramNotificationService
    ) {
        $this->memberService = $memberService;
        $this->pointEarnRuleService = $pointEarnRuleService;
        $this->activityLogger = $activityLogger;
        $this->huioneService = $huioneService;
        $this->telegramNotificationService = $telegramNotificationService;
    }

    /**
     * Handle member integration for order
     */
    protected function handleMemberIntegration(Order $order, $memberId = null): void
    {
        if (!$memberId) {
            return;
        }

        $member = Member::find($memberId);
        if (!$member) {
            return;
        }

        // Update order with member info only
        // Points will only be calculated and added to member balance when order is paid
        $order->update([
            'member_id' => $member->id,
            'points_earned' => 0, // Will be set when order is paid
        ]);

        Log::info("Member {$member->id} linked to order #{$order->order_serial_no}");
    }

    /**
     * Calculate points earned from order total
     */
    public function calculatePointsEarned($order): int
    {
        if (is_array($order)) {
            $order = (object) $order;
        }
        return $this->pointEarnRuleService->calculatePointsForAmount($order->total + $order->total_tax);
    }

    /**
     * Handle point redemption for order
     */
    protected function handlePointRedemption(Order $order, int $pointsToRedeem = 0): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'discount_amount' => 0,
        ];

        if ($pointsToRedeem <= 0 || !$order->member_id) {
            return $result;
        }

        $member = $order->member;
        if (!$member) {
            $result['message'] = 'Member not found';
            return $result;
        }

        // Validate member has enough points
        if ($member->point_balance < $pointsToRedeem) {
            $result['message'] = 'Insufficient points balance';
            return $result;
        }

        // Calculate discount amount based on point usage rules
        $discountAmount = $this->calculatePointDiscountAmount($pointsToRedeem);

        // For unpaid orders: just reserve the points and record the redemption
        // Points will be actually deducted when the order is paid
        if ($order->payment_status !== PaymentStatus::PAID) {
            // Update order with redeemed points (reserved)
            $order->update([
                'points_redeemed' => $pointsToRedeem,
            ]);

            $result['success'] = true;
            $result['message'] = 'Points reserved for redemption';
            $result['discount_amount'] = $discountAmount;

            Log::info("Reserved {$pointsToRedeem} points for redemption on order #{$order->order_serial_no}");
        } else {
            // For paid orders: immediately deduct points
            $this->memberService->deductPoints(
                $member,
                $pointsToRedeem,
                'order_redemption',
                $order->id,
                "Points redeemed for paid order #{$order->order_serial_no}"
            );

            // Update order with redeemed points
            $order->update([
                'points_redeemed' => $pointsToRedeem,
            ]);

            $result['success'] = true;
            $result['message'] = 'Points redeemed successfully';
            $result['discount_amount'] = $discountAmount;

            Log::info("Deducted {$pointsToRedeem} points from member {$member->id} for paid order #{$order->order_serial_no}");
        }

        return $result;
    }

    /**
     * Calculate discount amount from points (placeholder - implement based on your rules)
     */
    protected function calculatePointDiscountAmount(int $points): float
    {
        // This should be implemented based on your PointUsageRule logic
        // For now, returning a simple 1:1 ratio as placeholder
        return (float) $points;
    }

    /**
     * Handle point reversal when order payment status changes from paid to unpaid
     */
    protected function handlePointReversalOnUnpaid(Order $order): void
    {
        if (!$order->member_id) {
            return;
        }

        $member = $order->member;
        if (!$member) {
            // Log::warning("Member not found for order #{$order->order_serial_no} during point reversal on unpaid");
            return;
        }

        // Revert earned points if any
        if ($order->points_earned > 0) {
            $pointsToRevert = $order->points_earned;

            $this->memberService->deductPoints(
                $member,
                $pointsToRevert,
                'order_unpaid',
                $order->id,
                "Points reverted from unpaid order #{$order->order_serial_no}"
            );

            // Reset points earned to 0
            $order->update(['points_earned' => 0]);

            Log::info("Reverted {$pointsToRevert} earned points from member {$member->id} due to order #{$order->order_serial_no} becoming unpaid");
        }

        // Refund redeemed points if any (since they were deducted when paid, now we refund them back)
        if ($order->points_redeemed > 0) {
            $this->memberService->addPoints(
                $member,
                $order->points_redeemed,
                'order_unpaid_refund',
                $order->id,
                "Points refunded from unpaid order #{$order->order_serial_no}"
            );

            Log::info("Refunded {$order->points_redeemed} redeemed points to member {$member->id} due to order #{$order->order_serial_no} becoming unpaid");
        }
    }
    /**
     * Handle point allocation when order is paid
     */
    protected function handlePointAllocationOnPayment(Order $order): void
    {
        // Only allocate points if order has member, hasn't already earned points, and is paid
        // if (!$order->member_id || $order->points_earned > 0 || $order->payment_status !== PaymentStatus::PAID) {
        //     return;
        // }

        // Log::info("handlePointAllocationOnPayment called for order #{$order->order_serial_no} with member_id: {$order->member_id}, points_earned: {$order->points_earned}, payment_status: {$order->payment_status}");

        $member = $order->member;
        if (!$member) {
            Log::warning("Member not found for order #{$order->order_serial_no} during point allocation");
            return;
        }

        // Calculate points earned from this order
        $pointsEarned = $this->calculatePointsEarned($order);
        Log::info("Calculated {$pointsEarned} points for order #{$order->order_serial_no}");

        if ($pointsEarned > 0) {
            // Update order with points earned
            $order->update(['points_earned' => $pointsEarned]);

            // Add points to member balance and create transaction
            $this->memberService->addPoints(
                $member,
                $pointsEarned,
                Order::class, // Save the class name as reference_type
                $order->id,
                "Points earned from paid order #{$order->order_serial_no}"
            );

            Log::info("Points allocated to member {$member->id}: {$pointsEarned} points for order #{$order->order_serial_no}");
        }
    }

    /**
     * Handle point redemption when order payment status changes to PAID
     */
    protected function handlePointRedemptionOnPayment(Order $order): void
    {
        // Only process redemption if order has redeemed points and member
        if (!$order->member_id || $order->points_redeemed <= 0) {
            return;
        }

        $member = $order->member;
        if (!$member) {
            // Log::warning("Member not found for order #{$order->order_serial_no} during point redemption on payment");
            return;
        }

        // Validate member still has enough points
        if ($member->point_balance < $order->points_redeemed) {
            // Log::warning("Insufficient points for redemption on payment. Member {$member->id} has {$member->point_balance} but needs {$order->points_redeemed}");
            // Reset redemption if member doesn't have enough points
            $order->update(['points_redeemed' => 0]);
            return;
        }

        // Deduct the reserved points from member balance
        $this->memberService->deductPoints(
            $member,
            $order->points_redeemed,
            Order::class, //'order_redemption_payment',
            $order->id,
            "Points deducted for paid order #{$order->order_serial_no}"
        );

        // Log::info("Deducted {$order->points_redeemed} redeemed points from member {$member->id} for paid order #{$order->order_serial_no}");
    }

    /**
     * Create transaction record for paid order
     */
    protected function createTransactionForOrder(Order $order): void
    {
        // Check if transaction already exists for this order
        $existingTransaction = Transaction::where('order_id', $order->id)->first();
        if ($existingTransaction) {
            Log::info("Transaction already exists for order #{$order->order_serial_no}");
            return;
        }

        // Get branch and currency information
        $branch = $order->branch;
        $baseCurrency = $branch->currency ?? null;
        $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
        $baseCurrencyId = $baseCurrency?->id ?? null;

        // Order currency (should be same as base currency)
        $orderCurrencyCode = $order->currency ?? $baseCurrencyCode;
        $orderCurrencyId = $order->currency_id ?? $baseCurrencyId;

        // Get actual payment details from PaywayTransaction if available
        $actualPaymentAmount = null;
        $actualPaymentCurrency = null;
        $actualPaymentCurrencyId = null;

        if ($order->payment_transaction_id) {
            $paywayTransaction = \App\Models\PaywayTransaction::where('tran_id', $order->payment_transaction_id)->first();
            if ($paywayTransaction) {
                // Use payment_amount (actual paid amount) if available, otherwise use amount (requested amount)
                $actualPaymentAmount = $paywayTransaction->payment_amount ?? $paywayTransaction->amount;
                $actualPaymentCurrency = $paywayTransaction->payment_currency ?? $paywayTransaction->currency;

                // Get currency ID from code
                if ($actualPaymentCurrency) {
                    $paymentCurrencyModel = \App\Models\Currency::where('code', $actualPaymentCurrency)->first();
                    $actualPaymentCurrencyId = $paymentCurrencyModel?->id;
                }

                Log::info('Using PaywayTransaction data for POS transaction record', [
                    'payment_amount' => $actualPaymentAmount,
                    'payment_currency' => $actualPaymentCurrency,
                    'payment_currency_id' => $actualPaymentCurrencyId
                ]);
            }
        }

        // Transaction currency - priority: PayWay actual > order receive_payment_currency > order currency
        $transactionCurrency = $actualPaymentCurrency ?? $order->receive_payment_currency ?? $orderCurrencyCode;
        $transactionCurrencyId = $actualPaymentCurrencyId ?? $order->receive_payment_currency_id ?? $orderCurrencyId;

        // Determine payment method name
        // Check if it's a POS payment method (relationship) or a frontend payment method
        $paymentMethodName = 'Cash'; // Default

        // Try POS payment method first (for POS orders)
        if ($order->pos_payment_method && $order->posPaymentMethod) {
            $paymentMethodName = $order->posPaymentMethod->name ?? 'POS';
        }
        // Try payment_method field (for frontend orders)
        elseif ($order->payment_method) {
            $paymentMethodName = $order->payment_method;
        }

        // Use payment_transaction_id as transaction_no if available (for PayWay payments),
        // otherwise generate one
        $transactionNo = $order->payment_transaction_id ?? ('TXN-' . $order->order_serial_no . '-' . time());

        // Calculate order total (in order currency)
        $orderTotal = $order->total + $order->total_tax;

        // Transaction amount = ACTUAL amount customer paid in payment currency
        // Priority: PayWay actual amount > POS received amount > order total
        if ($actualPaymentAmount !== null) {
            // Use actual amount from PayWay
            $transactionAmount = $actualPaymentAmount;
        } elseif ($order->pos_received_amount !== null) {
            // Use POS received amount (this should already be in receive_payment_currency)
            $transactionAmount = $order->pos_received_amount;
        } else {
            // Convert order total to payment currency if different
            if ($transactionCurrency !== $orderCurrencyCode) {
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $orderCurrencyCode)
                    ->where('target_currency', $transactionCurrency)
                    ->first();

                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $transactionAmount = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    // Try reverse
                    $reverseRateLookup = \App\Models\ExchangeRate::where('base_currency', $transactionCurrency)
                        ->where('target_currency', $orderCurrencyCode)
                        ->first();
                    if ($reverseRateLookup && floatval($reverseRateLookup->rate) > 0) {
                        $transactionAmount = $orderTotal / floatval($reverseRateLookup->rate);
                    } else {
                        $transactionAmount = $orderTotal;
                    }
                }
            } else {
                $transactionAmount = $orderTotal;
            }
        }

        // Calculate change amount (in transaction currency)
        // For gateway payments (PayWay), no change is given - customer pays exact amount requested
        // For cash/manual payments, calculate actual change to be given back
        $changeAmount = 0;
        $currencyConversionDifference = 0;

        if (!$order->payment_transaction_id) {
            // Only calculate change for non-gateway payments (cash, card at counter, etc.)
            $changeAmount = max(0, $transactionAmount - ($transactionCurrency === $orderCurrencyCode ? $orderTotal : 0));

            // If currencies are different, we need to convert order total to transaction currency for change calculation
            if ($transactionCurrency !== $orderCurrencyCode && $transactionAmount > 0) {
                // Convert order total to transaction currency
                $orderTotalInTransactionCurrency = $orderTotal;
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $orderCurrencyCode)
                    ->where('target_currency', $transactionCurrency)
                    ->first();

                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $orderTotalInTransactionCurrency = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    $reverseRateLookup = \App\Models\ExchangeRate::where('base_currency', $transactionCurrency)
                        ->where('target_currency', $orderCurrencyCode)
                        ->first();
                    if ($reverseRateLookup && floatval($reverseRateLookup->rate) > 0) {
                        $orderTotalInTransactionCurrency = $orderTotal / floatval($reverseRateLookup->rate);
                    }
                }

                $changeAmount = max(0, $transactionAmount - $orderTotalInTransactionCurrency);
            }
        } else {
            // For gateway payments, track the rounding difference for accounting purposes
            // This is NOT change given to customer, but a currency conversion artifact
            if ($transactionCurrency !== $orderCurrencyCode && $transactionAmount > 0) {
                $orderTotalInTransactionCurrency = $orderTotal;
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $orderCurrencyCode)
                    ->where('target_currency', $transactionCurrency)
                    ->first();

                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $orderTotalInTransactionCurrency = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    $reverseRateLookup = \App\Models\ExchangeRate::where('base_currency', $transactionCurrency)
                        ->where('target_currency', $orderCurrencyCode)
                        ->first();
                    if ($reverseRateLookup && floatval($reverseRateLookup->rate) > 0) {
                        $orderTotalInTransactionCurrency = $orderTotal / floatval($reverseRateLookup->rate);
                    }
                }

                $currencyConversionDifference = $transactionAmount - $orderTotalInTransactionCurrency;
            }
        }

        // Calculate exchange rate for record keeping
        $exchangeRate = null;
        $exchangeRateBase = null;
        $exchangeRateTarget = null;

        if ($transactionCurrency !== $orderCurrencyCode && $transactionAmount > 0) {
            // Calculate exchange rate: transaction currency -> order currency
            $exchangeRate = $orderTotal / $transactionAmount;
            $exchangeRateBase = $transactionCurrency;
            $exchangeRateTarget = $orderCurrencyCode;
        }

        // Calculate base currency amount (if order currency is different from base currency)
        if ($orderCurrencyCode === $baseCurrencyCode) {
            $amountBaseCurrency = $orderTotal;
        } else {
            $baseCurrencyExchangeRate = $baseCurrency?->exchange_rate ?? 1;
            $amountBaseCurrency = $orderTotal * $baseCurrencyExchangeRate;
        }

        // Create transaction record
        Transaction::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_no' => $transactionNo,
            // Amount in branch base currency (e.g., 10,000 KHR)
            'amount' => $orderTotal,
            'currency' => $baseCurrencyCode,
            'currency_id' => $baseCurrencyId,
            // Amount in base currency (same as amount)
            'amount_base_currency' => $amountBaseCurrency,
            'base_currency' => $baseCurrencyCode,
            'base_currency_id' => $baseCurrencyId,
            // Transaction amount = actual amount paid in payment currency (e.g., 10 USD)
            'transaction_amount' => $transactionAmount,
            'transaction_currency' => $transactionCurrency,
            'transaction_currency_id' => $transactionCurrencyId,
            // Change in payment currency (0 for gateway payments)
            'change_amount' => $changeAmount,
            'change_currency' => $changeAmount > 0 ? $transactionCurrency : null,
            'change_currency_id' => $changeAmount > 0 ? $transactionCurrencyId : null,
            // Exchange rate from payment currency to order currency
            'exchange_rate' => $exchangeRate,
            'exchange_rate_base' => $exchangeRateBase,
            'exchange_rate_target' => $exchangeRateTarget,
            'payment_method' => $paymentMethodName,
            'pos_payment_method' => $order->pos_payment_method,
            'sign' => '+',
            'type' => 'payment',
            'gateway_response' => $order->payment_transaction_data ?? null,
            'note' => $order->pos_payment_note ?? (
                // Add note for currency conversion difference in gateway payments
                $currencyConversionDifference != 0
                    ? "Gateway payment with currency conversion difference: " . number_format($currencyConversionDifference, 6) . " " . $transactionCurrency
                    : null
            ),
        ]);

        Log::info("Created transaction {$transactionNo} for order #{$order->order_serial_no}", [
            'payment_method' => $paymentMethodName,
            'order_amount' => $orderTotal,
            'order_currency' => $orderCurrencyCode,
            'transaction_amount' => $transactionAmount,
            'transaction_currency' => $transactionCurrency,
            'change_amount' => $changeAmount,
            'currency_conversion_difference' => $currencyConversionDifference ?? 0,
            'exchange_rate' => $exchangeRate,
            'base_currency' => $baseCurrencyCode,
            'has_gateway_transaction' => !empty($order->payment_transaction_id),
            'is_gateway_payment' => !empty($order->payment_transaction_id)
        ]);
    }

    /**
     * Create multiple transaction records for multi-currency payment
     *
     * @param Order $order The order being paid
     * @param array|object $receivedAmounts Object with currencyId => amount pairs
     *
     * IMPORTANT: The amounts in receivedAmounts MUST be the ORIGINAL amounts actually received in each currency,
     * NOT pre-converted amounts. For example:
     * - Customer pays 2.5 USD + 5000 KHR for a 10000 KHR order
     * - receivedAmounts should be: {"1": 2.5, "2": 5000}
     * - Where "1" is USD currency_id and "2" is KHR currency_id
     * - The 2.5 is the ACTUAL USD received, not 10000 converted to USD
     *
     * Example receivedAmounts: {"1": 5, "2": 20000} means ACTUALLY RECEIVED 5 USD + 20000 KHR
     */
    protected function createMultiCurrencyTransactions(Order $order, $receivedAmounts): void
    {
        // Check if transactions already exist for this order
        if (Transaction::where('order_id', $order->id)->exists()) {
            Log::info("Transactions already exist for order #{$order->order_serial_no}");
            return;
        }

        // Convert to array if it's an object
        if (is_object($receivedAmounts)) {
            $receivedAmounts = (array) $receivedAmounts;
        }

        if (empty($receivedAmounts)) {
            Log::warning("No received amounts provided for multi-currency payment");
            return;
        }

        Log::info("Creating multi-currency transactions for order #{$order->order_serial_no}", [
            'received_amounts' => $receivedAmounts,
            'order_total' => $order->total + $order->total_tax
        ]);

        // Get branch and currency information
        $branch = $order->branch;
        $baseCurrency = $branch->currency ?? null;
        $baseCurrencyId = $baseCurrency?->id;
        $baseCurrencyCode = $baseCurrency?->code ?? 'USD';

        // Order currency (same as base currency)
        $orderCurrencyCode = $order->currency ?? $baseCurrencyCode;
        $orderCurrencyId = $order->currency_id ?? $baseCurrencyId;

        // Determine payment method name
        $paymentMethodName = 'Cash';
        if ($order->pos_payment_method && $order->posPaymentMethod) {
            $paymentMethodName = $order->posPaymentMethod->name ?? 'POS';
        } elseif ($order->payment_method) {
            $paymentMethodName = $order->payment_method;
        }

        // Calculate order total
        $orderTotal = $order->total + $order->total_tax;
        $totalPaidInOrderCurrency = 0; // Track total actually paid (not capped)
        $totalAppliedToOrder = 0; // Track total applied to order balance (capped)
        $transactionNo = $order->payment_transaction_id ?? ('TXN-' . $order->order_serial_no . '-' . time());

        // Load all currencies at once for efficiency
        $currencyIds = array_keys($receivedAmounts);
        $currencies = Currency::whereIn('id', $currencyIds)->get()->keyBy('id');

        $transactionSequence = 0;
        $transactionsCreated = [];

        foreach ($receivedAmounts as $currencyId => $amount) {
            $amount = (float) $amount;

            if ($amount <= 0) {
                continue; // Skip zero or negative amounts
            }

            $transactionSequence++;
            $currency = $currencies->get($currencyId);

            if (!$currency) {
                Log::warning("Currency ID {$currencyId} not found");
                continue;
            }

            Log::info("Processing payment in {$currency->code}", [
                'amount_received' => $amount,
                'currency_id' => $currencyId,
                'currency_code' => $currency->code
            ]);

            // Convert amount to BASE currency (order currency = base currency)
            if ($currency->code == $baseCurrencyCode) {
                // Same as base currency, no conversion needed
                $amountInOrderCurrency = $amount;
                $exchangeRate = 1;
            } else {
                // Look up exchange rate from payment currency to base currency
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $currency->code)
                    ->where('target_currency', $baseCurrencyCode)
                    ->first();

                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    // Convert: amount in payment currency * rate = amount in base currency
                    $exchangeRate = floatval($exchangeRateLookup->rate);
                    $amountInOrderCurrency = $amount * $exchangeRate;
                } else {
                    // Try reverse lookup
                    $reverseRateLookup = \App\Models\ExchangeRate::where('base_currency', $baseCurrencyCode)
                        ->where('target_currency', $currency->code)
                        ->first();
                    if ($reverseRateLookup && floatval($reverseRateLookup->rate) > 0) {
                        // Reverse: amount in payment currency / reverse_rate = amount in base currency
                        $exchangeRate = 1 / floatval($reverseRateLookup->rate);
                        $amountInOrderCurrency = $amount / floatval($reverseRateLookup->rate);
                    } else {
                        // No exchange rate found, use 1:1 as fallback
                        Log::warning("No exchange rate found between {$currency->code} and {$baseCurrencyCode}, using 1:1");
                        $exchangeRate = 1;
                        $amountInOrderCurrency = $amount;
                    }
                }
            }

            // Base currency amount is same as order currency amount (they're the same)
            $amountInBaseCurrency = $amountInOrderCurrency;

            Log::info("Converted payment", [
                'from_currency' => $currency->code,
                'from_amount' => $amount,
                'to_currency' => $baseCurrencyCode,
                'to_amount' => $amountInOrderCurrency,
                'exchange_rate' => $exchangeRate
            ]);

            // Track total actually paid (in base currency) - not capped
            $totalPaidInOrderCurrency += $amountInOrderCurrency;

            // Calculate how much of this payment applies to the order (capped at remaining balance)
            $remainingBalance = max(0, $orderTotal - $totalAppliedToOrder);
            $amountAppliedToOrder = min($amountInOrderCurrency, $remainingBalance);

            // Update running total of amount applied to order
            $totalAppliedToOrder += $amountAppliedToOrder;

            // Create transaction for this payment
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'transaction_no' => $transactionNo . '-' . $transactionSequence,

                // Amount applied to order (capped at remaining balance)
                'amount' => round($amountAppliedToOrder, 6),
                'currency' => $baseCurrencyCode,
                'currency_id' => $baseCurrencyId,

                // Amount in base currency (for reporting)
                'amount_base_currency' => round($amountInBaseCurrency, 6),
                'base_currency' => $baseCurrencyCode,
                'base_currency_id' => $baseCurrencyId,

                // Actual money received (in the payment currency)
                'transaction_amount' => round($amount, 6),
                'transaction_currency' => $currency->code,
                'transaction_currency_id' => $currency->id,

                // Exchange rate info (from payment currency to base currency)
                'exchange_rate' => $exchangeRate != 1 ? $exchangeRate : null,
                'exchange_rate_base' => $exchangeRate != 1 ? $currency->code : null,
                'exchange_rate_target' => $exchangeRate != 1 ? $baseCurrencyCode : null,

                // Payment details
                'payment_method' => $paymentMethodName,
                'pos_payment_method' => $order->pos_payment_method,
                'sign' => '+',
                'type' => 'payment',
                'gateway_response' => $order->payment_transaction_data ?? null,
                'note' => $order->pos_payment_note ?? null,

                // No change on individual transactions, change calculated at end
                'change_amount' => 0,
                'change_currency' => null,
                'change_currency_id' => null,
            ]);

            $transactionsCreated[] = $transaction;

            Log::info("Created transaction {$transaction->transaction_no} for order #{$order->order_serial_no}", [
                'payment_currency' => $currency->code,
                'payment_amount' => $amount,
                'converted_amount' => $amountInOrderCurrency,
                'amount_applied_to_order' => $amountAppliedToOrder,
                'remaining_balance' => $remainingBalance,
                'exchange_rate' => $exchangeRate,
            ]);
        }

        // Calculate change (based on capped transaction amounts vs order total)
        // Since transaction amounts are capped at order total, change will be 0
        $changeAmount = max(0, $totalAppliedToOrder - $orderTotal);

        if ($changeAmount > 0 && !empty($transactionsCreated)) {
            // Add change to the last transaction
            $lastTransaction = end($transactionsCreated);
            $lastTransaction->update([
                'change_amount' => round($changeAmount, 6),
                'change_currency' => $orderCurrencyCode,
                'change_currency_id' => $orderCurrencyId,
            ]);

            Log::info("Added change to transaction {$lastTransaction->transaction_no}", [
                'change_amount' => $changeAmount,
                'change_currency' => $orderCurrencyCode,
            ]);
        }

        // Update order paid_amount and balance_due
        // paid_amount = sum of transaction.amount (order amounts, capped at total)
        // balance_due = remaining order balance
        $order->update([
            'paid_amount' => round($totalAppliedToOrder, 6),
            'balance_due' => round(max(0, $orderTotal - $totalAppliedToOrder), 6),
        ]);

        Log::info("Updated order #{$order->order_serial_no} payment tracking", [
            'total' => $orderTotal,
            'total_converted_payment' => $totalPaidInOrderCurrency,
            'paid_amount' => $totalAppliedToOrder,
            'balance_due' => $order->balance_due,
            'transactions_created' => count($transactionsCreated),
        ]);
    }

    /**
     * Mark a frontend order as PAID and create its transaction record.
     * Safe to call multiple times — idempotent (skips if already paid / transaction exists).
     */
    public function markFrontendOrderAsPaid(FrontendOrder $order, ?string $tranId = null): void
    {
        // Attach tran_id to the order if not already stored
        if ($tranId && !$order->payment_transaction_id) {
            $order->payment_transaction_id = $tranId;
            $order->save();
        }

        if ($order->payment_status !== PaymentStatus::PAID) {
            $order->payment_status = PaymentStatus::PAID;
            $order->save();

            Log::info('Order marked as PAID via PayWay callback', [
                'order_id'        => $order->id,
                'order_serial_no' => $order->order_serial_no,
                'tran_id'         => $tranId,
            ]);
        }

        // Create transaction record (no-op if one already exists)
        $this->createTransactionForFrontendOrder($order);
    }

    /**
     * Create transaction record for paid frontend order (Telegram Mini App, Table Order, Online Order)
     */
    protected function createTransactionForFrontendOrder(FrontendOrder $order): void
    {
        // Check if transaction already exists for this order
        $existingTransaction = Transaction::where('order_id', $order->id)->first();
        if ($existingTransaction) {
            Log::info("Transaction already exists for order #{$order->order_serial_no}");
            return;
        }

        // Get branch and currency information
        $branch = $order->branch;
        $baseCurrency = $branch->currency ?? null;
        $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
        $baseCurrencyId = $baseCurrency?->id ?? null;

        // Order currency (should be same as base currency)
        $orderCurrencyCode = $order->currency ?? $baseCurrencyCode;
        $orderCurrencyId = $order->currency_id ?? $baseCurrencyId;

        // Get actual payment details from PaywayTransaction if available
        $actualPaymentAmount = null;
        $actualPaymentCurrency = null;
        $actualPaymentCurrencyId = null;

        if ($order->payment_transaction_id) {
            $paywayTransaction = \App\Models\PaywayTransaction::where('tran_id', $order->payment_transaction_id)->first();
            if ($paywayTransaction) {
                // IMPORTANT: For transaction reference, we want the REQUESTED amount, not the actual payment
                // - amount/currency = what was requested from PayWay (e.g., 28,700 KHR)
                // - payment_amount/payment_currency = what customer actually paid (e.g., 7.18 USD)
                // We want the REQUESTED amount to preserve the payment currency reference
                $actualPaymentAmount = $paywayTransaction->amount; // Use requested amount
                $actualPaymentCurrency = $paywayTransaction->currency; // Use requested currency

                // Get currency ID from code
                if ($actualPaymentCurrency) {
                    $paymentCurrencyModel = \App\Models\Currency::where('code', $actualPaymentCurrency)->first();
                    $actualPaymentCurrencyId = $paymentCurrencyModel?->id;
                }

                Log::info('Using PaywayTransaction data for Telegram mini app transaction record', [
                    'order_id' => $order->id,
                    'tran_id' => $order->payment_transaction_id,
                    'requested_amount' => $paywayTransaction->amount,
                    'requested_currency' => $paywayTransaction->currency,
                    'actual_payment_amount' => $paywayTransaction->payment_amount,
                    'actual_payment_currency' => $paywayTransaction->payment_currency,
                    'using_amount' => $actualPaymentAmount,
                    'using_currency' => $actualPaymentCurrency,
                    'using_currency_id' => $actualPaymentCurrencyId,
                    'order_currency' => $orderCurrencyCode,
                    'order_total' => $order->total + $order->total_tax
                ]);
            } else {
                Log::warning('PaywayTransaction not found for order', [
                    'order_id' => $order->id,
                    'payment_transaction_id' => $order->payment_transaction_id
                ]);
            }
        }

        // Fallback to order's receive_payment_currency if PayWay data not available
        $transactionCurrency = $actualPaymentCurrency ?? $order->receive_payment_currency ?? $orderCurrencyCode;
        $transactionCurrencyId = $actualPaymentCurrencyId ?? $order->receive_payment_currency_id ?? $orderCurrencyId;

        // Log the currency resolution process
        Log::info('Transaction currency resolution for Telegram mini app order', [
            'order_id' => $order->id,
            'order_currency' => $orderCurrencyCode,
            'order_currency_id' => $orderCurrencyId,
            'order_receive_payment_currency' => $order->receive_payment_currency,
            'order_receive_payment_currency_id' => $order->receive_payment_currency_id,
            'payway_payment_currency' => $actualPaymentCurrency,
            'payway_payment_currency_id' => $actualPaymentCurrencyId,
            'final_transaction_currency' => $transactionCurrency,
            'final_transaction_currency_id' => $transactionCurrencyId,
        ]);

        // If we have currency code but no ID, look it up
        if ($transactionCurrency && !$transactionCurrencyId) {
            $currencyModel = \App\Models\Currency::where('code', $transactionCurrency)->first();
            if ($currencyModel) {
                $transactionCurrencyId = $currencyModel->id;
                Log::info('Looked up transaction currency ID from code', [
                    'currency_code' => $transactionCurrency,
                    'currency_id' => $transactionCurrencyId
                ]);
            }
        }

        // Determine payment method name
        $paymentMethodName = 'Online';
        if ($order->paymentMethod) {
            $paymentMethodName = $order->paymentMethod->name ?? 'Online';
        }

        // Use payment_transaction_id as transaction_no if available, otherwise generate one
        $transactionNo = $order->payment_transaction_id ?? ('TXN-' . $order->order_serial_no . '-' . time());

        // Calculate order total (in order currency)
        $orderTotal = $order->total + $order->total_tax;

        // Transaction amount = ACTUAL amount customer paid in payment currency
        // IMPORTANT: For Telegram mini app with PayWay, we should ALWAYS have PayWay data
        // The PayWay transaction amount is already in the payment currency
        // We should NOT convert it - just use it as-is to preserve the reference
        if ($actualPaymentAmount !== null) {
            // Use the actual payment amount from PayWay (already in payment currency)
            $transactionAmount = $actualPaymentAmount;
            Log::info('Transaction amount from PayWay data (no conversion)', [
                'transaction_amount' => $transactionAmount,
                'transaction_currency' => $transactionCurrency
            ]);
        } else {
            // Fallback: If no PayWay data, convert order total to payment currency
            // This should rarely happen for Telegram mini app orders
            if ($transactionCurrency !== $orderCurrencyCode) {
                Log::warning('No PayWay data available, converting order total to payment currency', [
                    'order_total' => $orderTotal,
                    'from_currency' => $orderCurrencyCode,
                    'to_currency' => $transactionCurrency
                ]);

                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $orderCurrencyCode)
                    ->where('target_currency', $transactionCurrency)
                    ->first();

                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $transactionAmount = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    // Try reverse
                    $reverseRateLookup = \App\Models\ExchangeRate::where('base_currency', $transactionCurrency)
                        ->where('target_currency', $orderCurrencyCode)
                        ->first();
                    if ($reverseRateLookup && floatval($reverseRateLookup->rate) > 0) {
                        $transactionAmount = $orderTotal / floatval($reverseRateLookup->rate);
                    } else {
                        $transactionAmount = $orderTotal;
                    }
                }
            } else {
                $transactionAmount = $orderTotal;
            }
        }

        // Calculate exchange rate for record keeping
        $exchangeRate = null;
        $exchangeRateBase = null;
        $exchangeRateTarget = null;

        if ($transactionCurrency !== $orderCurrencyCode && $transactionAmount > 0) {
            // Calculate exchange rate: transaction currency -> order currency
            $exchangeRate = $orderTotal / $transactionAmount;
            $exchangeRateBase = $transactionCurrency;
            $exchangeRateTarget = $orderCurrencyCode;
        }

        // Calculate base currency amount (if order currency is different from base currency)
        if ($orderCurrencyCode === $baseCurrencyCode) {
            $amountBaseCurrency = $orderTotal;
        } else {
            $baseCurrencyExchangeRate = $baseCurrency?->exchange_rate ?? 1;
            $amountBaseCurrency = $orderTotal * $baseCurrencyExchangeRate;
        }

        // Log all values before creating Telegram mini app transaction
        Log::info('Creating Telegram mini app transaction with values:', [
            'order_id' => $order->id,
            'order_serial_no' => $order->order_serial_no,
            'transaction_no' => $transactionNo,
            'amount' => $orderTotal,
            'currency' => $orderCurrencyCode,
            'currency_id' => $orderCurrencyId,
            'transaction_amount' => $transactionAmount,
            'transaction_currency' => $transactionCurrency,
            'transaction_currency_id' => $transactionCurrencyId,
            'exchange_rate' => $exchangeRate,
            'payment_method' => $paymentMethodName,
        ]);

        // Create transaction record
        Transaction::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_no' => $transactionNo,
            // Amount = order total in order currency (which equals branch base currency)
            'amount' => $orderTotal,
            'currency' => $orderCurrencyCode,
            'currency_id' => $orderCurrencyId,
            // Amount in base currency (for accounting purposes)
            'amount_base_currency' => $amountBaseCurrency,
            'base_currency' => $baseCurrencyCode,
            'base_currency_id' => $baseCurrencyId,
            // Transaction amount = ACTUAL amount paid in PAYMENT currency (MUST NOT be converted)
            // This is the reference to the real payment amount customer paid
            'transaction_amount' => $transactionAmount,
            'transaction_currency' => $transactionCurrency,
            'transaction_currency_id' => $transactionCurrencyId,
            'change_amount' => 0,
            'change_currency' => null,
            'change_currency_id' => null,
            // Exchange rate from payment currency to order currency
            'exchange_rate' => $exchangeRate,
            'exchange_rate_base' => $exchangeRateBase,
            'exchange_rate_target' => $exchangeRateTarget,
            'payment_method' => $paymentMethodName,
            'pos_payment_method' => $order->pos_payment_method,
            'sign' => '+',
            'type' => 'payment',
            'gateway_response' => $order->payment_transaction_data ?? null,
        ]);

        Log::info("Created transaction {$transactionNo} for Telegram mini app order #{$order->order_serial_no}", [
            'payment_method' => $paymentMethodName,
            'order_amount' => $orderTotal,
            'order_currency' => $orderCurrencyCode,
            'transaction_amount' => $transactionAmount,
            'transaction_currency' => $transactionCurrency,
            'currencies_match' => ($transactionCurrency === $orderCurrencyCode),
            'exchange_rate' => $exchangeRate,
            'base_currency' => $baseCurrencyCode,
        ]);
    }

    /**
     * Handle point reversal when order is deleted
     */
    protected function handlePointReversalOnDelete(Order $order): void
    {
        if (!$order->member_id) {
            return;
        }

        $member = $order->member;
        if (!$member) {
            // Log::warning("Member not found for order #{$order->order_serial_no} during point reversal");
            return;
        }

        // Revert earned points if any
        if ($order->points_earned > 0) {
            $this->memberService->deductPoints(
                $member,
                $order->points_earned,
                Order::class, // Save the class name as reference_type, 'order_delete'
                $order->id,
                "Points reverted from deleted order #{$order->order_serial_no}"
            );
            Log::info("Reverted {$order->points_earned} earned points from member {$member->id} for deleted order #{$order->order_serial_no}");
        }

        // Refund redeemed points if any
        if ($order->points_redeemed > 0) {
            $this->memberService->addPoints(
                $member,
                $order->points_redeemed,
                Order::class, // Save the class name as reference_type, 'order_delete_refund'
                $order->id,
                "Points refunded from deleted order #{$order->order_serial_no}"
            );
            Log::info("Refunded {$order->points_redeemed} redeemed points to member {$member->id} for deleted order #{$order->order_serial_no}");
        }
    }

    /**
     * Get member by phone or card number
     */
    public function findMemberByPhoneOrCard(string $identifier): ?Member
    {
        return Member::where('phone', $identifier)
            ->orWhere('card_number', $identifier)
            ->first();
    }

    public $orderTime = 0;
    protected array $orderFilter = [
        'order_serial_no',
        'user_id',
        'branch_id',
        'total',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'delivery_boy_id',
        'source'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            $orders = Order::with(['transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'user' => function($query) {
                    $query->select('id', 'name');
                }])
                ->where(function ($query) use ($requests) {
                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    } else {
                        $first_date = Carbon::today()->startOfDay();
                        $last_date  = Carbon::today()->endOfDay();

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    }
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {

                                $query->where($key, (int)$request);

                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('pos_payment_method', abs($request));
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn, $orderType)
                ->$method($methodValue);
            return $orders;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */

    public function listOrderDeleted(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            $order = OrderDeleted::with('paymentMethod','user','branch')->where(function ($query) use ($requests) {
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = AppLibrary::filterDateTime($requests['from_date']);
                    $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                    $query->whereBetween('order_datetime', [$first_date, $last_date]);
                } else {
                    $first_date = Carbon::today()->startOfDay();
                    $last_date  = Carbon::today()->endOfDay();

                    $query->whereBetween('order_datetime', [$first_date, $last_date]);
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        if ($key === "order_serial_no") {
                            $query->where($key, (int)$request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method($methodValue);
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listOrderItemDeleted(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            $order = OrderItemDeleted::with('item')->where(function ($query) use ($requests) {
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = AppLibrary::filterDateTime($requests['from_date']);
                    $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                    $query->whereBetween('order_created_at', [$first_date, $last_date]);
                } else {
                    $first_date = Carbon::today()->startOfDay();
                    $last_date  = Carbon::today()->endOfDay();

                    $query->whereBetween('order_created_at', [$first_date, $last_date]);
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        if ($key === "status") {
                            // $query->where($key, (int)$request);
                            $query->where('order_status_id', (int)$request);
                        } else if ($key === 'payment_method' && (int)$request < 0) {
                            $query->where('pos_payment_method', abs($request));
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method($methodValue);

            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listPendings(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            $orders = Order::with('orderStatus','orderType','transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'member', 'user')
                ->where(function ($query) use ($requests) {

                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    }
                    //  else {
                    //     $first_date = Carbon::today()->startOfDay();
                    //     $last_date  = Carbon::today()->endOfDay();

                    //     $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    // }


                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {
                                $query->where('status', (int)$request);
                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('pos_payment_method', abs($request));
                            }  else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn, $orderType)->$method(
                    $methodValue
                );
            Log::info($orders);
            return $orders;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listUnpaids(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';


            $orders = Order::with('orderStatus','orderType','transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'member', 'user')
                ->where('payment_status', PaymentStatus::UNPAID)
                ->where(function ($query) use ($requests) {

                    if (isset($requests['from_date']) && isset($requests['to_date'])) {
                        $first_date = AppLibrary::filterDateTime($requests['from_date']);
                        $last_date  = AppLibrary::filterDateTime($requests['to_date']);

                        $query->whereBetween('order_datetime', [$first_date, $last_date]);
                    }

                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {
                                $query->where('status', (int)$request);
                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('pos_payment_method', abs($request));
                            }  else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn, $orderType)->$method(
                    $methodValue
                );
            return $orders;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function userOrder(PaginateRequest $request, User $user)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::where('order_type', "!=", OrderType::POS)->where(function ($query) use ($requests, $user) {
                $query->where('user_id', $user->id);
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('status', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function deliveredOrder(PaginateRequest $request, User $user)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::where('delivery_boy_id', $user->id)->where('order_type', "!=", OrderType::POS)->where(
                function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('status', '!=', $explode);
                                }
                            }
                        }
                    }
                }
            )->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function myOrderStore(OrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $this->order = Order::create(
                    $request->validated() + [
                        'user_id'          => Auth::user()->id,
                        'status'           => OrderStatus::PENDING,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('site')->get('site_food_preparation_time')
                    ]
                );

                $i            = 0;
                $totalTax     = 0;
                $subtotal     = 0; // Add this
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'discount_percentage'  => (float)($item->discount_percentage ?? 0),
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $subtotal += $item->total_price + $taxPrice; // Add this
                        $totalTax = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                // $this->order->subtotal_without_tax_currency_price = $subtotal; // Add this
                // $this->order->total_currency_price = $subtotal + $totalTax;    // Add this
                $this->order->save();

                if ($request->address_id) {
                    $address = Address::find($request->address_id);
                    if ($address) {
                        OrderAddress::create([
                            'order_id'  => $this->order->id,
                            'user_id'   => Auth::user()->id,
                            'label'     => $address->label,
                            'address'   => $address->address,
                            'apartment' => $address->apartment,
                            'latitude'  => $address->latitude,
                            'longitude' => $address->longitude
                        ]);
                    }
                }
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Create order in POS system, include paid order and unpaid order
     * Can check if the order is paid or not by payment status
     */
    public function posOrderStore(PosOrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $today = date('Y-m-d');
                    $last_waiting_number = Order::whereDate('created_at', $today)->max('waiting_number');
                    $waiting_number = $last_waiting_number ? $last_waiting_number + 1 : 1;

                    $orderDateTime = Carbon::now()->toDateTimeString();
                    // $checkInTime = $request->check_in_time ? $request->check_in_time : $orderDateTime;




                    $this->order = Order::create(
                        $request->validated() + [
                            'user_id'           => $request->customer_id,
                            'order_user_id'     => Auth::user()->id,
                            'status'            => OrderStatus::ACCEPT,
                            'token'             => $request->token,
                            'payment_status'    => (int) $request->payment_status,
                            'order_datetime'    => $orderDateTime,
                            'preparation_time'  => Settings::group('order_setup')->get('order_setup_food_preparation_time'),
                            'waiting_number'    => $waiting_number,
                            'business_date'     => $request->business_date ?: Carbon::now(),
                            'payment_method_id' => $request->pos_payment_method,
                            'payment_method'    => $request->payment_method,
                            'check_in_time'     => $orderDateTime,
                            'checkout'    => $request->check_out_time ? $orderDateTime : null,
                            'payment_transaction_id' => $request->payment_transaction_id,
                            'payment_transaction_data' => $request->payment_transaction_data,
                        ]
                    );
                $i            = 0;
                $totalTax     = 0;
                $subtotal     = 0; // Add this
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');
                $requestOrderDinings = $request->order_dinings;

                if (!blank($requestItems)) {

                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'discount_percentage'  => (float)($item->discount_percentage ?? 0),
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                            'created_at'           => date('Y-m-d H:i:s')
                        ];
                        $subtotal += $item->total_price + $taxPrice; // Add this
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }


                foreach ($itemsArray as $item) {
                    $itemData = Item::find($item['item_id']);
                    if ($itemData['manage_stock'] == ManageStock::YES) {
                        $stock = DB::table('stock_records')
                            ->leftJoin('items', 'stock_records.item_id', '=', 'items.id')
                            ->leftJoin('item_stocks', 'stock_records.stock_id', '=', 'item_stocks.id')
                            ->where('stock_records.item_id', '=', $item['item_id'])
                            ->select('items.id', 'item_stocks.id as stock_id')
                            ->first();
                        if (!$stock || !$stock->id) {
                            continue;
                        }
                        StockRecord::insert([
                            'item_id'    => $item['item_id'],
                            'stock_id'   => $stock->stock_id,
                            'user_id'    => $this->order->user_id,
                            'order_id'   => (int)$item['order_id'],
                            'quantity'   => $item['quantity'] * (-1),
                            'record_type' => 'STOCK_OUT',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                if (is_array($requestOrderDinings)) {
                    foreach ($requestOrderDinings as $orderDining) {
                        OrderDining::create([
                            'order_id' => $this->order->id,
                            'dining_table_id' => $orderDining['id'],
                            'branch_id' => $request->branch_id
                        ]);
                        DiningTable::where('id', $orderDining['id'])->update([
                            'current_order_id' => $this->order->id
                        ]);
                    }
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $lastSerial = Order::max('order_serial_no') ?? 0;
                $this->order->order_serial_no = $lastSerial + 1;
                // $this->order->total_tax       = $totalTax;
                $this->order->save();

                // Log POS order creation activity
                $this->activityLogger->logOrderActivity('created via POS', $this->order, [
                    'payment_method' => $request->payment_method,
                    'pos_payment_method' => $request->pos_payment_method,
                    'items_count' => count($requestItems ?? []),
                    'table_count' => count($requestOrderDinings ?? []),

                ]);

                // Handle member integration and points
                if (!empty($request->member_id)) {
                    $this->handleMemberIntegration($this->order, $request->member_id);
                }

                // Handle point redemption if specified
                if (!empty($request->points_to_redeem) && $request->points_to_redeem > 0) {
                    $redemptionResult = $this->handlePointRedemption($this->order, $request->points_to_redeem);
                    if ($redemptionResult['success'] && $redemptionResult['discount_amount'] > 0) {
                        // Update order total with discount
                        $this->order->discount += $redemptionResult['discount_amount'];
                        $this->order->total -= $redemptionResult['discount_amount'];
                        $this->order->save();
                    }
                }

                if ($this->order->payment_status == PaymentStatus::PAID) {
                    // Log::info("Order #{$this->order->order_serial_no} is paid, handling point allocation and redemption");

                    // Set check_out_time when order is paid
                    if (!$this->order->check_out_time) {
                        $this->order->check_out_time = now();
                        $this->order->save();
                    }

                    $this->handlePointAllocationOnPayment($this->order);
                    $this->handlePointRedemptionOnPayment($this->order);

                    // Create transaction record(s) for the payment
                    // Check if multi-currency payment data exists
                    // BUT: If payment_transaction_id exists (PayWay/gateway payment), use single transaction
                    // to preserve the gateway's transaction ID without sequence suffix
                    if ($request->has('received_amounts') && !empty($request->received_amounts) && !$this->order->payment_transaction_id) {
                        $this->createMultiCurrencyTransactions($this->order, $request->received_amounts);
                    } else {
                        $this->createTransactionForOrder($this->order);
                    }

                    // Auto-release dining tables if branch setting is enabled
                    $this->autoReleaseDiningTablesOnPayment($this->order);
                }
            });
            // $this->telegramNotificationService->sendMessage('Order: ' . json_encode($this->order));
            // $this->sendTelegramNotificationByStatus($order);

            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Add order item in POS system for existing order
     * Payment status here still in unpaid status
     */
    public function posAddOrderStore(PosOrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $today = date('Y-m-d');
                $waiting_number = Order::whereDate('created_at', $today)->count();
                $waiting_number += 1;

                $i            = 0;
                $totalTax     = 0;
                $subtotal     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                $order_times = 0;


                $orderItem = OrderItem::where('order_id', $request->id)->orderBy('order_times', 'desc')->first();

                if ($orderItem) {
                    $order_times = $orderItem->order_times + 1;
                    $this->orderTime = $order_times;
                }

                $this->order = Order::find($request->id);
                if (!$this->order) {
                    throw new Exception('Order not found', 404);
                }

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId    = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName  = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate  = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType  = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $discount = 0;

                        // if($this->order->discount_percentage) {
                        //     $taxPrice = $taxPrice * (100 - $this->order->discount_percentage) / 100;
                        // }

                        $itemsArray[$i] = [
                            'order_id'             => $request->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => $discount,
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'order_times'          => $order_times,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            // 'total_price'          => $item->total_price + $taxPrice, // Add tax price to total price
                            'total_price'          => $item->total_price,
                            'created_at'           => date('Y-m-d H:i:s')
                        ];

                        // Order::where('id', $request->id)->update([
                        //     'total_tax' => DB::raw('total_tax + ' . $taxPrice),
                        //     'subtotal' => DB::raw('subtotal + ' . $item->total_price),
                        //     'discount' => $order->discount_percentage ? ($order->subtotal * $order->discount_percentage / 100) : 0,
                        //     'total'    => DB::raw('total + ' . $item->total_price . ' - ' . $taxPrice),

                        // ]);

                        $subtotal += $item->total_price + $taxPrice; // Add this
                        $totalTax += $taxPrice;
                        $i++;
                    }
                }

                foreach ($itemsArray as $item) {
                    $itemData = Item::find($item['item_id']);
                    if ($itemData && $itemData['manage_stock'] == ManageStock::YES) {
                        $stock = DB::table('stock_records')
                            ->leftJoin('items', 'stock_records.item_id', '=', 'items.id')
                            ->leftJoin('item_stocks', 'stock_records.stock_id', '=', 'item_stocks.id')
                            ->where('stock_records.item_id', '=', $item['item_id'])
                            ->select('items.id', 'item_stocks.id as stock_id')
                            ->first();
                        if (!$stock || !$stock->id) {
                            continue;
                        }
                        StockRecord::insert([
                            'item_id'    => $item['item_id'],
                            'stock_id'   => $stock->stock_id,
                            'user_id'    => optional(Order::find($request->id))->user_id,
                            'order_id'   => (int)$item['order_id'],
                            'quantity'   => $item['quantity'] * (-1),
                            'record_type' => 'STOCK_OUT',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                // Log POS order creation activity
                $this->activityLogger->logOrderActivity('Added items to existing order', $this->order, [
                    'payment_method' => $request->payment_method,
                    'pos_payment_method' => $request->pos_payment_method,
                    'items_added_count' => count($requestItems ?? []),
                    'order_id' => $this->order->id,
                    'order_times' => $order_times,
                ]);

                $order = Order::find($request->id);

                if ($order) {

                    $this->updateOrderTotalPrice($order);

                    // $subtotal = 0;
                    // $total_tax = 0;
                    // $discount = 0;
                    // $total = 0;

                    // $subtotal = $order->orderItems()->sum('total_price');
                    // $total_tax = $order->orderItems()->sum('tax_amount');

                    // if($order->discount_percentage > 0){
                    //     $discount = ($subtotal * $order->discount_percentage) / 100;
                    //     $total_tax = $total_tax * (100 - $order->discount_percentage) / 100;
                    // }
                    // $total = $subtotal - $discount;

                    // $order->subtotal = $subtotal;
                    // $order->discount = $discount;
                    // $order->total_tax = $total_tax;  //Tax after discount
                    // $order->total = $total; // Total after discount

                    // $order->save();

                    //Last Order Time Items
                    $this->lastOrderTimeItems = clone $order;

                    $this->lastOrderTimeItems->orderItems = $this->lastOrderTimeItems->orderItems->filter(function ($item) use ($order_times) {
                        return $item->order_times == $order_times;
                    });

                    $lastOrder_subtotal = 0;
                    $lastOrder_totalDiscount = 0;
                    $lastOrder_totalTax = 0;
                    $lastOrder_total = 0;

                    foreach ($this->lastOrderTimeItems->orderItems as $item) {
                        $lastOrder_subtotal += $item->total_price + $item->tax_amount; // Add this
                        $lastOrder_totalTax += $item->tax_amount;
                        // $total += $item->total_price + $item->tax_amount;
                    }

                    $lastOrder_totalDiscount = $this->lastOrderTimeItems->discount_percentage ? ($lastOrder_subtotal * $this->lastOrderTimeItems->discount_percentage / 100) : 0;
                    $lastOrder_totalTax = $this->lastOrderTimeItems->discount_percentage ? ($lastOrder_totalTax * (100 - $this->lastOrderTimeItems->discount_percentage / 100)) : $lastOrder_totalTax;
                    $lastOrder_total = $lastOrder_subtotal - $lastOrder_totalDiscount;

                    $this->lastOrderTimeItems->subtotal = $lastOrder_subtotal;
                    $this->lastOrderTimeItems->discount = $lastOrder_totalDiscount;
                    $this->lastOrderTimeItems->total_tax = $lastOrder_totalTax;
                    $this->lastOrderTimeItems->total = $lastOrder_total;

                    // $this->lastOrderTimeItems->subtotal_without_tax_currency_price = $subtotal;
                    // $this->lastOrderTimeItems->total_currency_price = $subtotal + $totalTax;
                }
            });
            return $this->lastOrderTimeItems;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Delete an order and its items, with backup to deleted tables
     */
    public function destroy(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                $this->handlePointReversalOnDelete($order);
                OrderDeleted::create([
                    'order_serial_no' => $order->order_serial_no,
                    'invoice_number' => $order->invoice_number,
                    'token' => $order->token,
                    'user_id' => $order->user_id,
                    'branch_id' => $order->branch_id,
                    'subtotal' => $order->subtotal,
                    'discount' => $order->discount,
                    'delivery_charge' => $order->delivery_charge,
                    'total_tax' => $order->total_tax,
                    'total' => $order->total,
                    'order_type' => $order->order_type,
                    'order_datetime' => $order->order_datetime,
                    'delivery_time' => $order->delivery_time,
                    'preparation_time' => $order->preparation_time,
                    'is_advance_order' => $order->is_advance_order,
                    'payment_method' => $order->payment_method,
                    'pos_payment_method' => $order->pos_payment_method,
                    'pos_payment_note' => $order->pos_payment_note,
                    'payment_status' => $order->payment_status,
                    'status' => $order->payment_status == PaymentStatus::PAID ? OrderStatus::VOID : OrderStatus::CANCELED,
                    'dining_table' => $order->orderDinings->map(function($dining) {
                      return DiningTable::find($dining->dining_table_id)?->name;
                    })->toArray(),
                    'delivery_boy_id' => $order->delivery_boy_id,
                    'reason' => $order->reason,
                    'source' => $order->source,
                    'waiting_number' => $order->waiting_number,
                    'business_date' => $order->business_date,
                ]);

                //TODO: loop to delete order items
                // foreach ($order->orderItems as $orderItem) {
                //     OrderItemDeleted::create([
                //         'order_id'  => $order->id,
                //         'order_serial_no' => $order->order_serial_no,
                //         // 'delete_reason' => '', // You can add a reason if available
                //         'branch_id' => $orderItem->branch_id,
                //         'item_id' => $orderItem->item_id,
                //         'quantity' => $orderItem->quantity,
                //         'discount' => $orderItem->discount,
                //         'discount_percentage' => $orderItem->discount_percentage ?? 0,
                //         'tax_name' => $orderItem->tax_name,
                //         'tax_rate' => $orderItem->tax_rate,
                //         'tax_type' => $orderItem->tax_type,
                //         'tax_amount' => $orderItem->tax_amount,
                //         'price' => $orderItem->price,
                //         'item_variations' => $orderItem->item_variations,
                //         'item_extras' => $orderItem->item_extras,
                //         'item_variation_total' => $orderItem->item_variation_total,
                //         'item_extra_total' => $orderItem->item_extra_total,
                //         'total_price' => $orderItem->total_price,
                //         'instruction' => $orderItem->instruction,
                //         'order_times' => $orderItem->order_times,
                //         'order_item_status' => $orderItem->order_item_status,
                //         'reasons' => $orderItem->reasons,
                //         'creator_type' => $orderItem->creator_type,
                //         'creator_id' => $orderItem->creator_id,
                //         'editor_type' => $orderItem->editor_type,
                //         'editor_id' => $orderItem->editor_id,
                //         'order_created_at' => $orderItem->created_at,
                //         'order_updated_at' => $orderItem->updated_at,
                //     ]);
                // }


                // Set current_order_id to NULL for all dining tables associated with this order
                DiningTable::where('current_order_id', $order->id)->update(['current_order_id' => null]);

                $this->removeLinkedMassageSessionsOnOrderDelete($order);

                $order->address()?->delete();
                $order->orderItems()?->delete();
                $order->orderDinings()->delete();

                $this->activityLogger->logOrderActivity('deleted', $order, [
                    'deleted_by' => Auth::user() ? Auth::user()->id : null,
                    'order_id' => $order->id,
                    'order_serial_no' => $order->order_serial_no,
                ]);

                // Finally, delete the order
                $order->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function removeLinkedMassageSessionsOnOrderDelete(Order $order): void
    {
        $groupIds = collect();

        $directSessions = SubSession::where('order_id', $order->id)->get(['id', 'group_session_id']);
        if ($directSessions->isNotEmpty()) {
            $groupIds = $groupIds->merge($directSessions->pluck('group_session_id')->filter());
            foreach ($directSessions as $subSession) {
                $subSession->delete();
            }
        } elseif (!empty($order->group_session_id)) {
            // Fallback for legacy/edge records where checked-out sessions were not tied with order_id.
            $fallbackSessions = SubSession::where('group_session_id', $order->group_session_id)
                ->where(function ($query) {
                    $query->where('is_checked_out', true)
                        ->orWhere('status', SubSessionStatus::DONE);
                })
                ->get(['id', 'group_session_id']);

            if ($fallbackSessions->isNotEmpty()) {
                $groupIds = $groupIds->merge($fallbackSessions->pluck('group_session_id')->filter());
                foreach ($fallbackSessions as $subSession) {
                    $subSession->delete();
                }
            }
        }

        $groupIds = $groupIds->unique()->values();
        foreach ($groupIds as $groupId) {
            $groupSession = GroupSession::find($groupId);
            if (!$groupSession) {
                continue;
            }

            $groupSession->syncGuestCount();

            if ($groupSession->subSessions()->count() === 0) {
                $groupSession->delete();
            }
        }
    }

     public function destroyOrderDeleted(OrderDeleted $orderDeleted)
    {
        try {
            DB::transaction(function () use ($orderDeleted) {
                $orderDeleted->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Void a transaction by creating a negative transaction and updating order
     *
     * @param int $transactionId
     * @param float $voidAmount
     * @return Transaction
     * @throws Exception
     */
    public function voidTransaction(int $transactionId, float $voidAmount): Transaction
    {
        try {
            // Find the original transaction
            $originalTransaction = Transaction::findOrFail($transactionId);

            // Validate that the transaction can be voided
            if ($originalTransaction->type !== 'payment') {
                throw new Exception('Only payment transactions can be voided', 422);
            }

            if ($originalTransaction->sign !== '+') {
                throw new Exception('Only positive transactions can be voided', 422);
            }

            // Check if payment method provider is payway
            if ($originalTransaction->pos_payment_method) {
                $paymentMethod = PaymentMethod::find($originalTransaction->pos_payment_method);
                if ($paymentMethod && $paymentMethod->provider && strtolower($paymentMethod->provider) === 'payway') {
                    throw new Exception('PayWay transactions cannot be voided, use refund instead', 422);
                }
            }

            // Check if already voided
            $existingVoid = Transaction::where('reference_transaction', $originalTransaction->id)
                ->where('type', 'void')
                ->first();

            if ($existingVoid) {
                throw new Exception('This transaction has already been voided', 422);
            }

            DB::beginTransaction();

            // Get branch and currency information for the void transaction
            $order = Order::with('branch.currency')->find($originalTransaction->order_id);
            $baseCurrency = $order?->branch?->currency ?? null;
            $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
            $orderCurrencyCode = $originalTransaction->currency ?? $baseCurrencyCode;
            $transactionCurrency = $originalTransaction->transaction_currency ?? $orderCurrencyCode;

            // Calculate base currency amount for void
            if ($orderCurrencyCode === $baseCurrencyCode) {
                $amountBaseCurrency = $voidAmount;
                $exchangeRate = null;
            } else {
                $exchangeRate = $baseCurrency?->exchange_rate ?? 1;
                $amountBaseCurrency = $voidAmount * $exchangeRate;
            }

            // Create void transaction with negative sign
            $voidTransaction = Transaction::create([
                'order_id' => $originalTransaction->order_id,
                'user_id' => Auth::id(),
                'transaction_no' => 'VOID-' . $originalTransaction->transaction_no . '-' . time(),
                'amount' => $voidAmount,
                'currency' => $baseCurrencyCode,
                'amount_base_currency' => $amountBaseCurrency,
                'base_currency' => $baseCurrencyCode,
                'transaction_amount' => $voidAmount,
                'transaction_currency' => $transactionCurrency,
                'change_amount' => 0,
                'change_currency' => null,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_base' => $exchangeRate ? $orderCurrencyCode : null,
                'exchange_rate_target' => $exchangeRate ? $baseCurrencyCode : null,
                'payment_method' => $originalTransaction->payment_method,
                'pos_payment_method' => $originalTransaction->pos_payment_method,
                'sign' => '-',
                'type' => 'void',
                'reference_transaction' => $originalTransaction->id,
                'gateway_response' => null,
            ]);

            // Update order's pos_received_amount and payment_status
            if ($order) {
                // Reduce received amount by void amount
                $newReceivedAmount = max(0, $order->pos_received_amount - $voidAmount);
                $order->pos_received_amount = $newReceivedAmount;

                // Calculate total transactions (payment - refund - void)
                $totalTransactions = Transaction::where('order_id', $order->id)
                    ->selectRaw("SUM(CASE WHEN sign = '+' THEN amount ELSE -amount END) as net_amount")
                    ->value('net_amount') ?? 0;

                // Update payment status based on remaining amount
                $orderTotal = $order->total + $order->total_tax;
                if ($totalTransactions <= 0) {
                    $order->payment_status = PaymentStatus::UNPAID;
                } elseif ($totalTransactions < $orderTotal) {
                    $order->payment_status = PaymentStatus::UNPAID; // Partial void considered unpaid
                } else {
                    $order->payment_status = PaymentStatus::PAID;
                }

                $order->save();

                Log::info("Updated order #{$order->order_serial_no} after void: pos_received_amount={$newReceivedAmount}, payment_status={$order->payment_status}");
            }

            DB::commit();

            Log::info("Created void transaction {$voidTransaction->transaction_no} for original transaction {$originalTransaction->transaction_no}");

            return $voidTransaction;

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Void transaction failed: ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Delete an order item and backup to deleted table
     */
    public function destroyOrderItem(OrderItem $orderItem, Request $request)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:255',
            ]);

            $order = Order::find($orderItem->order_id);
            if ($order) {
                OrderItemDeleted::create([
                    'order_id'  => $order->id,
                    'order_serial_no' => $order->order_serial_no,
                    'delete_reason' => $request->input('reason'),
                    'branch_id' => $orderItem->branch_id,
                    'item_id' => $orderItem->item_id,
                    'quantity' => $orderItem->quantity,
                    'discount' => $orderItem->discount,
                    'discount_percentage' => $orderItem->discount_percentage ?? 0,
                    'tax_name' => $orderItem->tax_name,
                    'tax_rate' => $orderItem->tax_rate,
                    'tax_type' => $orderItem->tax_type,
                    'tax_amount' => $orderItem->tax_amount,
                    'price' => $orderItem->price,
                    'item_variations' => $orderItem->item_variations,
                    'item_extras' => $orderItem->item_extras,
                    'item_variation_total' => $orderItem->item_variation_total,
                    'item_extra_total' => $orderItem->item_extra_total,
                    'total_price' => $orderItem->total_price,
                    'instruction' => $orderItem->instruction,
                    'order_times' => $orderItem->order_times,
                    'order_item_status' => $orderItem->order_item_status,
                    'reasons' => $orderItem->reasons,
                    'creator_type' => $orderItem->creator_type,
                    'creator_id' => $orderItem->creator_id,
                    'editor_type' => $orderItem->editor_type,
                    'editor_id' => $orderItem->editor_id,
                    'order_created_at' => $orderItem->created_at,
                    'order_updated_at' => $orderItem->updated_at,
                ]);

                $this->removeLinkedGroupSessionProductItems($order, $orderItem);

                $orderItem->delete();

                $this->activityLogger->logOrderActivity('deleted order item', $order, [
                    'deleted_by' => Auth::user() ? Auth::user()->id : null,
                    'order_id' => $order->id,
                    'order_serial_no' => $order->order_serial_no,
                ]);

                $subtotal = 0;
                $total_tax = 0;
                $discount = 0;
                $total = 0;

                $subtotal = $order->orderItems()->sum('total_price');
                $total_tax = $order->orderItems()->sum('tax_amount');

                if($order->discount_percentage > 0){
                    $discount = ($subtotal * $order->discount_percentage) / 100;
                    $total_tax = $total_tax * (100 - $order->discount_percentage) / 100;
                }
                $total = $subtotal - $discount;

                $order->subtotal = $subtotal;
                $order->discount = $discount;
                $order->total_tax = $total_tax;
                $order->total = $total;

                $order->save();
                return $orderItem;
            }
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function removeLinkedGroupSessionProductItems(Order $order, OrderItem $orderItem): void
    {
        if (empty($order->group_session_id)) {
            return;
        }

        $itemKind = (int) Item::withTrashed()
            ->where('id', $orderItem->item_id)
            ->value('item_kind');

        if ($itemKind === ItemKind::SERVICE) {
            return;
        }

        $subSessionIds = SubSession::where('group_session_id', $order->group_session_id)
            ->where('order_id', $order->id)
            ->pluck('id');

        if ($subSessionIds->isEmpty()) {
            return;
        }

        $remainingQty = max(1, (int) ($orderItem->quantity ?? 1));

        $sessionItems = SessionItem::whereIn('sub_session_id', $subSessionIds)
            ->where('item_id', $orderItem->item_id)
            ->orderByDesc('id')
            ->get();

        foreach ($sessionItems as $sessionItem) {
            if ($remainingQty <= 0) {
                break;
            }

            $sessionQty = max(1, (int) ($sessionItem->quantity ?? 1));

            if ($sessionQty > $remainingQty) {
                $newQty = $sessionQty - $remainingQty;
                $unitFinalPrice = $sessionQty > 0
                    ? ((float) ($sessionItem->final_price ?? 0) / $sessionQty)
                    : (float) ($sessionItem->final_price ?? 0);

                $sessionItem->quantity = $newQty;
                $sessionItem->final_price = round($unitFinalPrice * $newQty, 2);
                $sessionItem->save();
                $remainingQty = 0;
                break;
            }

            $remainingQty -= $sessionQty;
            $sessionItem->delete();
        }

        if ($remainingQty > 0) {
            Log::warning('Could not fully sync group session items after order item delete.', [
                'order_id' => $order->id,
                'group_session_id' => $order->group_session_id,
                'order_item_id' => $orderItem->id,
                'item_id' => $orderItem->item_id,
                'remaining_qty' => $remainingQty,
            ]);
        }
    }

    public function updateOrderInfo(Order $order, Request $request): object
    {
        try {
            DB::transaction(function () use ($order, $request) {
                // Update basic order fields
                $order->order_type = $request->input('order_type', $order->order_type);
                $order->number_of_people = $request->input('number_of_people', $order->number_of_people);
                $order->order_note = $request->input('order_note', $order->order_note);
                $order->token = $request->input('token', $order->token);
                $order->save();

                // Update dining tables if provided
                if ($request->has('order_dinings')) {
                    $requestOrderDinings = $request->input('order_dinings');

                    // Get current dining table IDs
                    $currentDiningTableIds = $order->orderDinings()->pluck('dining_table_id')->toArray();
                    $newDiningTableIds = array_map(function($dining) {
                        return $dining['id'];
                    }, $requestOrderDinings);

                    // Remove old dining table associations
                    $tablesToRemove = array_diff($currentDiningTableIds, $newDiningTableIds);
                    if (!empty($tablesToRemove)) {
                        // Clear current_order_id from removed tables
                        DiningTable::whereIn('id', $tablesToRemove)
                            ->where('current_order_id', $order->id)
                            ->update(['current_order_id' => null]);

                        // Delete the order_dining records
                        OrderDining::where('order_id', $order->id)
                            ->whereIn('dining_table_id', $tablesToRemove)
                            ->delete();
                    }

                    // Add new dining table associations
                    $tablesToAdd = array_diff($newDiningTableIds, $currentDiningTableIds);
                    if (!empty($tablesToAdd)) {
                        foreach ($tablesToAdd as $tableId) {
                            OrderDining::create([
                                'order_id' => $order->id,
                                'dining_table_id' => $tableId,
                                'branch_id' => $order->branch_id
                            ]);

                            // Set current_order_id for the table
                            DiningTable::where('id', $tableId)
                                ->update(['current_order_id' => $order->id]);
                        }
                    }
                }
            });

            // Reload the order with relationships to return fresh data
            return $order->fresh(['orderDinings', 'orderDinings.diningTable']);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update an order item and recalculate the order totals
     */
    public function updateOrderItems(OrderItem $orderItem, Request $request): object
    {
        try {
            // Log::info('Updating order item: ' . $orderItem->id);
            // Log::info('Order item details: ' . json_encode($orderItem->toArray()));

            // Log::info('Request data: ' . json_encode($request->toArray()));

            DB::transaction(function () use ($orderItem, $request) {

                // Update the order item
                // $orderItem->quantity = $request->quantity;
                $orderItem->quantity = $request->input('quantity', $orderItem->quantity);
                $orderItem->order_item_custom_name = $request->input('order_item_custom_name', $orderItem->order_item_custom_name);
                $orderItem->tax_amount = $request->input('tax_amount', $orderItem->tax_amount);
                $orderItem->price = $request->input('price', $orderItem->price);

                $orderItem->discount = $request->input('discount', $orderItem->discount) ?? 0;
                $orderItem->discount_percentage = $request->input('discount_percentage', $orderItem->discount_percentage) ?? 0;

                $orderItem->total_price = $request->input('total_price', $orderItem->total_price);

                $orderItem->save();

                //TODO: update stock records if necessary
            });

            $order = Order::find($orderItem->order_id);
            if ($order) {
                // Recalculate total_tax and subtotal from all order items


                $subtotal = 0;
                $total_tax = 0;
                $discount = 0;
                $total = 0;


                $subtotal = $order->orderItems()->sum('total_price');
                $total_tax = $order->orderItems()->sum('tax_amount');

                if($order->discount_percentage > 0){
                    $discount = ($subtotal * $order->discount_percentage) / 100;
                    $total_tax = $total_tax * (100 - $order->discount_percentage) / 100;
                }
                $total = $subtotal - $discount;

                $order->subtotal = $subtotal;
                $order->discount = $discount;
                $order->total_tax = $total_tax;  //Tax after discount
                $order->total = $total; // Total after discount

                $order->save();
            }

            return $orderItem;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Remove member from the given order.
     *
     * @param \App\Models\Order $order
     * @return \App\Models\Order
     */
    public function removeMemberFromOrder(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {

                $order->member_id = null;
                $order->save();
                 $order->load('member');
            });

            return $order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Set a member to an order.
     *
     * @param \App\Models\Order $order
     * @param int $memberId
     * @return \App\Models\Order
     * @throws \Exception
     */
    public function setMemberToOrder(Order $order, int $memberId)
    {
         try {

            DB::transaction(function () use ($order, $memberId) {
                $member = Member::findOrFail($memberId);
                $order->member_id = $member->id;
                $order->save();

                // Optionally, reload relationships if needed
                $order->load('member');
            });

            return $order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Pay for an order in POS system
     */
    public function payOrder(Request $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $order = Order::find($request->order_id);
                if ($order) {
                    // Note: invoice_number should be set during order creation, not payment
                    // If needed, it can be generated here only if truly missing
                    if (!$order->invoice_number) {
                        $order->invoice_number = $this->generateInvoiceNumber();
                    }
                    $previousPaymentStatus = $order->payment_status;

                    $order->pos_payment_method = $request->pos_payment_method;
                    $order->payment_method = $request->payment_method;

                    $order->pos_received_amount = $request->pos_received_amount;
                    $order->pos_payment_note = $request->pos_payment_note;
                    $order->payment_status = $request->payment_status;

                    // Save PayWay transaction data if present
                    if ($request->has('payment_transaction_id')) {
                        $order->payment_transaction_id = $request->payment_transaction_id;
                    }
                    if ($request->has('payment_transaction_data')) {
                        $order->payment_transaction_data = $request->payment_transaction_data;
                    }

                    $order->check_out_time = $request->check_out_time ?: now();
                    $order->checkout = $request->check_out_time ? Carbon::now()->toDateTimeString() : null;

                    //start input change amount
                    $changeAmountData = [];
                    if ($request->has('changeAmount') && $request->changeAmount !== null) {
                        $changeAmountData['primary'] = [
                            'amount' => $request->changeAmount,
                            'currency' => $request->currency['code'] ?? 'USD'
                        ];
                    }
                    if ($request->has('secondChangeAmount') && $request->secondChangeAmount !== null) {
                        $changeAmountData['secondary'] = [
                            'amount' => $request->secondChangeAmount,
                            'currency' => $request->currency['second_currency'] ?? 'KHR'
                        ];
                    }

                    if (!empty($changeAmountData)) {
                        $order->change_amount = $changeAmountData;
                    }
                    // end
                    $order->save();

                    // Handle point allocation when payment status changes to paid
                    if ($previousPaymentStatus != PaymentStatus::PAID && $request->payment_status == PaymentStatus::PAID) {
                        $this->handlePointAllocationOnPayment($order);
                        $this->handlePointRedemptionOnPayment($order);

                        // Create transaction record(s) for the payment
                        // Check if multi-currency payment (received_amounts contains multiple currencies)
                        // BUT: If payment_transaction_id exists (PayWay/gateway payment), use single transaction
                        // to preserve the gateway's transaction ID without sequence suffix
                        if ($request->has('received_amounts') && !empty($request->received_amounts) && !$order->payment_transaction_id) {
                            $this->createMultiCurrencyTransactions($order, $request->received_amounts);
                        } else {
                            $this->createTransactionForOrder($order);
                        }

                        // Auto-release dining tables if branch setting is enabled
                        $this->autoReleaseDiningTablesOnPayment($order);
                    }
                }
                $this->order = $order;
            });

            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function generateInvoiceNumber()
    {
        $today = Carbon::now()->format('Ymd');

        // Check both active orders and deleted orders for the highest invoice number
        $lastActiveInvoice = Order::whereDate('order_datetime', Carbon::today())
            ->whereNotNull('invoice_number')
            ->where('invoice_number', 'like', $today . '%')
            ->orderBy('invoice_number', 'desc')
            ->lockForUpdate()
            ->first();

        $lastDeletedInvoice = OrderDeleted::whereDate('order_datetime', Carbon::today())
            ->whereNotNull('invoice_number')
            ->where('invoice_number', 'like', $today . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $number = 1;
        $highestNumber = 0;

        // Get the highest number from active orders
        if ($lastActiveInvoice && $lastActiveInvoice->invoice_number) {
            $activeNumber = (int) substr($lastActiveInvoice->invoice_number, -3);
            $highestNumber = max($highestNumber, $activeNumber);
        }

        // Get the highest number from deleted orders
        if ($lastDeletedInvoice && $lastDeletedInvoice->invoice_number) {
            $deletedNumber = (int) substr($lastDeletedInvoice->invoice_number, -3);
            $highestNumber = max($highestNumber, $deletedNumber);
        }

        if ($highestNumber > 0) {
            $number = $highestNumber + 1;
        }

        // Generate invoice number and ensure it's globally unique
        $invoiceNumber = $today . str_pad($number, 3, '0', STR_PAD_LEFT);

        // Check if this invoice number already exists globally (in case of data inconsistencies)
        while (Order::where('invoice_number', $invoiceNumber)->exists() ||
               OrderDeleted::where('invoice_number', $invoiceNumber)->exists()) {
            $number++;
            $invoiceNumber = $today . str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        return $invoiceNumber;
    }

    /**
     * Create order from table order link, no need to pass member_id and no need to calculate points
     */
    public function tableOrderStore(TableOrderRequest $request): object
    {
        try {

            DB::transaction(function () use ($request) {
                $this->order = FrontendOrder::create(
                    $request->validated() + [
                        // 'user_id'          => $request->customer_id,
                        // 'order_user_id'    => Auth::user()->id,
                        'status'           => OrderStatus::PENDING,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('site')->get('site_food_preparation_time')
                    ]
                );

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'discount_percentage'  => (float)($item->discount_percentage ?? 0),
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                $this->order->order_note          = $request->order_note;
                $this->order->save();

                SendOrderGotMail::dispatch(['order_id' => $this->order->id]);
                SendOrderGotSms::dispatch(['order_id' => $this->order->id]);
                SendOrderGotPush::dispatch(['order_id' => $this->order->id]);

                // Send Telegram notification if order has Telegram info
                // Dispatch after transaction to prevent affecting order creation
                if ($this->order->telegram_user_id || $this->order->telegram_chat_id) {
                    try {
                        SendOrderTelegramNotification::dispatch($this->order, 'order_created');
                    } catch (\Exception $e) {
                        // Log error but don't fail order creation
                        Log::error("Failed to dispatch Telegram notification for table order creation", [
                            'order_id' => $this->order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Create order from online order link, no need to pass member_id and no need to calculate points
     */
    public function onlineOrderOrderStore(OnlineOrderOrderRequest $request): object
    {
        try {

            DB::transaction(function () use ($request) {
                $this->order = FrontendOrder::create(
                    $request->validated() + [
                        'user_id'          => $request->customer_id,
                        'status'           => OrderStatus::PENDING,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('site')->get('site_food_preparation_time')
                    ]
                );

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'discount_percentage'  => (float)($item->discount_percentage ?? 0),
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                $this->order->order_note          = $request->order_note;
                $this->order->save();

                SendOrderGotMail::dispatch(['order_id' => $this->order->id]);
                SendOrderGotSms::dispatch(['order_id' => $this->order->id]);
                SendOrderGotPush::dispatch(['order_id' => $this->order->id]);

                // Send Telegram notification if order has Telegram info
                // Dispatch after transaction to prevent affecting order creation
                if ($this->order->telegram_user_id || $this->order->telegram_chat_id) {
                    try {
                        SendOrderTelegramNotification::dispatch($this->order, 'order_created');
                    } catch (\Exception $e) {
                        // Log error but don't fail order creation
                        Log::error("Failed to dispatch Telegram notification for online order creation", [
                            'order_id' => $this->order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function telegramMiniAppOrderStore(TelegramMiniAppOrderRequest $request): object
    {
        Log::info('TelegramMiniAppOrderStore - Request Data:', $request->all());
        try {
            DB::transaction(function () use ($request) {
                $this->order = FrontendOrder::create(
                    $request->validated() + [
                        'user_id'          => $request->customer_id,
                        'status'           => OrderStatus::PENDING,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('site')->get('site_food_preparation_time'),
                        // Add Telegram-specific fields
                        'telegram_user_id' => $request->telegram_user_id,
                        'telegram_chat_id' => $request->telegram_chat_id,
                        'telegram_username' => $request->telegram_username,
                        // Add PayWay transaction fields
                        'payment_transaction_id' => $request->payment_transaction_id,
                        'payment_transaction_data' => $request->payment_transaction_data,
                        'currency' => $request->currency,
                    ]
                );

                Log::info('TelegramMiniApp Order Created:', [
                    'order_id' => $this->order->id,
                    'order_serial_no' => $this->order->order_serial_no,
                    'payment_transaction_id' => $this->order->payment_transaction_id,
                    'has_transaction_data' => !empty($this->order->payment_transaction_data),
                    'order_currency' => $this->order->currency,
                    'order_currency_id' => $this->order->currency_id,
                    'receive_payment_currency' => $this->order->receive_payment_currency,
                    'receive_payment_currency_id' => $this->order->receive_payment_currency_id,
                ]);

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'order_item_custom_name' => $item->order_item_custom_name ?? null,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'discount_percentage'  => (float)($item->discount_percentage ?? 0),
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                $this->order->order_note      = $request->order_note;
                $this->order->save();

                // Check if payment is made via integrated payment gateway (PayWay)
                if ($request->payment_method_id && $request->payment_transaction_id) {

                    // Get the payment method to check if it's integrated payment
                    $paymentMethod = PaymentMethod::find($request->payment_method_id);

                    if ($paymentMethod && $paymentMethod->is_pos_bank_integrate_payment == Status::ACTIVE) {
                        Log::info('Processing integrated payment for TelegramMiniApp order', [
                            'order_id' => $this->order->id,
                            'payment_method_id' => $paymentMethod->id,
                            'transaction_id' => $request->payment_transaction_id
                        ]);

                        // Get transaction data from PaywayTransaction table
                        try {
                            $paywayTransaction = \App\Models\PaywayTransaction::where('tran_id', $request->payment_transaction_id)->first();

                            if ($paywayTransaction) {
                                Log::info('Found PaywayTransaction record', [
                                    'tran_id' => $paywayTransaction->tran_id,
                                    'amount' => $paywayTransaction->amount,
                                    'currency' => $paywayTransaction->currency,
                                    'payment_amount' => $paywayTransaction->payment_amount,
                                    'payment_currency' => $paywayTransaction->payment_currency,
                                    'payment_status' => $paywayTransaction->payment_status
                                ]);

                                // Get the payment amount and currency from PaywayTransaction
                                // Use payment_amount (actual paid) if available, otherwise use amount (requested)
                                $transactionAmount = $paywayTransaction->payment_amount ?? $paywayTransaction->amount;
                                $transactionCurrency = $paywayTransaction->payment_currency ?? $paywayTransaction->currency;

                                // Get order currency
                                $orderCurrency = $this->order->currency ?? $this->order->branch->currency_id->code;

                                // Calculate full order amount (including tax)
                                $orderTotalWithTax = $this->order->total + $this->order->total_tax;

                                // Convert transaction amount to order currency if needed
                                $transactionAmountInOrderCurrency = $transactionAmount;
                                if ($transactionCurrency !== $orderCurrency) {
                                    // Get exchange rate
                                    $exchangeRate = \App\Models\ExchangeRate::where('base_currency', $transactionCurrency)
                                        ->where('target_currency', $orderCurrency)
                                        ->first();

                                    if ($exchangeRate && $exchangeRate->rate) {
                                        $transactionAmountInOrderCurrency = $transactionAmount * floatval($exchangeRate->rate);
                                        Log::info('Converting transaction amount to order currency', [
                                            'from_currency' => $transactionCurrency,
                                            'to_currency' => $orderCurrency,
                                            'original_amount' => $transactionAmount,
                                            'exchange_rate' => $exchangeRate->rate,
                                            'converted_amount' => $transactionAmountInOrderCurrency
                                        ]);
                                    } else {
                                        // Try reverse lookup
                                        $reverseRate = \App\Models\ExchangeRate::where('base_currency', $orderCurrency)
                                            ->where('target_currency', $transactionCurrency)
                                            ->first();

                                        if ($reverseRate && $reverseRate->rate && floatval($reverseRate->rate) > 0) {
                                            $transactionAmountInOrderCurrency = $transactionAmount / floatval($reverseRate->rate);
                                            Log::info('Converting transaction amount using reverse exchange rate', [
                                                'from_currency' => $transactionCurrency,
                                                'to_currency' => $orderCurrency,
                                                'original_amount' => $transactionAmount,
                                                'reverse_rate' => $reverseRate->rate,
                                                'converted_amount' => $transactionAmountInOrderCurrency
                                            ]);
                                        } else {
                                            Log::warning('No exchange rate found for currency conversion', [
                                                'from_currency' => $transactionCurrency,
                                                'to_currency' => $orderCurrency
                                            ]);

                                            // Fallback: Try getting exchange rate from Currency model
                                            $targetCurrency = \App\Models\Currency::where('code', $orderCurrency)->first();
                                            $sourceCurrency = \App\Models\Currency::where('code', $transactionCurrency)->first();

                                            if ($targetCurrency && $targetCurrency->exchange_rate && $sourceCurrency && $sourceCurrency->exchange_rate) {
                                                // Convert via base currency (usually USD)
                                                // transaction amount (in source) -> base -> target
                                                $transactionAmountInOrderCurrency = $transactionAmount / floatval($sourceCurrency->exchange_rate) * floatval($targetCurrency->exchange_rate);
                                                Log::info('Converting using Currency model exchange rates', [
                                                    'from_currency' => $transactionCurrency,
                                                    'to_currency' => $orderCurrency,
                                                    'original_amount' => $transactionAmount,
                                                    'source_rate' => $sourceCurrency->exchange_rate,
                                                    'target_rate' => $targetCurrency->exchange_rate,
                                                    'converted_amount' => $transactionAmountInOrderCurrency
                                                ]);
                                            }
                                        }
                                    }
                                }

                                Log::info('Transaction amount comparison', [
                                    'order_currency' => $orderCurrency,
                                    'order_total' => $this->order->total,
                                    'order_total_tax' => $this->order->total_tax,
                                    'order_total_with_tax' => $orderTotalWithTax,
                                    'transaction_currency' => $transactionCurrency,
                                    'transaction_amount' => $transactionAmount,
                                    'transaction_amount_in_order_currency' => $transactionAmountInOrderCurrency,
                                    'payment_status' => $paywayTransaction->payment_status
                                ]);

                                // Calculate tolerance based on the order total magnitude
                                // For large amounts (e.g., KHR), use 1% tolerance or 1 unit, whichever is larger
                                // For small amounts (e.g., USD), use 0.01
                                $tolerance = max(0.01, $orderTotalWithTax * 0.01, 1.0);

                                Log::info('Using tolerance for comparison', [
                                    'tolerance' => $tolerance,
                                    'difference' => abs($transactionAmountInOrderCurrency - $orderTotalWithTax)
                                ]);

                                // Compare transaction amount with order total INCLUDING TAX (with calculated tolerance)
                                if ($transactionAmountInOrderCurrency !== null && abs($transactionAmountInOrderCurrency - $orderTotalWithTax) < $tolerance) {
                                    // Amounts match - mark order as paid
                                    $this->order->payment_status = PaymentStatus::PAID;
                                    $this->order->save();

                                    Log::info('Order marked as PAID - transaction amount matches order total with tax', [
                                        'order_id' => $this->order->id,
                                        'order_serial_no' => $this->order->order_serial_no,
                                        'amount' => $orderTotalWithTax,
                                        'currency' => $orderCurrency,
                                        'tolerance_used' => $tolerance
                                    ]);
                                } else {
                                    Log::warning('Transaction amount mismatch for TelegramMiniApp order', [
                                        'order_id' => $this->order->id,
                                        'order_currency' => $orderCurrency,
                                        'order_total' => $this->order->total,
                                        'order_total_tax' => $this->order->total_tax,
                                        'order_total_with_tax' => $orderTotalWithTax,
                                        'transaction_currency' => $transactionCurrency,
                                        'transaction_amount' => $transactionAmount,
                                        'transaction_amount_in_order_currency' => $transactionAmountInOrderCurrency,
                                        'difference' => $transactionAmountInOrderCurrency ? abs($transactionAmountInOrderCurrency - $orderTotalWithTax) : 'null',
                                        'tolerance' => $tolerance
                                    ]);
                                }
                            } else {
                                Log::warning('PaywayTransaction not found', [
                                    'tran_id' => $request->payment_transaction_id,
                                    'order_id' => $this->order->id
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to retrieve PaywayTransaction for TelegramMiniApp order', [
                                'order_id' => $this->order->id,
                                'tran_id' => $request->payment_transaction_id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                // Create transaction record if payment is completed
                if ($this->order->payment_status === PaymentStatus::PAID) {
                    $this->createTransactionForFrontendOrder($this->order);
                    Log::info('Transaction created for paid TelegramMiniApp order #' . $this->order->order_serial_no);
                }

                // Handle member integration if customer_id is provided
                // if ($request->customer_id) {
                //     $this->handleMemberIntegration($this->order, $request->customer_id);
                // }

                SendOrderGotMail::dispatch(['order_id' => $this->order->id]);
                SendOrderGotSms::dispatch(['order_id' => $this->order->id]);
                SendOrderGotPush::dispatch(['order_id' => $this->order->id]);

                // Send Telegram notification if order has Telegram info
                // Dispatch after transaction to prevent affecting order creation
                if ($this->order->telegram_user_id || $this->order->telegram_chat_id) {
                    try {
                        SendOrderTelegramNotification::dispatch($this->order, 'order_created');
                    } catch (\Exception $e) {
                        // Log error but don't fail order creation
                        Log::error("Failed to dispatch Telegram notification for order creation", [
                            'order_id' => $this->order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Get one order details
     */
    public function show(Order $order, $auth = false): Order|array
    {
        try {
            $itemDeleted = OrderItemDeleted::where('order_id', $order->id)->get();
            if ($order->orderItems) {
                $orderItemsUnique = [];
                foreach ($order->orderItems as $item) {
                    if (!isset($orderItemsUnique[$item->item_id])) {
                        // Clone to avoid reference issues
                        $orderItemsUnique[$item->item_id] = clone $item;
                    } else {
                        $orderItemsUnique[$item->item_id]->quantity += $item->quantity;
                        $orderItemsUnique[$item->item_id]->tax_amount += $item->tax_amount;
                        $orderItemsUnique[$item->item_id]->total_price += $item->total_price;
                    }
                }
                $order->itemDeleted = $itemDeleted;
                $order->orderItemsUnique = collect(array_values($orderItemsUnique));
            }
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    return $order;
                } else {
                    return [];
                }
            } else {
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }



    public function showOrderDeleted(OrderDeleted $orderDeleted, $auth = false): OrderDeleted|array
    {
        try {
            return $orderDeleted;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }



    /**
     * Order details for user
     */
    public function orderDetails(User $user, Order $order): Order|array
    {
        try {
            if ($order->user_id == $user->id) {
                return $order;
            } else {
                return [];
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * Update order status REJECTED or CANCELED or ACCEPTED or COMPLETED
     */
    public function changeStatus(Order $order, $auth = false, ChangeOrderStatusRequest $request): Order|array
    {
        try {

            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    if ($request->reason) {
                        $order->reason = $request->reason;
                    }

                    if ($request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED) {
                        if ($order->transaction) {
                            app(PaymentService::class)->cashBack(
                                $order,
                                'credit',
                                rand(111111111111111, 99999999999999)
                            );
                        }
                    }

                    $order->status = $request->status;
                    $order->save();
                }
            } else {
                if ($request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED) {
                    $request->validate([
                        'reason' => 'required|max:700',
                    ]);

                    if ($request->reason) {
                        $order->reason = $request->reason;
                    }

                    /**
                     * Check if order payment status is paid,
                     * if so, trigger the cashback process.
                     */

                    if ($order->transaction) {
                        app(PaymentService::class)->cashBack(
                            $order,
                            'credit',
                            rand(111111111111111, 99999999999999)
                        );
                    }
                }
                 Log::info($request->status);

                $order->status = $request->status;
                $order->save();

                // Send Telegram notification based on new status
                if ($order->telegram_user_id || $order->telegram_chat_id) {
                    try {
                        $this->sendTelegramNotificationByStatus($order);
                    } catch (\Exception $e) {
                        // Log error but don't fail status change
                        Log::error("Failed to send Telegram notification for status change", [
                            'order_id' => $order->id,
                            'new_status' => $request->status,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update payment status of an order
     */
    public function changePaymentStatus(Order $order, $auth = false, PaymentStatusRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $previousPaymentStatus = $order->payment_status;
                    $order->payment_status = $request->payment_status;

                    $order->save();

                    // Log payment status change activity
                    $this->activityLogger->logPaymentActivity('status changed', $order, [
                        'previous_status' => $previousPaymentStatus,
                        'new_status' => $request->payment_status,
                        'changed_by' => 'customer',
                    ]);

                    // Handle point allocation when payment status changes to paid
                    if ($previousPaymentStatus != PaymentStatus::PAID && $request->payment_status == PaymentStatus::PAID) {
                        // Set check_out_time when order is paid
                        if (!$order->check_out_time) {
                            $order->check_out_time = now();
                            $order->save();
                        }

                        $this->handlePointAllocationOnPayment($order);
                        $this->handlePointRedemptionOnPayment($order);

                        // Create transaction record for the payment
                        $this->createTransactionForOrder($order);

                        // Auto-release dining tables if branch setting is enabled
                        $this->autoReleaseDiningTablesOnPayment($order);

                        // Log payment completion
                        $this->activityLogger->logPaymentActivity('completed', $order, [
                            'points_allocated' => $order->points_earned ?? 0,
                            'points_redeemed' => $order->points_redeemed ?? 0,
                        ]);
                    }
                    // Handle point reversal when payment status changes from paid to unpaid
                    elseif ($previousPaymentStatus == PaymentStatus::PAID && $request->payment_status != PaymentStatus::PAID) {
                        $this->handlePointReversalOnUnpaid($order);

                        // Log payment reversal
                        $this->activityLogger->logPaymentActivity('reversed', $order, [
                            'reason' => 'payment status changed to unpaid',
                        ]);
                    }

                    return $order;
                } else {
                    return [];
                }
            } else {
                $previousPaymentStatus = $order->payment_status;
                $order->payment_status = $request->payment_status;

                $order->save();

                // Log payment status change activity
                $this->activityLogger->logPaymentActivity('status changed', $order, [
                    'previous_status' => $previousPaymentStatus,
                    'new_status' => $request->payment_status,
                    'changed_by' => 'admin',
                ]);

                // Handle point allocation when payment status changes to paid
                if ($previousPaymentStatus != PaymentStatus::PAID && $request->payment_status == PaymentStatus::PAID) {
                    // Set check_out_time when order is paid
                    if (!$order->check_out_time) {
                        $order->check_out_time = now();
                        $order->save();
                    }

                    $this->handlePointAllocationOnPayment($order);
                    $this->handlePointRedemptionOnPayment($order);

                    // Create transaction record for the payment
                    $this->createTransactionForOrder($order);

                    // Auto-release dining tables if branch setting is enabled
                    $this->autoReleaseDiningTablesOnPayment($order);

                    // Log payment completion
                    $this->activityLogger->logPaymentActivity('completed', $order, [
                        'points_allocated' => $order->points_earned ?? 0,
                        'points_redeemed' => $order->points_redeemed ?? 0,
                    ]);
                }
                // Handle point reversal when payment status changes from paid to unpaid
                elseif ($previousPaymentStatus == PaymentStatus::PAID && $request->payment_status != PaymentStatus::PAID) {
                    $this->handlePointReversalOnUnpaid($order);

                    // Log payment reversal
                    $this->activityLogger->logPaymentActivity('reversed', $order, [
                        'reason' => 'payment status changed to unpaid',
                    ]);
                }

                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Change payment method of an order
     */
    public function changePaymentMethod(Order $order, $auth = false, PosPaymentMethodRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $order->pos_payment_method = $request->pos_payment_method;
                    $order->payment_method_id = $request->payment_method_id;
                    $order->save();
                    return $order;
                } else {
                    return [];
                }
            } else {
                $order->pos_payment_method = $request->pos_payment_method;
                $order->payment_method_id = $request->payment_method_id;
                $order->save();
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function addDiningTable(Order $order, Request $request): Order|array
    {
        try {
            if ($order) {
                foreach ($request->diningTable as $i) {
                    OrderDining::create([
                        'order_id' => $order->id,
                        'dining_table_id' => $i['id'],
                        'branch_id' => $order->branch_id
                    ]);
                    DiningTable::where('id', $i['id'])->update([
                        'current_order_id' => $order->id
                    ]);
                }
                $order->save();
            }
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Automatically release dining tables when payment is made (if branch setting is enabled)
     */
    private function autoReleaseDiningTablesOnPayment(Order $order): void
    {
        try {
            // Check if the branch has auto-release enabled
            $branch = $order->branch;
            if (!$branch || $branch->payment_auto_release_table != Status::ACTIVE) {
                return;
            }

            // Use the releaseDiningTable method to release all tables
            // Pass empty request to release all tables
            $emptyRequest = new Request();
            $this->releaseDiningTableByOrderId($order->id);

        } catch (Exception $e) {
            // Log error but don't fail the payment process
            Log::error("Failed to auto-release dining tables for order #{$order->order_serial_no}: " . $e->getMessage());
        }
    }

    /**
     * Remove current_order_id from dining table
     * If dining_table_id is provided in request, release that specific table
     * If no dining_table_id provided, release all tables associated with the order
     */
    /**
     * Release a specific dining table by setting current_order_id to null
     */
    public function releaseDiningTable(DiningTable $diningTable): DiningTable
    {
        try {
            $diningTable->current_order_id = null;
            $diningTable->save();

            Log::info("Released dining table #{$diningTable->id}");

            return $diningTable;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Release all dining tables associated with a given order ID
     */
    public function releaseDiningTableByOrderId($orderId): array
    {
        try {
            $diningTables = DiningTable::where('current_order_id', $orderId)->get();

            if ($diningTables->count() > 0) {
                foreach ($diningTables as $table) {
                    $table->current_order_id = null;
                    $table->save();
                }

                Log::info("Released all dining tables for order ID #{$orderId}");
            }

            return $diningTables->toArray();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Create token for order
     */
    public function tokenCreate(Order $order, $auth = false, TableOrderTokenRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $order->token = $request->token;
                    $order->save();
                    return $order;
                } else {
                    return [];
                }
            } else {
                $order->token = $request->token;
                $order->save();
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Remove current_order_id from dining table
     */
    public function remove_dining_table($param)
    {

        try {
            DiningTable::where('current_order_id', $param)->update([
                'current_order_id' => null
            ]);
            Log::info(['Dining table removed from order' => DiningTable::where('current_order_id', $param)->get()]);
            return response('', 202);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function discount($order, $request): Order
    {
        try {
            DB::transaction(function () use ($order, $request) {
                $order->discount = $request->discount;
                $order->discount_percentage = $request->discount_percentage;
                $order->total_tax = $order->total_tax * (100 - $request->discount_percentage) / 100;

                $order->total = $order->subtotal - $order->discount;
                $order->save();
            });
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Update order total price based on order items and discount
     */
    public function updateOrderTotalPrice(Order $order){
        $order = Order::find($order->id);

        $subtotal = 0;
        $total_tax = 0;
        $discount = 0;
        $total = 0;

        $subtotal = $order->orderItems()->sum('total_price');
        $total_tax = $order->orderItems()->sum('tax_amount');

        Log::info('Subtotal: ' . $subtotal);
        Log::info('Total Tax: ' . $total_tax);

        if($order->discount_percentage > 0){
            $discount = ($subtotal * $order->discount_percentage) / 100;
            $total_tax = $total_tax * (100 - $order->discount_percentage) / 100;
        }
        $total = $subtotal - $discount;

        $order->subtotal = $subtotal;
        $order->discount = $discount;
        $order->total_tax = $total_tax;  //Tax after discount
        $order->total = $total; // Total after discount

        $order->save();
    }

    /**
     * Reject an order and handle refunds if necessary
     *
     * @param Order $order
     * @param Request $request
     * @return Order
     * @throws Exception
     */
    public function rejectOrder(Order $order, Request $request): Order
    {
        // Check if order is already rejected
        if ($order->status === OrderStatus::REJECTED) {
            throw new Exception('Order is already rejected');
        }

        // Validate request data
        $request->validate([
            'rejection_reason' => 'nullable|string|max:255'
        ]);

        $rejectionReason = $request->rejection_reason ?? 'Order rejected by admin';

        Log::info("Rejecting order: " . $order->id, [
            'current_status' => $order->status,
            'payment_status' => $order->payment_status,
            'reason' => $rejectionReason
        ]);

        // Handle refund if order is paid and uses Huione payment
        if ($order->payment_status === PaymentStatus::PAID) {
            $needsRefund = false;
            $refundProcessed = false;

            // Check if the order used Huione payment method
            if ($order->posPaymentMethod || $order->paymentMethod) {


                try {
                    // Find the payment order
                    $paymentOrder = PaymentOrder::where([
                        'order_id' => $order->id,
                        'status' => PaymentStatus::PAID
                    ])->first();

                    Log::info("Found payment order for refund processing", [
                        'payment_order_id' => $paymentOrder->id,
                        'payment_gateway' => $paymentOrder->payment_gateway,
                        'amount' => $paymentOrder->amount,
                        'transaction_id' => $paymentOrder->transaction_id
                    ]);

                    if ($paymentOrder) {

                        Log::info("Order uses Huione payment method, processing refund");
                        $needsRefund = true;

                        if($paymentOrder->payment_gateway == OnlinePaymentGatewayEnum::HUIONE){

                            Log::info("Start Process Huione Refund");

                            // Process full refund
                            $refundResult = $this->huioneService->processRefund(
                                $paymentOrder,
                                $paymentOrder->amount,
                                $rejectionReason
                            );

                            Log::info("Huione Refund Result: ", $refundResult);

                            if ($refundResult['status']) {
                                $refundProcessed = true;
                                Log::info("Refund processed successfully for order: " . $order->id);
                            } else {
                                Log::error("Refund processing failed for order: " . $order->id);
                            }
                        }


                    } else {
                        Log::warning("No paid payment order found for Huione refund: " . $order->id);
                    }
                } catch (Exception $e) {
                    Log::error("Refund processing error for order {$order->id}: " . $e->getMessage());
                    // Continue with rejection even if refund fails
                }
            }

            // If payment method is not Huione, just log the information
            if (!$needsRefund) {
                Log::info("Order payment method does not require automatic refund", [
                    'order_id' => $order->id,
                    'pos_payment_method' => $order->posPaymentMethod?->name,
                    'payment_method' => $order->paymentMethod?->name
                ]);
            }
        }else{
            Log::info("No payment method, order might not paid");
        }

        // Update order status to rejected
        $order->update([
            'status' => OrderStatus::REJECTED,
            'rejection_reason' => $rejectionReason,
            'rejected_at' => now()
        ]);

        // Reload the order with relationships
        $order->load('paymentMethod', 'posPaymentMethod');

        // Send Telegram notification for order rejection
        if ($order->telegram_user_id || $order->telegram_chat_id) {
            try {
                $this->telegramNotificationService->sendOrderRejectedNotification($order);
            } catch (\Exception $e) {
                // Log error but don't fail order rejection
                Log::error("Failed to send Telegram notification for order rejection", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Order rejected successfully: " . $order->id);

        return $order;
    }

    /**
     * Count all online orders with pending status
     *
     * @return int
     */
    public function countPendingOnlineOrders(): int
    {
        return Order::where('status', OrderStatus::PENDING)
               ->where('order_type', OrderType::ONLINE_ORDER)
               ->where('payment_status', PaymentStatus::PAID)
               ->count();
    }

    /**
     * Count all online orders with pending status
     *
     * @return int
     */
    public function countPendingOrders(): int
    {
        return Order::where('status', OrderStatus::PENDING)
               ->count();
    }

    /**
     * Send Telegram notification based on order status
     *
     * @param Order $order
     * @return void
     */
    protected function sendTelegramNotificationByStatus(Order $order): void
    {
        try {
            if (!$order->telegram_user_id && !$order->telegram_chat_id) {
                return;
            }

            $this->telegramNotificationService->sendNotificationByStatus($order);
        } catch (Exception $e) {
            Log::error("Failed to send Telegram notification for order status change", [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get paginated orders for a specific Telegram user
     */
    public function getTelegramUserOrders($telegramUserId, PaginateRequest $request)
    {

        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('per_page', 10);
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';
            // $telegramUserId = $request->get('telegram_user_id');


            $order = Order::with('orderStatus','orderType','transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'member', 'user')
                ->where('telegram_user_id', $telegramUserId)
                ->where(function ($query) use ($requests) {
                    // $first_date = null;
                    // $last_date = null;

                    // if (!empty($requests['from_date']) && !empty($requests['to_date'])) {
                    //     $first_date = Carbon::createFromFormat('Y-m-d h:i:s A', $requests['from_date'])->toDateTimeString();
                    //     $last_date  = Carbon::createFromFormat('Y-m-d h:i:s A', $requests['to_date'])->toDateTimeString();
                    // }
                    // else {
                    //     $first_date = Carbon::today()->startOfDay()->toDateTimeString();
                    //     $last_date  = Carbon::today()->endOfDay()->toDateTimeString();
                    // }

                    // Log::info("Date range query", [
                    //     'first_date' => $first_date,
                    //     'last_date' => $last_date
                    // ]);

                    // if(!empty($first_date) && !empty($last_date)) {
                    //     $query->whereBetween('created_at', [$first_date, $last_date]);
                    // }

                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            if ($key === "status") {
                                $query->where($key, (int)$request);
                            } else if ($key === 'payment_method' && (int)$request < 0) {
                                $query->where('pos_payment_method', abs($request));
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }

                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('order_type', '!=', $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn, $orderType)
                ->$method($methodValue);

            Log::info($order);
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }


        // $query = Order::with('transaction', 'orderItems', 'orderDinings', 'paymentMethod', 'posPaymentMethod', 'user')
        //     ->where('telegram_user_id', $telegramUserId);

        // // Apply sorting
        // $sort = $params['sort'] ?? 'created_at';
        // $order = $params['order'] ?? 'desc';

        // // Validate sort column to prevent SQL injection
        // $allowedSortColumns = ['created_at', 'updated_at', 'order_datetime', 'total', 'status'];
        // if (!in_array($sort, $allowedSortColumns)) {
        //     $sort = 'created_at';
        // }

        // $query->orderBy($sort, $order);

        // // Apply pagination
        // $perPage = min($params['per_page'] ?? 10, 50); // Limit to 50 per page max

        // return $query->paginate($perPage);
    }

    /**
     * Combine multiple orders into one target order
     * Moves all order items and dining tables from source orders to target order
     *
     * @param array $sourceOrderIds Array of order IDs to combine from
     * @param int $targetOrderId Target order ID to combine into
     * @return Order The updated target order
     * @throws Exception
     */
    public function combineOrders(array $sourceOrderIds, int $targetOrderId): Order
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();

            // Load target order with relationships
            $targetOrder = Order::with(['orderItems', 'orderDinings'])->findOrFail($targetOrderId);

            // Get current authenticated user
            $currentUser = Auth::user();

            // Validate all source orders exist and are different from target
            $sourceOrders = Order::with(['orderItems', 'orderDinings'])
                ->whereIn('id', $sourceOrderIds)
                ->where('id', '!=', $targetOrderId)
                ->get();

            if ($sourceOrders->count() !== count($sourceOrderIds)) {
                throw new Exception('One or more source orders not found or invalid.', 422);
            }

            // Track combined order numbers for audit trail
            $combinedOrderNumbers = [];

            // Get the highest order_times from target order for proper sequencing
            $maxOrderTimes = $targetOrder->orderItems->max('order_times') ?? 0;
            $nextOrderTime = $maxOrderTimes + 1;

            foreach ($sourceOrders as $sourceOrder) {
                $combinedOrderNumbers[] = $sourceOrder->order_serial_no;

                // Move all order items from source to target
                foreach ($sourceOrder->orderItems as $orderItem) {
                    $orderItem->update([
                        'order_id' => $targetOrderId,
                        'move_from_order' => $sourceOrder->order_serial_no,
                        'move_by' => $userId,
                        'order_times' => $nextOrderTime, // Assign unique order_times
                        'editor_type' => get_class($currentUser),
                        'editor_id' => $userId,
                    ]);

                    // Increment order time for next item batch
                    $nextOrderTime++;
                }

                // Move dining tables from source to target
                OrderDining::where('order_id', $sourceOrder->id)->update([
                    'order_id' => $targetOrderId
                ]);

                // Update dining tables to point to target order
                DiningTable::where('current_order_id', $sourceOrder->id)->update([
                    'current_order_id' => $targetOrderId
                ]);

                // Recalculate target order totals
                $this->recalculateOrderTotals($targetOrder);

                // Log the combination activity
                $this->activityLogger->logOrderActivity(
                    "combined from order {$sourceOrder->order_serial_no}",
                    $targetOrder,
                    [
                        'source_order_id' => $sourceOrder->id,
                        'source_order_no' => $sourceOrder->order_serial_no,
                        'items_moved' => $sourceOrder->orderItems->count(),
                        'tables_moved' => $sourceOrder->orderDinings->count(),
                    ]
                );

                // Delete the source order (soft delete or hard delete based on your requirement)
                // We'll soft delete to maintain audit trail
                $sourceOrder->delete();
            }

            // Reload target order with updated items
            $targetOrder = $targetOrder->fresh(['orderItems', 'orderDinings', 'user', 'orderUser', 'branch']);

            // Log combined orders summary on target order
            $this->activityLogger->logOrderActivity(
                'order combined successfully',
                $targetOrder,
                [
                    'combined_from_orders' => $combinedOrderNumbers,
                    'total_items' => $targetOrder->orderItems->count(),
                    'total_tables' => $targetOrder->orderDinings->count(),
                ]
            );

            DB::commit();

            return $targetOrder;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Order combination failed: ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Recalculate order totals after combining orders
     * This follows the same calculation logic as updateOrderTotalPrice
     *
     * @param Order $order
     * @return void
     */
    private function recalculateOrderTotals(Order $order): void
    {
        $subtotal = 0;
        $totalTax = 0;

        // Sum totals from all order items
        // Note: total_price in OrderItem already includes quantity calculation
        $subtotal = $order->orderItems()->sum('total_price');
        $totalTax = $order->orderItems()->sum('tax_amount');

        // Apply existing discount percentage if any
        $discount = 0;
        if ($order->discount_percentage > 0) {
            $discount = ($subtotal * $order->discount_percentage) / 100;
            // Reduce tax by discount percentage (consistent with updateOrderTotalPrice)
            $totalTax = $totalTax * (100 - $order->discount_percentage) / 100;
        }

        // Calculate final total (subtotal - discount, tax is already calculated separately)
        $total = $subtotal - $discount + $order->delivery_charge;

        $order->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total_tax' => $totalTax,
            'total' => $total,
        ]);
    }

    public function transferOrderItems(int $sourceOrderId, int $targetOrderId, array $items): Order
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();

            // Load source and target orders with relationships
            $sourceOrder = Order::with(['orderItems'])->findOrFail($sourceOrderId);
            $targetOrder = Order::with(['orderItems'])->findOrFail($targetOrderId);

            // Get current authenticated user
            $currentUser = Auth::user();

            // Get the highest order_times from target order for proper sequencing
            $maxOrderTimes = $targetOrder->orderItems->max('order_times') ?? 0;
            $nextOrderTime = $maxOrderTimes + 1;

            // Track transferred items for audit trail
            $transferredItems = [];

            foreach ($items as $itemData) {
                $orderItemId = $itemData['orderItemId'];
                $quantityToTransfer = $itemData['quantity'];

                // Find the order item in source order
                $sourceOrderItem = $sourceOrder->orderItems->where('id', $orderItemId)->first();

                if (!$sourceOrderItem) {
                    throw new Exception("Order item #{$orderItemId} not found in source order.", 422);
                }

                // Validate quantity
                if ($quantityToTransfer > $sourceOrderItem->quantity) {
                    throw new Exception("Cannot transfer {$quantityToTransfer} of {$sourceOrderItem->item_name}. Only {$sourceOrderItem->quantity} available.", 422);
                }

                if ($quantityToTransfer === $sourceOrderItem->quantity) {
                    // Transfer entire item - move the record
                    $sourceOrderItem->update([
                        'order_id' => $targetOrderId,
                        'move_from_order' => $sourceOrder->order_serial_no,
                        'move_by' => $userId,
                        'order_times' => $nextOrderTime,
                        'editor_type' => get_class($currentUser),
                        'editor_id' => $userId,
                    ]);

                    $transferredItems[] = [
                        'item_name' => $sourceOrderItem->item_name,
                        'quantity' => $quantityToTransfer,
                        'action' => 'moved'
                    ];
                } else {
                    // Transfer partial quantity - split the item
                    $remainingQuantity = $sourceOrderItem->quantity - $quantityToTransfer;

                    // Calculate unit price and unit tax for proportional split
                    $unitPrice = $sourceOrderItem->total_price / $sourceOrderItem->quantity;
                    $unitTax = $sourceOrderItem->tax_amount / $sourceOrderItem->quantity;

                    // Calculate totals for transferred quantity
                    $transferredTotalPrice = $unitPrice * $quantityToTransfer;
                    $transferredTaxAmount = $unitTax * $quantityToTransfer;

                    // Calculate totals for remaining quantity
                    $remainingTotalPrice = $unitPrice * $remainingQuantity;
                    $remainingTaxAmount = $unitTax * $remainingQuantity;

                    // Update source order item with remaining quantity and recalculated totals
                    $sourceOrderItem->update([
                        'quantity' => $remainingQuantity,
                        'total_price' => $remainingTotalPrice,
                        'tax_amount' => $remainingTaxAmount,
                        'editor_type' => get_class($currentUser),
                        'editor_id' => $userId,
                    ]);

                    // Create new order item in target order with transferred quantity
                    $newOrderItem = $sourceOrderItem->replicate();
                    $newOrderItem->order_id = $targetOrderId;
                    $newOrderItem->quantity = $quantityToTransfer;
                    $newOrderItem->total_price = $transferredTotalPrice;
                    $newOrderItem->tax_amount = $transferredTaxAmount;
                    $newOrderItem->move_from_order = $sourceOrder->order_serial_no;
                    $newOrderItem->move_by = $userId;
                    $newOrderItem->order_times = $nextOrderTime;
                    $newOrderItem->creator_type = get_class($currentUser);
                    $newOrderItem->creator_id = $userId;
                    $newOrderItem->editor_type = get_class($currentUser);
                    $newOrderItem->editor_id = $userId;
                    $newOrderItem->save();

                    // item_variations is already copied via replicate() as it's a JSON field

                    $transferredItems[] = [
                        'item_name' => $sourceOrderItem->item_name,
                        'quantity' => $quantityToTransfer,
                        'action' => 'split'
                    ];
                }

                $nextOrderTime++;
            }

            // Recalculate both orders' totals
            $this->recalculateOrderTotals($sourceOrder);
            $this->recalculateOrderTotals($targetOrder);

            // Check if source order has no items left, if so, delete it
            $sourceOrder->refresh();
            if ($sourceOrder->orderItems->count() === 0) {
                // Release dining tables if any
                DiningTable::where('current_order_id', $sourceOrder->id)->update([
                    'current_order_id' => null
                ]);

                // Delete order_dinings records first to avoid foreign key constraint
                OrderDining::where('order_id', $sourceOrder->id)->delete();

                // Delete the order
                $sourceOrder->delete();

                $this->activityLogger->logOrderActivity(
                    "deleted after all items transferred to order {$targetOrder->order_serial_no}",
                    $sourceOrder,
                    [
                        'target_order_id' => $targetOrder->id,
                        'target_order_no' => $targetOrder->order_serial_no,
                    ]
                );
            } else {
                // Log the transfer activity for source order
                $this->activityLogger->logOrderActivity(
                    "items transferred to order {$targetOrder->order_serial_no}",
                    $sourceOrder,
                    [
                        'target_order_id' => $targetOrder->id,
                        'target_order_no' => $targetOrder->order_serial_no,
                        'items_transferred' => $transferredItems,
                    ]
                );
            }

            // Log the transfer activity for target order
            $this->activityLogger->logOrderActivity(
                "items received from order {$sourceOrder->order_serial_no}",
                $targetOrder,
                [
                    'source_order_id' => $sourceOrder->id,
                    'source_order_no' => $sourceOrder->order_serial_no,
                    'items_received' => $transferredItems,
                ]
            );

            DB::commit();

            // Reload target order with fresh data
            return Order::with([
                'orderStatus',
                'orderType',
                'orderItems',
                'orderItems.orderItem',
                'orderDinings',
                'orderDinings.diningTable',
                'member'
            ])->findOrFail($targetOrderId);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
