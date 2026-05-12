<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert PayWay payment gateway
        $gatewayId = DB::table('payment_gateways')->insertGetId([
            'name' => 'PayWay (ABA Bank KHQR)',
            'slug' => 'payway',
            'misc' => 'Cambodia KHQR payment gateway powered by ABA Bank',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert gateway options for PayWay
        $options = [
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'merchant_id',
                'value' => '', // To be configured by admin
                'type' => 1, // Text input
                'activities' => 'PayWay Merchant ID from ABA Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'api_key',
                'value' => '', // To be configured by admin
                'type' => 1, // Text input (should be password in admin panel)
                'activities' => 'PayWay API Key from ABA Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'mode',
                'value' => 'sandbox', // Default to sandbox
                'type' => 2, // Select dropdown
                'activities' => 'Payment mode: sandbox or production',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'callback_url',
                'value' => url('/api/payway/callback'),
                'type' => 1, // Text input
                'activities' => 'PayWay callback URL for payment notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'continue_success_url',
                'value' => url('/payment/success'),
                'type' => 1, // Text input
                'activities' => 'URL to redirect after successful payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'model_id' => $gatewayId,
                'model_type' => 'App\\Models\\PaymentGateway',
                'option' => 'return_url',
                'value' => url('/payment/return'),
                'type' => 1, // Text input
                'activities' => 'URL to return from payment page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('gateway_options')->insert($options);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete gateway options first (foreign key constraint)
        DB::table('gateway_options')
            ->where('model_type', 'App\\Models\\PaymentGateway')
            ->whereIn('model_id', function ($query) {
                $query->select('id')
                    ->from('payment_gateways')
                    ->where('slug', 'payway');
            })
            ->delete();

        // Delete PayWay gateway
        DB::table('payment_gateways')
            ->where('slug', 'payway')
            ->delete();
    }
};
