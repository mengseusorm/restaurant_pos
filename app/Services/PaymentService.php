<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Models\User;

class PaymentService
{
    public function payment($order, $gatewaySlug, $transactionNo)
    {
        $transaction = Transaction::where(['order_id' => $order->id])->first();
        if (!$transaction) {
            // Get branch and currency information
            $branch = $order->branch;
            $baseCurrency = $branch->currency ?? null;
            $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
            $baseCurrencyId = $baseCurrency?->id;
            $receivePaymentCurrency = $order->receive_payment_currency ?? $baseCurrencyCode;
            $receivePaymentCurrencyId = $order->receive_payment_currency_id ?? $baseCurrencyId;
            $orderTotal = $order->total;
            
            // Determine transaction amount (actual payment amount in payment currency)
            if ($order->pos_received_amount !== null) {
                // Use actual received amount for POS payments
                $transactionAmount = $order->pos_received_amount;
            } elseif ($receivePaymentCurrency !== $baseCurrencyCode) {
                // Convert order total to payment currency for gateway payments
                $exchangeRateLookup = \App\Models\ExchangeRate::where('base_currency', $baseCurrencyCode)
                    ->where('target_currency', $receivePaymentCurrency)
                    ->first();
                if ($exchangeRateLookup && $exchangeRateLookup->rate) {
                    $transactionAmount = $orderTotal * floatval($exchangeRateLookup->rate);
                } else {
                    $transactionAmount = $orderTotal;
                }
            } else {
                // Same currency - transaction amount equals order total
                $transactionAmount = $orderTotal;
            }
            
            // Calculate exchange rate
            $amountBaseCurrency = $orderTotal;
            $exchangeRate = null;
            $exchangeRateBase = null;
            $exchangeRateTarget = null;
            
            if ($receivePaymentCurrency !== $baseCurrencyCode && $transactionAmount > 0) {
                $exchangeRate = $orderTotal / $transactionAmount;
                $exchangeRateBase = $receivePaymentCurrency;
                $exchangeRateTarget = $baseCurrencyCode;
            }
            
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $transactionNo,
                'amount'         => $orderTotal,
                'currency'       => $baseCurrencyCode,
                'currency_id'    => $baseCurrencyId,
                'amount_base_currency' => $amountBaseCurrency,
                'base_currency' => $baseCurrencyCode,
                'base_currency_id' => $baseCurrency?->id,
                'transaction_amount' => $transactionAmount,
                'transaction_currency' => $receivePaymentCurrency,
                'transaction_currency_id' => $receivePaymentCurrencyId,
                'change_amount' => 0,
                'change_currency' => null,
                'change_currency_id' => null,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_base' => $exchangeRateBase,
                'exchange_rate_target' => $exchangeRateTarget,
                'payment_method' => $gatewaySlug,
                'sign'           => '+',
                'type'           => 'payment'
            ]);
        }
        $order->payment_status = PaymentStatus::PAID;
        $order->save();
        return $transaction;
    }

    public function cashBack($order, $gatewaySlug, $transactionNo)
    {
        $existingTransaction = Transaction::where(['order_id' => $order->id])->first();
        if ($existingTransaction) {
            // Get branch and currency information
            $branch = $order->branch;
            $baseCurrency = $branch->currency ?? null;
            $baseCurrencyCode = $baseCurrency?->code ?? 'USD';
            $baseCurrencyId = $baseCurrency?->id;
            $transactionCurrency = $existingTransaction->transaction_currency ?? $baseCurrencyCode;
            $transactionCurrencyId = $existingTransaction->transaction_currency_id ?? $baseCurrencyId;
            $orderTotal = $order->total;
            
            // Use the same transaction amount as the original transaction
            $transactionAmount = $existingTransaction->transaction_amount ?? $orderTotal;
            
            // Calculate exchange rate
            $amountBaseCurrency = $orderTotal;
            $exchangeRate = null;
            $exchangeRateBase = null;
            $exchangeRateTarget = null;
            
            if ($transactionCurrency !== $baseCurrencyCode && $transactionAmount > 0) {
                $exchangeRate = $orderTotal / $transactionAmount;
                $exchangeRateBase = $transactionCurrency;
                $exchangeRateTarget = $baseCurrencyCode;
            }
            
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $transactionNo,
                'amount'         => $orderTotal,
                'currency'       => $baseCurrencyCode,
                'currency_id'    => $baseCurrencyId,
                'amount_base_currency' => $amountBaseCurrency,
                'base_currency' => $baseCurrencyCode,
                'base_currency_id' => $baseCurrency?->id,
                'transaction_amount' => $transactionAmount,
                'transaction_currency' => $transactionCurrency,
                'transaction_currency_id' => $transactionCurrencyId,
                'change_amount' => 0,
                'change_currency' => null,
                'change_currency_id' => null,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_base' => $exchangeRateBase,
                'exchange_rate_target' => $exchangeRateTarget,
                'payment_method' => $gatewaySlug,
                'sign'           => '-',
                'type'           => 'cash_back'
            ]);

            $user = User::find($order->user_id);
            if ($user) {
                $user->balance = ($user->balance + $order->total);
                $user->save();
            }
        }

        return $transaction;
    }
}
